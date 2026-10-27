<?php

namespace App\Http\Controllers\Pkl;

use App\Http\Controllers\Controller;
use App\Models\Dudi;
use App\Models\Penempatan;
use App\Models\Periode;
use App\Models\SuratPkl;
use App\Services\AbsensiService;
use App\Services\Akses;
use App\Services\Audit;
use App\Services\NilaiService;
use App\Services\TuKlien;
use App\Support\Xlsx;
use Illuminate\Http\Request;

/** Rekap absensi & nilai (layar, cetak, Excel), surat pengantar/penarikan per DUDI, sertifikat PKL. */
class LaporanController extends Controller
{
    private function periode(Request $r): ?Periode
    {
        return Periode::find((int) $r->query('periode')) ?? Periode::where('aktif', true)->orderByDesc('mulai')->first() ?? Periode::orderByDesc('mulai')->first();
    }

    private function data(Request $r, ?Periode $per)
    {
        $komponen = NilaiService::komponen();

        return Akses::penempatan($this->me())->with(['siswa.kelas', 'dudi', 'guru', 'absensi', 'nilai'])
            ->when($per, fn ($q) => $q->where('periode_id', $per->id))
            ->when((int) $r->query('dudi'), fn ($q) => $q->where('dudi_id', (int) $r->query('dudi')))
            ->get()->sortBy(fn ($p) => [($p->siswa->kelas->nama ?? ''), ($p->siswa->nama ?? '')])->values()
            ->map(fn ($p) => ['p' => $p, 'rekap' => AbsensiService::rekap($p), 'nilai' => NilaiService::hitung($p, $komponen)]);
    }

    public function index(Request $request)
    {
        $per = $this->periode($request);

        return view('laporan.index', ['periode' => Periode::orderByDesc('mulai')->get(), 'per' => $per, 'rows' => $this->data($request, $per),
            'dudi' => Dudi::orderBy('nama')->get(), 'dudiId' => (int) $request->query('dudi'), 'komponen' => NilaiService::komponen(),
            'cetak' => $request->boolean('cetak'), 'cfg' => config('pkl.surat')]);
    }

    public function unduh(Request $request)
    {
        $per = $this->periode($request);
        $komponen = NilaiService::komponen();
        $rows = [array_merge(['No', 'NIS', 'Nama', 'Kelas', 'DUDI', 'Guru Pembimbing', 'Hari Kerja', 'Hadir', 'Izin', 'Sakit', 'Alpa', 'Tanpa Keterangan', 'Terlambat', '% Hadir'],
            $komponen->pluck('nama')->all(), ['Nilai Akhir', 'Predikat'])];
        foreach ($this->data($request, $per) as $i => $r) {
            $p = $r['p'];
            $a = $r['rekap'];
            $rows[] = array_merge([$i + 1, $p->siswa->nis ?? '', $p->siswa->nama ?? '', $p->siswa->kelas->nama ?? '', $p->dudi->nama ?? '', $p->guru->nama ?? '',
                $a['hari'], $a['H'], $a['I'], $a['S'], $a['A'], $a['kosong'], $a['T'], $a['persen'] === null ? '' : $a['persen'].'%'],
                array_map(fn ($x) => $x['skor'] ?? '', $r['nilai']['rincian']), [$r['nilai']['akhir'] ?? '', $r['nilai']['lengkap'] ? $r['nilai']['huruf'] : '']);
        }
        Audit::catat('Unduh rekap PKL', $this->me()->nama, $per->nama ?? '-');

        return Xlsx::download('Rekap_PKL_'.preg_replace('/[^A-Za-z0-9]+/', '_', $per->nama ?? 'semua').'.xlsx', ['Rekap PKL' => $rows]);
    }

    /** Surat pengantar (penempatan) atau penarikan siswa untuk satu DUDI pada satu periode. */
    public function surat(Request $request, string $jenis)
    {
        abort_unless(in_array($jenis, ['pengantar', 'penarikan'], true), 404);
        $per = $this->periode($request) ?? abort(404, 'Belum ada periode PKL.');
        $dudi = Dudi::findOrFail((int) $request->query('dudi'));
        $siswa = Penempatan::with(['siswa.kelas', 'guru'])->where('periode_id', $per->id)->where('dudi_id', $dudi->id)->where('status', '!=', 'ditarik')->get()
            ->sortBy(fn ($p) => $p->siswa->nama ?? '')->values();
        Audit::catat('Cetak surat '.$jenis.' PKL', $this->me()->nama, $dudi->nama);

        $arsip = SuratPkl::where(['jenis' => $jenis, 'periode_id' => $per->id, 'dudi_id' => $dudi->id])->first();

        return view('laporan.surat', ['jenis' => $jenis, 'per' => $per, 'dudi' => $dudi, 'list' => $siswa, 'cfg' => config('pkl.surat'), 'arsip' => $arsip,
            'tuAktif' => TuKlien::aktif(), 'tanggal' => $arsip->tanggal ?? \App\Support\Dates::today(),
            'nomor' => $arsip->nomor ?? (string) $request->query('nomor'), 'tempat' => (string) $request->query('tempat', config('pkl.surat.kota'))]);
    }

    /** Beri nomor surat: dari register Surat Keluar TU (bila tersambung) atau diisi manual. Sekali diberi, nomor tetap. */
    public function nomorSurat(Request $request, string $jenis)
    {
        abort_unless(in_array($jenis, ['pengantar', 'penarikan'], true), 404);
        $per = Periode::findOrFail((int) $request->input('periode'));
        $dudi = Dudi::findOrFail((int) $request->input('dudi'));
        $kunci = ['jenis' => $jenis, 'periode_id' => $per->id, 'dudi_id' => $dudi->id];
        if (SuratPkl::where($kunci)->exists()) {
            return back()->with('success', 'Surat ini sudah bernomor.');
        }
        $tanggal = \App\Support\Dates::today();
        $manual = trim((string) $request->input('nomor'));
        if ($request->boolean('manual') && $manual === '') {
            return back()->with('error', 'Isi nomor surat manual terlebih dahulu.');
        }
        if ($manual !== '') {
            SuratPkl::create($kunci + ['nomor' => mb_substr($manual, 0, 80), 'tanggal' => $tanggal, 'sumber' => 'manual', 'dibuat_oleh' => $this->me()->id]);
            Audit::catat('Nomor surat PKL (manual)', $this->me()->nama, "$jenis {$dudi->nama}: $manual");

            return back()->with('success', 'Nomor surat disimpan.');
        }
        $jumlah = Penempatan::where('periode_id', $per->id)->where('dudi_id', $dudi->id)->where('status', '!=', 'ditarik')->count();
        $perihal = ($jenis === 'pengantar' ? 'Permohonan Penempatan Siswa PKL' : 'Penarikan Siswa PKL').' — '.$dudi->nama;
        try {
            $r = TuKlien::suratKeluar("$jenis:periode-{$per->id}:dudi-{$dudi->id}", $tanggal, 'Pimpinan '.$dudi->nama, $perihal,
                "{$per->nama}: $jumlah siswa. Dibuat dari aplikasi PKL.", $this->me()->nama);
        } catch (\RuntimeException $e) {
            return back()->with('error', 'Nomor dari TU gagal diambil: '.$e->getMessage().' Anda bisa mengisi nomor manual.');
        }
        SuratPkl::create($kunci + ['nomor' => (string) $r['nomor'], 'tanggal' => (string) ($r['tanggal'] ?? $tanggal), 'sumber' => 'tu',
            'agenda_tu' => $r['agenda'] ?? null, 'dibuat_oleh' => $this->me()->id]);
        Audit::catat('Nomor surat PKL dari TU', $this->me()->nama, "$jenis {$dudi->nama}: {$r['nomor']}");

        return back()->with('success', "Nomor surat dari TU: {$r['nomor']} (tercatat di register Surat Keluar TU).");
    }

    public function sertifikat(Penempatan $penempatan)
    {
        abort_unless(Akses::bolehLihat($this->me(), $penempatan), 403);
        $p = $penempatan->load(['siswa.kelas', 'dudi', 'periode', 'pembimbingIndustri']);
        $n = NilaiService::hitung($p);
        abort_unless($n['lengkap'], 422, 'Nilai PKL siswa ini belum lengkap.');

        return view('laporan.sertifikat', ['p' => $p, 'n' => $n, 'cfg' => config('pkl.surat')]);
    }
}
