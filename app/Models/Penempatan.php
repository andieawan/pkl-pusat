<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Seorang siswa ditempatkan di satu DUDI pada satu periode, dengan pembimbing sekolah & industri. */
class Penempatan extends Model
{
    protected $table = 'penempatan';
    protected $guarded = [];

    public const STATUS = ['aktif' => 'Aktif', 'selesai' => 'Selesai', 'ditarik' => 'Ditarik'];

    protected function casts(): array
    {
        return ['mulai' => 'date:Y-m-d', 'selesai' => 'date:Y-m-d'];
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function dudi()
    {
        return $this->belongsTo(Dudi::class);
    }

    public function guru()
    {
        return $this->belongsTo(Pegawai::class, 'guru_id');
    }

    public function pembimbingIndustri()
    {
        return $this->belongsTo(PembimbingIndustri::class);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function jurnal()
    {
        return $this->hasMany(Jurnal::class);
    }

    public function kunjungan()
    {
        return $this->hasMany(Kunjungan::class);
    }

    public function nilai()
    {
        return $this->hasMany(Nilai::class);
    }

    public function scopeAktif($q)
    {
        return $q->where('status', 'aktif');
    }

    /** Berjalan pada tanggal ini (status aktif dan dalam rentang). */
    public function berjalan(string $ymd): bool
    {
        return $this->status === 'aktif' && $ymd >= $this->mulai->format('Y-m-d') && $ymd <= $this->selesai->format('Y-m-d');
    }
}
