@extends('layouts.app', ['title' => 'Jurnal PKL'])
@section('content')
<div class="head"><div><h1>Jurnal Kegiatan</h1><small>Tulis kegiatan setiap hari kerja, lalu ajukan ke pembimbing.</small></div></div>
@if(! $p)
    <div class="card"><div class="empty">Anda belum memiliki penempatan PKL.</div></div>
@else
    @php($kunci = $ini && $ini->status === 'disetujui')
    <form method="get" class="card filter-card"><div class="fields">
        <div class="field"><label for="tgl">Tanggal</label><input id="tgl" type="date" name="tanggal" value="{{ $tanggal }}" min="{{ $p->mulai->format('Y-m-d') }}" max="{{ min($hari, $p->selesai->format('Y-m-d')) }}" onchange="this.form.submit()"></div>
    </div></form>
    <form method="post" action="{{ route('siswa.jurnal.simpan') }}" enctype="multipart/form-data" class="card" data-unsaved>
        @csrf <input type="hidden" name="tanggal" value="{{ $tanggal }}">
        <h2>{{ \App\Support\Dates::long($tanggal) }} @if($ini)<span class="badge {{ ['disetujui' => 'b-ok', 'revisi' => 'b-bad', 'diajukan' => 'b-warn'][$ini->status] ?? '' }}">{{ \App\Models\Jurnal::STATUS[$ini->status] }}</span>@endif</h2>
        @if($ini?->catatan)<div class="alert alert-warn"><span class="alert-txt"><b>Catatan pembimbing:</b> {{ $ini->catatan }}</span></div>@endif
        <div class="field"><label for="keg">Kegiatan / pekerjaan hari ini</label>
            <textarea id="keg" name="kegiatan" rows="6" maxlength="5000" required @disabled($kunci) placeholder="mis. Membantu instalasi jaringan LAN di ruang administrasi, crimping 12 kabel...">{{ old('kegiatan', $ini->kegiatan ?? '') }}</textarea></div>
        @if($ini?->foto)<p><a href="{{ route('berkas', ['jurnal', $ini->id, 'foto']) }}" target="_blank">📷 Lihat foto kegiatan</a></p>@endif
        @unless($kunci)
            <div class="field"><label for="fj">Foto kegiatan (opsional)</label><input id="fj" type="file" name="foto" accept="image/*"></div>
            <div class="row"><button class="btn" name="aksi" value="draft">Simpan draf</button><button class="btn btn-pri" name="aksi" value="ajukan">Ajukan ke pembimbing</button></div>
        @endunless
    </form>
    <div class="card card-tight">
        <div style="padding:14px 16px;border-bottom:1px solid var(--line2)"><h2>Jurnal sebelumnya</h2></div>
        @forelse($daftar as $j)
            <a class="stu" style="color:inherit" href="{{ route('siswa.jurnal', ['tanggal' => $j->tanggal->format('Y-m-d')]) }}">
                <span class="nm"><b>{{ \App\Support\Dates::human($j->tanggal->format('Y-m-d')) }}</b><small>{{ \Illuminate\Support\Str::limit($j->kegiatan, 90) }}</small></span>
                <span class="badge {{ ['disetujui' => 'b-ok', 'revisi' => 'b-bad', 'diajukan' => 'b-warn'][$j->status] ?? '' }}">{{ \App\Models\Jurnal::STATUS[$j->status] }}</span></a>
        @empty
            <div class="empty">Belum ada jurnal.</div>
        @endforelse
        @include('partials.pager', ['p' => $daftar])
    </div>
@endif
@endsection
