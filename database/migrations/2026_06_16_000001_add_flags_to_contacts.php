<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->boolean('is_main_company')->default(false)->after('is_supplier');
            $table->boolean('is_sub_client')->default(false)->after('is_main_company');
        });
    }

    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['is_main_company', 'is_sub_client']);
        });
    }
};
?>
