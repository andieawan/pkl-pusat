<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    protected $table = 'nilai';
    protected $guarded = [];

    protected function casts(): array
    {
        return ['skor' => 'float'];
    }

    public function komponen()
    {
        return $this->belongsTo(KomponenNilai::class, 'komponen_id');
    }
}
