<?php

// Konfigurasi khusus aplikasi Manajemen PKL.
return [
    'school_name' => env('SCHOOL_NAME', 'SMK Islam Bustanul Ulum Pakusari'),
    'timezone' => env('PKL_TIMEZONE', 'Asia/Jakarta'),
    // Singkatan sekolah pada nomor surat: 001/PKL/SMKIBU/X/2026
    'kode_surat' => env('KODE_SURAT', 'SMKIBU'),

    // Login username+password: pembimbing industri (akun dibuat admin PKL) dan admin lokal darurat.
    'login_lokal' => (bool) env('PKL_LOGIN_LOKAL', true),
    'password_awal' => env('PKL_PASSWORD_AWAL', ''), // kosong = dibuat acak & ditampilkan sekali

    'toleransi_menit' => (int) env('PKL_TOLERANSI_MENIT', 15),  // terlambat bila masuk > jam masuk DUDI + toleransi
    'radius_bawaan' => (int) env('PKL_RADIUS_DEFAULT', 200),      // meter, untuk DUDI baru
    'foto_maks_kb' => (int) env('PKL_MAKS_FOTO_KB', 4096),
    'jurnal_tertunda_hari' => (int) env('PKL_JURNAL_TERTUNDA_HARI', 3), // peringatan jurnal belum diisi
    'koordinator' => env('PKL_KOORDINATOR', ''),                // nama koordinator PKL di surat/sertifikat

    // Identitas pada kop surat (sama dengan BK/TU; ubah lewat .env)
    'surat' => [
        'yayasan' => env('SURAT_YAYASAN', 'YAYASAN PENDIDIKAN ISLAM'),
        'yayasan2' => env('SURAT_YAYASAN2', '“ BUSTANUL ULUM ”'),
        'nama' => env('SURAT_NAMA', 'SMK ISLAM BUSTANUL ULUM PAKUSARI'),
        'nss' => env('SURAT_NSS', '342052423288'),
        'npsn' => env('SURAT_NPSN', '20570966'),
        'bidang' => env('SURAT_BIDANG', 'Kelompok Bisnis Manajemen dan Teknologi Informasi Komunikasi'),
        'alamat' => env('SURAT_ALAMAT', 'Jl. Himalaya No. 17 Telp. (0331) 7255753 Kode Pos. 68181 Pakusari – Jember'),
        'kota' => env('SURAT_KOTA', 'Pakusari'),
        'kepsek' => env('SURAT_KEPSEK', 'MUHAMMAD MUSLIM, S.Pd., Gr'),
        'kepsek_nuptk' => env('SURAT_KEPSEK_NUPTK', '7951767668130072'),
    ],
];
