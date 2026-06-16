<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'asset', 'name' => 'الأصول', 'normal_balance' => 'debit'],
            ['code' => 'liability', 'name' => 'الخصوم', 'normal_balance' => 'credit'],
            ['code' => 'equity', 'name' => 'حقوق الملكية', 'normal_balance' => 'credit'],
            ['code' => 'revenue', 'name' => 'الإيرادات', 'normal_balance' => 'credit'],
            ['code' => 'expense', 'name' => 'المصروفات', 'normal_balance' => 'debit'],
        ];

        foreach ($types as $type) {
            AccountType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}