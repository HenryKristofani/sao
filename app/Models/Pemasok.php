<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemasok extends Model
{
    protected $table = 'pemasok';
    protected $primaryKey = 'id_pemasok';
    public $timestamps = false;

    protected $fillable = [
        'nama_pemasok',
        'alamat_pemasok',
        'telepon_pemasok',
        'email_pemasok',
        'id_jenis_pemasok'
    ];

    public function jenisPemasok()
    {
        return $this->belongsTo(JenisPemasok::class, 'id_jenis_pemasok', 'id_jenis_pemasok');
    }

    public function pembelian()
    {
        return $this->hasMany(Pembelian::class, 'id_pemasok', 'id_pemasok');
    }

    public function draftPembelian()
    {
        return $this->hasMany(DraftPembelian::class, 'id_pemasok', 'id_pemasok');
    }
} 