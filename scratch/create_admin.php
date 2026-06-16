<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

\App\Models\User::updateOrCreate(
    ['email' => 'admin@admin.com'],
    [
        'name' => 'Admin',
        'password' => \Illuminate\Support\Facades\Hash::make('password')
    ]
);
echo "Admin user created successfully!\n";
