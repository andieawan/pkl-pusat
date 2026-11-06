# PKL — Manajemen Praktik Kerja Lapangan SMKS Islam Bustanul Ulum

Aplikasi mandiri untuk PKL: DUDI, pembimbing industri, periode & penempatan siswa, absen siswa dari HP (selfie + GPS dengan radius),
izin/sakit, jurnal harian, pemeriksaan pembimbing, kunjungan, nilai berbobot, rekap Excel, surat pengantar/penarikan, dan sertifikat.
Warga sekolah login lewat Pusat Sekolah (SSO); pembimbing industri memakai akun lokal. Kelas, siswa, dan guru disalin dari Pusat.

Pemasangan: [docs/PASANG-PKL.md](docs/PASANG-PKL.md). Laravel 13, PHP 8.3+, MariaDB.

## Impor data PKL lama
**Data PKL → Impor** (Admin & koordinator): unduh templat → isi lembar DUDI dan PEMBIMBING INDUSTRI (satu baris = satu
data) → *Periksa saja* → *Impor*. Pembimbing dicocokkan ke DUDI lewat nama; Username Login opsional (password dibuat
otomatis, ditampilkan sekali). Bila ada satu baris salah, tidak ada yang disimpan; data yang sudah ada dilewati.
Kode: `app/Services/ImporMaster.php`.
