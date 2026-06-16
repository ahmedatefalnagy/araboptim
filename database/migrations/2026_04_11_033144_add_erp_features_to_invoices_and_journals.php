<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Workflow fields
            $table->foreignId('parent_document_id')->nullable()->constrained('invoices')->onDelete('set null')->comment('Reference to origin document for conversion');
            
            // Cost Centers
            $table->foreignId('cost_center_id')->nullable()->constrained()->onDelete('set null');

            // ZATCA Phase 2 E-Invoicing cryptographic fields
            $table->uuid('xml_uuid')->nullable()->unique()->comment('ZATCA specific Universally Unique Identifier');
            $table->string('zatca_hash')->nullable()->comment('SHA256 Hash of the XML Base64');
            $table->string('previous_hash')->nullable()->comment('PIH: Previous Invoice Hash');
            $table->text('qr_code_base64')->nullable()->comment('TLV Base64 QR Code string');
            $table->longText('xml_content')->nullable()->comment('Generated UBL 2.1 XML');
            $table->enum('zatca_status', ['pending', 'reported', 'cleared', 'rejected'])->nullable()->default('pending');
        });

        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->foreignId('cost_center_id')->nullable()->constrained()->onDelete('set null');
        });

        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->foreignId('cost_center_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['parent_document_id']);
            $table->dropForeign(['cost_center_id']);
            $table->dropColumn(['parent_document_id', 'cost_center_id', 'xml_uuid', 'zatca_hash', 'previous_hash', 'qr_code_base64', 'xml_content', 'zatca_status']);
        });

        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->dropForeign(['cost_center_id']);
            $table->dropColumn('cost_center_id');
        });

        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropForeign(['cost_center_id']);
            $table->dropColumn('cost_center_id');
        });
    }
};
