<?php

$sqlFile = 'C:/Users/AHMED/Downloads/simple_accounting (1).sql';
$content = file_get_contents($sqlFile);

if (preg_match_all('/INSERT INTO `vehicles`[^;]+;/', $content, $matches)) {
    foreach ($matches[0] as $match) {
        echo $match . "\n\n";
    }
}
