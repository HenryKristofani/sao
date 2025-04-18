<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $table = 'penjualan';
    protected $primaryKey = 'id_penjualan';
    public $timestamps = false;
    
    protected $fillable = [
        'id_pelanggan',
        'tanggal_penjualan',
        'total_harga_penjualan',
        'id_karyawan',
        'status'
    ];
    
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }
    
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan', 'id_karyawan');
    }
    
    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'id_penjualan', 'id_penjualan');
    }
    
    // New method to get the PO number
    public function getNoPoJual()
    {
        // Get the first detail to extract the PO number
        $firstDetail = $this->detailPenjualan()->first();
        return $firstDetail ? $firstDetail->no_po_jual : null;
    }
}