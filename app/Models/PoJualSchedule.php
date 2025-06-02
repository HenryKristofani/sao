<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoJualSchedule extends Model
{
    use HasFactory;

    protected $table = 'po_jual_schedules';

    protected $fillable = [
        'po_jual_id',
        'stage',
        'start_date',
        'end_date',
        'status',
    ];

    // Define relationship with Penjualan
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'po_jual_id');
    }
}
