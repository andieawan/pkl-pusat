@extends('layouts.app', ['title' => 'Periksa'])
@section('content')
<div class="head"><div><h1>Periksa Absensi &amp; Jurnal</h1><small>Absen di luar lokasi DUDI, laporan izin/sakit, dan jurnal yang diajukan siswa.</small></div></div>

<form method="post" action="{{ route('bimbingan.verifikasi.absensi') }}" class="card card-tight">@csrf
    <div style="padding:14px 16px;border-bottom:1px solid var(--line2)"><h2>Absensi ({{ $absensi->count() }})</h2></div>
    @forelse($absensi as $a)
        <label class="stu" style="cursor:pointer"><input type="checkbox" name="id[]" value="{{ $a->id }}" class="chk-in" style="width:20px;height:20px">
            <span class="nm"><b>{{ $a->penempatan->siswa->nama ?? '-' }}</b>
                <small>{{ \App\Support\Dates::human($a->tanggal->format('Y-m-d')) }} · {{ $a->penempatan->dudi->nama ?? '' }} · {{ \App\Models\Absensi::STATUS[$a->status] }}@if($a->jam_masuk) {{ $a->jam_masuk }}@endif
                    @if($a->perlu_dicek) · {{ $a->lat === null ? 'lokasi tidak terbaca' : 'jarak '.$a->jarak.' m dari DUDI' }}@endif
                    @if($a->keterangan) · {{ $a->keterangan }}@endif</small>
                <small>@if($a->lat !== null)<a href="https://www.google.com/maps?q={{ $a->lat }},{{ $a->lng }}" target="_blank" rel="noopener">🗺 Peta</a> @endif @include('bimbingan._foto', ['row' => $a, 'jenis' => 'absensi', 'kolom' => ['foto_masuk' => 'Selfie', 'foto_pulang' => 'Pulang', 'bukti' => 'Bukti']])</small></span>
            <span class="lg {{ $a->status }}">{{ $a->status }}</span></label>
    @empty
        <div class="empty">Tidak ada absensi yang perlu diperiksa. 👍</div>
    @endforelse
    @if($absensi->isNotEmpty())
        <div class="sticky-save"><span class="mut">Centang lalu pilih:</span><div class="row"><button class="btn" name="hasil" value="tolak">✕ Tolak</button><button class="btn btn-pri" name="hasil" value="ok">✓ Setujui</button></div></div>
    @endif
</form>

<form method="post" action="{{ route('bimbingan.verifikasi.jurnal') }}" class="card card-tight">@csrf
    <div style="padding:14px 16px;border-bottom:1px solid var(--line2)"><h2>Jurnal diajukan ({{ $jurnal->count() }})</h2></div>
    @forelse($jurnal as $j)
        <label class="stu" style="cursor:pointer;align-items:flex-start"><input type="checkbox" name="id[]" value="{{ $j->id }}" style="width:20px;height:20px">
            <span class="nm"><b>{{ $j->penempatan->siswa->nama ?? '-' }} · {{ \App\Support\Dates::human($j->tanggal->format('Y-m-d')) }}</b>
                <small style="white-space:pre-line">{{ $j->kegiatan }}</small>
                @if($j->foto)<small><a href="{{ route('berkas', ['jurnal', $j->id, 'foto']) }}" target="_blank" rel="noopener">📷 Foto</a></small>@endif</span></label>
    @empty
        <div class="empty">Tidak ada jurnal yang menunggu. 👍</div>
    @endforelse
    @if($jurnal->isNotEmpty())
        <div style="padding:12px 16px"><div class="field"><label for="cat">Catatan (wajib bila minta revisi)</label><input id="cat" type="text" name="catatan" maxlength="500"></div>
            <div class="row"><button class="btn" name="hasil" value="revisi">↺ Minta revisi</button><button class="btn btn-pri" name="hasil" value="disetujui">✓ Setujui</button></div></div>
    @endif
</form>
@endsection
