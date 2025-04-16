<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoPoJualToDetailPenjualanTable extends Migration
{
    public function up()
    {
        Schema::table('detail_penjualan', function (Blueprint $table) {
            $table->string('no_po_jual', 255)->nullable()->after('id_penjualan');
        });
    }

    public function down()
    {
        Schema::table('detail_penjualan', function (Blueprint $table) {
            $table->dropColumn('no_po_jual');
        });
    }
}