<?php

namespace App\Support;

use App\Exceptions\UserError;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Simpan foto (selfie absen, jurnal, kunjungan, bukti izin) di disk privat "local".
 * Foto siswa tidak pernah diletakkan di folder publik; dibuka lewat controller yang memeriksa hak akses.
 */
final class Foto
{
    public const EKSTENSI = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];

    public static function simpan(UploadedFile $berkas, string $folder, bool $bolehPdf = false): string
    {
        $ext = strtolower($berkas->getClientOriginalExtension() ?: $berkas->extension());
        $izin = $bolehPdf ? [...self::EKSTENSI, 'pdf'] : self::EKSTENSI;
        if (! in_array($ext, $izin, true)) {
            throw new UserError('Berkas harus berupa foto (JPG/PNG/WEBP/HEIC)'.($bolehPdf ? ' atau PDF' : '').'.');
        }
        $maksKb = (int) config('pkl.foto_maks_kb', 4096);
        if ($berkas->getSize() > $maksKb * 1024 * 3) { // sebelum diperkecil boleh lebih besar; hasil akhir tetap kecil
            throw new UserError("Ukuran berkas terlalu besar (maks. sekitar ".round($maksKb * 3 / 1024)." MB).");
        }
        $nama = $folder.'/'.date('Y/m').'/'.bin2hex(random_bytes(10));
        $hasil = $ext !== 'pdf' ? GambarUpload::perkecil($berkas->getRealPath(), 1280) : null;
        if ($hasil !== null) {
            [$isi, $e] = $hasil;
            Storage::disk('local')->put("$nama.$e", $isi);

            return "$nama.$e";
        }
        if ($berkas->getSize() > $maksKb * 1024) {
            throw new UserError("Ukuran berkas maksimal {$maksKb} KB.");
        }

        return $berkas->storeAs(dirname($nama), basename($nama).'.'.$ext, 'local');
    }

    public static function hapus(?string $path): void
    {
        if ($path) {
            Storage::disk('local')->delete($path);
        }
    }
}
