<?php

$dir = 'C:/Users/AHMED/Downloads';
$files = scandir($dir);

foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
        $path = "$dir/$file";
        $content = file_get_contents($path);
        
        // Let's check if the file contains vehicle/vehicles table insert or create
        if (preg_match_all('/INSERT INTO `vehicles`[^;]+;/', $content, $matches)) {
            echo "File: $file - Found vehicles INSERT!\n";
            // Count total rows in the insert
            $totalRows = 0;
            foreach ($matches[0] as $match) {
                // Count occurrences of values pattern
                // A row typically looks like: (val1, val2, ...), or similar
                // Let's count the number of opening parentheses not inside quotes, or simple count
                $rowCount = preg_match_all('/\([^)]+\)/', $match, $dummy);
                $totalRows += $rowCount;
            }
            echo "Estimated vehicle rows: $totalRows\n";
            
            // Print a sample
            echo "Sample: " . substr($matches[0][0], 0, 500) . "\n\n";
        }
    }
}
