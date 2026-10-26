@extends('layouts.app', ['title' => 'Sertifikat PKL', 'bare' => true])
@push('head')<style>
@page{size:A4 landscape;margin:12mm}
.serti{background:#fff;max-width:1050px;margin:0 auto;padding:36px 48px;border:6px double #1e3a8a;font-family:Georgia,"Times New Roman",serif;color:#111;text-align:center}
.serti h1{font-size:34pt;letter-spacing:.12em;margin:12px 0 0;color:#1e3a8a}.serti .nm{font-size:24pt;font-weight:700;margin:10px 0;border-bottom:1px solid #999;display:inline-block;padding:0 24px}
.serti table{margin:14px auto;border-collapse:collapse;font-size:11pt}.serti td,.serti th{border:1px solid #555;padding:4px 10px}
.serti .ttd{display:flex;justify-content:space-between;margin-top:26px}.serti .ttd>div{min-width:260px}.serti .sp{height:64px}
@media print{.noprint{display:none!important}.serti{border-width:6px}body{background:#fff}}
</style>@endpush
@section('content')
<div class="noprint" style="text-align:center;margin:12px"><button class="btn btn-pri" onclick="window.print()">Cetak / Simpan PDF</button> <small>Atur kertas A4 mendatar (landscape).</small></div>
<div class="serti">
    <div style="display:flex;align-items:center;gap:14px;justify-content:center"><img src="{{ asset('assets/logo-ibu.png') }}" alt="" width="70" height="70"><div><b>{{ $cfg['yayasan'] }} {{ $cfg['yayasan2'] }}</b><br><b style="font-size:15pt">{{ $cfg['nama'] }}</b><br><small>{{ $cfg['alamat'] }}</small></div></div>
    <h1>SERTIFIKAT</h1><div>PRAKTIK KERJA LAPANGAN</div>
    <p style="margin-top:14px">Diberikan kepada:</p>
    <div class="nm">{{ $p->siswa->nama ?? '-' }}</div>
    <p>NIS {{ $p->siswa->nis ?? '-' }} · Kelas {{ $p->siswa->kelas->nama ?? '-' }}</p>
    <p>yang telah melaksanakan Praktik Kerja Lapangan di <b>{{ $p->dudi->nama ?? '-' }}</b><br>pada {{ \App\Support\Dates::long($p->mulai->format('Y-m-d')) }} s.d. {{ \App\Support\Dates::long($p->selesai->format('Y-m-d')) }} dengan hasil:</p>
    <table><thead><tr><th>Komponen</th><th>Nilai</th></tr></thead><tbody>
        @foreach($n['rincian'] as $r)<tr><td style="text-align:left">{{ $r['komponen']->nama }}</td><td>{{ $r['skor'] }}</td></tr>@endforeach
        <tr><th style="text-align:left">Nilai Akhir</th><th>{{ $n['akhir'] }} ({{ $n['huruf'] }} – {{ \App\Services\NilaiService::predikat($n['akhir']) }})</th></tr></tbody></table>
    <div class="ttd">
        <div>Pimpinan / Pembimbing Industri<br>{{ $p->dudi->nama ?? '' }}<div class="sp"></div><b><u>{{ $p->pembimbingIndustri->nama ?? '(........................)' }}</u></b></div>
        <div>{{ $cfg['kota'] }}, {{ \App\Support\Dates::long($p->selesai->format('Y-m-d')) }}<br>Kepala Sekolah<div class="sp"></div><b><u>{{ $cfg['kepsek'] }}</u></b><br>NUPTK. {{ $cfg['kepsek_nuptk'] }}</div>
    </div>
</div>
@endsection
