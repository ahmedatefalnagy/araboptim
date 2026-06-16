<?php

use Illuminate\Support\Facades\DB;

$columns = DB::select('SHOW COLUMNS FROM employees');
foreach ($columns as $column) {
    echo "Field: {$column->Field} | Type: {$column->Type} | Null: {$column->Null}\n";
}
