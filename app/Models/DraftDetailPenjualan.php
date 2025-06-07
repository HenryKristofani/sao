<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftDetailPenjualan extends Model
{
    protected $table = 'draft_detail_penjualan';
    protected $primaryKey = 'id_detail_penjualan';
    public $timestamps = false;
    
    protected $fillable = [
        'id_penjualan',
        'id_item',
        'jumlah_jual',
        'harga_jual_satuan',
        'subtotal_harga',
        'perlu_pabrikasi'
    ];
    
    public function draftPenjualan()
    {
        return $this->belongsTo(DraftPenjualan::class, 'id_penjualan', 'id_penjualan');
    }
    
    public function item()
    {
        return $this->belongsTo(Item::class, 'id_item', 'id_item');
    }
}