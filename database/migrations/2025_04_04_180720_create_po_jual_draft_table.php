<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('po_jual_draft', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_po')->unique();
            $table->string('customer');
            $table->decimal('total_harga', 15, 2);
            $table->timestamps();
        });
    }    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('po_jual_draft');
    }
};
