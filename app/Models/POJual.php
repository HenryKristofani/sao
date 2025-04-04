<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POJual extends Model
{
    use HasFactory;

    protected $table = 'po_jual';

    protected $fillable = ['nomor_po', 'customer', 'total_harga', 'status'];
}