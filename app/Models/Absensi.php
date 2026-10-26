<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';
    protected $guarded = [];

    public const STATUS = ['H' => 'Hadir', 'I' => 'Izin', 'S' => 'Sakit', 'A' => 'Alpa'];

    protected function casts(): array
    {
        return ['tanggal' => 'date:Y-m-d', 'terlambat' => 'boolean', 'perlu_dicek' => 'boolean', 'jarak' => 'integer', 'lat' => 'float', 'lng' => 'float'];
    }

    public function penempatan()
    {
        return $this->belongsTo(Penempatan::class);
    }
}
