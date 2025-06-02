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
        Schema::table('draft_penjualan', function (Blueprint $table) {
            $table->boolean('perlu_pabrikasi')->default(false)->nullable()->after('id_karyawan');
        });

        Schema::create('po_jual_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_jual_id')->constrained('penjualan')->onDelete('cascade');
            $table->string('stage'); // e.g., Beli, Produksi, Packing, Kirim
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->enum('status', ['dijadwalkan', 'berlangsung', 'selesai', 'ditunda'])->default('dijadwalkan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('po_jual_schedules');
        
        Schema::table('draft_penjualan', function (Blueprint $table) {
            $table->dropColumn('perlu_pabrikasi');
        });
    }
};
