<?php

namespace Database\Seeders;

use App\Models\FiscalYear;
use Illuminate\Database\Seeder;

class FiscalYearSeeder extends Seeder
{
    public function run(): void
    {
        FiscalYear::updateOrCreate(
            ['name' => '2026'],
            [
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_closed' => false,
            ]
        );
    }
}