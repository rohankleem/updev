<?php

// Load JShrink, no namespaces
require_once __DIR__ . '/JShrink/Minifier.php';


function obf_find_js_files($dir, $excludes = []) {
    $out = [];

    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($it as $file) {
        if ($file->isDir()) continue;

        $path = $file->getPathname();

        // Skip already-minified vendor scripts
        if (preg_match('/\.min\.js$/i', $path)) continue;

        if (pathContainsEx($path, $excludes, $dir)) continue;

        if (substr($path, -3) === '.js') {
            $out[] = $path;
        }
    }

    sort($out);
    return $out;
}


function obf_run_js_minify($inputDir, $excludes) {
    println("Minifying JS with JShrink…");

    $jsFiles = obf_find_js_files($inputDir, $excludes);
    println("JS files: " . count($jsFiles));

    $count = 0;

    foreach ($jsFiles as $file) {

        $original = file_get_contents($file);
        if ($original === false) continue;

        try {
            // ★ Use JShrink safely
            $minified = Minifier::minify($original, [
                'flaggedComments' => false  // we don't want /*! license */ blocks
            ]);
        } catch (Exception $e) {
            println("[JS MIN ERROR] $file : " . $e->getMessage());
            continue;
        }

        file_put_contents($file, $minified);
        verbose("[JS MIN] $file");

        $count++;
    }

    println("JShrink JS minify complete. Processed: $count");
}
