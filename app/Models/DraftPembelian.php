<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftPembelian extends Model
{
    protected $table = 'draft_pembelian';
    protected $primaryKey = 'id_pembelian';
    public $timestamps = false;

    protected $fillable = [
        'id_pemasok',
        'tanggal_pembelian',
        'total_harga_pembelian',
        'id_karyawan',
        'original_po_id'
    ];

    public function pemasok()
    {
        return $this->belongsTo(Pemasok::class, 'id_pemasok', 'id_pemasok');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }

    public function detailPembelian()
    {
        return $this->hasMany(DraftDetailPembelian::class, 'id_pembelian', 'id_pembelian');
    }
} 