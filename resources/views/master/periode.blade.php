@extends('layouts.app', ['title' => 'Periode PKL'])
@section('content')
<div class="head"><div><h1>Data PKL</h1><small>Gelombang/periode PKL. Tanggal periode menjadi tanggal bawaan penempatan.</small></div></div>
@include('master._tabs')
<details class="card" @if($list->isEmpty()) open @endif><summary><b>+ Tambah periode</b></summary>
    <form method="post" action="{{ route('master.periode.simpan') }}" class="fields" style="margin-top:12px">@csrf
        <div class="field"><label>Nama</label><input type="text" name="nama" required placeholder="PKL 2026/2027 Gelombang 1"></div>
        <div class="field"><label>Mulai</label><input type="date" name="mulai" required></div>
        <div class="field"><label>Selesai</label><input type="date" name="selesai" required></div>
        <div class="field"><button class="btn btn-pri">Simpan</button></div>
    </form></details>
<div class="card card-tight">
@forelse($list as $p)
    <form method="post" action="{{ route('master.periode.ubah', $p) }}" class="stu fields">@csrf @method('PUT')
        <div class="field"><label>Nama</label><input type="text" name="nama" value="{{ $p->nama }}" required></div>
        <div class="field"><label>Mulai</label><input type="date" name="mulai" value="{{ $p->mulai->format('Y-m-d') }}" required></div>
        <div class="field"><label>Selesai</label><input type="date" name="selesai" value="{{ $p->selesai->format('Y-m-d') }}" required></div>
        <div class="field"><label class="chk"><input type="checkbox" name="aktif" value="1" @checked($p->aktif)> Aktif</label><small>{{ $p->penempatan_count }} penempatan</small></div>
        <div class="field"><button class="btn btn-sm">Simpan</button></div>
    </form>
@empty
    <div class="empty">Belum ada periode.</div>
@endforelse
</div>
@endsection
