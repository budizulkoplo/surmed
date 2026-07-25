<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lembur extends Model
{
    use HasFactory;

    protected $table = 'lembur';
    protected $primaryKey = 'idlembur';
    public $timestamps = true; // agar created_at otomatis

    protected $fillable = [
        'nik',
        'tgl_lembur',
    ];

    // optional: relasi ke User
    public function pegawai()
    {
        return $this->belongsTo(User::class, 'nik', 'nik');
    }
}
