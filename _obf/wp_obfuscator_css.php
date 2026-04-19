<?php

// wp_obfuscator_css.inc.php — SAFE CSS MINIFIER (literal-safe)

function obf_find_css_files($dir, $excludes = []) {
    $out = [];
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($it as $file) {
        if ($file->isDir()) continue;

        $path = $file->getPathname();

        // Skip already-minified vendor assets
        if (preg_match('/\.min\.css$/i', $path)) continue;
        if (pathContainsEx($path, $excludes, $dir)) continue;

        if (substr($path, -4) === '.css') {
            $out[] = $path;
        }
    }

    sort($out);
    return $out;
}


/**
 * SAFE CSS MINIFIER
 *
 * Strategy:
 *  - Extract & protect literals:
 *      1. double-quoted string "..."
 *      2. single-quoted string '...'
 *      3. url(...) blocks
 *  - Replace with placeholders
 *  - Minify the remaining CSS safely
 *  - Restore literals
 */
function obf_minify_css($code) {

    // Remove CSS comments safely first
    $code = preg_replace('!/\*.*?\*/!s', '', $code);

    $literals = [];
    $placeholderIndex = 0;

    /**
     * STEP 1 — protect quoted strings "..." and '...'
     */
    $code = preg_replace_callback('/(["\'])(?:\\\\.|(?!\1).)*\1/sU', function ($m) use (&$literals, &$placeholderIndex) {
        $key = "___CSS_LITERAL_" . ($placeholderIndex++) . "___";
        $literals[$key] = $m[0];
        return $key;
    }, $code);

    /**
     * STEP 2 — protect url(...) including data: URLs
     */
    $code = preg_replace_callback('/url\(\s*[^)]*\s*\)/i', function ($m) use (&$literals, &$placeholderIndex) {
        $key = "___CSS_LITERAL_" . ($placeholderIndex++) . "___";
        $literals[$key] = $m[0];
        return $key;
    }, $code);

    /**
     * STEP 3 — safe whitespace minification
     */

    // Collapse multiple spaces/newlines to 1 space
    $code = preg_replace('/\s+/', ' ', $code);

    // Remove space around symbols
    $code = preg_replace('/\s*([{};:,>+~])\s*/', '$1', $code);

    // Fix spaces before important characters
    $code = preg_replace('/\s*\(\s*/', '(', $code);
    $code = preg_replace('/\s*\)\s*/', ')', $code);

    // Remove final unnecessary semicolons inside blocks
    $code = preg_replace('/;}/', '}', $code);

    // Trim global whitespace
    $code = trim($code);

    /**
     * STEP 4 — restore literals exactly as they were
     */
    foreach ($literals as $key => $value) {
        $code = str_replace($key, $value, $code);
    }

    return $code;
}


function obf_run_css_minify($inputDir, $excludes) {
    println("Minifying CSS safely…");

    $cssFiles = obf_find_css_files($inputDir, $excludes);
    println("CSS files: " . count($cssFiles));

    $count = 0;

    foreach ($cssFiles as $file) {
        $content = file_get_contents($file);
        if ($content === false) continue;

        $min = obf_minify_css($content);

        if (file_put_contents($file, $min) !== false) {
            $count++;
            verbose("[CSS MIN] $file");
        }
    }

    println("CSS minify complete. Processed: $count");
}

?>
