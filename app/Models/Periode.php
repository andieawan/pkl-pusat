<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $table = 'periode';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['mulai' => 'date:Y-m-d', 'selesai' => 'date:Y-m-d', 'aktif' => 'boolean'];
    }

    public function penempatan()
    {
        return $this->hasMany(Penempatan::class);
    }
}
