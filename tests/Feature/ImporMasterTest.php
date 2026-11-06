<?php

namespace Tests\Feature;

use App\Models\Dudi;
use App\Models\PembimbingIndustri;
use App\Models\User;
use App\Services\ImporMaster;
use App\Support\Xlsx;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/** Impor DUDI & pembimbing industri lama (semua-atau-tidak-sama-sekali). */
class ImporMasterTest extends TestCase
{
    use RefreshDatabase;

    private User $koor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->koor = User::create(['username' => 'koor', 'nama' => 'Koordinator', 'roles' => ['koordinator'], 'aktif' => true]);
    }

    private function unggah(array $dudi = [], array $pembimbing = [], bool $uji = false)
    {
        $tpl = ImporMaster::templat();
        $tpl['DUDI'] = array_merge($tpl['DUDI'], $dudi);
        $tpl['PEMBIMBING INDUSTRI'] = array_merge($tpl['PEMBIMBING INDUSTRI'], $pembimbing);
        $path = tempnam(sys_get_temp_dir(), 'pkl').'.xlsx';
        file_put_contents($path, Xlsx::build($tpl));

        return $this->actingAs($this->koor)->post('/impor', ['berkas' => new UploadedFile($path, 'p.xlsx', null, null, true), 'uji' => $uji ? 1 : 0]);
    }

    public function test_halaman_dan_templat(): void
    {
        $this->actingAs($this->koor)->get('/impor')->assertOk()->assertSee('PEMBIMBING INDUSTRI');
        $res = $this->get('/impor/templat')->assertOk();
        $path = tempnam(sys_get_temp_dir(), 'tp').'.xlsx';
        file_put_contents($path, $res->getContent());
        $this->assertSame(array_values(ImporMaster::DUDI), Xlsx::readAllSheets($path, 'x.xlsx')[1][0]);
    }

    public function test_impor_dudi_dan_pembimbing_dengan_akun(): void
    {
        $dudi = [['CV Maju Jaya', 'Percetakan', 'Jl. Mawar 1', 'Pak Andi', '0812', '', '-8.1567', '113.7643', '150', '7.30', '15:00', 'Senin-Jumat', '4'],
            ['Toko Komputer Sentosa', '', '', '', '', '', '', '', '', '', '', '', '']];
        $pemb = [['cv maju  jaya', 'Andi Wijaya', 'Supervisor', '0813', 'andi.maju'], ['Toko Komputer Sentosa', 'Rina', '', '', '']];
        $this->unggah($dudi, $pemb, true)->assertSessionHas('info')->assertSessionHas('akun_impor', []);
        $this->assertSame([0, 0, 1], [Dudi::count(), PembimbingIndustri::count(), User::count()]);

        $this->unggah($dudi, $pemb)->assertSessionHas('success', fn ($m) => str_contains($m, '2 DUDI, 2 pembimbing'))
            ->assertSessionHas('akun_impor', fn ($a) => count($a) === 1 && $a[0]['username'] === 'andi.maju' && strlen($a[0]['password']) >= 8);
        $d = Dudi::where('nama', 'CV Maju Jaya')->first();
        $this->assertSame(['07:30', '15:00', [1, 2, 3, 4, 5], 150, 4, -8.1567], [$d->jam_masuk, $d->jam_pulang, $d->hari_kerja, $d->radius, $d->kuota, $d->lat]);
        $this->assertSame([1, 2, 3, 4, 5, 6], Dudi::where('nama', 'Toko Komputer Sentosa')->first()->hari_kerja);
        $this->assertSame(['industri'], User::where('username', 'andi.maju')->first()->roles);

        $this->unggah($dudi, $pemb)->assertSessionHas('success', fn ($m) => str_contains($m, '4 data sudah ada'));
        $this->assertSame([2, 2], [Dudi::count(), PembimbingIndustri::count()]);
    }

    public function test_satu_baris_salah_membatalkan_semua(): void
    {
        $this->unggah([
            ['CV Benar', '', '', '', '', '', '', '', '', '', '', '', ''],
            ['CV Salah', '', '', '', '', '', 'abc', '113', '', '25:00', '', 'Senin-Libur', 'x'],
        ], [['PT Hantu', 'Budi', '', '', ''], ['CV Benar', 'Ani', '', '', 'A!']])
            ->assertSessionHas('error')->assertSessionHas('galat', function ($g) {
                $t = implode("\n", $g);

                return str_contains($t, 'DUDI baris 3') && str_contains($t, 'Hari Kerja') && str_contains($t, 'DUDI "PT Hantu"') && str_contains($t, 'Username');
            });
        $this->assertSame(0, Dudi::count());
    }

    public function test_hari_kerja(): void
    {
        $this->assertSame([1, 2, 3, 4, 5, 6], ImporMaster::hari('Senin - Sabtu'));
        $this->assertSame([1, 3, 5], ImporMaster::hari('senin, rabu, jumat'));
        $this->assertSame([1, 2, 3, 4, 5], ImporMaster::hari('1-5'));
        $this->assertSame([6, 7], ImporMaster::hari('Sabtu dan Minggu'));
        $this->assertNull(ImporMaster::hari('kadang-kadang'));
    }

    public function test_guru_biasa_tidak_bisa_impor(): void
    {
        $this->actingAs(User::create(['username' => 'g', 'nama' => 'G', 'roles' => ['guru'], 'aktif' => true, 'pegawai_uuid' => 'g']))->get('/impor')->assertForbidden();
    }
}
