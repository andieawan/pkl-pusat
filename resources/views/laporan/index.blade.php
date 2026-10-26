@extends('layouts.app', ['title' => 'Laporan PKL', 'width' => '1400px'])
@section('content')
@php($me = auth()->user())
<div class="head noprint"><div><h1>Rekap PKL</h1><small>Kehadiran dan nilai per siswa{{ $per ? ' · '.$per->nama : '' }}.</small></div>
    <div class="row"><a class="btn" href="{{ route('laporan.unduh', request()->query()) }}">⬇ Excel</a><button type="button" class="btn" onclick="window.print()">🖨 Cetak</button></div></div>
<form method="get" class="card filter-card noprint"><div class="fields">
    <div class="field"><label>Periode</label>@include('partials.select', ['name' => 'periode', 'options' => $periode->pluck('nama', 'id')->all(), 'selected' => $per?->id])</div>
    @if($me->isPengelola())<div class="field"><label>DUDI</label>@include('partials.select', ['name' => 'dudi', 'options' => ['' => 'Semua DUDI'] + $dudi->pluck('nama', 'id')->all(), 'selected' => $dudiId ?: ''])</div>@endif
    <div class="field"><button class="btn">Tampilkan</button></div>
</div></form>
<div class="card card-tight scroll">
    <h2 class="only-print" style="display:none;padding:10px">Rekap PKL {{ $per->nama ?? '' }} — {{ config('pkl.school_name') }}</h2>
    <table class="tbl" style="font-size:13px">
    <thead><tr><th>No</th><th>Siswa</th><th>Kelas</th><th>DUDI</th><th class="c">Hari</th><th class="c">H</th><th class="c">I</th><th class="c">S</th><th class="c">A</th><th class="c">Kosong</th><th class="c">Telat</th><th class="c">% Hadir</th>
        @foreach($komponen as $k)<th class="c" title="{{ $k->nama }} ({{ $k->bobot }}%)">{{ \Illuminate\Support\Str::limit($k->nama, 14) }}</th>@endforeach<th class="c">Akhir</th><th></th></tr></thead>
    <tbody>
    @forelse($rows as $i => $r)
        @php($p = $r['p']) @php($a = $r['rekap'])
        <tr><td>{{ $i + 1 }}</td><td><a href="{{ route('bimbingan.show', $p) }}">{{ $p->siswa->nama ?? '-' }}</a>@if($p->status !== 'aktif') <small class="mut">({{ \App\Models\Penempatan::STATUS[$p->status] }})</small>@endif</td>
            <td>{{ $p->siswa->kelas->nama ?? '' }}</td><td>{{ $p->dudi->nama ?? '' }}</td>
            <td class="c">{{ $a['hari'] }}</td><td class="c">{{ $a['H'] }}</td><td class="c">{{ $a['I'] }}</td><td class="c">{{ $a['S'] }}</td><td class="c">{{ $a['A'] }}</td>
            <td class="c {{ $a['kosong'] ? 'bad' : '' }}">{{ $a['kosong'] }}</td><td class="c">{{ $a['T'] }}</td><td class="c">{{ $a['persen'] === null ? '-' : $a['persen'].'%' }}</td>
            @foreach($r['nilai']['rincian'] as $x)<td class="c">{{ $x['skor'] ?? '–' }}</td>@endforeach
            <td class="c"><b>{{ $r['nilai']['akhir'] ?? '–' }}</b> {{ $r['nilai']['lengkap'] ? $r['nilai']['huruf'] : '' }}</td>
            <td class="noprint">@if($r['nilai']['lengkap'])<a href="{{ route('laporan.sertifikat', $p) }}" target="_blank" title="Sertifikat">🎓</a>@endif</td></tr>
    @empty
        <tr><td colspan="{{ 14 + $komponen->count() }}"><div class="empty">Tidak ada data untuk periode ini.</div></td></tr>
    @endforelse
    </tbody></table>
</div>
<p class="mut noprint"><small>Kosong = hari kerja DUDI tanpa catatan absen apa pun. % hadir dihitung dari hari kerja yang sudah berlalu.</small></p>
@endsection
