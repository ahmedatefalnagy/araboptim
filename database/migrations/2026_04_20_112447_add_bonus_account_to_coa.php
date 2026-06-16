<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $parent = \App\Models\Account::where('code', '5210')->first();
        if ($parent) {
             \App\Models\Account::firstOrCreate(
                ['code' => '5215'],
                [
                    'name' => 'مكافآت وجوائز الموظفين',
                    'account_type_id' => $parent->account_type_id,
                    'parent_id' => $parent->id,
                    'is_postable' => true,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\Account::where('code', '5215')->delete();
    }
};
