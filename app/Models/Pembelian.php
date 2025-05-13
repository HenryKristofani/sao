<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $table = 'pembelian';
    protected $primaryKey = 'id_pembelian';
    public $timestamps = false;

    protected $fillable = [
        'id_pemasok',
        'tanggal_pembelian',
        'total_harga_pembelian',
        'id_karyawan',
        'original_po_id',
        'status'
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
        return $this->hasMany(DetailPembelian::class, 'id_pembelian', 'id_pembelian');
    }

    public function getNoPoBeli()
    {
        $detail = $this->detailPembelian()->first();
        return $detail ? $detail->no_po_beli : null;
    }
} 