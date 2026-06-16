<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function printAccount($parent_id = null, $indent = "") {
    $accounts = App\Models\Account::where('parent_id', $parent_id)->orderBy('code')->get();
    foreach ($accounts as $account) {
        echo $indent . $account->code . " - " . $account->name . " (ID: " . $account->id . ")\n";
        printAccount($account->id, $indent . "  ");
    }
}

printAccount(null);
