<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Dunia Usaha / Dunia Industri tempat PKL. */
class Dudi extends Model
{
    protected $table = 'dudi';
    protected $guarded = [];

    public const HARI = [1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    protected function casts(): array
    {
        return ['hari_kerja' => 'array', 'aktif' => 'boolean', 'lat' => 'float', 'lng' => 'float', 'radius' => 'integer', 'kuota' => 'integer'];
    }

    public function pembimbing()
    {
        return $this->hasMany(PembimbingIndustri::class);
    }

    public function penempatan()
    {
        return $this->hasMany(Penempatan::class);
    }

    public function punyaLokasi(): bool
    {
        return $this->lat !== null && $this->lng !== null;
    }

    /** Hari kerja (1 = Senin … 7 = Minggu). Bawaan Senin–Sabtu. */
    public function hariKerja(): array
    {
        return $this->hari_kerja ?: [1, 2, 3, 4, 5, 6];
    }
}
