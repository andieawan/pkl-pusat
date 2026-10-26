<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Jurnal;
use App\Models\Penempatan;
use App\Services\Akses;
use App\Support\Dates;

/** Beranda sesuai peran: siswa → kartu absen & jurnal; pembimbing/pengelola → pantauan siswa bimbingan hari ini. */
class BerandaController extends Controller
{
    public function __invoke()
    {
        $u = $this->me();
        if (Akses::pembimbing($u)) {
            $hari = Dates::today();
            $aktif = Akses::penempatan($u)->aktif()->whereDate('mulai', '<=', $hari)->whereDate('selesai', '>=', $hari)
                ->with(['siswa.kelas', 'dudi'])->get()->sortBy(fn ($p) => $p->siswa->nama ?? '')->values();
            $ids = Akses::penempatan($u)->pluck('id');
            $absenHariIni = Absensi::whereIn('penempatan_id', $aktif->pluck('id'))->whereDate('tanggal', $hari)->get()->keyBy('penempatan_id');

            return view('beranda', [
                'aktif' => $aktif, 'absen' => $absenHariIni, 'hari' => $hari,
                'perluDicek' => Absensi::whereIn('penempatan_id', $ids)->where(fn ($q) => $q->where('perlu_dicek', true)->orWhere(fn ($w) => $w->whereIn('status', ['I', 'S'])->whereNull('verifikasi')))->count(),
                'jurnalMenunggu' => Jurnal::whereIn('penempatan_id', $ids)->where('status', 'diajukan')->count(),
                'totalPenempatan' => $u->isPengelola() ? Penempatan::count() : $ids->count(),
            ]);
        }
        if ($u->hasRole('siswa') && $u->siswa()) {
            return redirect()->route('siswa.beranda');
        }

        return view('beranda_kosong');
    }
}
