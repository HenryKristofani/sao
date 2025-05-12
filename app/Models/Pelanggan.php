<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';
    public $timestamps = false;

    protected $fillable = [
        'id_jenis',
        'nama_pelanggan',
        'alamat_pelanggan',
        'no_telp_pelanggan',
        'email_pelanggan',
    
    ];

    public function jenisPelanggan()
    {
        return $this->belongsTo(JenisPelanggan::class, 'id_jenis', 'id_jenis');
    }
}
