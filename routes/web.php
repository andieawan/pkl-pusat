<?php

use App\Http\Controllers\Admin\IntegrasiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\Pkl\BerkasController;
use App\Http\Controllers\Pkl\BimbinganController;
use App\Http\Controllers\Pkl\LaporanController;
use App\Http\Controllers\Pkl\MasterController;
use App\Http\Controllers\Pkl\SiswaController;
use App\Http\Controllers\PusatSsoController;
use App\Http\Controllers\PusatWebhookController;
use Illuminate\Support\Facades\Route;

// Penerima webhook dari Pusat Sekolah (tanda tangan HMAC; tanpa CSRF — lihat bootstrap/app.php).
Route::post('/api/pusat/webhook', PusatWebhookController::class)->middleware('throttle:120,1');

// Login: warga sekolah lewat Pusat (SSO); pembimbing industri & admin darurat dengan username + password.
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'form'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});
Route::get('/sso/login', [PusatSsoController::class, 'mulai'])->middleware('throttle:30,1')->name('sso.login');
Route::get('/sso/callback', [PusatSsoController::class, 'callback'])->middleware('throttle:30,1')->name('sso.callback');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'akun.aktif'])->group(function () {
    Route::get('/', BerandaController::class)->name('beranda');
    Route::get('/berkas/{jenis}/{id}/{kolom}', BerkasController::class)->whereIn('jenis', ['absensi', 'jurnal', 'kunjungan'])->whereNumber('id')->name('berkas');

    // Siswa PKL
    Route::middleware('pkl:siswa')->prefix('saya')->name('siswa.')->group(function () {
        Route::get('/', [SiswaController::class, 'beranda'])->name('beranda');
        Route::post('/absen', [SiswaController::class, 'absen'])->middleware('throttle:20,1')->name('absen');
        Route::get('/riwayat', [SiswaController::class, 'riwayat'])->name('riwayat');
        Route::get('/jurnal', [SiswaController::class, 'jurnal'])->name('jurnal');
        Route::post('/jurnal', [SiswaController::class, 'simpanJurnal'])->middleware('throttle:30,1')->name('jurnal.simpan');
        Route::get('/nilai', [SiswaController::class, 'nilai'])->name('nilai');
    });

    // Pembimbing sekolah, pembimbing industri, pengelola
    Route::middleware('pkl:pembimbing')->group(function () {
        Route::get('/bimbingan', [BimbinganController::class, 'index'])->name('bimbingan.index');
        Route::get('/bimbingan/periksa', [BimbinganController::class, 'verifikasi'])->name('bimbingan.verifikasi');
        Route::post('/bimbingan/periksa/absensi', [BimbinganController::class, 'verifikasiAbsensi'])->name('bimbingan.verifikasi.absensi');
        Route::post('/bimbingan/periksa/jurnal', [BimbinganController::class, 'verifikasiJurnal'])->name('bimbingan.verifikasi.jurnal');
        Route::get('/bimbingan/{penempatan}', [BimbinganController::class, 'show'])->whereNumber('penempatan')->name('bimbingan.show');
        Route::post('/bimbingan/{penempatan}/kunjungan', [BimbinganController::class, 'kunjungan'])->whereNumber('penempatan')->name('bimbingan.kunjungan');
        Route::post('/bimbingan/{penempatan}/nilai', [BimbinganController::class, 'nilai'])->whereNumber('penempatan')->name('bimbingan.nilai');
        Route::post('/bimbingan/{penempatan}/koreksi', [BimbinganController::class, 'koreksi'])->whereNumber('penempatan')->name('bimbingan.koreksi');
        Route::get('/bimbingan/{penempatan}/sertifikat', [LaporanController::class, 'sertifikat'])->whereNumber('penempatan')->name('laporan.sertifikat');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/unduh', [LaporanController::class, 'unduh'])->name('laporan.unduh');
    });

    // Admin & koordinator PKL
    Route::middleware('pkl:pengelola')->group(function () {
        Route::get('/dudi', [MasterController::class, 'dudi'])->name('master.dudi');
        Route::post('/dudi', [MasterController::class, 'simpanDudi'])->name('master.dudi.simpan');
        Route::put('/dudi/{dudi}', [MasterController::class, 'simpanDudi'])->whereNumber('dudi')->name('master.dudi.ubah');
        Route::get('/pembimbing-industri', [MasterController::class, 'pembimbing'])->name('master.pembimbing');
        Route::post('/pembimbing-industri', [MasterController::class, 'simpanPembimbing'])->name('master.pembimbing.simpan');
        Route::put('/pembimbing-industri/{pembimbing}', [MasterController::class, 'simpanPembimbing'])->whereNumber('pembimbing')->name('master.pembimbing.ubah');
        Route::get('/periode', [MasterController::class, 'periode'])->name('master.periode');
        Route::post('/periode', [MasterController::class, 'simpanPeriode'])->name('master.periode.simpan');
        Route::put('/periode/{periode}', [MasterController::class, 'simpanPeriode'])->whereNumber('periode')->name('master.periode.ubah');
        Route::get('/penempatan', [MasterController::class, 'penempatanBaru'])->name('master.penempatan');
        Route::post('/penempatan', [MasterController::class, 'simpanPenempatan'])->name('master.penempatan.simpan');
        Route::put('/penempatan/{penempatan}', [MasterController::class, 'ubahPenempatan'])->whereNumber('penempatan')->name('master.penempatan.ubah');
        Route::get('/komponen-nilai', [MasterController::class, 'komponen'])->name('master.komponen');
        Route::put('/komponen-nilai', [MasterController::class, 'simpanKomponen'])->name('master.komponen.simpan');
        Route::get('/surat/{jenis}', [LaporanController::class, 'surat'])->whereIn('jenis', ['pengantar', 'penarikan'])->name('laporan.surat');
    });

    Route::middleware('pkl:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/integrasi', [IntegrasiController::class, 'index'])->name('integrasi');
        Route::post('/integrasi/uji-pusat', [IntegrasiController::class, 'ujiPusat'])->middleware('throttle:10,1')->name('integrasi.pusat');
        Route::post('/integrasi/sinkron', [IntegrasiController::class, 'sinkron'])->middleware('throttle:5,1')->name('integrasi.sinkron');
    });
});
