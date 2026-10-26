<?php

namespace App\Services;

use App\Exceptions\UserError;
use App\Models\Absensi;
use App\Models\Penempatan;
use App\Models\User;
use App\Support\Dates;
use App\Support\Foto;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

/**
 * Absensi siswa PKL: masuk (selfie + GPS), pulang, lapor izin/sakit; verifikasi oleh pembimbing; koreksi oleh pengelola.
 * GPS di HP tidak selalu akurat, jadi absen di luar radius TETAP tersimpan tetapi ditandai "perlu dicek".
 */
class AbsensiService
{
    public static function nowHm(): string
    {
        return CarbonImmutable::now(Dates::tz())->format('H:i');
    }

    /** Jarak (meter) dua titik koordinat, rumus haversine. */
    public static function jarak(float $lat1, float $lng1, float $lat2, float $lng2): int
    {
        $r = 6371000;
        $a = deg2rad($lat2 - $lat1);
        $b = deg2rad($lng2 - $lng1);
        $h = sin($a / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($b / 2) ** 2;

        return (int) round(2 * $r * asin(min(1, sqrt($h))));
    }

    /** Batas jam terlambat = jam masuk DUDI + toleransi. */
    public static function batasMasuk(Penempatan $p): string
    {
        [$h, $m] = array_map('intval', explode(':', $p->dudi->jam_masuk ?: '08:00'));
        $t = $h * 60 + $m + (int) config('pkl.toleransi_menit', 15);

        return sprintf('%02d:%02d', intdiv(min($t, 1439), 60), min($t, 1439) % 60);
    }

    private static function penempatan(User $u): Penempatan
    {
        return Akses::penempatanSiswa($u) ?? throw new UserError('Anda tidak sedang dalam masa PKL (belum ada penempatan aktif hari ini). Hubungi koordinator PKL.');
    }

    public static function hariIni(Penempatan $p): ?Absensi
    {
        return Absensi::where('penempatan_id', $p->id)->whereDate('tanggal', Dates::today())->first();
    }

    /** @return array{0:?int,1:bool} [jarak meter, perlu dicek] */
    private static function lokasi(Penempatan $p, mixed $lat, mixed $lng): array
    {
        $ada = is_numeric($lat) && is_numeric($lng) && abs((float) $lat) <= 90 && abs((float) $lng) <= 180;
        if (! $ada) {
            return [null, true]; // tanpa lokasi: tetap tersimpan, pembimbing memeriksa
        }
        if (! $p->dudi->punyaLokasi()) {
            return [null, false];
        }
        $d = self::jarak((float) $lat, (float) $lng, $p->dudi->lat, $p->dudi->lng);

        return [$d, $d > max(1, (int) $p->dudi->radius)];
    }

    public static function masuk(User $u, mixed $lat, mixed $lng, ?UploadedFile $foto): Absensi
    {
        $p = self::penempatan($u);
        if (! $foto) {
            throw new UserError('Ambil foto selfie dulu sebelum absen masuk.');
        }
        $row = self::hariIni($p);
        if ($row && $row->jam_masuk) {
            throw new UserError("Anda sudah absen masuk pukul {$row->jam_masuk}.");
        }
        if ($row && $row->status !== 'H') {
            throw new UserError('Hari ini sudah tercatat '.Absensi::STATUS[$row->status].'. Jika keliru, hubungi pembimbing.');
        }
        [$jarak, $cek] = self::lokasi($p, $lat, $lng);
        $jam = self::nowHm();
        $data = [
            'status' => 'H', 'jam_masuk' => $jam, 'terlambat' => $jam > self::batasMasuk($p), 'jarak' => $jarak, 'perlu_dicek' => $cek,
            'lat' => is_numeric($lat) ? (float) $lat : null, 'lng' => is_numeric($lng) ? (float) $lng : null,
            'foto_masuk' => Foto::simpan($foto, 'absensi'), 'sumber' => 'siswa', 'verifikasi' => null,
        ];
        if ($row) {
            $row->update($data);

            return $row;
        }

        return Absensi::create($data + ['penempatan_id' => $p->id, 'tanggal' => Dates::today()]);
    }

    public static function pulang(User $u, mixed $lat, mixed $lng, ?UploadedFile $foto): Absensi
    {
        $p = self::penempatan($u);
        $row = self::hariIni($p);
        if (! $row || $row->status !== 'H' || ! $row->jam_masuk) {
            throw new UserError('Absen masuk dulu sebelum absen pulang.');
        }
        if ($row->jam_pulang) {
            throw new UserError("Anda sudah absen pulang pukul {$row->jam_pulang}.");
        }
        [, $cek] = self::lokasi($p, $lat, $lng);
        $row->update(['jam_pulang' => self::nowHm(), 'perlu_dicek' => $row->perlu_dicek || $cek, 'foto_pulang' => $foto ? Foto::simpan($foto, 'absensi') : null]);

        return $row;
    }

    /** Izin/Sakit hari ini (atau besok untuk izin terencana), dengan keterangan wajib & bukti opsional. */
    public static function lapor(User $u, string $status, string $keterangan, ?UploadedFile $bukti): Absensi
    {
        $p = self::penempatan($u);
        if (! in_array($status, ['I', 'S'], true)) {
            throw new UserError('Pilih Izin atau Sakit.');
        }
        $keterangan = mb_substr(trim($keterangan), 0, 500);
        if (mb_strlen($keterangan) < 3) {
            throw new UserError('Tulis alasan singkat.');
        }
        $row = self::hariIni($p);
        if ($row && $row->jam_masuk) {
            throw new UserError('Anda sudah absen masuk hari ini.');
        }
        $data = ['status' => $status, 'keterangan' => $keterangan, 'sumber' => 'siswa', 'verifikasi' => null, 'perlu_dicek' => false,
            'bukti' => $bukti ? Foto::simpan($bukti, 'bukti', true) : $row?->bukti];
        if ($row) {
            $row->update($data);

            return $row;
        }

        return Absensi::create($data + ['penempatan_id' => $p->id, 'tanggal' => Dates::today()]);
    }

    /** Setujui / tolak banyak absensi sekaligus. @return int jumlah yang diubah */
    public static function verifikasi(User $u, array $ids, string $hasil): int
    {
        if (! in_array($hasil, ['ok', 'tolak'], true)) {
            throw new UserError('Pilihan verifikasi tidak dikenal.');
        }
        $n = 0;
        foreach (Absensi::with('penempatan')->whereIn('id', array_map('intval', $ids))->get() as $a) {
            if (Akses::pembimbingDari($u, $a->penempatan)) {
                $a->update(['verifikasi' => $hasil, 'diverifikasi_oleh' => $u->id, 'perlu_dicek' => $hasil === 'ok' ? false : $a->perlu_dicek]);
                $n++;
            }
        }
        if ($n) {
            Audit::catat('Verifikasi absensi PKL', $u->nama, "$n data: $hasil");
        }

        return $n;
    }

    /** Koreksi oleh pengelola (mis. siswa lupa absen, HP rusak). */
    public static function koreksi(User $u, Penempatan $p, string $tanggal, string $status, string $keterangan): Absensi
    {
        if (! $u->isPengelola()) {
            throw new UserError('Koreksi absensi hanya oleh admin/koordinator PKL.');
        }
        if (! Dates::valid($tanggal) || Dates::isFuture($tanggal)) {
            throw new UserError('Tanggal tidak valid atau di masa depan.');
        }
        if ($tanggal < $p->mulai->format('Y-m-d') || $tanggal > $p->selesai->format('Y-m-d')) {
            throw new UserError('Tanggal di luar masa penempatan siswa ini.');
        }
        if (! isset(Absensi::STATUS[$status])) {
            throw new UserError('Status tidak dikenal.');
        }
        $row = Absensi::firstOrNew(['penempatan_id' => $p->id, 'tanggal' => $tanggal]);
        $row->fill(['status' => $status, 'keterangan' => mb_substr(trim($keterangan), 0, 500) ?: 'Koreksi koordinator', 'sumber' => 'koreksi',
            'verifikasi' => 'ok', 'diverifikasi_oleh' => $u->id, 'perlu_dicek' => false])->save();
        Audit::catat('Koreksi absensi PKL', $u->nama, "Penempatan #{$p->id} $tanggal = $status");

        return $row;
    }

    /**
     * Rekap satu penempatan: hari kerja berlalu, jumlah per status, tanpa keterangan (hari kerja tanpa catatan), persen hadir.
     *
     * @return array{H:int,I:int,S:int,A:int,T:int,kosong:int,hari:int,persen:?float}
     */
    public static function rekap(Penempatan $p, ?Collection $rows = null): array
    {
        $rows ??= $p->absensi;
        $c = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0, 'T' => 0];
        foreach ($rows as $r) {
            $c[$r->status] = ($c[$r->status] ?? 0) + 1;
            $c['T'] += $r->terlambat ? 1 : 0;
        }
        $hari = self::hariKerja($p);
        $tercatat = $c['H'] + $c['I'] + $c['S'] + $c['A'];

        return $c + ['kosong' => max(0, $hari - $tercatat), 'hari' => $hari, 'persen' => $hari ? round($c['H'] / $hari * 100, 1) : null];
    }

    /** Jumlah hari kerja DUDI dari mulai penempatan sampai hari ini (atau selesai). */
    public static function hariKerja(Penempatan $p): int
    {
        $akhir = min(Dates::today(), $p->selesai->format('Y-m-d'));
        $mulai = $p->mulai->format('Y-m-d');
        if ($akhir < $mulai) {
            return 0;
        }
        $kerja = $p->dudi?->hariKerja() ?? [1, 2, 3, 4, 5, 6];
        $n = 0;
        for ($t = CarbonImmutable::parse($mulai, 'UTC'), $e = CarbonImmutable::parse($akhir, 'UTC'); $t <= $e; $t = $t->addDay()) {
            $n += in_array($t->dayOfWeekIso, $kerja, true) ? 1 : 0;
        }

        return $n;
    }
}
