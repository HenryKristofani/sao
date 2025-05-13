<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPemasok extends Model
{
    protected $table = 'jenis_pemasok';
    protected $primaryKey = 'id_jenis_pemasok';
    public $timestamps = false;

    protected $fillable = [
        'nama_jenis_pemasok'
    ];

    public function pemasok()
    {
        return $this->hasMany(Pemasok::class, 'id_jenis_pemasok', 'id_jenis_pemasok');
    }
} 