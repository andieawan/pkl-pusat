<?php

namespace App\Services;

use App\Exceptions\UserError;
use App\Models\KomponenNilai;
use App\Models\Nilai;
use App\Models\Penempatan;
use App\Models\User;
use Illuminate\Support\Collection;

/** Nilai PKL: tiap komponen 0–100 berbobot; nilai akhir = rata-rata berbobot dari komponen aktif. */
class NilaiService
{
    public static function huruf(?float $n): string
    {
        return match (true) {
            $n === null => '-',
            $n >= 90 => 'A',
            $n >= 80 => 'B',
            $n >= 70 => 'C',
            default => 'D',
        };
    }

    public static function predikat(?float $n): string
    {
        return ['A' => 'Sangat Baik', 'B' => 'Baik', 'C' => 'Cukup', 'D' => 'Kurang', '-' => '-'][self::huruf($n)];
    }

    /** @return Collection<int, KomponenNilai> */
    public static function komponen(): Collection
    {
        return KomponenNilai::aktif()->get();
    }

    /**
     * @return array{rincian: array<int, array{komponen:KomponenNilai, skor:?float}>, akhir:?float, huruf:string, lengkap:bool}
     */
    public static function hitung(Penempatan $p, ?Collection $komponen = null): array
    {
        $komponen ??= self::komponen();
        $skor = $p->relationLoaded('nilai') ? $p->nilai->keyBy('komponen_id') : Nilai::where('penempatan_id', $p->id)->get()->keyBy('komponen_id');
        $rincian = [];
        $jumlah = $bobot = 0;
        $lengkap = $komponen->isNotEmpty();
        foreach ($komponen as $k) {
            $s = isset($skor[$k->id]) ? (float) $skor[$k->id]->skor : null;
            $rincian[] = ['komponen' => $k, 'skor' => $s];
            if ($s === null) {
                $lengkap = false;

                continue;
            }
            $jumlah += $s * $k->bobot;
            $bobot += $k->bobot;
        }
        $akhir = $lengkap && $bobot > 0 ? round($jumlah / $bobot, 2) : null;

        return ['rincian' => $rincian, 'akhir' => $akhir, 'huruf' => self::huruf($akhir), 'lengkap' => $lengkap];
    }

    /** Simpan skor yang boleh diisi pengguna ini. @return int jumlah komponen tersimpan */
    public static function simpan(User $u, Penempatan $p, array $skor): int
    {
        $n = 0;
        foreach (self::komponen() as $k) {
            if (! array_key_exists($k->id, $skor) || ! Akses::bolehNilai($u, $p, $k)) {
                continue;
            }
            $v = trim((string) $skor[$k->id]);
            if ($v === '') {
                Nilai::where(['penempatan_id' => $p->id, 'komponen_id' => $k->id])->delete();

                continue;
            }
            if (! is_numeric(str_replace(',', '.', $v)) || (float) str_replace(',', '.', $v) < 0 || (float) str_replace(',', '.', $v) > 100) {
                throw new UserError("Nilai \"{$k->nama}\" harus angka 0–100.");
            }
            Nilai::updateOrCreate(['penempatan_id' => $p->id, 'komponen_id' => $k->id], ['skor' => round((float) str_replace(',', '.', $v), 2), 'diisi_oleh' => $u->id]);
            $n++;
        }
        if ($n) {
            Audit::catat('Isi nilai PKL', $u->nama, "Penempatan #{$p->id}: $n komponen");
        }

        return $n;
    }
}
