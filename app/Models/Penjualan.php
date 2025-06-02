<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penjualan extends Model
{
    use HasFactory;

    protected $table = 'penjualan';
    protected $primaryKey = 'id_penjualan';
    public $timestamps = true;
    
    protected $fillable = [
        'id_pelanggan',
        'tanggal_penjualan',
        'total_harga_penjualan',
        'id_karyawan',
        'status',
        'original_po_id',
    ];
    
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }
    
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan');
    }
    
    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class, 'id_penjualan');
    }
    
    public function schedule()
    {
        return $this->hasMany(PoJualSchedule::class, 'po_jual_id');
    }
    
    public function getNoPoJual()
    {
        $detail = $this->detailPenjualan()->first();
        return $detail ? $detail->no_po_jual : null;
    }
}