<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftPenjualan extends Model
{
    protected $table = 'draft_penjualan';
    protected $primaryKey = 'id_penjualan';
    public $timestamps = false;
    
    protected $fillable = [
        'id_pelanggan',
        'tanggal_penjualan',
        'total_harga_penjualan',
        'id_karyawan',
        'perlu_pabrikasi'
    ];
    
    public function detailPenjualan()
    {
        return $this->hasMany(DraftDetailPenjualan::class, 'id_penjualan', 'id_penjualan');
    }
    
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }
    
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }
}