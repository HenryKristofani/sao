<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPenjualan extends Model
{
    protected $table = 'detail_penjualan';
    protected $primaryKey = 'id_detail_penjualan';
    public $timestamps = false;
    
    protected $fillable = [
        'id_penjualan',
        'no_po_jual',
        'id_item',
        'jumlah_jual',
        'harga_jual_satuan',
        'subtotal_harga'
    ];
    
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'id_penjualan', 'id_penjualan');
    }
    
    public function item()
    {
        return $this->belongsTo(Item::class, 'id_item', 'id_item');
    }
}