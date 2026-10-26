<?php

namespace App\Http\Controllers\Pkl;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Dudi;
use App\Models\Jurnal;
use App\Models\Penempatan;
use App\Models\Periode;
use App\Services\AbsensiService;
use App\Services\Akses;
use App\Services\JurnalService;
use App\Services\NilaiService;
use App\Support\Dates;
use Illuminate\Http\Request;

/** Untuk pembimbing sekolah, pembimbing industri, dan pengelola: daftar siswa, detail, verifikasi, kunjungan, nilai. */
class BimbinganController extends Controller
{
    private function penempatan(Penempatan $p): Penempatan
    {
        abort_unless(Akses::bolehLihat($this->me(), $p), 403, 'Siswa ini bukan bimbingan Anda.');

        return $p->load(['siswa.kelas', 'dudi', 'guru', 'pembimbingIndustri', 'periode']);
    }

    public function index(Request $request)
    {
        $u = $this->me();
        $periodeId = (int) $request->query('periode');
        $dudiId = (int) $request->query('dudi');
        $status = array_key_exists((string) $request->query('status'), Penempatan::STATUS) ? $request->query('status') : '';
        $q = trim((string) $request->query('q'));
        $t = str_replace(['%', '_'], ['\\%', '\\_'], $q);
        $list = Akses::penempatan($u)->with(['siswa.kelas', 'dudi', 'guru', 'pembimbingIndustri', 'periode'])
            ->when($periodeId, fn ($x) => $x->where('periode_id', $periodeId))
            ->when($dudiId, fn ($x) => $x->where('dudi_id', $dudiId))
            ->when($status, fn ($x) => $x->where('status', $status))
            ->when($q !== '', fn ($x) => $x->whereHas('siswa', fn ($s) => $s->where('nama', 'like', "%$t%")->orWhere('nis', 'like', "%$t%")))
            ->orderByDesc('mulai')->paginate(30)->withQueryString();

        return view('bimbingan.index', ['list' => $list, 'periode' => Periode::orderByDesc('mulai')->get(), 'dudi' => $u->isPengelola() ? Dudi::orderBy('nama')->get() : collect(),
            'periodeId' => $periodeId, 'dudiId' => $dudiId, 'status' => $status, 'q' => $q]);
    }

    public function show(Penempatan $penempatan)
    {
        $p = $this->penempatan($penempatan);
        $u = $this->me();
        $komponen = NilaiService::komponen();

        return view('bimbingan.show', [
            'p' => $p, 'absensi' => $p->absensi()->orderByDesc('tanggal')->get(), 'jurnal' => $p->jurnal()->orderByDesc('tanggal')->get(),
            'kunjungan' => $p->kunjungan()->with('pencatat')->orderByDesc('tanggal')->get(), 'rekap' => AbsensiService::rekap($p),
            'nilai' => NilaiService::hitung($p, $komponen), 'bolehNilai' => $komponen->mapWithKeys(fn ($k) => [$k->id => \App\Services\Akses::bolehNilai($u, $p, $k)])->all(),
            'pembimbing' => Akses::pembimbingDari($u, $p), 'guruIni' => $u->isPengelola() || ($u->hasRole('guru') && $u->guru()?->id === $p->guru_id),
            'hari' => Dates::today(),
        ]);
    }

    /** Daftar yang menunggu pemeriksaan: absensi perlu dicek / izin-sakit belum diverifikasi, dan jurnal diajukan. */
    public function verifikasi()
    {
        $ids = Akses::penempatan($this->me())->pluck('id');

        return view('bimbingan.verifikasi', [
            'absensi' => Absensi::with('penempatan.siswa', 'penempatan.dudi')->whereIn('penempatan_id', $ids)
                ->where(fn ($q) => $q->where('perlu_dicek', true)->orWhere(fn ($w) => $w->whereIn('status', ['I', 'S'])->whereNull('verifikasi')))
                ->orderByDesc('tanggal')->limit(200)->get(),
            'jurnal' => Jurnal::with('penempatan.siswa')->whereIn('penempatan_id', $ids)->where('status', 'diajukan')->orderBy('tanggal')->limit(200)->get(),
        ]);
    }

    public function verifikasiAbsensi(Request $request)
    {
        $n = AbsensiService::verifikasi($this->me(), (array) $request->input('id', []), (string) $request->input('hasil'));

        return back()->with('success', "$n absensi diperbarui.");
    }

    public function verifikasiJurnal(Request $request)
    {
        $n = JurnalService::verifikasi($this->me(), (array) $request->input('id', []), (string) $request->input('hasil'), (string) $request->input('catatan'));

        return back()->with('success', "$n jurnal diperbarui.");
    }

    public function kunjungan(Request $request, Penempatan $penempatan)
    {
        $request->validate(['foto' => 'nullable|file|max:10240']);
        JurnalService::kunjungan($this->me(), $this->penempatan($penempatan), (string) $request->input('tanggal'), (string) $request->input('catatan'), $request->file('foto'));

        return redirect()->route('bimbingan.show', [$penempatan, '#kunjungan'])->with('success', 'Catatan kunjungan tersimpan.');
    }

    public function nilai(Request $request, Penempatan $penempatan)
    {
        $n = NilaiService::simpan($this->me(), $this->penempatan($penempatan), (array) $request->input('skor', []));

        return redirect()->route('bimbingan.show', [$penempatan, '#nilai'])->with('success', "$n komponen nilai tersimpan.");
    }

    public function koreksi(Request $request, Penempatan $penempatan)
    {
        AbsensiService::koreksi($this->me(), $this->penempatan($penempatan), (string) $request->input('tanggal'), (string) $request->input('status'), (string) $request->input('keterangan'));

        return redirect()->route('bimbingan.show', [$penempatan, '#absensi'])->with('success', 'Absensi dikoreksi.');
    }
}
