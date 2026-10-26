<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Skema Manajemen PKL. Kelas, siswa, dan guru disalin dari Pusat; sisanya milik aplikasi PKL:
 * DUDI, pembimbing industri, periode, penempatan, absensi (GPS + foto), jurnal, kunjungan, nilai.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('username', 60)->unique();
            $t->string('nama');
            $t->string('email')->nullable();
            $t->string('password')->nullable();           // pembimbing industri & admin lokal
            $t->json('roles')->nullable();                // admin | koordinator | guru | siswa | industri
            $t->boolean('aktif')->default(true);
            $t->string('pusat_uuid', 36)->nullable()->unique();
            $t->string('pegawai_uuid', 36)->nullable()->index();
            $t->string('siswa_uuid', 36)->nullable()->index();
            $t->rememberToken();
            $t->timestamps();
        });
        Schema::create('password_reset_tokens', function (Blueprint $t) {
            $t->string('email')->primary();
            $t->string('token');
            $t->timestamp('created_at')->nullable();
        });
        Schema::create('sessions', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->foreignId('user_id')->nullable()->index();
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->longText('payload');
            $t->integer('last_activity')->index();
        });

        // ---- Salinan data induk dari Pusat ----
        Schema::create('kelas', function (Blueprint $t) {
            $t->id();
            $t->string('pusat_uuid', 36)->unique();
            $t->string('nama');
            $t->string('tingkat', 10)->nullable();
            $t->string('jurusan', 30)->nullable();
            $t->string('tahun_ajaran', 20)->nullable();
            $t->boolean('tahun_aktif')->default(false);
            $t->string('wali_uuid', 36)->nullable()->index();
            $t->string('wali_nama')->nullable();
            $t->dateTime('pusat_diperbarui')->nullable();
            $t->dateTime('dihapus_pada')->nullable();
            $t->timestamps();
        });
        Schema::create('siswa', function (Blueprint $t) {
            $t->id();
            $t->string('pusat_uuid', 36)->unique();
            $t->string('nis', 30)->index();
            $t->string('nisn', 20)->nullable();
            $t->string('nama');
            $t->string('jk', 1)->nullable();
            $t->foreignId('kelas_id')->nullable()->index();
            $t->string('status', 16)->default('aktif')->index();
            $t->string('tempat_lahir', 100)->nullable();
            $t->date('tanggal_lahir')->nullable();
            $t->string('no_hp', 30)->nullable();
            $t->string('no_hp_ortu', 30)->nullable();
            $t->string('nama_ortu')->nullable();
            $t->dateTime('pusat_diperbarui')->nullable();
            $t->timestamps();
        });
        Schema::create('pegawai', function (Blueprint $t) {
            $t->id();
            $t->string('pusat_uuid', 36)->unique();
            $t->string('nip', 30)->nullable();
            $t->string('nama');
            $t->string('jenis', 20)->nullable();
            $t->string('jabatan')->nullable();
            $t->boolean('aktif')->default(true)->index();
            $t->dateTime('pusat_diperbarui')->nullable();
            $t->timestamps();
        });

        // ---- PKL ----
        Schema::create('dudi', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->string('bidang')->nullable();
            $t->string('alamat', 500)->nullable();
            $t->string('kontak_nama')->nullable();
            $t->string('telp', 30)->nullable();
            $t->string('email')->nullable();
            $t->double('lat')->nullable();
            $t->double('lng')->nullable();
            $t->integer('radius')->default(200);           // meter
            $t->string('jam_masuk', 5)->default('08:00');
            $t->string('jam_pulang', 5)->default('16:00');
            $t->json('hari_kerja')->nullable();            // [1..7], 1 = Senin
            $t->integer('kuota')->nullable();
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });
        Schema::create('pembimbing_industri', function (Blueprint $t) {
            $t->id();
            $t->foreignId('dudi_id')->index();
            $t->string('nama');
            $t->string('jabatan')->nullable();
            $t->string('telp', 30)->nullable();
            $t->foreignId('user_id')->nullable()->unique();  // akun login lokal
            $t->timestamps();
        });
        Schema::create('periode', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->date('mulai');
            $t->date('selesai');
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });
        Schema::create('penempatan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('periode_id')->index();
            $t->foreignId('siswa_id')->index();
            $t->foreignId('dudi_id')->index();
            $t->foreignId('guru_id')->nullable()->index();             // pegawai (pembimbing sekolah)
            $t->foreignId('pembimbing_industri_id')->nullable()->index();
            $t->date('mulai');
            $t->date('selesai');
            $t->string('status', 12)->default('aktif');               // aktif | selesai | ditarik
            $t->string('catatan', 500)->nullable();
            $t->timestamps();
            $t->unique(['periode_id', 'siswa_id']);
        });
        Schema::create('absensi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('penempatan_id')->index();
            $t->date('tanggal');
            $t->string('status', 1);                        // H I S A
            $t->string('jam_masuk', 5)->nullable();
            $t->string('jam_pulang', 5)->nullable();
            $t->boolean('terlambat')->default(false);
            $t->double('lat')->nullable();
            $t->double('lng')->nullable();
            $t->integer('jarak')->nullable();               // meter dari DUDI saat masuk
            $t->boolean('perlu_dicek')->default(false);     // di luar radius / tanpa lokasi
            $t->string('foto_masuk')->nullable();
            $t->string('foto_pulang')->nullable();
            $t->string('keterangan', 500)->nullable();
            $t->string('bukti')->nullable();                // foto surat izin/sakit
            $t->string('verifikasi', 10)->nullable();       // null = belum | ok | tolak
            $t->foreignId('diverifikasi_oleh')->nullable();
            $t->string('sumber', 10)->default('siswa');     // siswa | koreksi
            $t->timestamps();
            $t->unique(['penempatan_id', 'tanggal']);
        });
        Schema::create('jurnal', function (Blueprint $t) {
            $t->id();
            $t->foreignId('penempatan_id')->index();
            $t->date('tanggal');
            $t->text('kegiatan');
            $t->string('foto')->nullable();
            $t->string('status', 10)->default('draft');     // draft | diajukan | disetujui | revisi
            $t->string('catatan', 500)->nullable();          // catatan pembimbing
            $t->foreignId('diverifikasi_oleh')->nullable();
            $t->timestamps();
            $t->unique(['penempatan_id', 'tanggal']);
        });
        Schema::create('kunjungan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('penempatan_id')->index();
            $t->date('tanggal');
            $t->text('catatan');
            $t->string('foto')->nullable();
            $t->foreignId('dicatat_oleh')->nullable();
            $t->timestamps();
        });
        Schema::create('komponen_nilai', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->string('sumber', 10);                        // industri | sekolah
            $t->integer('bobot');                            // persen dari nilai akhir
            $t->integer('urutan')->default(0);
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });
        Schema::create('nilai', function (Blueprint $t) {
            $t->id();
            $t->foreignId('penempatan_id')->index();
            $t->foreignId('komponen_id');
            $t->decimal('skor', 5, 2);
            $t->foreignId('diisi_oleh')->nullable();
            $t->timestamps();
            $t->unique(['penempatan_id', 'komponen_id']);
        });
        Schema::create('pengaturan', function (Blueprint $t) {
            $t->string('kunci', 40)->primary();
            $t->text('nilai')->nullable();
            $t->timestamp('updated_at')->nullable();
        });

        // ---- Integrasi ----
        Schema::create('pusat_state', function (Blueprint $t) {
            $t->string('kunci', 40)->primary();
            $t->text('nilai')->nullable();
            $t->timestamp('updated_at')->nullable();
        });
        Schema::create('pusat_webhook', function (Blueprint $t) {
            $t->string('delivery_id', 64)->primary();
            $t->string('event', 60);
            $t->timestamp('created_at')->nullable()->index();
        });
        Schema::create('audit_log', function (Blueprint $t) {
            $t->id();
            $t->timestamp('waktu')->index();
            $t->string('aksi', 100);
            $t->string('pelaku')->nullable();
            $t->text('rincian')->nullable();
        });

        // Komponen nilai bawaan: industri 60%, sekolah 40% (bisa diubah di menu Komponen Nilai).
        $now = now();
        foreach ([
            ['Disiplin & kehadiran', 'industri', 15], ['Sikap & etika kerja', 'industri', 15], ['Keterampilan teknis', 'industri', 20], ['Kerja sama & komunikasi', 'industri', 10],
            ['Jurnal & laporan PKL', 'sekolah', 20], ['Presentasi / ujian PKL', 'sekolah', 20],
        ] as $i => [$nama, $sumber, $bobot]) {
            \Illuminate\Support\Facades\DB::table('komponen_nilai')->insert(['nama' => $nama, 'sumber' => $sumber, 'bobot' => $bobot, 'urutan' => $i + 1, 'aktif' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        foreach (['audit_log', 'pusat_webhook', 'pusat_state', 'pengaturan', 'nilai', 'komponen_nilai', 'kunjungan', 'jurnal', 'absensi', 'penempatan', 'periode',
            'pembimbing_industri', 'dudi', 'pegawai', 'siswa', 'kelas', 'sessions', 'password_reset_tokens', 'users'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
