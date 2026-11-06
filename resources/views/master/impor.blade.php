@extends('layouts.app', ['title' => 'Impor data PKL lama'])
@section('content')
<div class="head"><div><h1>Data PKL</h1><small>Impor DUDI &amp; pembimbing industri dari Excel lama.</small></div></div>
@include('master._tabs')

@if(session('galat'))
    <div class="card"><h2>Baris yang perlu diperbaiki</h2><ul class="galat">@foreach(session('galat') as $g)<li>{{ $g }}</li>@endforeach</ul></div>
@endif
@if($akun = session('akun_impor'))
    <div class="card"><h2>Akun pembimbing industri baru</h2>
        <p class="mut" style="margin:0 0 8px">Alamat login: <b>{{ url('/login') }}</b>. Catat atau cetak sekarang — password hanya ditampilkan sekali.</p>
        <div class="scroll"><table class="tbl"><thead><tr><th>Nama</th><th>Username</th><th>Password</th></tr></thead>
        <tbody>@foreach($akun as $a)<tr><td>{{ $a['nama'] }}</td><td class="mono">{{ $a['username'] }}</td><td class="mono">{{ $a['password'] }}</td></tr>@endforeach</tbody></table></div>
    </div>
@endif

<div class="card">
    <h2>1. Unduh templat</h2>
    <p class="mut" style="margin:0 0 10px">Berisi lembar PETUNJUK, DUDI, dan PEMBIMBING INDUSTRI. Satu baris = satu data.</p>
    <a class="btn" href="{{ route('master.impor.templat') }}">Unduh templat (.xlsx)</a>
    <div class="scroll" style="margin-top:12px"><table class="tbl">
        <thead><tr><th>Lembar</th><th>Kolom</th></tr></thead>
        <tbody>
            <tr><td><b>DUDI</b></td><td>{{ implode(' · ', \App\Services\ImporMaster::DUDI) }}</td></tr>
            <tr><td><b>PEMBIMBING INDUSTRI</b></td><td>{{ implode(' · ', \App\Services\ImporMaster::PEMBIMBING) }}</td></tr>
        </tbody>
    </table></div>
</div>

<div class="card">
    <h2>2. Isi lalu unggah</h2>
    <ul>
        <li>Pembimbing dicocokkan ke DUDI lewat <b>Nama DUDI</b> (dari lembar DUDI atau yang sudah ada di aplikasi).</li>
        <li>Username Login diisi bila pembimbing perlu akun; password dibuat otomatis dan ditampilkan sekali setelah impor.</li>
        <li>Data yang sudah ada dilewati. Bila ada satu baris salah, tidak ada yang disimpan — tekan <b>Periksa saja</b> dulu.</li>
    </ul>
    <form method="post" action="{{ route('master.impor.unggah') }}" enctype="multipart/form-data" class="fields" style="margin-top:10px">
        @csrf
        <div style="grid-column:span 2"><label for="berkas">Berkas templat (.xlsx)</label><input id="berkas" type="file" name="berkas" accept=".xlsx" required></div>
        <div class="row" style="align-self:end"><button class="btn" name="uji" value="1">Periksa saja</button><button class="btn btn-pri" name="uji" value="0">Impor</button></div>
    </form>
</div>
@endsection
