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
            // Jurnal tertunda per siswa (2 kueri untuk semua siswa, bukan per siswa)
            $dari = date('Y-m-d', strtotime($hari.' -31 days'));
            $peta = fn ($q) => $q->whereIn('penempatan_id', $aktif->pluck('id'))->whereDate('tanggal', '>=', $dari)->get(['penempatan_id', 'tanggal'])
                ->groupBy('penempatan_id')->map(fn ($g) => $g->mapWithKeys(fn ($r) => [$r->tanggal->format('Y-m-d') => true])->all());
            $jurnal = $peta(Jurnal::query());
            $libur = $peta(Absensi::where('status', '!=', 'H'));
            $batas = max(1, (int) config('pkl.jurnal_tertunda_hari'));
            $tertunda = $aktif->mapWithKeys(fn ($p) => [$p->id => \App\Services\JurnalService::tertunda($p, $jurnal[$p->id] ?? [], $libur[$p->id] ?? [])])
                ->filter(fn ($n) => $n >= $batas);

            return view('beranda', [
                'aktif' => $aktif, 'absen' => $absenHariIni, 'hari' => $hari, 'tertunda' => $tertunda,
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
