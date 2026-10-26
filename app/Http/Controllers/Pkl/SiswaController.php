<?php

namespace App\Http\Controllers\Pkl;

use App\Exceptions\UserError;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jurnal;
use App\Services\AbsensiService;
use App\Services\Akses;
use App\Services\JurnalService;
use App\Services\NilaiService;
use App\Support\Dates;
use Illuminate\Http\Request;

/** Halaman siswa: absen masuk/pulang (selfie + GPS), lapor izin/sakit, jurnal harian, riwayat, nilai. */
class SiswaController extends Controller
{
    private const FOTO = 'nullable|file|max:10240';

    public function beranda()
    {
        $u = $this->me();
        $p = Akses::penempatanSiswa($u);
        $terakhir = $p ?? Akses::penempatanTerakhir($u);

        return view('siswa.beranda', [
            'p' => $p, 'terakhir' => $terakhir, 'hari' => Dates::today(),
            'absen' => $p ? AbsensiService::hariIni($p) : null, 'batas' => $p ? AbsensiService::batasMasuk($p) : null,
            'jurnalHariIni' => $p ? Jurnal::where('penempatan_id', $p->id)->whereDate('tanggal', Dates::today())->first() : null,
            'rekap' => $terakhir ? AbsensiService::rekap($terakhir) : null,
            'revisi' => $terakhir ? Jurnal::where('penempatan_id', $terakhir->id)->where('status', 'revisi')->count() : 0,
        ]);
    }

    public function absen(Request $request)
    {
        $request->validate(['foto' => self::FOTO, 'bukti' => self::FOTO]);
        $u = $this->me();
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $msg = match ($request->input('aksi')) {
            'masuk' => (function () use ($u, $lat, $lng, $request) {
                $a = AbsensiService::masuk($u, $lat, $lng, $request->file('foto'));

                return "Absen masuk tercatat pukul {$a->jam_masuk}".($a->terlambat ? ' (terlambat)' : '').'.'
                    .($a->perlu_dicek ? ' Lokasi Anda di luar area DUDI atau tidak terbaca, jadi pembimbing akan memeriksanya.' : '');
            })(),
            'pulang' => 'Absen pulang tercatat pukul '.AbsensiService::pulang($u, $lat, $lng, $request->file('foto'))->jam_pulang.'. Terima kasih!',
            'lapor' => 'Laporan '.Absensi::STATUS[AbsensiService::lapor($u, (string) $request->input('status'), (string) $request->input('keterangan'), $request->file('bukti'))->status].' tercatat dan menunggu pembimbing.',
            default => throw new UserError('Aksi tidak dikenal.'),
        };

        return redirect()->route('siswa.beranda')->with('success', $msg);
    }

    public function riwayat()
    {
        $p = Akses::penempatanTerakhir($this->me());

        return view('siswa.riwayat', ['p' => $p, 'rows' => $p ? Absensi::where('penempatan_id', $p->id)->orderByDesc('tanggal')->get() : collect(),
            'rekap' => $p ? AbsensiService::rekap($p) : null]);
    }

    public function jurnal(Request $request)
    {
        $p = Akses::penempatanTerakhir($this->me());
        $tanggal = Dates::valid($request->query('tanggal')) ? $request->query('tanggal') : Dates::today();

        return view('siswa.jurnal', [
            'p' => $p, 'tanggal' => $tanggal, 'hari' => Dates::today(),
            'ini' => $p ? Jurnal::where('penempatan_id', $p->id)->whereDate('tanggal', $tanggal)->first() : null,
            'daftar' => $p ? Jurnal::where('penempatan_id', $p->id)->orderByDesc('tanggal')->paginate(20)->withQueryString() : collect(),
        ]);
    }

    public function simpanJurnal(Request $request)
    {
        $request->validate(['foto' => self::FOTO]);
        $j = JurnalService::simpan($this->me(), (string) $request->input('tanggal'), (string) $request->input('kegiatan'), $request->file('foto'), $request->input('aksi') === 'ajukan');

        return redirect()->route('siswa.jurnal', ['tanggal' => $j->tanggal->format('Y-m-d')])
            ->with('success', $j->status === 'diajukan' ? 'Jurnal diajukan ke pembimbing.' : 'Jurnal disimpan sebagai draf.');
    }

    public function nilai()
    {
        $p = Akses::penempatanTerakhir($this->me());

        return view('siswa.nilai', ['p' => $p, 'n' => $p ? NilaiService::hitung($p) : null]);
    }
}
