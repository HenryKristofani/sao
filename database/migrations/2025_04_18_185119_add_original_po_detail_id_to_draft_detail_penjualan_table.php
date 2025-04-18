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
            $table->unsignedBigInteger('original_po_detail_id')->nullable()->after('subtotal_harga');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('draft_detail_penjualan', function (Blueprint $table) {
            //
        });
    }
};
