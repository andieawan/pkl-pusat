<?php

namespace App\Http\Controllers\Pkl;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Jurnal;
use App\Models\Kunjungan;
use App\Services\Akses;
use Illuminate\Support\Facades\Storage;

/** Membuka foto (selfie, bukti izin, jurnal, kunjungan) hanya untuk yang berhak melihat penempatannya. */
class BerkasController extends Controller
{
    private const KOLOM = [
        'absensi' => [Absensi::class, ['foto_masuk', 'foto_pulang', 'bukti']],
        'jurnal' => [Jurnal::class, ['foto']],
        'kunjungan' => [Kunjungan::class, ['foto']],
    ];

    private const MIME = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp',
        'heic' => 'image/heic', 'heif' => 'image/heif', 'pdf' => 'application/pdf'];

    public function __invoke(string $jenis, int $id, string $kolom)
    {
        [$model, $boleh] = self::KOLOM[$jenis] ?? abort(404);
        abort_unless(in_array($kolom, $boleh, true), 404);
        $row = $model::with('penempatan')->findOrFail($id);
        abort_unless(Akses::bolehLihat($this->me(), $row->penempatan), 403);
        $path = $row->{$kolom} ?: abort(404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        // Jenis berkas ditentukan dari ekstensi yang diizinkan (bukan isi), dan dikunci CSP sandbox: berkas berisi skrip tidak bisa berjalan.
        $mime = self::MIME[strtolower(pathinfo($path, PATHINFO_EXTENSION))] ?? 'application/octet-stream';

        return Storage::disk('local')->response($path, null, ['Content-Type' => $mime, 'Cache-Control' => 'private, max-age=3600',
            'Content-Security-Policy' => 'sandbox', 'X-Content-Type-Options' => 'nosniff']);
    }
}
