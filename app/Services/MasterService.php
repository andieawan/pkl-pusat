<?php

namespace App\Services;

use App\Exceptions\UserError;
use App\Models\Dudi;
use App\Models\KomponenNilai;
use App\Models\PembimbingIndustri;
use App\Models\Penempatan;
use App\Models\Periode;
use App\Models\Siswa;
use App\Models\User;
use App\Support\Dates;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Data master PKL yang dikelola admin/koordinator: DUDI, pembimbing industri (+akun), periode, penempatan, komponen nilai. */
class MasterService
{
    private static function teks(array $in, string $k, int $max = 191): ?string
    {
        $v = mb_substr(trim((string) ($in[$k] ?? '')), 0, $max);

        return $v === '' ? null : $v;
    }

    private static function jam(array $in, string $k, string $bawaan): string
    {
        $v = (string) ($in[$k] ?? '');

        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $v) ? $v : $bawaan;
    }

    public static function simpanDudi(User $u, ?Dudi $d, array $in): Dudi
    {
        $nama = self::teks($in, 'nama') ?? throw new UserError('Nama DUDI wajib diisi.');
        $lat = $in['lat'] ?? null;
        $lng = $in['lng'] ?? null;
        $adaLokasi = is_numeric($lat) && is_numeric($lng);
        if (($lat !== null && $lat !== '' || $lng !== null && $lng !== '') && ! $adaLokasi) {
            throw new UserError('Koordinat harus angka (salin dari Google Maps, mis. -8.1567, 113.7643).');
        }
        if ($adaLokasi && (abs((float) $lat) > 90 || abs((float) $lng) > 180)) {
            throw new UserError('Koordinat di luar jangkauan.');
        }
        $hari = array_values(array_unique(array_filter(array_map('intval', (array) ($in['hari_kerja'] ?? [])), fn ($h) => $h >= 1 && $h <= 7)));
        sort($hari);
        $data = [
            'nama' => $nama, 'bidang' => self::teks($in, 'bidang'), 'alamat' => self::teks($in, 'alamat', 500),
            'kontak_nama' => self::teks($in, 'kontak_nama'), 'telp' => self::teks($in, 'telp', 30), 'email' => self::teks($in, 'email'),
            'lat' => $adaLokasi ? (float) $lat : null, 'lng' => $adaLokasi ? (float) $lng : null,
            'radius' => max(20, min(5000, (int) ($in['radius'] ?? config('pkl.radius_bawaan', 200)) ?: (int) config('pkl.radius_bawaan', 200))),
            'jam_masuk' => self::jam($in, 'jam_masuk', '08:00'), 'jam_pulang' => self::jam($in, 'jam_pulang', '16:00'),
            'hari_kerja' => $hari ?: [1, 2, 3, 4, 5, 6], 'kuota' => is_numeric($in['kuota'] ?? null) ? max(0, (int) $in['kuota']) : null,
            'aktif' => ! empty($in['aktif']) || $d === null,
        ];
        $d ? $d->update($data) : ($d = Dudi::create($data));
        Audit::catat('Simpan DUDI', $u->nama, $nama);

        return $d;
    }

    /**
     * Simpan pembimbing industri; buat/atur akun login lokalnya bila diminta.
     *
     * @return array{0:PembimbingIndustri,1:?string} [pembimbing, password polos baru (tampilkan sekali) atau null]
     */
    public static function simpanPembimbing(User $u, ?PembimbingIndustri $pi, array $in): array
    {
        $dudi = Dudi::find((int) ($in['dudi_id'] ?? 0)) ?? throw new UserError('Pilih DUDI.');
        $nama = self::teks($in, 'nama') ?? throw new UserError('Nama pembimbing wajib diisi.');
        $data = ['dudi_id' => $dudi->id, 'nama' => $nama, 'jabatan' => self::teks($in, 'jabatan'), 'telp' => self::teks($in, 'telp', 30)];

        return DB::transaction(function () use ($u, $pi, $in, $data) {
            $pi ? $pi->update($data) : ($pi = PembimbingIndustri::create($data));
            $password = null;
            $username = strtolower(trim((string) ($in['username'] ?? '')));
            if ($username !== '' || ! empty($in['reset_password'])) {
                [$akun, $password] = self::akunIndustri($pi, $username, ! empty($in['reset_password']));
                $pi->update(['user_id' => $akun->id]);
            }
            Audit::catat('Simpan pembimbing industri', $u->nama, $pi->nama.($password ? ' (akun/password baru)' : ''));

            return [$pi, $password];
        });
    }

    /** @return array{0:User,1:?string} */
    private static function akunIndustri(PembimbingIndustri $pi, string $username, bool $reset): array
    {
        $akun = $pi->user;
        if ($username !== '' && ! preg_match('/^[a-z0-9._-]{3,60}$/', $username)) {
            throw new UserError('Username 3–60 karakter: huruf kecil, angka, titik, garis bawah, atau strip.');
        }
        if ($username !== '' && User::where('username', $username)->when($akun, fn ($q) => $q->where('id', '!=', $akun->id))->exists()) {
            throw new UserError("Username \"$username\" sudah dipakai.");
        }
        $password = null;
        if (! $akun) {
            if ($username === '') {
                throw new UserError('Isi username untuk membuat akun login pembimbing industri.');
            }
            $password = (string) config('pkl.password_awal') ?: Str::password(10, symbols: false);
            $akun = User::create(['username' => $username, 'nama' => $pi->nama, 'roles' => ['industri'], 'aktif' => true, 'password' => $password]);
        } else {
            $upd = ['nama' => $pi->nama];
            if ($username !== '') {
                $upd['username'] = $username;
            }
            if ($reset) {
                $password = (string) config('pkl.password_awal') ?: Str::password(10, symbols: false);
                $upd['password'] = $password;
            }
            $akun->update($upd);
        }

        return [$akun, $password];
    }

    public static function simpanPeriode(User $u, ?Periode $p, array $in): Periode
    {
        $nama = self::teks($in, 'nama') ?? throw new UserError('Nama periode wajib diisi (mis. PKL 2026/2027 Gelombang 1).');
        $mulai = (string) ($in['mulai'] ?? '');
        $selesai = (string) ($in['selesai'] ?? '');
        if (! Dates::valid($mulai) || ! Dates::valid($selesai) || $selesai < $mulai) {
            throw new UserError('Tanggal mulai/selesai tidak valid.');
        }
        $data = ['nama' => $nama, 'mulai' => $mulai, 'selesai' => $selesai, 'aktif' => ! empty($in['aktif']) || $p === null];
        $p ? $p->update($data) : ($p = Periode::create($data));
        Audit::catat('Simpan periode PKL', $u->nama, $nama);

        return $p;
    }

    /**
     * Tempatkan satu atau banyak siswa sekaligus di satu DUDI. Siswa yang sudah punya penempatan di periode yang sama dilewati.
     *
     * @return array{dibuat:int, dilewati:int}
     */
    public static function tempatkan(User $u, array $in): array
    {
        $periode = Periode::find((int) ($in['periode_id'] ?? 0)) ?? throw new UserError('Pilih periode.');
        $dudi = Dudi::find((int) ($in['dudi_id'] ?? 0)) ?? throw new UserError('Pilih DUDI.');
        $guruId = (int) ($in['guru_id'] ?? 0) ?: null;
        $piId = (int) ($in['pembimbing_industri_id'] ?? 0) ?: null;
        if ($piId && ! PembimbingIndustri::where('id', $piId)->where('dudi_id', $dudi->id)->exists()) {
            throw new UserError('Pembimbing industri tidak berasal dari DUDI ini.');
        }
        $ids = array_values(array_unique(array_map('intval', (array) ($in['siswa'] ?? []))));
        if (! $ids) {
            throw new UserError('Pilih minimal satu siswa.');
        }
        $mulai = Dates::valid($in['mulai'] ?? '') ? $in['mulai'] : $periode->mulai->format('Y-m-d');
        $selesai = Dates::valid($in['selesai'] ?? '') ? $in['selesai'] : $periode->selesai->format('Y-m-d');
        if ($selesai < $mulai) {
            throw new UserError('Tanggal selesai sebelum tanggal mulai.');
        }
        $h = ['dibuat' => 0, 'dilewati' => 0];
        DB::transaction(function () use ($ids, $periode, $dudi, $guruId, $piId, $mulai, $selesai, &$h) {
            foreach (Siswa::aktif()->whereIn('id', $ids)->get() as $s) {
                if (Penempatan::where('periode_id', $periode->id)->where('siswa_id', $s->id)->exists()) {
                    $h['dilewati']++;

                    continue;
                }
                Penempatan::create(['periode_id' => $periode->id, 'siswa_id' => $s->id, 'dudi_id' => $dudi->id, 'guru_id' => $guruId,
                    'pembimbing_industri_id' => $piId, 'mulai' => $mulai, 'selesai' => $selesai, 'status' => 'aktif']);
                $h['dibuat']++;
            }
        });
        Audit::catat('Penempatan PKL', $u->nama, "{$dudi->nama}: {$h['dibuat']} siswa");

        return $h;
    }

    public static function ubahPenempatan(User $u, Penempatan $p, array $in): Penempatan
    {
        $dudi = Dudi::find((int) ($in['dudi_id'] ?? $p->dudi_id)) ?? throw new UserError('Pilih DUDI.');
        $piId = (int) ($in['pembimbing_industri_id'] ?? 0) ?: null;
        if ($piId && ! PembimbingIndustri::where('id', $piId)->where('dudi_id', $dudi->id)->exists()) {
            throw new UserError('Pembimbing industri tidak berasal dari DUDI ini.');
        }
        $status = (string) ($in['status'] ?? $p->status);
        if (! isset(Penempatan::STATUS[$status])) {
            throw new UserError('Status tidak dikenal.');
        }
        $mulai = Dates::valid($in['mulai'] ?? '') ? $in['mulai'] : $p->mulai->format('Y-m-d');
        $selesai = Dates::valid($in['selesai'] ?? '') ? $in['selesai'] : $p->selesai->format('Y-m-d');
        if ($selesai < $mulai) {
            throw new UserError('Tanggal selesai sebelum tanggal mulai.');
        }
        $p->update(['dudi_id' => $dudi->id, 'guru_id' => (int) ($in['guru_id'] ?? 0) ?: null, 'pembimbing_industri_id' => $piId,
            'mulai' => $mulai, 'selesai' => $selesai, 'status' => $status, 'catatan' => self::teks($in, 'catatan', 500)]);
        Audit::catat('Ubah penempatan PKL', $u->nama, "#{$p->id} {$dudi->nama} ($status)");

        return $p;
    }

    /** Simpan semua komponen nilai (nama, sumber, bobot, aktif). Total bobot aktif wajib 100. */
    public static function simpanKomponen(User $u, array $rows): void
    {
        $total = 0;
        $bersih = [];
        foreach ($rows as $key => $r) {
            $nama = mb_substr(trim((string) ($r['nama'] ?? '')), 0, 191);
            if ($nama === '') {
                continue;
            }
            $sumber = in_array($r['sumber'] ?? '', ['industri', 'sekolah'], true) ? $r['sumber'] : 'sekolah';
            $bobot = max(0, min(100, (int) ($r['bobot'] ?? 0)));
            $aktif = ! empty($r['aktif']);
            $total += $aktif ? $bobot : 0;
            $bersih[] = [is_numeric($key) && (int) $key > 0 ? (int) $key : null, ['nama' => $nama, 'sumber' => $sumber, 'bobot' => $bobot, 'aktif' => $aktif]];
        }
        if ($total !== 100) {
            throw new UserError("Total bobot komponen aktif harus 100 (sekarang $total).");
        }
        DB::transaction(function () use ($bersih) {
            foreach ($bersih as $i => [$id, $data]) {
                $data['urutan'] = $i + 1;
                $id && ($k = KomponenNilai::find($id)) ? $k->update($data) : KomponenNilai::create($data);
            }
        });
        Audit::catat('Ubah komponen nilai PKL', $u->nama, count($bersih).' komponen');
    }
}
