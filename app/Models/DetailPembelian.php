<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPembelian extends Model
{
    protected $table = 'detail_pembelian';
    protected $primaryKey = 'id_detail_pembelian';
    public $timestamps = false;

    protected $fillable = [
        'id_pembelian',
        'no_po_beli',
        'id_item',
        'jumlah_beli',
        'harga_beli_satuan',
        'subtotal_harga'
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'id_pembelian', 'id_pembelian');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'id_item', 'id_item');
    }
} 