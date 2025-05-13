<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftDetailPembelian extends Model
{
    protected $table = 'draft_detail_pembelian';
    protected $primaryKey = 'id_detail_pembelian';
    public $timestamps = false;

    protected $fillable = [
        'id_pembelian',
        'id_item',
        'jumlah_beli',
        'harga_beli_satuan',
        'subtotal_harga',
        'original_po_detail_id'
    ];

    public function pembelian()
    {
        return $this->belongsTo(DraftPembelian::class, 'id_pembelian', 'id_pembelian');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'id_item', 'id_item');
    }
} 