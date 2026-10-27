@extends('layouts.app', ['title' => 'Integrasi'])
@section('content')
<div class="head"><div><h1>Integrasi</h1><small>Sambungan ke Pusat Sekolah: akun serta data induk (kelas, siswa, guru) yang dipakai di PKL.</small></div></div>
@include('admin._pusat')
<div class="card"><h2>Tata Usaha (nomor surat)</h2>
    <p style="margin:6px 0 10px">Status: @if($tuAktif)<span class="badge b-ok">Kunci API terisi</span>@else<span class="badge b-warn">Belum diatur — isi TU_URL dan TU_API_KEY di .env</span>@endif</p>
    <div class="scroll"><table class="tbl" style="font-size:14px">
        <tr><td style="width:190px" class="mut">Alamat TU</td><td>{{ $tuUrl ?: '-' }}</td></tr>
        <tr><td class="mut">Kegunaan</td><td>Surat pengantar & penarikan mengambil nomor dari register Surat Keluar TU. Kunci = <code>TU_API_KUNCI_PKL</code> di .env TU.</td></tr>
    </table></div>
    <div class="row" style="margin-top:12px"><form method="post" action="{{ route('admin.integrasi.tu') }}">@csrf<button class="btn">Uji koneksi TU</button></form></div>
</div>
<div class="card"><h2>Data PKL untuk aplikasi lain</h2>
    <div class="scroll"><table class="tbl" style="font-size:14px">
        <tr><td style="width:190px" class="mut">BK (siswa perlu perhatian)</td><td>@if($apiBk)<span class="badge b-ok">Kunci BK terisi</span>@else<span class="badge b-warn">Belum — isi PKL_API_KUNCI_BK</span>@endif <small class="mut">BK membaca rekap kehadiran, jurnal tertunda, dan nilai akhir siswa PKL.</small></td></tr>
        <tr><td class="mut">Website (mitra DUDI)</td><td>@if($mitraUrl)<code>{{ $mitraUrl }}</code> <small class="mut">nama & bidang DUDI + jumlah siswa, tanpa data siswa</small>@else<span class="badge">Ditutup (PKL_MITRA_PUBLIK=false)</span>@endif</td></tr>
    </table></div>
</div>
@endsection
