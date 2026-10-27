@extends('layouts.app', ['title' => 'Ringkasan PKL'])
@section('content')
@php($me = auth()->user())
@php($hadir = $absen->where('status', 'H')->count())
@php($belum = $aktif->count() - $absen->count())
<div class="head"><div><h1>Ringkasan PKL</h1><small>{{ \App\Support\Dates::long($hari) }} · {{ $me->isPengelola() ? 'Semua siswa PKL' : 'Siswa bimbingan Anda' }}</small></div></div>

<div class="kpis kpis-4">
    <div class="kpi"><small>Sedang PKL hari ini</small><div class="n">{{ $aktif->count() }}</div><small>dari {{ $totalPenempatan }} penempatan</small></div>
    <div class="kpi"><small>Sudah absen masuk</small><div class="n ok">{{ $hadir }}</div><small>{{ $absen->whereIn('status', ['I', 'S'])->count() }} izin/sakit · {{ $absen->where('terlambat', true)->count() }} terlambat</small></div>
    <div class="kpi"><small>Belum absen</small><div class="n {{ $belum ? 'warn' : 'ok' }}">{{ $belum }}</div></div>
    <div class="kpi"><small>Perlu diperiksa</small><div class="n {{ $perluDicek + $jurnalMenunggu ? 'warn' : 'ok' }}">{{ $perluDicek + $jurnalMenunggu }}</div><small>{{ $perluDicek }} absensi · {{ $jurnalMenunggu }} jurnal @if($tertunda->isNotEmpty()) · {{ $tertunda->count() }} siswa jurnal tertunda @endif</small></div>
</div>

<div class="quick">
    @if($perluDicek + $jurnalMenunggu)<a class="quick-item primary" href="{{ route('bimbingan.verifikasi') }}"><span class="bk-ic" aria-hidden="true">✅</span><span><b>Periksa absensi &amp; jurnal</b><small>{{ $perluDicek + $jurnalMenunggu }} menunggu</small></span></a>@endif
    <a class="quick-item" href="{{ route('bimbingan.index') }}"><span class="bk-ic" aria-hidden="true">👥</span><span><b>Daftar siswa PKL</b><small>Detail, kunjungan, nilai</small></span></a>
    <a class="quick-item" href="{{ route('laporan.index') }}"><span class="bk-ic" aria-hidden="true">📊</span><span><b>Rekap &amp; laporan</b><small>Absensi, nilai, Excel</small></span></a>
    @if($me->isPengelola())<a class="quick-item" href="{{ route('master.penempatan') }}"><span class="bk-ic" aria-hidden="true">🏢</span><span><b>Penempatan siswa</b><small>Tempatkan siswa ke DUDI</small></span></a>@endif
</div>

<div class="card card-tight">
    <div style="padding:14px 16px;border-bottom:1px solid var(--line2)"><h2>Kehadiran hari ini</h2></div>
    @forelse($aktif as $p)
        @php($a = $absen[$p->id] ?? null)
        <a class="stu" style="color:inherit" href="{{ route('bimbingan.show', $p) }}">
            <span class="nm"><b>{{ $p->siswa->nama ?? '-' }}</b><small>{{ $p->siswa->kelas->nama ?? '' }} · {{ $p->dudi->nama ?? '-' }}@if($a?->jam_masuk) · masuk {{ $a->jam_masuk }}@endif @if($a?->jam_pulang)– pulang {{ $a->jam_pulang }}@endif</small></span>
            @if(isset($tertunda[$p->id]))<span class="badge b-warn" title="Hari kerja berturut-turut tanpa jurnal">Jurnal {{ $tertunda[$p->id] }} hari</span>@endif
            @if($a)
                @if($a->perlu_dicek)<span class="badge b-warn" title="Lokasi di luar area DUDI atau tidak terbaca">Cek lokasi</span>@endif
                @if($a->terlambat)<span class="badge b-warn">Terlambat</span>@endif
                <span class="lg {{ $a->status }}" title="{{ \App\Models\Absensi::STATUS[$a->status] }}">{{ $a->status }}</span>
            @else
                <span class="badge">Belum absen</span>
            @endif
        </a>
    @empty
        <div class="empty">Tidak ada siswa yang sedang PKL hari ini.@if($me->isPengelola()) Tambahkan periode dan penempatan di menu <a href="{{ route('master.dudi') }}">Data PKL</a>.@endif</div>
    @endforelse
</div>
@endsection
