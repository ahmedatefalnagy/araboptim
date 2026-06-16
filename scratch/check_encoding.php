<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$a = App\Models\Account::find(1);
if ($a) {
    echo "Raw Name: " . $a->name . "\n";
    echo "Is literal question marks: " . (strpos($a->name, '?') !== false ? 'Yes' : 'No') . "\n";
    echo "ASCII codes of name characters: ";
    for ($i = 0; $i < strlen($a->name); $i++) {
        echo ord($a->name[$i]) . " ";
    }
    echo "\n";
} else {
    echo "Account not found\n";
}
