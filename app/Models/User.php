<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $guarded = [];

    protected $hidden = ['password', 'password_pusat', 'remember_token'];

    private array $cache = [];

    protected function casts(): array
    {
        return ['roles' => 'array', 'aktif' => 'boolean', 'password' => 'hashed'];
    }

    public function hasRole(string ...$r): bool
    {
        return (bool) array_intersect($r, $this->roles ?? []);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /** Admin atau koordinator PKL: mengelola semua data. */
    public function isPengelola(): bool
    {
        return $this->hasRole('admin', 'koordinator');
    }

    /** Data guru (pegawai) milik akun ini — pembimbing sekolah. */
    public function guru(): ?Pegawai
    {
        return $this->ingat('guru', fn () => $this->pegawai_uuid ? Pegawai::where('pusat_uuid', $this->pegawai_uuid)->first() : null);
    }

    /** Data siswa milik akun ini. */
    public function siswa(): ?Siswa
    {
        return $this->ingat('siswa', fn () => $this->siswa_uuid ? Siswa::where('pusat_uuid', $this->siswa_uuid)->first() : null);
    }

    /** Data pembimbing industri milik akun lokal ini. */
    public function pembimbingIndustri(): ?PembimbingIndustri
    {
        return $this->ingat('industri', fn () => PembimbingIndustri::where('user_id', $this->id)->first());
    }

    private function ingat(string $k, callable $f): mixed
    {
        if (! array_key_exists($k, $this->cache)) {
            $this->cache[$k] = $f();
        }

        return $this->cache[$k];
    }

    public function roleLabel(): string
    {
        $m = ['admin' => 'Admin', 'koordinator' => 'Koordinator PKL', 'guru' => 'Guru Pembimbing', 'siswa' => 'Siswa', 'industri' => 'Pembimbing Industri'];

        return implode(', ', array_map(fn ($r) => $m[$r] ?? $r, $this->roles ?? []));
    }
}
