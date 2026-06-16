<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY type ENUM('sale', 'sale_return', 'purchase', 'purchase_return', 'sale_quotation', 'sale_order', 'purchase_quotation', 'purchase_order', 'work_order') NOT NULL");
        
        DB::statement("ALTER TABLE invoices MODIFY base_account_id BIGINT UNSIGNED NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY type ENUM('sale', 'sale_return', 'purchase', 'purchase_return') NOT NULL");
        DB::statement("ALTER TABLE invoices MODIFY base_account_id BIGINT UNSIGNED NOT NULL");
    }
};
