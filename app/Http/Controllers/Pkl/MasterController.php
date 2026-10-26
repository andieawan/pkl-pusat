<?php

namespace App\Http\Controllers\Pkl;

use App\Http\Controllers\Controller;
use App\Models\Dudi;
use App\Models\Kelas;
use App\Models\KomponenNilai;
use App\Models\Pegawai;
use App\Models\PembimbingIndustri;
use App\Models\Penempatan;
use App\Models\Periode;
use App\Models\Siswa;
use App\Services\MasterService;
use Illuminate\Http\Request;

/** Data master oleh admin/koordinator: DUDI, pembimbing industri, periode, penempatan, komponen nilai. */
class MasterController extends Controller
{
    // ---- DUDI ----
    public function dudi(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $t = str_replace(['%', '_'], ['\\%', '\\_'], $q);

        return view('master.dudi', ['list' => Dudi::withCount(['penempatan as aktif_count' => fn ($x) => $x->where('status', 'aktif')])
            ->when($q !== '', fn ($x) => $x->where('nama', 'like', "%$t%")->orWhere('bidang', 'like', "%$t%"))
            ->orderByDesc('aktif')->orderBy('nama')->paginate(30)->withQueryString(), 'q' => $q]);
    }

    public function simpanDudi(Request $request, ?Dudi $dudi = null)
    {
        $d = MasterService::simpanDudi($this->me(), $dudi?->exists ? $dudi : null, $request->all());

        return redirect()->route('master.dudi')->with('success', "DUDI {$d->nama} tersimpan.");
    }

    // ---- Pembimbing industri ----
    public function pembimbing(Request $request)
    {
        $dudiId = (int) $request->query('dudi');

        return view('master.pembimbing', ['list' => PembimbingIndustri::with(['dudi', 'user'])->when($dudiId, fn ($x) => $x->where('dudi_id', $dudiId))
            ->orderBy('nama')->paginate(30)->withQueryString(), 'dudi' => Dudi::orderBy('nama')->get(), 'dudiId' => $dudiId]);
    }

    public function simpanPembimbing(Request $request, ?PembimbingIndustri $pembimbing = null)
    {
        [$pi, $password] = MasterService::simpanPembimbing($this->me(), $pembimbing?->exists ? $pembimbing : null, $request->all());
        $r = redirect()->route('master.pembimbing')->with('success', "Pembimbing {$pi->nama} tersimpan.");

        return $password ? $r->with('akun_baru', ['nama' => $pi->nama, 'username' => $pi->fresh('user')->user->username, 'password' => $password]) : $r;
    }

    // ---- Periode ----
    public function periode()
    {
        return view('master.periode', ['list' => Periode::withCount('penempatan')->orderByDesc('mulai')->get()]);
    }

    public function simpanPeriode(Request $request, ?Periode $periode = null)
    {
        $p = MasterService::simpanPeriode($this->me(), $periode?->exists ? $periode : null, $request->all());

        return redirect()->route('master.periode')->with('success', "Periode {$p->nama} tersimpan.");
    }

    // ---- Penempatan ----
    public function penempatanBaru(Request $request)
    {
        $kelasId = (int) $request->query('kelas');
        $periodeId = (int) $request->query('periode') ?: (int) Periode::where('aktif', true)->orderByDesc('mulai')->value('id');
        $sudah = $periodeId ? Penempatan::where('periode_id', $periodeId)->pluck('dudi_id', 'siswa_id')->all() : [];

        return view('master.penempatan', [
            'kelas' => Kelas::where('tahun_aktif', true)->orderBy('nama')->get(), 'kelasId' => $kelasId,
            'periode' => Periode::orderByDesc('mulai')->get(), 'periodeId' => $periodeId,
            'siswa' => $kelasId ? Siswa::aktif()->where('kelas_id', $kelasId)->orderBy('nama')->get() : collect(), 'sudah' => $sudah,
            'dudi' => Dudi::where('aktif', true)->orderBy('nama')->with('pembimbing')->get(), 'guru' => Pegawai::aktif()->orderBy('nama')->get(),
        ]);
    }

    public function simpanPenempatan(Request $request)
    {
        $h = MasterService::tempatkan($this->me(), $request->all());

        return back()->with('success', "{$h['dibuat']} siswa ditempatkan.".($h['dilewati'] ? " {$h['dilewati']} siswa dilewati karena sudah punya penempatan di periode ini." : ''));
    }

    public function ubahPenempatan(Request $request, Penempatan $penempatan)
    {
        MasterService::ubahPenempatan($this->me(), $penempatan, $request->all());

        return redirect()->route('bimbingan.show', $penempatan)->with('success', 'Penempatan diperbarui.');
    }

    // ---- Komponen nilai ----
    public function komponen()
    {
        return view('master.komponen', ['list' => KomponenNilai::orderBy('urutan')->orderBy('id')->get()]);
    }

    public function simpanKomponen(Request $request)
    {
        MasterService::simpanKomponen($this->me(), (array) $request->input('komponen', []));

        return back()->with('success', 'Komponen nilai disimpan.');
    }
}
