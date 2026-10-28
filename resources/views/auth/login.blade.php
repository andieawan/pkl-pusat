@extends('layouts.app', ['title' => 'Masuk', 'bare' => true])
@section('content')
<div class="card login">
    <img class="logo-login" src="{{ asset('icons/icon-192.png') }}" alt="" width="56" height="56">
    <h1>Manajemen PKL</h1>
    <p class="mut">{{ config('pkl.school_name') }}<br>Guru &amp; siswa: masuk memakai akun Pusat Sekolah. Pembimbing industri: username &amp; password dari sekolah.</p>
    @if(\App\Services\PusatKlien::aktif())<a class="btn btn-pri" style="width:100%;text-align:center;display:block" href="{{ route('sso.login') }}">Masuk lewat Pusat</a>@endif
    @if(config('pkl.login_lokal'))
    <h2 style="margin:18px 0 6px;font-size:15px">Masuk dengan username &amp; password</h2>
    <p class="mut" style="font-size:13px;margin:0 0 8px">Untuk pembimbing industri, dan akun Pusat (password Pusat) bila Pusat sedang tidak bisa dihubungi.</p>
    <form method="post" action="{{ route('login') }}" autocomplete="off">
        @csrf
        <div class="field"><label for="u">Username</label><input id="u" type="text" name="username" value="{{ old('username') }}" autocapitalize="none" autocomplete="username" required></div>
        <div class="field"><label for="pw">Password</label><input id="pw" type="password" name="password" autocomplete="current-password" required></div>
        <button class="btn" style="width:100%" data-busy="Memeriksa…">Masuk</button>
    </form>
    @endif
</div>
@endsection
