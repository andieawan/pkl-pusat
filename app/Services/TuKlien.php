<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/** Klien API aplikasi TU: meminta nomor Surat Keluar (tercatat di register TU). */
class TuKlien
{
    public static function aktif(): bool
    {
        return config('pkl.tu.url') !== '' && config('pkl.tu.api_key') !== '';
    }

    private static function http()
    {
        return Http::baseUrl(config('pkl.tu.url').'/api/v1')->withToken((string) config('pkl.tu.api_key'))->acceptJson()
            ->withOptions(['verify' => (bool) config('pkl.tu.verify_ssl')])->timeout(config('pkl.tu.timeout'));
    }

    private static function periksa($res): array
    {
        if ($res->status() === 401) {
            throw new RuntimeException('Kunci API TU salah (samakan TU_API_KEY di PKL dengan TU_API_KUNCI_PKL di TU).');
        }
        if (! $res->successful()) {
            throw new RuntimeException((string) ($res->json('error.pesan') ?: 'TU membalas HTTP '.$res->status().'.'));
        }

        return (array) $res->json('data');
    }

    public static function ping(): array
    {
        if (! self::aktif()) {
            throw new RuntimeException('Sambungan ke TU belum diatur (TU_URL dan TU_API_KEY di .env).');
        }
        try {
            return self::periksa(self::http()->get('/ping'));
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new RuntimeException('Aplikasi TU tidak dapat dihubungi. Periksa TU_URL dan jaringan.');
        }
    }

    /** @return array{nomor:string, tanggal:string, agenda:string} */
    public static function suratKeluar(string $ref, string $tanggal, string $pihak, string $perihal, string $isi, string $petugas): array
    {
        if (! self::aktif()) {
            throw new RuntimeException('Sambungan ke TU belum diatur (TU_URL dan TU_API_KEY di .env).');
        }
        try {
            $res = self::http()->post('/surat-keluar', compact('ref', 'tanggal', 'pihak', 'perihal', 'isi', 'petugas'));
        } catch (\Throwable $e) {
            report($e);
            throw new RuntimeException('Aplikasi TU tidak dapat dihubungi. Periksa TU_URL dan jaringan.');
        }

        return self::periksa($res);
    }
}
