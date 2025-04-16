<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';
    public $timestamps = false;
    
    public function jenisPelanggan()
    {
        return $this->belongsTo(JenisPelanggan::class, 'id_jenis', 'id_jenis');
    }
}