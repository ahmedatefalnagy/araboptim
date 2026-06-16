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
        Schema::table('employees', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('nationality');
            $table->string('account_no')->nullable()->after('bank_name');
            $table->string('operation_card_no')->nullable()->after('iqama_no');
            $table->string('driver_card_no')->nullable()->after('operation_card_no');
            $table->string('transport_license_no')->nullable()->after('driver_card_no');
            $table->date('license_expiry')->nullable()->after('iqama_expiry');
            $table->date('authorization_expiry')->nullable()->after('license_expiry');
            $table->date('work_card_expiry')->nullable()->after('authorization_expiry');
            $table->date('driver_card_expiry')->nullable()->after('work_card_expiry');
            $table->date('transport_license_expiry')->nullable()->after('driver_card_expiry');
            $table->decimal('commission', 15, 2)->default(0)->after('basic_salary');
            $table->string('authorization_copy')->nullable()->after('document_file');
            $table->string('operation_card_copy')->nullable()->after('authorization_copy');
            $table->string('driver_card_copy')->nullable()->after('operation_card_copy');
            $table->string('combined_documents_pdf')->nullable()->after('driver_card_copy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'birth_date',
                'account_no',
                'operation_card_no',
                'driver_card_no',
                'transport_license_no',
                'license_expiry',
                'authorization_expiry',
                'work_card_expiry',
                'driver_card_expiry',
                'transport_license_expiry',
                'commission',
                'authorization_copy',
                'operation_card_copy',
                'driver_card_copy',
                'combined_documents_pdf',
            ]);
        });
    }
};
