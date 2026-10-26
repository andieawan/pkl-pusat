@extends('layouts.app', ['title' => 'Siswa PKL'])
@section('content')
@php($me = auth()->user())
<div class="head"><div><h1>Siswa PKL</h1><small>{{ $me->isPengelola() ? 'Semua penempatan' : 'Siswa bimbingan Anda' }}</small></div>
    @if($me->isPengelola())<a class="btn btn-pri" href="{{ route('master.penempatan') }}">+ Tempatkan siswa</a>@endif</div>
<form method="get" class="card filter-card"><div class="fields">
    <div class="field"><label>Periode</label>@include('partials.select', ['name' => 'periode', 'options' => ['' => 'Semua periode'] + $periode->pluck('nama', 'id')->all(), 'selected' => $periodeId ?: ''])</div>
    @if($dudi->isNotEmpty())<div class="field"><label>DUDI</label>@include('partials.select', ['name' => 'dudi', 'options' => ['' => 'Semua DUDI'] + $dudi->pluck('nama', 'id')->all(), 'selected' => $dudiId ?: ''])</div>@endif
    <div class="field"><label>Status</label>@include('partials.select', ['name' => 'status', 'options' => ['' => 'Semua'] + \App\Models\Penempatan::STATUS, 'selected' => $status])</div>
    <div class="field"><label for="q">Cari siswa</label><input id="q" type="search" name="q" value="{{ $q }}" placeholder="Nama / NIS"></div>
    <div class="field"><button class="btn">Tampilkan</button></div>
</div></form>
<div class="card card-tight scroll"><table class="tbl tbl-cards">
    <thead><tr><th>Siswa</th><th>Kelas</th><th>DUDI</th><th>Pembimbing</th><th>Masa</th><th>Status</th></tr></thead>
    <tbody>
    @forelse($list as $p)
        <tr><td class="card-title"><a href="{{ route('bimbingan.show', $p) }}"><b>{{ $p->siswa->nama ?? '-' }}</b></a><br><small class="mono">{{ $p->siswa->nis ?? '' }}</small></td>
            <td data-label="Kelas">{{ $p->siswa->kelas->nama ?? '-' }}</td>
            <td data-label="DUDI">{{ $p->dudi->nama ?? '-' }}</td>
            <td data-label="Pembimbing"><small>{{ $p->guru->nama ?? '-' }}<br>{{ $p->pembimbingIndustri->nama ?? '' }}</small></td>
            <td data-label="Masa"><small>{{ \App\Support\Dates::human($p->mulai->format('Y-m-d')) }} – {{ \App\Support\Dates::human($p->selesai->format('Y-m-d')) }}</small></td>
            <td data-label="Status"><span class="badge {{ $p->status === 'aktif' ? 'b-ok' : ($p->status === 'ditarik' ? 'b-bad' : '') }}">{{ \App\Models\Penempatan::STATUS[$p->status] }}</span></td></tr>
    @empty
        <tr><td colspan="6"><div class="empty">Belum ada siswa PKL{{ $q !== '' ? ' yang cocok' : '' }}.</div></td></tr>
    @endforelse
    </tbody></table>
    @include('partials.pager', ['p' => $list])
</div>
@endsection
