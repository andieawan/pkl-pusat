<?php

namespace Tests\Feature;

use App\Services\Sambungan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Sambungan antar-aplikasi diatur dari Pusat (Administrasi → Sambungan): diambil lewat API, disimpan terenkripsi, menimpa .env. */
class SambunganPusatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        @unlink(Sambungan::berkas());
        config(['pusat.enabled' => true, 'pusat.url' => 'https://p.test', 'pusat.client_id' => 'c', 'pusat.client_secret' => 's', 'pusat.webhook_secret' => 'wh']);
    }

    protected function tearDown(): void
    {
        @unlink(Sambungan::berkas());
        parent::tearDown();
    }

    private function kirim(string $event, array $data, string $id)
    {
        $badan = json_encode(['event' => $event, 'data' => $data]);
        $t = time();

        return $this->call('POST', '/api/pusat/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_X_PUSAT_EVENT' => $event, 'HTTP_X_PUSAT_DELIVERY' => $id,
            'HTTP_X_PUSAT_TIMESTAMP' => $t, 'HTTP_X_PUSAT_SIGNATURE' => 'sha256='.hash_hmac('sha256', $t.'.'.$badan, 'wh'),
        ], $badan);
    }

    private function pusat(array $data, int $status = 200): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory); // ganti balasan palsu sebelumnya
        Http::fake(['p.test/api/v1/sambungan' => Http::response(['data' => $data], $status)]);
    }

    private function s(string $jenis, bool $aktif, string $url = '', string $kunci = ''): array
    {
        return ['jenis' => $jenis, 'aktif' => $aktif, 'peran' => 'x', 'mitra' => 'm', 'url' => $url, 'kunci' => $kunci];
    }

    public function test_webhook_mengambil_sambungan_dan_menerapkannya(): void
    {
        config(['pkl.api_kunci' => [], 'pkl.tu.url' => '', 'pkl.tu.api_key' => '', 'pkl.mitra_publik' => true]);
        $bk = str_repeat('b', 40);
        $this->pusat([$this->s('pkl-bk', true, 'https://bk.test', $bk), $this->s('pkl-tu', true, 'https://tu.test', str_repeat('t', 40)), $this->s('pkl-web', false)]);
        $this->getJson('/api/v1/ping', ['Authorization' => 'Bearer '.$bk])->assertStatus(401);

        $this->kirim('sambungan.diperbarui', ['jenis' => 'pkl-bk'], 'd1')->assertOk();
        $this->getJson('/api/v1/ping', ['Authorization' => 'Bearer '.$bk])->assertOk()->assertJsonPath('data.klien', 'bk');
        $this->assertSame('https://tu.test', config('pkl.tu.url'));
        $this->assertSame(str_repeat('t', 40), config('pkl.tu.api_key'));
        $this->getJson('/api/publik/mitra')->assertNotFound();
        $this->assertStringNotContainsString($bk, (string) file_get_contents(Sambungan::berkas())); // terenkripsi

        // Saat aplikasi dimulai ulang, nilai dari berkas tetap dipakai (menimpa .env)
        config(['pkl.api_kunci' => ['bk' => 'kunci-lama-dari-env-123456'], 'pkl.tu.url' => '']);
        Sambungan::terapkan();
        $this->assertSame($bk, config('pkl.api_kunci.bk'));
        $this->assertSame('https://tu.test', config('pkl.tu.url'));

        // Diputus di Pusat → kunci tidak berlaku lagi
        $this->pusat([$this->s('pkl-bk', false), $this->s('pkl-tu', false), $this->s('pkl-web', true)]);
        $this->kirim('sambungan.diperbarui', ['jenis' => 'pkl-bk'], 'd2')->assertOk();
        $this->getJson('/api/v1/ping', ['Authorization' => 'Bearer '.$bk])->assertStatus(401);
        $this->assertSame('', config('pkl.tu.url'));
        $this->assertTrue(config('pkl.mitra_publik'));
    }

    public function test_pusat_gagal_webhook_tidak_dicatat_agar_dikirim_ulang(): void
    {
        $this->pusat([], 500);
        $this->kirim('sambungan.diperbarui', ['jenis' => 'pkl-bk'], 'd9')->assertStatus(500);
        $this->assertSame(0, DB::table('pusat_webhook')->count());
        $this->assertFileDoesNotExist(Sambungan::berkas());
    }

    public function test_tanpa_pengaturan_pusat_env_tetap_dipakai(): void
    {
        config(['pkl.tu.url' => 'https://tu-env.test']);
        Sambungan::terapkan();
        $this->assertSame('https://tu-env.test', config('pkl.tu.url'));
    }
}
