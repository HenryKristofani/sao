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
        Schema::table('draft_detail_penjualan', function (Blueprint $table) {
            $table->boolean('perlu_pabrikasi')->default(false)->after('subtotal_harga');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('draft_detail_penjualan', function (Blueprint $table) {
            $table->dropColumn('perlu_pabrikasi');
        });
    }
};
