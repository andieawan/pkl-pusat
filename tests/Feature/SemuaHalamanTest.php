<?php

namespace Tests\Feature;

use App\Models\Dudi;
use App\Models\Kelas;
use App\Models\Pegawai;
use App\Models\Periode;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Jelajah semua halaman: mulai dari beranda tiap peran, ikuti setiap tautan di aplikasi, dan pastikan tidak ada
 * halaman yang galat (5xx) atau tautan yang mengarah ke halaman tidak ada (404) / terlarang (403) bagi peran itu.
 * Set JELAJAH_SIMPAN=folder untuk menyimpan HTML tiap halaman (bahan tangkapan layar).
 */
class SemuaHalamanTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{masalah: list<string>, halaman: array<string,string>} */
    private function jelajah($user, string $mulai, int $maks = 250): array
    {
        $antre = [[$mulai, '(awal)']];
        $lihat = [];
        $pola = [];
        $masalah = [];
        $halaman = [];
        while ($antre && count($lihat) < $maks) {
            [$url, $dari] = array_shift($antre);
            if (isset($lihat[$url])) {
                continue;
            }
            $lihat[$url] = true;
            $res = ($user ? $this->actingAs($user) : $this)->get($url);
            $s = $res->getStatusCode();
            if ($s >= 500 || in_array($s, [403, 404, 405], true)) {
                $e = $res->exception;
                $masalah[] = "$s $url (dari $dari)".($e ? ' — '.class_basename($e).': '.mb_substr($e->getMessage(), 0, 200).' @ '.str_replace(base_path().'/', '', $e->getFile()).':'.$e->getLine() : '');

                continue;
            }
            if ($s >= 300 && $s < 400) {
                $loc = $this->lokal((string) $res->headers->get('Location'));
                $loc && ! isset($lihat[$loc]) && $antre[] = [$loc, $url];

                continue;
            }
            if (! str_contains((string) $res->headers->get('Content-Type'), 'html')) {
                continue;
            }
            $html = (string) $res->getContent();
            $halaman[$url] = $html;
            preg_match_all('/<a\b[^>]*\bhref="([^"]+)"/i', $html, $m);
            foreach ($m[1] as $h) {
                $h = $this->lokal(html_entity_decode($h));
                if (! $h || isset($lihat[$h]) || preg_match('#/(logout|sso/|unduh|download)|^/keluar$#', $h)) {
                    continue;
                }
                $p = preg_replace(['/\d+/', '/\?.*/'], ['N', '?'], $h); // batasi variasi id/kueri per pola
                if (($pola[$p] = ($pola[$p] ?? 0) + 1) <= 3) {
                    $antre[] = [$h, $url];
                }
            }
        }

        return ['masalah' => $masalah, 'halaman' => $halaman];
    }

    private function lokal(string $h): ?string
    {
        $h = trim($h);
        foreach ([rtrim(url('/'), '/'), rtrim((string) config('app.url'), '/'), 'http://localhost', 'https://localhost'] as $akar) {
            if ($akar !== '' && str_starts_with($h, $akar)) {
                $h = substr($h, strlen($akar)) ?: '/';
                break;
            }
        }
        if ($h === '' || $h[0] !== '/' || str_starts_with($h, '//')) {
            return null;
        }

        return strtok($h, '#') ?: null;
    }

    private function simpan(string $peran, array $halaman): void
    {
        if (! ($dir = getenv('JELAJAH_SIMPAN'))) {
            return;
        }
        @mkdir("$dir/$peran", 0777, true);
        foreach ($halaman as $url => $html) {
            file_put_contents("$dir/$peran/".(trim(preg_replace('/[^a-z0-9]+/i', '_', $url), '_') ?: 'beranda').'.html', $html);
        }
    }

    public function test_semua_halaman_bisa_dibuka_tiap_peran(): void
    {
        Storage::fake('local');
        $mk = fn ($u, $roles, $x = []) => User::create(['username' => $u, 'nama' => ucfirst($u), 'roles' => $roles, 'aktif' => true] + $x);
        $kor = $mk('kor', ['koordinator']);
        $k = Kelas::create(['pusat_uuid' => 'k1', 'nama' => 'XII TKJ 1', 'tahun_ajaran' => '2026/2027', 'tahun_aktif' => true]);
        $s1 = Siswa::create(['pusat_uuid' => 's1', 'nis' => '1001', 'nama' => 'Budi Santoso', 'jk' => 'L', 'kelas_id' => $k->id, 'status' => 'aktif']);
        $s2 = Siswa::create(['pusat_uuid' => 's2', 'nis' => '1002', 'nama' => 'Ani Lestari', 'jk' => 'P', 'kelas_id' => $k->id, 'status' => 'aktif']);
        $g = Pegawai::create(['pusat_uuid' => 'g1', 'nip' => 'g1', 'nama' => 'Guru Pembimbing', 'jenis' => 'guru', 'aktif' => true]);
        $this->actingAs($kor)->post('/dudi', ['nama' => 'CV Jaringan Nusantara', 'lat' => '-8.1567', 'lng' => '113.7643', 'radius' => 150,
            'jam_masuk' => '07:30', 'jam_pulang' => '15:00', 'hari_kerja' => [1, 2, 3, 4, 5]]);
        $this->post('/periode', ['nama' => 'PKL Gelombang 1', 'mulai' => date('Y-m-d', strtotime('-10 days')), 'selesai' => date('Y-m-d', strtotime('+60 days'))]);
        $this->post('/penempatan', ['periode_id' => Periode::value('id'), 'dudi_id' => Dudi::value('id'), 'guru_id' => $g->id, 'siswa' => [$s1->id, $s2->id]]);
        $peran = [
            'koordinator' => $kor,
            'admin' => $mk('admin', ['admin']),
            'guru' => $mk('guru', ['guru_pembimbing'], ['pegawai_uuid' => 'g1']),
            'siswa' => $mk('budi', ['siswa'], ['siswa_uuid' => 's1']),
        ];
        $semua = [];
        foreach ($peran as $nama => $u) {
            $h = $this->jelajah($u, '/');
            $this->simpan($nama, $h['halaman']);
            $this->assertGreaterThan(0, count($h['halaman']), "$nama tidak membuka satu halaman pun");
            foreach ($h['masalah'] as $m) {
                $semua[] = "[$nama] $m";
            }
        }
        $this->app["auth"]->forgetGuards();
        $h = $this->jelajah(null, '/login', 5); // halaman masuk untuk tamu
        $this->simpan('tamu', $h['halaman']);
        foreach ($h['masalah'] as $m) {
            $semua[] = "[tamu] $m";
        }
        $this->assertSame([], $semua, "Halaman bermasalah:\n".implode("\n", $semua));
    }
}
