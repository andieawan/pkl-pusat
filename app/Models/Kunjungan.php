<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Catatan kunjungan / monitoring guru pembimbing ke DUDI. */
class Kunjungan extends Model
{
    protected $table = 'kunjungan';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['tanggal' => 'date:Y-m-d'];
    }

    public function penempatan()
    {
        return $this->belongsTo(Penempatan::class);
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
