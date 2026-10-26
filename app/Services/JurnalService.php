<?php

namespace App\Services;

use App\Exceptions\UserError;
use App\Models\Jurnal;
use App\Models\Kunjungan;
use App\Models\Penempatan;
use App\Models\User;
use App\Support\Dates;
use App\Support\Foto;
use Illuminate\Http\UploadedFile;

/** Jurnal harian siswa (draf → diajukan → disetujui / perlu revisi) dan catatan kunjungan pembimbing. */
class JurnalService
{
    /** Simpan jurnal sendiri; langsung diajukan bila $ajukan. */
    public static function simpan(User $u, string $tanggal, string $kegiatan, ?UploadedFile $foto, bool $ajukan): Jurnal
    {
        $p = Akses::penempatanTerakhir($u) ?? throw new UserError('Anda belum punya penempatan PKL.');
        if ($p->status === 'ditarik') {
            throw new UserError('Penempatan PKL Anda sudah ditarik.');
        }
        if (! Dates::valid($tanggal) || Dates::isFuture($tanggal)) {
            throw new UserError('Tanggal tidak valid atau di masa depan.');
        }
        if ($tanggal < $p->mulai->format('Y-m-d') || $tanggal > $p->selesai->format('Y-m-d')) {
            throw new UserError('Tanggal di luar masa PKL Anda ('.Dates::human($p->mulai->format('Y-m-d')).' – '.Dates::human($p->selesai->format('Y-m-d')).').');
        }
        $kegiatan = trim($kegiatan);
        if (mb_strlen($kegiatan) < 10) {
            throw new UserError('Ceritakan kegiatan hari itu (minimal 10 karakter).');
        }
        $j = Jurnal::firstOrNew(['penempatan_id' => $p->id, 'tanggal' => $tanggal]);
        if ($j->exists && $j->status === 'disetujui') {
            throw new UserError('Jurnal tanggal ini sudah disetujui dan tidak bisa diubah.');
        }
        $data = ['kegiatan' => mb_substr($kegiatan, 0, 5000), 'status' => $ajukan ? 'diajukan' : ($j->status === 'revisi' ? 'revisi' : 'draft')];
        if ($foto) {
            Foto::hapus($j->foto);
            $data['foto'] = Foto::simpan($foto, 'jurnal');
        }
        $j->fill($data)->save();

        return $j;
    }

    /** Setujui / minta revisi banyak jurnal. @return int */
    public static function verifikasi(User $u, array $ids, string $hasil, string $catatan = ''): int
    {
        if (! in_array($hasil, ['disetujui', 'revisi'], true)) {
            throw new UserError('Pilihan verifikasi tidak dikenal.');
        }
        if ($hasil === 'revisi' && trim($catatan) === '') {
            throw new UserError('Tulis catatan apa yang perlu diperbaiki.');
        }
        $n = 0;
        foreach (Jurnal::with('penempatan')->whereIn('id', array_map('intval', $ids))->where('status', '!=', 'draft')->get() as $j) {
            if (Akses::pembimbingDari($u, $j->penempatan)) {
                $j->update(['status' => $hasil, 'catatan' => mb_substr(trim($catatan), 0, 500) ?: null, 'diverifikasi_oleh' => $u->id]);
                $n++;
            }
        }
        if ($n) {
            Audit::catat('Verifikasi jurnal PKL', $u->nama, "$n jurnal: $hasil");
        }

        return $n;
    }

    public static function kunjungan(User $u, Penempatan $p, string $tanggal, string $catatan, ?UploadedFile $foto): Kunjungan
    {
        $g = $u->hasRole('guru') ? $u->guru() : null;
        if (! $u->isPengelola() && ! ($g && $p->guru_id === $g->id)) {
            throw new UserError('Catatan kunjungan diisi oleh guru pembimbing siswa ini.');
        }
        if (! Dates::valid($tanggal) || Dates::isFuture($tanggal)) {
            throw new UserError('Tanggal tidak valid atau di masa depan.');
        }
        if (mb_strlen(trim($catatan)) < 5) {
            throw new UserError('Tulis hasil kunjungan.');
        }
        $k = Kunjungan::create(['penempatan_id' => $p->id, 'tanggal' => $tanggal, 'catatan' => mb_substr(trim($catatan), 0, 5000),
            'foto' => $foto ? Foto::simpan($foto, 'kunjungan') : null, 'dicatat_oleh' => $u->id]);
        Audit::catat('Kunjungan PKL', $u->nama, "Penempatan #{$p->id} $tanggal");

        return $k;
    }
}
