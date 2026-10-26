@extends('layouts.app', ['title' => 'Riwayat Absen'])
@section('content')
<div class="head"><div><h1>Riwayat Absen PKL</h1><small>{{ $p?->dudi?->nama }}</small></div></div>
@if(! $p)
    <div class="card"><div class="empty">Anda belum memiliki penempatan PKL.</div></div>
@else
    <div class="kpis kpis-4">
        <div class="kpi"><small>Hadir</small><div class="n ok">{{ $rekap['H'] }}</div><small>{{ $rekap['persen'] === null ? '-' : $rekap['persen'].'%' }} dari {{ $rekap['hari'] }} hari kerja</small></div>
        <div class="kpi"><small>Izin / Sakit</small><div class="n">{{ $rekap['I'] + $rekap['S'] }}</div></div>
        <div class="kpi"><small>Alpa / tanpa keterangan</small><div class="n {{ $rekap['A'] + $rekap['kosong'] ? 'bad' : 'ok' }}">{{ $rekap['A'] + $rekap['kosong'] }}</div></div>
        <div class="kpi"><small>Terlambat</small><div class="n {{ $rekap['T'] ? 'warn' : 'ok' }}">{{ $rekap['T'] }}</div></div>
    </div>
    <div class="card card-tight scroll"><table class="tbl tbl-cards">
        <thead><tr><th>Tanggal</th><th>Status</th><th>Masuk</th><th>Pulang</th><th>Keterangan</th></tr></thead>
        <tbody>
        @forelse($rows as $a)
            <tr><td class="card-title">{{ \App\Support\Dates::human($a->tanggal->format('Y-m-d')) }}</td>
                <td data-label="Status"><span class="lg {{ $a->status }}">{{ $a->status }}</span> @if($a->terlambat)<span class="badge b-warn">Terlambat</span>@endif @if($a->verifikasi === 'tolak')<span class="badge b-bad">Ditolak</span>@elseif($a->perlu_dicek)<span class="badge b-warn">Diperiksa</span>@endif</td>
                <td data-label="Masuk" class="mono">{{ $a->jam_masuk ?: '' }}</td><td data-label="Pulang" class="mono">{{ $a->jam_pulang ?: '' }}</td>
                <td data-label="Ket">{{ $a->keterangan }}</td></tr>
        @empty
            <tr><td colspan="5"><div class="empty">Belum ada catatan absen.</div></td></tr>
        @endforelse
        </tbody></table></div>
@endif
@endsection
