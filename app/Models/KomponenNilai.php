<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomponenNilai extends Model
{
    protected $table = 'komponen_nilai';
    protected $guarded = [];

    public const SUMBER = ['industri' => 'Pembimbing industri', 'sekolah' => 'Pembimbing sekolah'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean', 'bobot' => 'integer', 'urutan' => 'integer'];
    }

    public function scopeAktif($q)
    {
        return $q->where('aktif', true)->orderBy('urutan')->orderBy('id');
    }
}
