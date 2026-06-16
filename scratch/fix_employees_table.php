<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('employees', function (Blueprint $table) {
    // Make restrictive enums and required fields nullable or string
    $table->string('nationality')->nullable()->change();
    $table->string('job_title')->nullable()->change();
    $table->date('hire_date')->nullable()->change();
    $table->decimal('basic_salary', 15, 2)->default(0)->nullable()->change();
    $table->decimal('housing_allowance', 15, 2)->default(0)->nullable()->change();
    $table->decimal('transport_allowance', 15, 2)->default(0)->nullable()->change();
    $table->decimal('other_allowances', 15, 2)->default(0)->nullable()->change();
    $table->string('status')->default('active')->nullable()->change();
});

echo "Employee table schema updated to allow any nationality and optional fields.";
