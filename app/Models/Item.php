<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $table = 'item';
    protected $primaryKey = 'id_item';
    public $timestamps = false;
    
    public function jenisItem()
    {
        return $this->belongsTo(JenisItem::class, 'id_jenis', 'id_jenis');
    }
    
    public function lokasiItem()
    {
        return $this->belongsTo(LokasiItem::class, 'id_lokasi_item', 'id_lokasi_item');
    }
}