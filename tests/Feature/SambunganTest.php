<?php

namespace Tests\Feature;

use App\Models\Dudi;
use App\Models\Kelas;
use App\Models\Pegawai;
use App\Models\Penempatan;
use App\Models\Periode;
use App\Models\Siswa;
use App\Models\SuratPkl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Sambungan PKL ke aplikasi lain: API untuk BK, mitra publik untuk Website, nomor surat dari TU, menu aplikasi lain. */
class SambunganTest extends TestCase
{
    use RefreshDatabase;

    private const KUNCI = 'kunci-bk-uji-1234567890abcdef';

    private User $kor;

    private Dudi $dudi;

    private Periode $per;

    protected function setUp(): void
    {
        parent::setUp();
        config(['pkl.api_kunci' => ['bk' => self::KUNCI], 'pkl.batas_perhatian' => 3]);
        $this->kor = User::create(['username' => 'kor', 'nama' => 'Kor', 'roles' => ['koordinator'], 'aktif' => true]);
        $k = Kelas::create(['pusat_uuid' => 'k1', 'nama' => 'XII TKJ 1', 'tahun_ajaran' => '2026/2027', 'tahun_aktif' => true]);
        $s = Siswa::create(['pusat_uuid' => 's1', 'nis' => '1001', 'nama' => 'Budi', 'jk' => 'L', 'kelas_id' => $k->id, 'status' => 'aktif']);
        $g = Pegawai::create(['pusat_uuid' => 'g1', 'nip' => 'g1', 'nama' => 'Guru Satu', 'jenis' => 'guru', 'aktif' => true]);
        $this->dudi = Dudi::create(['nama' => 'CV Jaringan', 'bidang' => 'Jaringan komputer', 'alamat' => 'Jl. Rahasia 1', 'telp' => '0812', 'radius' => 200,
            'jam_masuk' => '07:30', 'jam_pulang' => '00:00', 'hari_kerja' => [1, 2, 3, 4, 5, 6, 7], 'aktif' => true]);
        $this->per = Periode::create(['nama' => 'PKL Gel 1', 'mulai' => date('Y-m-d', strtotime('-10 days')), 'selesai' => date('Y-m-d', strtotime('+30 days')), 'aktif' => true]);
        Penempatan::create(['periode_id' => $this->per->id, 'siswa_id' => $s->id, 'dudi_id' => $this->dudi->id, 'guru_id' => $g->id,
            'mulai' => $this->per->mulai->format('Y-m-d'), 'selesai' => $this->per->selesai->format('Y-m-d'), 'status' => 'aktif']);
    }

    public function test_api_bk_berkunci_dan_menampilkan_siswa_perlu_perhatian(): void
    {
        $this->getJson('/api/v1/perhatian')->assertStatus(401);
        $this->getJson('/api/v1/perhatian', ['Authorization' => 'Bearer salah-salah-salah-salah'])->assertStatus(401);
        $h = ['Authorization' => 'Bearer '.self::KUNCI];
        $this->getJson('/api/v1/ping', $h)->assertOk()->assertJsonPath('data.klien', 'bk');
        $r = $this->getJson('/api/v1/perhatian', $h)->assertOk();
        $this->assertSame('Budi', $r->json('data.0.siswa.nama'));
        $this->assertSame('s1', $r->json('data.0.siswa.uuid'));
        $this->assertGreaterThanOrEqual(10, $r->json('data.0.rekap.kosong'));
        $this->assertNotEmpty($r->json('data.0.alasan'));
        $this->getJson('/api/v1/siswa/s1', $h)->assertOk()->assertJsonPath('data.0.dudi', 'CV Jaringan');
        $this->getJson('/api/v1/siswa/tidak-ada', $h)->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_api_tertutup_bila_kunci_kosong(): void
    {
        config(['pkl.api_kunci' => []]);
        $this->getJson('/api/v1/ping', ['Authorization' => 'Bearer '])->assertStatus(401);
    }

    public function test_mitra_publik_tanpa_data_pribadi(): void
    {
        $r = $this->getJson('/api/publik/mitra')->assertOk();
        $this->assertSame('CV Jaringan', $r->json('data.mitra.0.nama'));
        $this->assertSame(1, $r->json('data.mitra.0.siswa_periode_ini'));
        $teks = $r->getContent();
        foreach (['Budi', '1001', 'Rahasia', '0812', 's1'] as $pribadi) {
            $this->assertStringNotContainsString($pribadi, $teks);
        }
        config(['pkl.mitra_publik' => false]);
        \Illuminate\Support\Facades\Cache::flush();
        $this->getJson('/api/publik/mitra')->assertNotFound();
    }

    public function test_nomor_surat_dari_tu_sekali_saja(): void
    {
        config(['pkl.tu' => ['url' => 'https://tu.test', 'api_key' => 'kunci-tu', 'verify_ssl' => true, 'timeout' => 5]]);
        Http::fake(['tu.test/api/v1/surat-keluar' => Http::response(['data' => ['nomor' => '007/PKL/SMKIBU/X/2026', 'tanggal' => '2026-10-27', 'agenda' => '7/2026']], 201)]);
        $url = '/surat/pengantar?dudi='.$this->dudi->id.'&periode='.$this->per->id;
        $this->actingAs($this->kor)->get($url)->assertOk()->assertSee('Ambil nomor dari TU');
        $this->post('/surat/pengantar/nomor', ['dudi' => $this->dudi->id, 'periode' => $this->per->id])->assertSessionHas('success');
        Http::assertSent(fn ($r) => $r->hasHeader('Authorization', 'Bearer kunci-tu') && str_starts_with($r['ref'], 'pengantar:periode-') && str_contains($r['perihal'], 'CV Jaringan'));
        $this->get($url)->assertOk()->assertSee('007/PKL/SMKIBU/X/2026')->assertDontSee('Ambil nomor dari TU');
        $this->post('/surat/pengantar/nomor', ['dudi' => $this->dudi->id, 'periode' => $this->per->id]);
        Http::assertSentCount(1);
        $this->assertSame(1, SuratPkl::count());
    }

    public function test_nomor_surat_manual_bila_tu_belum_tersambung(): void
    {
        $this->actingAs($this->kor)->post('/surat/penarikan/nomor', ['dudi' => $this->dudi->id, 'periode' => $this->per->id, 'manual' => 1])->assertSessionHas('error');
        $this->post('/surat/penarikan/nomor', ['dudi' => $this->dudi->id, 'periode' => $this->per->id, 'nomor' => '012/PKL/X/2026', 'manual' => 1])->assertSessionHas('success');
        $this->assertSame('manual', SuratPkl::first()->sumber);
    }

    public function test_menu_aplikasi_lain_hanya_tautan_pusat(): void
    {
        config(['pusat.url' => 'https://pusat.test']);
        $this->actingAs($this->kor)->withSession(['aplikasi_lain' => [
            ['nama' => 'Bimbingan Konseling', 'url' => 'https://pusat.test/buka/bk'],
            ['nama' => 'Jahat', 'url' => 'https://jahat.test/x'],
        ]])->get('/')->assertOk()->assertSee('Bimbingan Konseling')->assertSee('https://pusat.test/buka/bk')->assertDontSee('jahat.test');
    }
}
