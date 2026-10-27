@extends('layouts.app', ['title' => 'Pembimbing Industri'])
@section('content')
<div class="head"><div><h1>Data PKL</h1><small>Pembimbing dari DUDI. Bila diberi username, ia bisa login untuk memeriksa absensi, jurnal, dan mengisi nilai.</small></div></div>
@include('master._tabs')
@if($a = session('akun_baru'))
    <div class="alert alert-info" role="status"><span class="alert-ic" aria-hidden="true">🔑</span><span class="alert-txt">
        Akun untuk <b>{{ $a['nama'] }}</b>: username <b class="mono">{{ $a['username'] }}</b>, password <b class="mono">{{ $a['password'] }}</b><br>
        Alamat login: <b>{{ url('/login') }}</b>. Catat sekarang — password ini hanya ditampilkan sekali.</span></div>
@endif
<details class="card" @if($list->isEmpty()) open @endif><summary><b>+ Tambah pembimbing industri</b></summary>
    <form method="post" action="{{ route('master.pembimbing.simpan') }}" style="margin-top:12px">@csrf @include('master._pembimbing-form', ['pi' => null])</form></details>
<form method="get" class="card filter-card"><div class="fields"><div class="field"><label>DUDI</label>@include('partials.select', ['name' => 'dudi', 'options' => ['' => 'Semua DUDI'] + $dudi->pluck('nama', 'id')->all(), 'selected' => $dudiId ?: '', 'attrs' => 'onchange="this.form.submit()"'])</div></div></form>
<div class="card card-tight">
@forelse($list as $pi)
    <details class="stu" style="display:block">
        <summary style="display:flex;gap:10px;align-items:center;cursor:pointer"><span class="nm" style="flex:1"><b>{{ $pi->nama }}</b><small>{{ $pi->dudi->nama ?? '-' }}{{ $pi->jabatan ? ' · '.$pi->jabatan : '' }}{{ $pi->telp ? ' · '.$pi->telp : '' }}</small></span>
            @if($pi->user && $pi->user->aktif)<span class="badge b-ok">{{ '@'.$pi->user->username }}</span>@elseif($pi->user)<span class="badge b-bad">{{ '@'.$pi->user->username }} · nonaktif</span>@else<span class="badge">Tanpa akun</span>@endif</summary>
        <form method="post" action="{{ route('master.pembimbing.ubah', $pi) }}" style="margin-top:12px">@csrf @method('PUT') @include('master._pembimbing-form', ['pi' => $pi])</form>
    </details>
@empty
    <div class="empty">Belum ada pembimbing industri.</div>
@endforelse
@include('partials.pager', ['p' => $list])
</div>
@endsection
