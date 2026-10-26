# Memasang Aplikasi Manajemen PKL

Aplikasi PKL berdiri sendiri (1 aplikasi = 1 VM). Siswa, guru pembimbing, koordinator, dan admin login lewat **Pusat Sekolah**;
**pembimbing industri** login dengan username + password yang dibuat koordinator di aplikasi PKL (mereka bukan warga sekolah).

Isinya: data DUDI (lokasi, radius, jam & hari kerja), pembimbing industri, periode, penempatan siswa (banyak sekaligus),
absen siswa dari HP (selfie + GPS), lapor izin/sakit, jurnal harian, pemeriksaan oleh pembimbing, kunjungan/monitoring,
nilai berbobot, rekap (layar/cetak/Excel), surat pengantar & penarikan per DUDI, dan sertifikat.

## 1. VM baru
```bash
sudo git clone https://github.com/andieawan/pkl-pusat.git /var/www/pkl-pusat
cd /var/www/pkl-pusat
sudo DOMAIN=pkl.smkibupakusari.sch.id APP_URL=https://pkl.smkibupakusari.sch.id PROXY_IP=192.168.10.10 bash deploy/install.sh
```
(Bila repo dijadikan private, clone dengan token GitHub seperti TU.)
Lalu di NPMplus buat Proxy Host `pkl.smkibupakusari.sch.id` → IP VM, port 80, SSL Let's Encrypt, **Force SSL**; dan DNS di Cloudflare.

> **HTTPS wajib.** Browser HP hanya memberi akses kamera dan lokasi (GPS) pada alamat `https://`.

## 2. Daftarkan di Pusat (VM Pusat)
Perbarui Pusat dulu (`sudo bash deploy/update.sh`), lalu:
```bash
cd /var/www/pusat-sekolah
sudo -u www-data php artisan pusat:aplikasi-baru pkl --base-url=https://pkl.smkibupakusari.sch.id
```
Templat "pkl" memberi:
- Scope `tahun_ajaran`, `jurusan`, `kelas`, `siswa` (termasuk no. HP siswa), `pegawai_dasar`, `akun`
- Peran `admin`, `koordinator` (koordinator PKL: mengelola semua data), `guru_pembimbing`, `siswa`

Salin `client_id`, `client_secret`, `webhook secret` (tampil sekali) ke `.env` PKL:
```
PUSAT_ENABLED=true
PUSAT_URL=https://pusat.smkibupakusari.sch.id
PUSAT_CLIENT_ID=...
PUSAT_CLIENT_SECRET=...
PUSAT_WEBHOOK_SECRET=...
TRUSTED_PROXIES=192.168.10.10
PKL_KOORDINATOR="Nama Koordinator PKL, S.Pd."
```

## 3. Aktifkan
```bash
cd /var/www/pkl-pusat
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan pkl:periksa
sudo -u www-data php artisan pkl:sinkron      # salin kelas, siswa, guru dari Pusat
```

## 4. Akun & peran (di Pusat → Akun)
- Koordinator PKL: peran **koordinator** untuk aplikasi Manajemen PKL.
- Guru pembimbing: **guru_pembimbing**, dan akunnya **ditautkan ke data pegawainya**. Ia hanya melihat siswa yang ia bimbing.
- Siswa: **siswa**, akun **ditautkan ke data siswanya**. (Bisa diberikan massal ke akun siswa kelas XII/XI yang akan PKL.)
- Pembimbing industri: dibuat di aplikasi PKL → **Data PKL → Pembimbing Industri**, isi username. Password tampil **sekali**;
  centang "Buat password baru" bila lupa. Alamat login: `https://pkl.smkibupakusari.sch.id/login`.

## 5. Urutan kerja koordinator
1. **DUDI**: nama, alamat, koordinat (tombol "Isi dengan lokasi saya" saat berada di lokasi, atau salin dari Google Maps),
   radius absen (bawaan 200 m), jam masuk/pulang, hari kerja.
2. **Pembimbing Industri** per DUDI (opsional akun login).
3. **Periode** (mis. "PKL 2026/2027 Gelombang 1").
4. **Penempatan**: pilih periode & kelas, centang siswa, pilih DUDI, guru pembimbing, pembimbing industri.
5. **Komponen Nilai**: bawaan 4 komponen industri (60%) + 2 sekolah (40%); total bobot aktif harus 100.
6. Cetak **surat pengantar** dari halaman DUDI; di akhir periode **surat penarikan** dan **sertifikat** (setelah nilai lengkap).

## Catatan absen
- Absen masuk wajib selfie; lokasi dibaca otomatis. Absen **di luar radius atau tanpa lokasi tetap tersimpan** tetapi
  ditandai *perlu dicek* — pembimbing menyetujui/menolak di menu **Periksa** (GPS HP tidak selalu akurat).
- Terlambat = masuk setelah jam masuk DUDI + `PKL_TOLERANSI_MENIT` (bawaan 15).
- Siswa lupa absen / HP rusak: koordinator memakai **Koreksi absensi** di halaman siswa.
- Foto disimpan privat di `storage/app/private` (diperkecil ke 1280 px, perlu `php-gd`) dan hanya bisa dibuka oleh yang berhak.

## Pembaruan & cadangan
`sudo bash deploy/update.sh` (juga memasang jadwal backup harian 01:45, termasuk foto) dan uji pulih
`sudo bash deploy/uji-pulih.sh` — lihat docs/UJI-PULIH.md.
