@extends('surat.layout', ['title' => 'Surat '.ucfirst($jenis).' PKL'])
@php($tgl = fn ($ymd) => \App\Support\Dates::long($ymd))
@section('toolbar')
    <input type="hidden" name="dudi" value="{{ $dudi->id }}"><input type="hidden" name="periode" value="{{ $per->id }}">
    <label>Nomor surat<input type="text" name="nomor" value="{{ $nomor }}" placeholder="mis. 012/PKL/{{ config('pkl.kode_surat') }}/X/2026"></label>
    <label>Tempat surat<input type="text" name="tempat" value="{{ $tempat }}"></label>
@endsection
@section('letter')
@include('surat._kop')
<table style="margin-top:10px"><tr><td>Nomor</td><td>:</td><td>{{ $nomor ?: '........................' }}</td></tr>
    <tr><td>Lampiran</td><td>:</td><td>-</td></tr>
    <tr><td>Perihal</td><td>:</td><td><b>{{ $jenis === 'pengantar' ? 'Permohonan Penempatan Siswa PKL' : 'Penarikan Siswa PKL' }}</b></td></tr></table>
<p style="margin-top:14px">Kepada Yth.<br>Pimpinan {{ $dudi->nama }}<br>{{ $dudi->alamat }}<br>di Tempat</p>
<p>Dengan hormat,</p>
@if($jenis === 'pengantar')
    <p>Sehubungan dengan pelaksanaan Praktik Kerja Lapangan (PKL) {{ $cfg['nama'] }}, bersama ini kami menyerahkan siswa berikut untuk melaksanakan PKL di instansi/perusahaan Bapak/Ibu pada tanggal {{ $tgl($per->mulai->format('Y-m-d')) }} sampai dengan {{ $tgl($per->selesai->format('Y-m-d')) }}:</p>
@else
    <p>Sehubungan dengan berakhirnya masa Praktik Kerja Lapangan (PKL) {{ $cfg['nama'] }} pada tanggal {{ $tgl($per->selesai->format('Y-m-d')) }}, bersama ini kami bermaksud menarik kembali siswa berikut:</p>
@endif
<table class="tbl" style="border:1px solid #000;margin:8px 0">
    <thead><tr><th style="border:1px solid #000">No</th><th style="border:1px solid #000">Nama</th><th style="border:1px solid #000">NIS</th><th style="border:1px solid #000">Kelas</th></tr></thead>
    <tbody>@forelse($list as $i => $p)<tr><td style="border:1px solid #000">{{ $i + 1 }}</td><td style="border:1px solid #000">{{ $p->siswa->nama ?? '-' }}</td><td style="border:1px solid #000">{{ $p->siswa->nis ?? '' }}</td><td style="border:1px solid #000">{{ $p->siswa->kelas->nama ?? '' }}</td></tr>
    @empty<tr><td colspan="4">Belum ada siswa ditempatkan.</td></tr>@endforelse</tbody></table>
@if($jenis === 'pengantar')
    <p>Kami mohon bimbingan dan pengawasan Bapak/Ibu selama siswa melaksanakan PKL. Guru pembimbing dari sekolah: {{ $list->pluck('guru.nama')->filter()->unique()->implode(', ') ?: '........................' }}.</p>
@else
    <p>Kami mengucapkan terima kasih atas kesempatan, bimbingan, dan kerja sama yang telah diberikan selama siswa kami melaksanakan PKL. Mohon kesediaan Bapak/Ibu mengisi penilaian siswa.</p>
@endif
<p>Demikian surat ini kami sampaikan. Atas perhatian dan kerja samanya kami ucapkan terima kasih.</p>
<p style="text-align:right;margin-top:24px">{{ $tempat ?: '........................' }}, {{ $tgl(\App\Support\Dates::today()) }}</p>
<div class="ttd"><div>@if(config('pkl.koordinator'))Koordinator PKL,<div class="sp"></div><b><u>{{ config('pkl.koordinator') }}</u></b>@endif</div>
    <div>Kepala Sekolah,<div class="sp"></div><b><u>{{ $cfg['kepsek'] }}</u></b><br>NUPTK. {{ $cfg['kepsek_nuptk'] }}</div></div>
@endsection
