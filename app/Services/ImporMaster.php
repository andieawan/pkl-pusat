<?php

namespace App\Services;

use App\Exceptions\UserError;
use App\Models\Dudi;
use App\Models\PembimbingIndustri;
use App\Models\User;
use App\Support\Xlsx;
use Illuminate\Support\Facades\DB;

/**
 * Impor data PKL lama dari templat Excel: lembar DUDI dan PEMBIMBING INDUSTRI (satu baris = satu data).
 * Tiap baris disimpan lewat MasterService (aturan sama dengan form), di dalam satu transaksi:
 * bila ada satu baris gagal, semuanya dibatalkan dan daftar kesalahan ditampilkan. "Periksa saja" = selalu dibatalkan.
 * DUDI dengan nama yang sudah ada, dan pembimbing dengan nama yang sama di DUDI yang sama, dilewati (aman diulang).
 */
class ImporMaster
{
    public const DUDI = ['nama' => 'Nama DUDI', 'bidang' => 'Bidang', 'alamat' => 'Alamat', 'kontak_nama' => 'Nama Kontak', 'telp' => 'Telepon',
        'email' => 'Email', 'lat' => 'Lintang', 'lng' => 'Bujur', 'radius' => 'Radius (m)', 'jam_masuk' => 'Jam Masuk', 'jam_pulang' => 'Jam Pulang',
        'hari_kerja' => 'Hari Kerja', 'kuota' => 'Kuota'];

    public const PEMBIMBING = ['dudi' => 'Nama DUDI', 'nama' => 'Nama Pembimbing', 'jabatan' => 'Jabatan', 'telp' => 'Telepon', 'username' => 'Username Login'];

    private const HARI = ['senin' => 1, 'selasa' => 2, 'rabu' => 3, 'kamis' => 4, 'jumat' => 5, "jum'at" => 5, 'sabtu' => 6, 'minggu' => 7, 'ahad' => 7];

    public static function templat(): array
    {
        $petunjuk = [['PETUNJUK IMPOR DATA PKL LAMA'], [''],
            ['1. Isi lembar DUDI lalu PEMBIMBING INDUSTRI mulai baris ke-2. Jangan ubah judul kolom dan nama lembar.'],
            ['2. DUDI wajib: Nama DUDI. Lintang/Bujur: salin dari Google Maps (mis. -8.1567 dan 113.7643); kosong = tanpa cek lokasi absen.'],
            ['3. Radius kosong = '.(int) config('pkl.radius_bawaan', 200).' m. Jam format 08:00. Hari Kerja: mis. "Senin-Sabtu", "Senin, Rabu, Jumat", atau "1-5" (kosong = Senin–Sabtu).'],
            ['4. PEMBIMBING INDUSTRI wajib: Nama DUDI (persis seperti di lembar DUDI atau yang sudah ada di aplikasi) dan Nama Pembimbing.'],
            ['5. Username Login (opsional): diisi bila pembimbing perlu akun untuk memeriksa jurnal/absen siswa. Password dibuat otomatis dan ditampilkan SEKALI setelah impor.'],
            ['6. DUDI dengan nama yang sudah ada, dan pembimbing yang sudah ada di DUDI yang sama, dilewati.'],
            ['7. Bila ada satu baris salah, tidak ada yang disimpan. Pakai "Periksa saja" dulu.']];

        return ['PETUNJUK' => ['rows' => $petunjuk, 'tebal' => [0]], 'DUDI' => [array_values(self::DUDI)], 'PEMBIMBING INDUSTRI' => [array_values(self::PEMBIMBING)]];
    }

    /** @return array{dudi:int, pembimbing:int, dilewati:int, akun:list<array{nama:string,username:string,password:string}>} */
    public static function proses(User $u, string $path, string $nama, bool $uji): array
    {
        $lembar = [];
        $namaLembar = self::namaLembar($path, $nama);
        foreach (Xlsx::readAllSheets($path, $nama) as $i => $rows) {
            $lembar[$namaLembar[$i] ?? ''] = $rows;
        }
        if (! isset($lembar['DUDI']) && ! isset($lembar['PEMBIMBING INDUSTRI'])) {
            throw new UserError('Lembar DUDI / PEMBIMBING INDUSTRI tidak ditemukan. Pakai templat dari halaman ini.');
        }
        $h = ['dudi' => 0, 'pembimbing' => 0, 'dilewati' => 0, 'akun' => []];
        $galat = [];
        DB::beginTransaction();
        try {
            $dudi = Dudi::get(['id', 'nama'])->keyBy(fn ($d) => self::kunci($d->nama));
            foreach (self::baris($lembar['DUDI'] ?? [], self::DUDI, 'DUDI') as [$baris, $r]) {
                if ($r['nama'] === '') {
                    $galat[] = "$baris: Nama DUDI kosong.";

                    continue;
                }
                if (isset($dudi[self::kunci($r['nama'])])) {
                    $h['dilewati']++;

                    continue;
                }
                $hari = self::hari($r['hari_kerja']);
                if ($hari === null) {
                    $galat[] = "$baris ({$r['nama']}): Hari Kerja \"{$r['hari_kerja']}\" tidak dikenali.";

                    continue;
                }
                foreach (['jam_masuk', 'jam_pulang'] as $k) {
                    if ($r[$k] !== '' && ! preg_match('/^([01]?\d|2[0-3])[:.][0-5]\d$/', $r[$k])) {
                        $galat[] = "$baris ({$r['nama']}): ".self::DUDI[$k]." \"{$r[$k]}\" harus berformat 08:00.";

                        continue 2;
                    }
                    $r[$k] = $r[$k] === '' ? '' : str_pad(str_replace('.', ':', $r[$k]), 5, '0', STR_PAD_LEFT);
                }
                if ($r['kuota'] !== '' && ! ctype_digit($r['kuota'])) {
                    $galat[] = "$baris ({$r['nama']}): Kuota harus angka.";

                    continue;
                }
                try {
                    $d = MasterService::simpanDudi($u, null, ['hari_kerja' => $hari, 'radius' => $r['radius'] ?: null, 'aktif' => 1] + $r);
                    $dudi[self::kunci($d->nama)] = $d;
                    $h['dudi']++;
                } catch (UserError $e) {
                    $galat[] = "$baris ({$r['nama']}): ".$e->getMessage();
                }
            }

            $usernames = [];
            foreach (self::baris($lembar['PEMBIMBING INDUSTRI'] ?? [], self::PEMBIMBING, 'PEMBIMBING INDUSTRI') as [$baris, $r]) {
                $d = $dudi[self::kunci($r['dudi'])] ?? null;
                $username = strtolower($r['username']);
                $err = match (true) {
                    $r['nama'] === '' => 'Nama Pembimbing kosong',
                    ! $d => "DUDI \"{$r['dudi']}\" tidak ada di lembar DUDI maupun di aplikasi",
                    $username !== '' && isset($usernames[$username]) => "username \"$username\" dipakai dua kali di berkas",
                    default => null,
                };
                if ($err) {
                    $galat[] = "$baris: $err.";

                    continue;
                }
                if (PembimbingIndustri::where('dudi_id', $d->id)->get(['nama'])->contains(fn ($p) => self::kunci($p->nama) === self::kunci($r['nama']))) {
                    $h['dilewati']++;

                    continue;
                }
                $usernames[$username] = true;
                try {
                    [$pi, $password] = MasterService::simpanPembimbing($u, null, ['dudi_id' => $d->id, 'nama' => $r['nama'], 'jabatan' => $r['jabatan'], 'telp' => $r['telp'], 'username' => $username]);
                    $h['pembimbing']++;
                    if ($password) {
                        $h['akun'][] = ['nama' => $pi->nama, 'username' => $username, 'password' => $password];
                    }
                } catch (UserError $e) {
                    $galat[] = "$baris ({$r['nama']}): ".$e->getMessage();
                }
            }

            if ($galat) {
                throw new UserError(count($galat).' baris bermasalah — belum ada yang disimpan. Perbaiki lalu unggah lagi.'."\n".implode("\n", $galat));
            }
            if (! $h['dudi'] && ! $h['pembimbing'] && ! $h['dilewati']) {
                throw new UserError('Tidak ada data yang terisi. Isi lembar DUDI / PEMBIMBING INDUSTRI di templat.');
            }
            $uji ? DB::rollBack() : DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
        if ($uji) {
            $h['akun'] = []; // password belum dibuat sungguhan
        } else {
            Audit::catat('Impor data PKL lama', $u->nama, "{$h['dudi']} DUDI, {$h['pembimbing']} pembimbing industri".($h['dilewati'] ? ", {$h['dilewati']} sudah ada" : ''));
        }

        return $h;
    }

    /** Baris berisi pada satu lembar → [label baris, kolom => nilai]. */
    private static function baris(array $rows, array $kolom, string $lembar): \Generator
    {
        if (! $rows) {
            return;
        }
        $hdr = array_map(fn ($v) => mb_strtolower(trim((string) $v)), array_shift($rows));
        $idx = array_map(fn ($j) => array_search(mb_strtolower($j), $hdr, true), $kolom);
        if ($idx[array_key_first($kolom)] === false) {
            throw new UserError("Lembar $lembar: kolom \"".reset($kolom).'" tidak ditemukan. Pakai templat dari halaman ini.');
        }
        foreach ($rows as $n => $r) {
            if (trim(implode('', array_map('strval', $r))) === '') {
                continue;
            }
            yield ["$lembar baris ".($n + 2), array_map(fn ($i) => $i === false ? '' : trim((string) ($r[$i] ?? '')), $idx)];
        }
    }

    private static function kunci(string $v): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim($v)));
    }

    /** "Senin-Sabtu", "Senin, Rabu", "1-5", "1,3,5" → [1..7]; kosong → []; tidak dikenal → null. */
    public static function hari(string $v): ?array
    {
        $v = mb_strtolower(trim($v));
        if ($v === '') {
            return [];
        }
        $angka = fn (string $x) => ctype_digit($x) && (int) $x >= 1 && (int) $x <= 7 ? (int) $x : (self::HARI[$x] ?? null);
        $hasil = [];
        foreach (preg_split('/\s*[,;\/]\s*|\s+dan\s+/u', $v) as $bagian) {
            $ujung = preg_split('/\s*(?:-|–|s\.?\s*d\.?|sampai)\s*/u', $bagian);
            $a = $angka($ujung[0]);
            $b = $angka(end($ujung));
            if ($a === null || $b === null || $b < $a) {
                return null;
            }
            $hasil = array_merge($hasil, range($a, $b));
        }

        return array_values(array_unique($hasil));
    }

    /** Nama lembar .xlsx sesuai urutan readAllSheets. */
    private static function namaLembar(string $path, string $nama): array
    {
        if (strtolower(pathinfo($nama, PATHINFO_EXTENSION)) !== 'xlsx') {
            throw new UserError('Berkas harus .xlsx (templat dari halaman ini).');
        }
        $zip = new \ZipArchive;
        if ($zip->open($path) !== true) {
            return [];
        }
        $xml = (string) $zip->getFromName('xl/workbook.xml');
        $zip->close();
        preg_match_all('/<sheet\b[^>]*\bname="([^"]*)"/', $xml, $m);

        return array_map(fn ($n) => mb_strtoupper(trim(html_entity_decode($n, ENT_QUOTES | ENT_XML1, 'UTF-8'))), $m[1]);
    }
}
