<?php

/**
 * wp_obfuscator_inplace.php (WP-safe, normalized lookups)
 *
 * Usage:
 *  php wp_obfuscator_inplace.php <plugin_folder>
 *      [--hex-encode-strings] [--encode-strings]
 *      [--minify] [--strip-comments]
 *      [--rename-identifiers]
 *      [--dry-run]
 *      [--exclude=comma,separated]
 *      [--exclude-list=/path/to/file]
 *      [--salt=string]
 *      [--verbose]
 *      [--export-to=/path/to/output]
 *
 * Notes:
 * - Default mode: Creates backup <folder>_clean (or _clean1, _clean2…) alongside the folder,
 *   then obfuscates files in-place inside the original folder.
 * - Export mode (--export-to): Copies plugin to the given path, obfuscates the copy,
 *   and leaves the original completely untouched. No backup is created.
 */

if (php_sapi_name() !== 'cli') {
    echo "Run from CLI only.\n";
    exit(1);
}

$argv0 = array_shift($argv);
if (count($argv) < 1) {
    echo "Usage: php {$argv0} <plugin_folder> [--hex-encode-strings] [--minify] [--strip-comments] [--rename-identifiers] [--dry-run] [--exclude=csv] [--exclude-list=path] [--salt=string] [--verbose] [--export-to=path]\n";
    exit(1);
}

$inputDir = rtrim($argv[0], "/\\");
$options  = array_slice($argv, 1);

// ---- flags / options
$optHexStrings  = in_array('--hex-encode-strings', $options) || in_array('--encode-strings', $options);
$optRename      = in_array('--rename-identifiers', $options);
$optMinify      = in_array('--minify', $options);
$optStripCom    = in_array('--strip-comments', $options);
$optDryRun      = in_array('--dry-run', $options);
$optVerbose     = in_array('--verbose', $options);
$excludeArg     = null;
$excludeListArg = null;
$salt           = null;
$optExportTo    = null;

foreach ($options as $o) {
    if (strpos($o, '--exclude=') === 0)      $excludeArg     = substr($o, strlen('--exclude='));
    if (strpos($o, '--exclude-list=') === 0) $excludeListArg = substr($o, strlen('--exclude-list='));
    if (strpos($o, '--salt=') === 0)         $salt           = substr($o, strlen('--salt='));
    if (strpos($o, '--export-to=') === 0)    $optExportTo    = rtrim(substr($o, strlen('--export-to=')), "/\\");
}

function println($s = '')
{
    echo $s . PHP_EOL;
}
function verbose($s)
{
    global $optVerbose;
    if ($optVerbose) println($s);
}
function abort($s)
{
    echo "ERROR: $s\n";
    exit(1);
}

// ---- sanity
if (!is_dir($inputDir))    abort("Input folder not found: $inputDir");
if (!is_readable($inputDir)) abort("Input folder not readable: $inputDir");

$parent = dirname($inputDir);
$base   = basename($inputDir);

// ---- excludes (CLI list)
$excludes = [];
if ($excludeArg) {
    $excludes = array_map('trim', explode(',', $excludeArg));
    $excludes = array_values(array_filter($excludes, fn($v) => $v !== ''));
}

// ---- exclude-list discovery order
$excludeListPath = null;
if ($excludeListArg) {
    if (is_readable($excludeListArg)) $excludeListPath = $excludeListArg;
    else verbose("[WARN] --exclude-list provided but not readable: $excludeListArg");
}
if ($excludeListPath === null) {
    $candidate = __DIR__ . DIRECTORY_SEPARATOR . '_obf' . DIRECTORY_SEPARATOR . 'exclude-list.txt';
    if (is_readable($candidate)) $excludeListPath = $candidate;
}
if ($excludeListPath === null) {
    $candidate = __DIR__ . DIRECTORY_SEPARATOR . 'exclude-list.txt';
    if (is_readable($candidate)) $excludeListPath = $candidate;
}
if ($excludeListPath) {
    $lines = file($excludeListPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        $line = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $line);
        if (!in_array($line, $excludes, true)) $excludes[] = $line;
    }
    $excludes = array_values(array_unique($excludes));
    verbose("[INFO] Loaded exclude-list: $excludeListPath (" . count($excludes) . " items)");
}

// ---- helpers
function pathContainsEx($absPath, $excludes, $root = null)
{
    $p = $absPath;
    if ($root && str_starts_with($absPath, $root)) {
        $p = ltrim(substr($absPath, strlen($root)), DIRECTORY_SEPARATOR);
    }
    foreach ($excludes as $ex) {
        if ($ex === '') continue;
        if (stripos($p, $ex) !== false) return true;
        if (basename($p) === $ex) return true;
    }
    return false;
}

function removeDirRecursive($dir)
{
    if (!is_dir($dir)) return;
    $rii = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($rii as $item) {
        if ($item->isDir()) @rmdir($item->getPathname());
        else @unlink($item->getPathname());
    }
    @rmdir($dir);
}

function next_backup_name($parent, $base)
{
    $i = 0;
    while (true) {
        $candidate = $parent . DIRECTORY_SEPARATOR . $base . ($i === 0 ? '_clean' : '_clean' . $i);
        if (!file_exists($candidate)) return $candidate;
        $i++;
        if ($i > 1000) throw new Exception("Too many backups, aborting.");
    }
}

function makeFilteredIterator($dir, $excludes, $mode = RecursiveIteratorIterator::LEAVES_ONLY)
{
    $dirIter = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
    $filtered = new RecursiveCallbackFilterIterator($dirIter, function ($current, $key, $iterator) use ($excludes, $dir) {
        $p = $current->getPathname();
        if (pathContainsEx($p, $excludes, $dir)) {
            verbose("[SKIP] " . basename($p) . "/");
            return false;   // prune entire subtree
        }
        return true;
    });
    return new RecursiveIteratorIterator($filtered, $mode);
}

function copyDirRecursive($src, $dst, $excludes = [], $dryRun = false)
{
    $rii = makeFilteredIterator($src, $excludes, RecursiveIteratorIterator::SELF_FIRST);
    $copied = 0;
    foreach ($rii as $item) {
        $srcPath = $item->getPathname();
        $rel    = substr($srcPath, strlen($src));
        $target = $dst . $rel;
        if ($item->isDir()) {
            if (!$dryRun && !is_dir($target)) @mkdir($target, 0775, true);
        } else {
            $tdir = dirname($target);
            if (!$dryRun && !is_dir($tdir)) @mkdir($tdir, 0775, true);
            if (!$dryRun) copy($srcPath, $target);
        }
        $copied++;
    }
    return $copied;
}

function findPhpFiles($dir, $excludes = [])
{
    $out = [];
    $rii = makeFilteredIterator($dir, $excludes);
    foreach ($rii as $f) {
        if ($f->isDir()) continue;
        $p = $f->getPathname();
        if (substr($p, -4) === '.php') $out[] = $p;
    }
    sort($out);
    return $out;
}

// ---- WordPress-aware protections
$wpCoreFunctions = [
    'add_action',
    'add_filter',
    'remove_action',
    'remove_filter',
    'do_action',
    'apply_filters',
    'get_option',
    'update_option',
    'add_option',
    'register_activation_hook',
    'register_deactivation_hook',
    'register_rest_route',
    'wp_remote_post',
    'wp_remote_get',
    'add_shortcode',
    'register_post_type'
];

$phpSuperglobals = ['$_GET', '$_POST', '$_REQUEST', '$_COOKIE', '$_SERVER', '$_FILES', '$_ENV', '$_SESSION', '$GLOBALS', '$wpdb', '$post', '$product', '$woocommerce', '$this'];

$gettextFns = [
    '__',
    '_e',
    '_x',
    '_n',
    '_nx',
    'esc_html__',
    'esc_attr__',
    'esc_html_e',
    'esc_attr_e',
    'esc_html_x',
    'esc_attr_x'
];

// -- detect if current string token is an arg of one of $fnNames
function isArgOfFunction($tokens, $iStringTok, $fnNames)
{
    $i = $iStringTok - 1;
    $sawParen = false;
    while ($i >= 0) {
        $t = $tokens[$i];
        if (is_array($t) && $t[0] === T_WHITESPACE) {
            $i--;
            continue;
        }
        if ($t === '(') {
            $sawParen = true;
            $i--;
            continue;
        }
        if ($sawParen && is_array($t) && $t[0] === T_STRING) {
            $name = strtolower($t[1]);
            return in_array($name, array_map('strtolower', $fnNames), true);
        }
        if ($t === ';' || $t === '{' || $t === '}') break;
        $i--;
    }
    return false;
}

function looksLikeSQL($s)
{
    return (bool) preg_match('/\b(SELECT|INSERT|UPDATE|DELETE|FROM|WHERE|JOIN|INNER|LEFT|RIGHT|VALUES|SET|LIMIT|ORDER\s+BY|GROUP\s+BY)\b/i', $s);
}

function looksLikePathOrUrl($s)
{
    if (str_contains($s, '://')) return true;
    if (str_contains($s, '/') || str_contains($s, '\\')) return true;
    $lower = strtolower($s);
    foreach (['.php', '.js', '.css', '.map', '.json', '.png', '.jpg', '.jpeg', '.gif', '.svg'] as $ext) {
        if (str_ends_with($lower, $ext)) return true;
    }
    return false;
}

// hex-encode into a double-quoted PHP string of \xNN bytes
function phpHexEncodeString($s)
{
    $bytes = str_split($s);
    $out = '"';
    foreach ($bytes as $ch) {
        $out .= '\\x' . strtoupper(bin2hex($ch));
    }
    $out .= '"';
    return $out;
}

/**
 * NEW: normalize_for_lookup
 * Only for building/checking skip lists. Does NOT alter runtime values.
 */
function normalize_for_lookup(string $s): string
{
    $map_from = [
        "\xE2\x80\x99", // ’
        "\xE2\x80\x98", // ‘
        "\xE2\x80\x9C", // “
        "\xE2\x80\x9D", // ”
        "\xE2\x80\x94", // —
        "\xE2\x80\x93", // –
        "\xC2\xA0",     // NBSP
    ];
    $map_to = ["'", "'", '"', '"', '-', '-', ' '];
    return str_replace($map_from, $map_to, $s);
}

// ---- start
println("=== WP In-place Obfuscator (WP-safe) ===");
println("Input plugin folder: $inputDir");
println("Mode: " . ($optExportTo ? "EXPORT to $optExportTo" : "IN-PLACE (backup + obfuscate)"));
println("Options: hex_strings=" . ($optHexStrings ? '1' : '0') . " minify=" . ($optMinify ? '1' : '0') . " strip_comments=" . ($optStripCom ? '1' : '0') . " rename=" . ($optRename ? '1' : '0') . " dry_run=" . ($optDryRun ? '1' : '0'));
if (!empty($excludes)) verbose("Excludes: " . implode(', ', $excludes));

// ---- source dir for first-pass analysis (always the original)
$sourceDir = $inputDir;

// ---- dry run
if ($optDryRun) {
    $phpFiles = findPhpFiles($inputDir, $excludes);
    println("[DRY RUN] No files will be changed.");
    println("PHP files found (process candidates): " . count($phpFiles));
    if ($optExportTo) {
        println("Would copy all files: $inputDir -> $optExportTo");
        println("Would obfuscate PHP files inside export: $optExportTo");
    } else {
        $backup = next_backup_name($parent, $base);
        println("Would back up (excluding: " . (empty($excludes) ? '(none)' : implode(',', $excludes)) . "): $inputDir -> $backup");
        println("Would obfuscate PHP files in-place inside: $inputDir");
        println("Restore with: bash obf.sh restore");
    }
    if ($optRename) println("[DRY RUN] rename-identifiers is ON (risky) - mapping will be written");
    exit(0);
}

// ---- export mode vs in-place mode
if ($optExportTo) {
    // Export mode: copy to export path, obfuscate there, leave original untouched
    if (is_dir($optExportTo)) {
        println("Cleaning existing export folder: $optExportTo");
        removeDirRecursive($optExportTo);
    }
    @mkdir($optExportTo, 0775, true);
    if (!is_dir($optExportTo)) abort("Could not create export folder: $optExportTo");

    println("Copying plugin to export folder: $optExportTo");
    // Copy everything except .claude/ (dev tooling). unipixel.php and assets/
    // must be in the export for a complete plugin — they just won't be obfuscated.
    $exportExcludes = array_filter($excludes, function($e) { return $e === '.claude' || $e === 'CLAUDE.md'; });
    $copied = copyDirRecursive($inputDir, $optExportTo, $exportExcludes, false);
    if ($copied <= 0) abort("Export copy failed or no files copied.");
    println("Export copy complete (approx items copied): $copied");

    // Redirect obfuscation target to the export copy
    $inputDir = $optExportTo;
} else {
    // In-place mode: copy processable files to backup, then obfuscate the original
    // Original folder stays put — no rename, no Windows lock issues
    $backup = next_backup_name($parent, $base);
    println("Creating clean backup: $backup");

    @mkdir($backup, 0775, true);
    if (!is_dir($backup)) abort("Could not create backup folder: $backup");

    $copied = copyDirRecursive($inputDir, $backup, $excludes, false);
    if ($copied <= 0) abort("Backup copy failed or no files copied.");
    println("Backup complete (items): $copied");
    // Excluded items (.claude/, assets/, unipixel.php) are never modified — they stay in $inputDir untouched
    // Obfuscation runs in-place on $inputDir below
}

// ---- scan files
$phpFiles = findPhpFiles($inputDir, $excludes);
println("PHP files to process: " . count($phpFiles));

// ---- first pass: gather protected strings / symbol defs (WITH NORMALIZATION)
$protectedStrings  = []; // keys are normalized
$definedFunctions  = [];
$definedClasses    = [];
$definedVariables  = [];

foreach ($phpFiles as $file) {
    $code   = file_get_contents($file);
    if ($code === false) continue;
    $tokens = token_get_all($code);

    for ($i = 0; $i < count($tokens); $i++) {
        $t = $tokens[$i];
        if (!is_array($t)) continue;

        $tid = $t[0];
        $txt = $t[1];

        if ($tid === T_STRING) {
            $lname = strtolower($txt);

            if (in_array($lname, ['add_action', 'add_filter', 'remove_action', 'remove_filter', 'do_action', 'apply_filters'])) {
                $j = $i + 1;
                while ($j < count($tokens) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
                if ($j < count($tokens) && $tokens[$j] === '(') {
                    $k = $j + 1;
                    while ($k < count($tokens)) {
                        $tk = $tokens[$k];
                        if (is_array($tk) && $tk[0] === T_CONSTANT_ENCAPSED_STRING) {
                            $s = substr($tk[1], 1, -1);
                            $protectedStrings[normalize_for_lookup($s)] = true; // normalize here
                            break;
                        } elseif ($tk === ')') break;
                        $k++;
                    }
                }
            } elseif (in_array($lname, ['get_option', 'update_option', 'add_option'])) {
                $j = $i + 1;
                while ($j < count($tokens) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
                if ($j < count($tokens) && $tokens[$j] === '(') {
                    $k = $j + 1;
                    while ($k < count($tokens)) {
                        $tk = $tokens[$k];
                        if (is_array($tk) && $tk[0] === T_CONSTANT_ENCAPSED_STRING) {
                            $s = substr($tk[1], 1, -1);
                            $protectedStrings[normalize_for_lookup($s)] = true; // normalize here
                            break;
                        } elseif ($tk === ')') break;
                        $k++;
                    }
                }
            } elseif ($lname === 'register_rest_route') {
                $j = $i + 1;
                while ($j < count($tokens) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
                if ($j < count($tokens) && $tokens[$j] === '(') {
                    $k     = $j + 1;
                    $found = 0;
                    while ($k < count($tokens) && $found < 2) {
                        $tk = $tokens[$k];
                        if (is_array($tk) && $tk[0] === T_CONSTANT_ENCAPSED_STRING) {
                            $found++;
                            $s = substr($tk[1], 1, -1);
                            if ($found === 2) $protectedStrings[normalize_for_lookup($s)] = true; // normalize here
                        }
                        $k++;
                    }
                }
            } elseif (in_array($lname, $gettextFns)) {
                $j = $i + 1;
                while ($j < count($tokens) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
                if ($j < count($tokens) && $tokens[$j] === '(') {
                    $k = $j + 1;
                    while ($k < count($tokens)) {
                        $tk = $tokens[$k];
                        if (is_array($tk) && $tk[0] === T_CONSTANT_ENCAPSED_STRING) {
                            $s = substr($tk[1], 1, -1);
                            $protectedStrings[normalize_for_lookup($s)] = true; // normalize here
                            break;
                        } elseif ($tk === ')') break;
                        $k++;
                    }
                }
            }
        }

        if ($tid === T_CONSTANT_ENCAPSED_STRING) {
            $val = substr($txt, 1, -1);
            if (str_starts_with($val, 'wp_ajax_') || str_starts_with($val, 'wp_ajax_nopriv_') || str_contains($val, 'admin-ajax.php')) {
                $protectedStrings[normalize_for_lookup($val)] = true; // normalize here
            }
        }

        if ($tid === T_FUNCTION) {
            $j = $i + 1;
            while ($j < count($tokens) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
            if ($j < count($tokens) && is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) $definedFunctions[] = $tokens[$j][1];
        } elseif ($tid === T_CLASS) {
            $j = $i + 1;
            while ($j < count($tokens) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
            if ($j < count($tokens) && is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) $definedClasses[] = $tokens[$j][1];
        } elseif ($tid === T_VARIABLE) {
            $j = $i + 1;
            while ($j < count($tokens) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
            if ($j < count($tokens) && $tokens[$j] === '=') $definedVariables[] = $txt;
            else {
                if ($i > 0 && is_array($tokens[$i - 1]) && $tokens[$i - 1][0] === T_GLOBAL) $definedVariables[] = $txt;
            }
        }
    }
}

$definedFunctions = array_values(array_unique($definedFunctions));
$definedClasses   = array_values(array_unique($definedClasses));
$definedVariables = array_values(array_unique($definedVariables));

println("Protected literal strings count: " . count($protectedStrings));
println("Detected function defs: " . count($definedFunctions) . ", classes: " . count($definedClasses) . ", variables: " . count($definedVariables));

// ---- rename maps (off by default)
$functionMap = $classMap = $varMap = [];
if ($optRename) {
    $cntF = $cntC = $cntV = 0;
    foreach ($definedFunctions as $fn) {
        $low = strtolower($fn);
        if (in_array($low, $wpCoreFunctions)) continue;
        if (isset($protectedStrings[normalize_for_lookup($fn)])) continue;
        if (str_starts_with($fn, 'wp_') || str_starts_with($fn, 'wc_') || str_starts_with($fn, 'woocommerce_')) continue;
        $cntF++;
        $functionMap[$fn] = '_' . substr(hash('sha256', "fn{$cntF}" . ($salt ?? '')), 0, 10);
    }
    foreach ($definedClasses as $c) {
        if (isset($protectedStrings[normalize_for_lookup($c)])) continue;
        if (str_starts_with($c, 'WP_') || str_starts_with($c, 'WC_')) continue;
        $cntC++;
        $classMap[$c] = '_' . substr(hash('sha256', "cls{$cntC}" . ($salt ?? '')), 0, 10);
    }
    foreach ($definedVariables as $v) {
        if (in_array($v, $phpSuperglobals)) continue;
        if ($v === '$this') continue;
        $cntV++;
        $varMap[$v] = '$' . '_' . substr(hash('sha256', "v{$cntV}" . ($salt ?? '')), 0, 10);
    }
}
println("Planned renames: functions=" . count($functionMap) . " classes=" . count($classMap) . " vars=" . count($varMap));

// ---- transform files (in-place)
$processed = 0;
foreach ($phpFiles as $file) {
    $code = file_get_contents($file);
    if ($code === false) continue;
    $tokens = token_get_all($code);
    $out = '';

    for ($i = 0; $i < count($tokens); $i++) {
        $t = $tokens[$i];

        if (is_array($t)) {
            $tid = $t[0];
            $txt = $t[1];

            if ($optStripCom && ($tid === T_COMMENT || $tid === T_DOC_COMMENT)) continue;

            if ($optMinify && $tid === T_WHITESPACE) {
                $out .= ' ';
                continue;
            }

            if ($optHexStrings && $tid === T_CONSTANT_ENCAPSED_STRING) {
                // 0) Array key: look ahead for =>
                $isArrayKey = false;
                $j = $i + 1;
                while ($j < count($tokens) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
                if ($j < count($tokens) && is_array($tokens[$j]) && $tokens[$j][0] === T_DOUBLE_ARROW) $isArrayKey = true;

                // 1) Default param: look behind for '='
                $isDefaultParam = false;
                $k = $i - 1;
                while ($k >= 0 && is_array($tokens[$k]) && $tokens[$k][0] === T_WHITESPACE) $k--;
                if ($k >= 0 && $tokens[$k] === '=') $isDefaultParam = true;

                // 2) gettext arg?
                $isGettextArg = isArgOfFunction($tokens, $i, $gettextFns);

                // 3) raw and normalized
                $val  = substr($txt, 1, -1);
                $norm = normalize_for_lookup($val);

                // 4) skip rules
                $shouldSkip =
                    $isArrayKey ||
                    $isDefaultParam ||
                    $isGettextArg ||
                    isset($protectedStrings[$norm]) ||  // normalized lookup
                    looksLikePathOrUrl($val) ||
                    looksLikeSQL($val) ||
                    str_contains($val, '::') || str_contains($val, '->') ||
                    str_starts_with($val, 'a:'); // serialized

                if ($shouldSkip) {
                    $out .= $txt;
                } else {
                    $out .= phpHexEncodeString($val); // encode actual value; runtime unchanged
                }
                continue;
            }

            if ($optRename) {
                if ($tid === T_STRING && isset($functionMap[$txt])) {
                    $out .= $functionMap[$txt];
                    continue;
                }
                if ($tid === T_STRING && isset($classMap[$txt])) {
                    $out .= $classMap[$txt];
                    continue;
                }
                if ($tid === T_VARIABLE && isset($varMap[$txt])) {
                    $out .= $varMap[$txt];
                    continue;
                }
            }

            $out .= $txt;
        } else {
            $out .= $t;
        }
    }

    if ($optMinify) $out = preg_replace("/\n{3,}/", "\n\n", $out);

    if (file_put_contents($file, $out) === false) {
        println("Warning: failed to write file: $file");
    } else {
        $processed++;
        verbose("Obfuscated: $file");
    }
}

println("PHP obfuscation complete. Files processed: $processed");


require 'wp_obfuscator_js.php';
require 'wp_obfuscator_css.php';

obf_run_js_minify($inputDir, $excludes);
obf_run_css_minify($inputDir, $excludes);


if ($optExportTo) {
    println("Obfuscated export is at: $optExportTo");
    println("Original source is untouched at: $sourceDir");
} else {
    println("Clean backup is at: $backup");
    println("To restore: bash obf.sh restore");
}
println("Remember to flush OPcache / restart PHP-FPM if needed.");

exit(0);
