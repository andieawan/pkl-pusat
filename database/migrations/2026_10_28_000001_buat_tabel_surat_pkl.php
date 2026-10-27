<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Nomor surat pengantar/penarikan per DUDI per periode (dari register Surat Keluar TU, atau diisi manual). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_pkl', function (Blueprint $t) {
            $t->id();
            $t->string('jenis', 12);                       // pengantar | penarikan
            $t->foreignId('periode_id');
            $t->foreignId('dudi_id');
            $t->string('nomor', 80);
            $t->string('tanggal', 10);
            $t->string('sumber', 8)->default('tu');        // tu | manual
            $t->string('agenda_tu', 20)->nullable();
            $t->foreignId('dibuat_oleh')->nullable();
            $t->timestamps();
            $t->unique(['jenis', 'periode_id', 'dudi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_pkl');
    }
};
