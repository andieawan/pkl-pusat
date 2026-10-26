@extends('layouts.app', ['title' => 'Manajemen PKL'])
@section('content')
<div class="head"><div><h1>Manajemen PKL</h1><small>{{ config('pkl.school_name') }}</small></div></div>
<div class="card"><div class="empty">
    Akun Anda belum terhubung ke data PKL.<br>
    @if(auth()->user()->hasRole('siswa'))
        Data siswa Anda belum tersalin dari Pusat Sekolah. Minta admin PKL menjalankan <b>Sinkron sekarang</b>.
    @elseif(auth()->user()->hasRole('guru'))
        Anda belum menjadi guru pembimbing siswa PKL, atau data guru Anda belum tersalin dari Pusat Sekolah. Hubungi koordinator PKL.
    @elseif(auth()->user()->hasRole('industri'))
        Akun pembimbing industri ini belum dikaitkan ke data pembimbing. Hubungi koordinator PKL sekolah.
    @else
        Hubungi koordinator PKL.
    @endif
</div></div>
@endsection
