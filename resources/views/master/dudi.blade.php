@extends('layouts.app', ['title' => 'DUDI'])
@section('content')
<div class="head"><div><h1>Data PKL</h1><small>Dunia Usaha / Dunia Industri tempat siswa PKL.</small></div></div>
@include('master._tabs')
<details class="card" @if($list->isEmpty()) open @endif><summary><b>+ Tambah DUDI</b></summary>
    <form method="post" action="{{ route('master.dudi.simpan') }}" style="margin-top:12px">@csrf @include('master._dudi-form', ['d' => null])</form></details>
<form method="get" class="card filter-card"><div class="fields"><div class="field"><label for="q">Cari</label><input id="q" type="search" name="q" value="{{ $q }}" placeholder="Nama / bidang"></div><div class="field"><button class="btn">Cari</button></div></div></form>
<div class="card card-tight">
@forelse($list as $d)
    <details class="stu" style="display:block">
        <summary style="display:flex;gap:10px;align-items:center;cursor:pointer"><span class="nm" style="flex:1"><b>{{ $d->nama }}</b>
            <small>{{ $d->bidang ?: '-' }} · {{ $d->jam_masuk }}–{{ $d->jam_pulang }} · {{ $d->aktif_count }} siswa aktif{{ $d->kuota ? ' / kuota '.$d->kuota : '' }}</small></span>
            @unless($d->punyaLokasi())<span class="badge b-warn" title="Absen tidak dicek jaraknya">Tanpa lokasi</span>@endunless
            @unless($d->aktif)<span class="badge">Nonaktif</span>@endunless</summary>
        <form method="post" action="{{ route('master.dudi.ubah', $d) }}" style="margin-top:12px">@csrf @method('PUT') @include('master._dudi-form', ['d' => $d])</form>
        <p style="margin-top:8px"><a href="{{ route('laporan.surat', ['pengantar', 'dudi' => $d->id]) }}" target="_blank">✉ Surat pengantar</a> · <a href="{{ route('laporan.surat', ['penarikan', 'dudi' => $d->id]) }}" target="_blank">✉ Surat penarikan</a> · <a href="{{ route('master.pembimbing', ['dudi' => $d->id]) }}">Pembimbing</a></p>
    </details>
@empty
    <div class="empty">Belum ada DUDI.</div>
@endforelse
@include('partials.pager', ['p' => $list])
</div>
@endsection
