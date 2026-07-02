<?php

function findSqlFiles($dir) {
    $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'sql') {
            // Avoid node_modules and vendor
            if (strpos($file->getPathname(), 'node_modules') !== false || strpos($file->getPathname(), 'vendor') !== false) {
                continue;
            }
            echo $file->getPathname() . " - Size: " . $file->getSize() . " bytes\n";
        }
    }
}

findSqlFiles('c:/xampp/htdocs');
