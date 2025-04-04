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
        Schema::table('po_jual_draft', function (Blueprint $table) {
            $table->boolean('is_amendment')->default(false);
        });               
    }
    
    public function down()
    {
        Schema::table('po_jual_draft', function (Blueprint $table) {
            $table->dropColumn('is_amendment');
        });
    }
    
};
