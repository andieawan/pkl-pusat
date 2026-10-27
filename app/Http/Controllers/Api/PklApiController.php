<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dudi;
use App\Models\Penempatan;
use App\Models\Periode;
use App\Models\Siswa;
use App\Services\AbsensiService;
use App\Services\JurnalService;
use App\Services\NilaiService;
use App\Support\Dates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * API baca-saja untuk aplikasi lain.
 *  - BK (berkunci): siswa PKL yang perlu perhatian & riwayat PKL satu siswa.
 *  - Publik: daftar mitra DUDI (nama, bidang, jumlah siswa) untuk Website — tanpa data pribadi.
 */
class PklApiController extends Controller
{
    private function baris(Penempatan $p): array
    {
        $r = AbsensiService::rekap($p);
        $n = NilaiService::hitung($p);

        return [
            'siswa' => ['uuid' => $p->siswa?->pusat_uuid, 'nis' => $p->siswa?->nis, 'nama' => $p->siswa?->nama, 'kelas' => $p->siswa?->kelas?->nama],
            'periode' => $p->periode?->nama, 'mulai' => $p->mulai->format('Y-m-d'), 'selesai' => $p->selesai->format('Y-m-d'), 'status' => $p->status,
            'dudi' => $p->dudi?->nama, 'guru_pembimbing' => $p->guru?->nama, 'pembimbing_industri' => $p->pembimbingIndustri?->nama,
            'rekap' => $r, 'jurnal_tertunda' => $p->status === 'aktif' ? JurnalService::tertunda($p) : 0,
            'nilai_akhir' => $n['akhir'], 'predikat' => $n['lengkap'] ? $n['huruf'] : null,
            'tautan' => route('bimbingan.show', $p),
        ];
    }

    public function ping(Request $request)
    {
        return response()->json(['data' => ['ok' => true, 'aplikasi' => 'pkl', 'klien' => $request->attributes->get('klien')]]);
    }

    /** Penempatan aktif (berjalan hari ini) yang perlu perhatian: alpa + tanpa keterangan ≥ batas, atau jurnal tertunda. */
    public function perhatian()
    {
        $batas = max(1, (int) config('pkl.batas_perhatian', 3));
        $batasJurnal = max(1, (int) config('pkl.jurnal_tertunda_hari', 3));
        $hari = Dates::today();
        $hasil = Penempatan::with(['siswa.kelas', 'dudi', 'guru', 'pembimbingIndustri', 'periode', 'absensi', 'nilai'])->aktif()
            ->whereDate('mulai', '<=', $hari)->whereDate('selesai', '>=', $hari)->get()
            ->map(function ($p) use ($batas, $batasJurnal) {
                $b = $this->baris($p);
                $alasan = [];
                if ($b['rekap']['A'] + $b['rekap']['kosong'] >= $batas) {
                    $alasan[] = ($b['rekap']['A'] + $b['rekap']['kosong']).' hari alpa / tanpa keterangan';
                }
                if ($b['jurnal_tertunda'] >= $batasJurnal) {
                    $alasan[] = "jurnal {$b['jurnal_tertunda']} hari belum diisi";
                }

                return $alasan ? $b + ['alasan' => $alasan] : null;
            })->filter()->sortByDesc(fn ($b) => $b['rekap']['A'] + $b['rekap']['kosong'])->values();

        return response()->json(['data' => $hasil, 'meta' => ['batas' => $batas, 'jumlah_pkl_aktif' => Penempatan::aktif()->whereDate('mulai', '<=', $hari)->whereDate('selesai', '>=', $hari)->count()]]);
    }

    public function siswa(string $uuid)
    {
        $s = Siswa::where('pusat_uuid', $uuid)->first();

        return response()->json(['data' => $s ? Penempatan::with(['siswa.kelas', 'dudi', 'guru', 'pembimbingIndustri', 'periode', 'absensi', 'nilai'])
            ->where('siswa_id', $s->id)->orderByDesc('mulai')->get()->map(fn ($p) => $this->baris($p))->values() : []]);
    }

    /** Publik: mitra DUDI aktif yang pernah/sedang menerima siswa PKL. */
    public function mitra()
    {
        abort_unless(config('pkl.mitra_publik'), 404);
        $data = Cache::remember('pkl:mitra_publik', 600, function () {
            $periode = Periode::where('aktif', true)->orderByDesc('mulai')->first();
            $jumlah = Penempatan::where('status', '!=', 'ditarik')->selectRaw('dudi_id, count(*) as n')->groupBy('dudi_id')->pluck('n', 'dudi_id');
            $sekarang = $periode ? Penempatan::where('periode_id', $periode->id)->where('status', '!=', 'ditarik')->selectRaw('dudi_id, count(*) as n')->groupBy('dudi_id')->pluck('n', 'dudi_id') : collect();

            return [
                'periode_aktif' => $periode?->nama,
                'mitra' => Dudi::where('aktif', true)->whereIn('id', $jumlah->keys())->orderBy('nama')->get()
                    ->map(fn ($d) => ['nama' => $d->nama, 'bidang' => $d->bidang, 'siswa_periode_ini' => (int) ($sekarang[$d->id] ?? 0), 'siswa_total' => (int) $jumlah[$d->id]])->values(),
                'total_siswa_periode_ini' => (int) $sekarang->sum(),
            ];
        });

        return response()->json(['data' => $data])->header('Cache-Control', 'public, max-age=600');
    }
}
