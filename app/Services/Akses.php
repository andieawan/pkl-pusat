<?php

namespace App\Services;

use App\Models\KomponenNilai;
use App\Models\Penempatan;
use App\Models\User;
use App\Support\Dates;
use Illuminate\Database\Eloquent\Builder;

/**
 * Siapa boleh melihat/mengubah apa. Pengelola (admin, koordinator): semua. Guru pembimbing: siswa bimbingannya.
 * Pembimbing industri: siswa yang ia bimbing. Siswa: penempatannya sendiri.
 */
class Akses
{
    /** Penempatan yang terlihat oleh pengguna ini. */
    public static function penempatan(User $u): Builder
    {
        $q = Penempatan::query();
        if ($u->isPengelola()) {
            return $q;
        }

        return $q->where(function ($w) use ($u) {
            $w->whereRaw('1 = 0');
            if ($u->hasRole('guru') && ($g = $u->guru())) {
                $w->orWhere('guru_id', $g->id);
            }
            if ($u->hasRole('industri') && ($pi = $u->pembimbingIndustri())) {
                $w->orWhere('pembimbing_industri_id', $pi->id);
            }
            if ($u->hasRole('siswa') && ($s = $u->siswa())) {
                $w->orWhere('siswa_id', $s->id);
            }
        });
    }

    public static function bolehLihat(User $u, Penempatan $p): bool
    {
        return self::penempatan($u)->whereKey($p->id)->exists();
    }

    /** Pembimbing (sekolah/industri) penempatan ini, atau pengelola. */
    public static function pembimbingDari(User $u, Penempatan $p): bool
    {
        if ($u->isPengelola()) {
            return true;
        }
        $g = $u->hasRole('guru') ? $u->guru() : null;
        $pi = $u->hasRole('industri') ? $u->pembimbingIndustri() : null;

        return ($g && $p->guru_id === $g->id) || ($pi && $p->pembimbing_industri_id === $pi->id);
    }

    public static function bolehNilai(User $u, Penempatan $p, KomponenNilai $k): bool
    {
        if ($u->isPengelola()) {
            return true;
        }
        if ($k->sumber === 'industri') {
            $pi = $u->hasRole('industri') ? $u->pembimbingIndustri() : null;

            return $pi && $p->pembimbing_industri_id === $pi->id;
        }
        $g = $u->hasRole('guru') ? $u->guru() : null;

        return $g && $p->guru_id === $g->id;
    }

    /** Punya daftar siswa bimbingan (pengelola, guru pembimbing, pembimbing industri). */
    public static function pembimbing(User $u): bool
    {
        return $u->isPengelola() || ($u->hasRole('guru') && $u->guru()) || ($u->hasRole('industri') && $u->pembimbingIndustri());
    }

    /** Penempatan siswa yang berjalan hari ini (atau null). */
    public static function penempatanSiswa(User $u): ?Penempatan
    {
        $s = $u->hasRole('siswa') ? $u->siswa() : null;
        if (! $s) {
            return null;
        }
        $hari = Dates::today();

        return Penempatan::with(['dudi', 'guru', 'pembimbingIndustri', 'periode'])->where('siswa_id', $s->id)->aktif()
            ->whereDate('mulai', '<=', $hari)->whereDate('selesai', '>=', $hari)->orderByDesc('mulai')->first();
    }

    /** Penempatan terakhir siswa (untuk riwayat & nilai, termasuk yang sudah selesai). */
    public static function penempatanTerakhir(User $u): ?Penempatan
    {
        $s = $u->hasRole('siswa') ? $u->siswa() : null;

        return $s ? Penempatan::with(['dudi', 'guru', 'pembimbingIndustri', 'periode'])->where('siswa_id', $s->id)->orderByDesc('mulai')->first() : null;
    }
}
