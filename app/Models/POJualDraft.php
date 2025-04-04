<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POJualDraft extends Model
{
    use HasFactory;

    protected $table = 'po_jual_draft'; // <- pakai nama tabel sesuai database

    protected $fillable = [
        'nomor_po',
        'customer',
        'total_harga',
        'is_amendment', // ✅ WAJIB ada
    ];
    
}