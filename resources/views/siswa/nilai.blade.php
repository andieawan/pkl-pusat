@extends('layouts.app', ['title' => 'Nilai PKL'])
@section('content')
<div class="head"><div><h1>Nilai PKL</h1><small>{{ $p?->dudi?->nama }}</small></div></div>
@if(! $p)
    <div class="card"><div class="empty">Anda belum memiliki penempatan PKL.</div></div>
@else
    <div class="kpis"><div class="kpi kpi-main"><small>Nilai akhir</small><div class="n">{{ $n['akhir'] ?? '–' }}</div><small>{{ $n['lengkap'] ? $n['huruf'].' · '.\App\Services\NilaiService::predikat($n['akhir']) : 'Belum lengkap, menunggu pembimbing' }}</small></div></div>
    <div class="card card-tight"><table class="tbl">
        <thead><tr><th>Komponen</th><th>Penilai</th><th class="c">Bobot</th><th class="c">Nilai</th></tr></thead>
        <tbody>@foreach($n['rincian'] as $r)
            <tr><td>{{ $r['komponen']->nama }}</td><td>{{ \App\Models\KomponenNilai::SUMBER[$r['komponen']->sumber] }}</td><td class="c">{{ $r['komponen']->bobot }}%</td><td class="c"><b>{{ $r['skor'] ?? '–' }}</b></td></tr>
        @endforeach</tbody></table></div>
@endif
@endsection
