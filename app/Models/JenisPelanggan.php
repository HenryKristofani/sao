<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPelanggan extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'jenis_pelanggan';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_jenis';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nama_jenis'
    ];

    /**
     * Get the pelanggans for this jenis pelanggan.
     */
    public function pelanggans()
    {
        return $this->hasMany(Pelanggan::class, 'id_jenis', 'id_jenis');
    }
}