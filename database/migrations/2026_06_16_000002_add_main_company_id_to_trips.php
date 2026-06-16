<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->unsignedBigInteger('main_company_id')->nullable()->after('broker_id');
            $table->foreign('main_company_id')->references('id')->on('contacts')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign(['main_company_id']);
            $table->dropColumn('main_company_id');
        });
    }
};
?>
