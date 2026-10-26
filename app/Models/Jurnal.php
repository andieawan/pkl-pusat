<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurnal extends Model
{
    protected $table = 'jurnal';
    protected $guarded = [];

    public const STATUS = ['draft' => 'Draf', 'diajukan' => 'Menunggu', 'disetujui' => 'Disetujui', 'revisi' => 'Perlu revisi'];

    protected function casts(): array
    {
        return ['tanggal' => 'date:Y-m-d'];
    }

    public function penempatan()
    {
        return $this->belongsTo(Penempatan::class);
    }
}
