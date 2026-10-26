<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Dudi;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\KomponenNilai;
use App\Models\Pegawai;
use App\Models\PembimbingIndustri;
use App\Models\Penempatan;
use App\Models\Periode;
use App\Models\Siswa;
use App\Models\User;
use App\Services\NilaiService;
use App\Support\Dates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PklTest extends TestCase
{
    use RefreshDatabase;

    private User $kor;

    private User $guru;

    private User $budi;

    private Siswa $s1;

    private Siswa $s2;

    private Dudi $dudi;

    private Penempatan $p1;

    private Penempatan $p2;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $mk = fn ($u, $roles, $x = []) => User::create(['username' => $u, 'nama' => ucfirst($u), 'roles' => $roles, 'aktif' => true] + $x);
        $this->kor = $mk('kor', ['koordinator']);
        $this->guru = $mk('guru', ['guru'], ['pegawai_uuid' => 'g1']);
        $this->budi = $mk('budi', ['siswa'], ['siswa_uuid' => 's1']);
        $mk('ani', ['siswa'], ['siswa_uuid' => 's2']);
        $k = Kelas::create(['pusat_uuid' => 'k1', 'nama' => 'XII TKJ 1', 'tahun_ajaran' => '2026/2027', 'tahun_aktif' => true]);
        $this->s1 = Siswa::create(['pusat_uuid' => 's1', 'nis' => '1001', 'nama' => 'Budi', 'jk' => 'L', 'kelas_id' => $k->id, 'status' => 'aktif']);
        $this->s2 = Siswa::create(['pusat_uuid' => 's2', 'nis' => '1002', 'nama' => 'Ani', 'jk' => 'P', 'kelas_id' => $k->id, 'status' => 'aktif']);
        $g = Pegawai::create(['pusat_uuid' => 'g1', 'nip' => 'g1', 'nama' => 'Guru Satu', 'jenis' => 'guru', 'aktif' => true]);

        $this->actingAs($this->kor)->post('/dudi', ['nama' => 'CV Jaringan', 'lat' => '-8.1567', 'lng' => '113.7643', 'radius' => 150,
            'jam_masuk' => '07:30', 'jam_pulang' => '15:00', 'hari_kerja' => [1, 2, 3, 4, 5, 6, 7]])->assertRedirect();
        $this->dudi = Dudi::firstOrFail();
        $this->post('/periode', ['nama' => 'PKL Gel 1', 'mulai' => date('Y-m-d', strtotime('-10 days')), 'selesai' => date('Y-m-d', strtotime('+60 days'))]);
        $this->post('/penempatan', ['periode_id' => Periode::value('id'), 'dudi_id' => $this->dudi->id, 'guru_id' => $g->id,
            'siswa' => [$this->s1->id, $this->s2->id]])->assertSessionHas('success');
        $this->p1 = Penempatan::where('siswa_id', $this->s1->id)->firstOrFail();
        $this->p2 = Penempatan::where('siswa_id', $this->s2->id)->firstOrFail();
    }

    public function test_penempatan_tidak_ganda_dalam_satu_periode(): void
    {
        $this->post('/penempatan', ['periode_id' => Periode::value('id'), 'dudi_id' => $this->dudi->id, 'siswa' => [$this->s1->id]])
            ->assertSessionHas('success', fn ($m) => str_contains($m, '1 siswa dilewati'));
        $this->assertSame(2, Penempatan::count());
    }

    public function test_absen_masuk_wajib_selfie_dan_menghitung_jarak(): void
    {
        $this->actingAs($this->budi)->post('/saya/absen', ['aksi' => 'masuk', 'lat' => '-8.1567', 'lng' => '113.7643'])->assertSessionHas('error');
        $this->post('/saya/absen', ['aksi' => 'masuk', 'lat' => '-8.1570', 'lng' => '113.7645', 'foto' => UploadedFile::fake()->image('a.jpg', 800, 600)])
            ->assertSessionHas('success');
        $a = Absensi::firstOrFail();
        $this->assertSame('H', $a->status);
        $this->assertLessThan(150, $a->jarak);
        $this->assertFalse($a->perlu_dicek);
        Storage::disk('local')->assertExists($a->foto_masuk);
        $this->post('/saya/absen', ['aksi' => 'masuk', 'foto' => UploadedFile::fake()->image('b.jpg')])->assertSessionHas('error');
    }

    public function test_absen_di_luar_radius_atau_tanpa_lokasi_ditandai_perlu_dicek(): void
    {
        $this->actingAs($this->budi)->post('/saya/absen', ['aksi' => 'masuk', 'lat' => '-8.2000', 'lng' => '113.7000', 'foto' => UploadedFile::fake()->image('a.jpg')]);
        $a = Absensi::firstOrFail();
        $this->assertTrue($a->perlu_dicek);
        $this->assertGreaterThan(1000, $a->jarak);

        $this->actingAs($this->guru)->get('/bimbingan/periksa')->assertOk()->assertSee('Budi');
        $this->post('/bimbingan/periksa/absensi', ['id' => [$a->id], 'hasil' => 'ok'])->assertSessionHas('success');
        $this->assertFalse($a->fresh()->perlu_dicek);
        $this->assertSame('ok', $a->fresh()->verifikasi);
    }

    public function test_foto_hanya_untuk_yang_berhak(): void
    {
        $this->actingAs($this->budi)->post('/saya/absen', ['aksi' => 'masuk', 'foto' => UploadedFile::fake()->image('a.jpg')]);
        $a = Absensi::firstOrFail();
        $url = "/berkas/absensi/{$a->id}/foto_masuk";
        $this->get($url)->assertOk()->assertHeader('Content-Security-Policy', 'sandbox');
        $this->actingAs(User::where('username', 'ani')->first())->get($url)->assertForbidden();
        $this->actingAs($this->guru)->get($url)->assertOk();
        $this->get("/berkas/absensi/{$a->id}/password")->assertNotFound();
    }

    public function test_jurnal_diajukan_lalu_disetujui_dan_terkunci(): void
    {
        $hari = Dates::today();
        $this->actingAs($this->budi)->post('/saya/jurnal', ['tanggal' => $hari, 'kegiatan' => 'Memasang kabel LAN di ruang server', 'aksi' => 'ajukan']);
        $j = Jurnal::firstOrFail();
        $this->assertSame('diajukan', $j->status);
        $this->actingAs($this->guru)->post('/bimbingan/periksa/jurnal', ['id' => [$j->id], 'hasil' => 'revisi'])->assertSessionHas('error');
        $this->post('/bimbingan/periksa/jurnal', ['id' => [$j->id], 'hasil' => 'disetujui']);
        $this->assertSame('disetujui', $j->fresh()->status);
        $this->actingAs($this->budi)->post('/saya/jurnal', ['tanggal' => $hari, 'kegiatan' => 'Ubah setelah disetujui', 'aksi' => 'draft'])->assertSessionHas('error');
        $this->post('/saya/jurnal', ['tanggal' => date('Y-m-d', strtotime('+1 day')), 'kegiatan' => 'Masa depan tidak boleh', 'aksi' => 'draft'])->assertSessionHas('error');
    }

    public function test_akses_per_peran(): void
    {
        $this->actingAs($this->budi)->get('/bimbingan')->assertForbidden();
        $this->get('/dudi')->assertForbidden();
        $this->actingAs($this->guru)->get('/dudi')->assertForbidden();
        $this->get('/bimbingan/'.$this->p1->id)->assertOk();
        $lain = User::create(['username' => 'g2', 'nama' => 'G2', 'roles' => ['guru'], 'aktif' => true, 'pegawai_uuid' => 'g2']);
        Pegawai::create(['pusat_uuid' => 'g2', 'nip' => 'g2', 'nama' => 'Guru Dua', 'jenis' => 'guru', 'aktif' => true]);
        $this->actingAs($lain)->get('/bimbingan/'.$this->p1->id)->assertForbidden();
        $this->post('/bimbingan/'.$this->p1->id.'/koreksi', ['tanggal' => Dates::today(), 'status' => 'A'])->assertForbidden();
        $this->actingAs($this->kor)->get('/admin/integrasi')->assertForbidden();
    }

    public function test_pembimbing_industri_login_lokal_dan_hanya_menilai_komponen_industri(): void
    {
        $this->post('/pembimbing-industri', ['dudi_id' => $this->dudi->id, 'nama' => 'Pak Industri', 'username' => 'pak.ind']);
        $akun = session('akun_baru');
        $this->assertNotEmpty($akun['password']);
        $pi = PembimbingIndustri::firstOrFail();
        $this->put('/penempatan/'.$this->p1->id, ['dudi_id' => $this->dudi->id, 'pembimbing_industri_id' => $pi->id, 'guru_id' => $this->p1->guru_id, 'status' => 'aktif']);

        auth()->logout();
        $this->post('/login', ['username' => 'pak.ind', 'password' => $akun['password']])->assertRedirect(route('beranda'));
        $ind = User::where('username', 'pak.ind')->firstOrFail();
        $this->assertSame(['industri'], $ind->roles);
        $this->actingAs($ind)->get('/')->assertOk()->assertSee('Budi')->assertDontSee('Ani');
        $skor = KomponenNilai::pluck('id')->mapWithKeys(fn ($id) => [$id => 90])->all();
        $this->post('/bimbingan/'.$this->p1->id.'/nilai', ['skor' => $skor]);
        $this->assertSame(KomponenNilai::where('sumber', 'industri')->count(), $this->p1->nilai()->count());
        $this->get('/bimbingan/'.$this->p2->id)->assertForbidden();
    }

    public function test_nilai_akhir_berbobot_dan_sertifikat(): void
    {
        $skor = KomponenNilai::all()->mapWithKeys(fn ($k) => [$k->id => $k->sumber === 'industri' ? 90 : 70])->all();
        $this->post('/bimbingan/'.$this->p1->id.'/nilai', ['skor' => $skor]);
        $n = NilaiService::hitung($this->p1->fresh());
        $this->assertTrue($n['lengkap']);
        $this->assertEquals(82.0, $n['akhir']); // 60% x 90 + 40% x 70
        $this->assertSame('B', $n['huruf']);
        $this->get('/bimbingan/'.$this->p1->id.'/sertifikat')->assertOk()->assertSee('SERTIFIKAT');
        $this->get('/bimbingan/'.$this->p2->id.'/sertifikat')->assertStatus(422);
        $this->post('/bimbingan/'.$this->p1->id.'/nilai', ['skor' => [KomponenNilai::value('id') => 101]])->assertSessionHas('error');
    }

    public function test_komponen_nilai_total_bobot_harus_100(): void
    {
        $rows = KomponenNilai::all()->mapWithKeys(fn ($k) => [$k->id => ['nama' => $k->nama, 'sumber' => $k->sumber, 'bobot' => $k->bobot, 'aktif' => 1]])->all();
        $salah = $rows;
        $salah[array_key_first($salah)]['bobot'] += 5;
        $this->put('/komponen-nilai', ['komponen' => $salah])->assertSessionHas('error');
        $this->put('/komponen-nilai', ['komponen' => $rows])->assertSessionHas('success');
    }

    public function test_laporan_excel_dan_surat(): void
    {
        $this->post('/bimbingan/'.$this->p2->id.'/koreksi', ['tanggal' => date('Y-m-d', strtotime('-1 day')), 'status' => 'A', 'keterangan' => 'tidak datang'])
            ->assertSessionHas('success');
        $this->get('/laporan')->assertOk()->assertSee('Budi')->assertSee('Ani');
        $this->get('/laporan/unduh')->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->get('/surat/pengantar?dudi='.$this->dudi->id)->assertOk()->assertSee('Permohonan Penempatan');
        $this->get('/surat/penarikan?dudi='.$this->dudi->id)->assertOk()->assertSee('Penarikan');
    }

    public function test_siswa_ditarik_tidak_bisa_absen(): void
    {
        $this->put('/penempatan/'.$this->p1->id, ['dudi_id' => $this->dudi->id, 'status' => 'ditarik']);
        $this->actingAs($this->budi)->get('/saya')->assertOk()->assertSee('bukan masa PKL');
        $this->post('/saya/absen', ['aksi' => 'masuk', 'foto' => UploadedFile::fake()->image('a.jpg')])->assertSessionHas('error');
    }
}
