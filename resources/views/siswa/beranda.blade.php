@extends('layouts.app', ['title' => 'Absen PKL'])
@section('content')
<div class="head"><div><h1>Absen PKL</h1><small>{{ \App\Support\Dates::long($hari) }}</small></div></div>
@if($tertunda >= max(1, (int) config('pkl.jurnal_tertunda_hari')))<div class="alert alert-warn"><span class="alert-ic" aria-hidden="true">!</span><span class="alert-txt">Jurnal <b>{{ $tertunda }} hari kerja</b> terakhir belum diisi. <a href="{{ route('siswa.jurnal', ['tanggal' => date('Y-m-d', strtotime($hari.' -1 day'))]) }}"><b>Isi jurnal</b></a></span></div>@endif
@if($revisi)<div class="alert alert-warn"><span class="alert-ic" aria-hidden="true">!</span><span class="alert-txt">{{ $revisi }} jurnal perlu diperbaiki. <a href="{{ route('siswa.jurnal') }}"><b>Buka jurnal</b></a></span></div>@endif

@if(! $p)
    <div class="card"><div class="empty">
        @if($terakhir)
            Hari ini bukan masa PKL Anda ({{ \App\Support\Dates::human($terakhir->mulai->format('Y-m-d')) }} – {{ \App\Support\Dates::human($terakhir->selesai->format('Y-m-d')) }}@if($terakhir->status !== 'aktif'), status {{ \App\Models\Penempatan::STATUS[$terakhir->status] }}@endif).
        @else
            Anda belum memiliki penempatan PKL. Hubungi koordinator PKL.
        @endif
    </div></div>
    @if($terakhir)@include('siswa._kartu', ['p' => $terakhir])@endif
@else
    @php($kerja = in_array(\App\Support\Dates::weekday($hari) ?: 7, $p->dudi->hariKerja(), true))
    <div class="card selfcheck">
        @if(! $absen)
            @unless($kerja)<p class="mut" style="margin-bottom:10px">Hari ini bukan hari kerja {{ $p->dudi->nama }}. Absen tetap bisa dilakukan bila Anda masuk.</p>@endunless
            <form method="post" action="{{ route('siswa.absen') }}" enctype="multipart/form-data" data-geo="1" data-geo-opsional>
                @csrf <input type="hidden" name="aksi" value="masuk"><input type="hidden" name="lat"><input type="hidden" name="lng">
                <h2>Absen masuk</h2>
                <p class="mut">Batas tepat waktu pukul <b>{{ $batas }}</b>. Ambil selfie di tempat PKL; lokasi HP akan dibaca otomatis.</p>
                <div class="field"><label for="foto">Foto selfie</label><input id="foto" type="file" name="foto" accept="image/*" capture="user" required></div>
                <button class="btn btn-pri" name="aksi" value="masuk" style="width:100%">📍 Absen Masuk</button>
            </form>
            <details style="margin-top:14px"><summary><b>Tidak masuk hari ini? Lapor izin / sakit</b></summary>
                <form method="post" action="{{ route('siswa.absen') }}" enctype="multipart/form-data" style="margin-top:10px">
                    @csrf <input type="hidden" name="aksi" value="lapor">
                    <div class="field"><label>Jenis</label>@include('partials.select', ['name' => 'status', 'options' => ['I' => 'Izin', 'S' => 'Sakit']])</div>
                    <div class="field"><label for="ket">Alasan</label><textarea id="ket" name="keterangan" rows="2" maxlength="500" required placeholder="mis. demam, sudah ke puskesmas"></textarea></div>
                    <div class="field"><label for="bukti">Bukti (foto surat dokter/izin, opsional)</label><input id="bukti" type="file" name="bukti" accept="image/*,application/pdf"></div>
                    <button class="btn">Kirim laporan</button>
                </form>
            </details>
        @elseif($absen->status === 'H' && ! $absen->jam_pulang)
            <p>✅ Masuk pukul <b>{{ $absen->jam_masuk }}</b>@if($absen->terlambat) <span class="badge b-warn">Terlambat</span>@endif @if($absen->perlu_dicek)<span class="badge b-warn">Lokasi diperiksa pembimbing</span>@endif</p>
            <form method="post" action="{{ route('siswa.absen') }}" enctype="multipart/form-data" data-geo="1" data-geo-opsional style="margin-top:12px">
                @csrf <input type="hidden" name="aksi" value="pulang"><input type="hidden" name="lat"><input type="hidden" name="lng">
                <h2>Absen pulang</h2>
                <p class="mut">Jam pulang {{ $p->dudi->jam_pulang }}.</p>
                <div class="field"><label for="foto2">Foto (opsional)</label><input id="foto2" type="file" name="foto" accept="image/*" capture="user"></div>
                <button class="btn btn-pri" name="aksi" value="pulang" style="width:100%">🏠 Absen Pulang</button>
            </form>
        @elseif($absen->status === 'H')
            <p>✅ Hari ini selesai: masuk <b>{{ $absen->jam_masuk }}</b>, pulang <b>{{ $absen->jam_pulang }}</b>.@if($absen->terlambat) <span class="badge b-warn">Terlambat</span>@endif</p>
        @else
            <p>Hari ini tercatat <span class="lg {{ $absen->status }}">{{ $absen->status }}</span> <b>{{ \App\Models\Absensi::STATUS[$absen->status] }}</b> — {{ $absen->keterangan }}
                @if($absen->verifikasi === 'ok')<span class="badge b-ok">Disetujui</span>@elseif($absen->verifikasi === 'tolak')<span class="badge b-bad">Ditolak</span>@else<span class="badge">Menunggu pembimbing</span>@endif</p>
        @endif
    </div>

    <div class="quick">
        <a class="quick-item {{ $jurnalHariIni ? '' : 'primary' }}" href="{{ route('siswa.jurnal') }}"><span class="bk-ic" aria-hidden="true">📝</span><span><b>Jurnal hari ini</b><small>{{ $jurnalHariIni ? \App\Models\Jurnal::STATUS[$jurnalHariIni->status] : 'Belum diisi' }}</small></span></a>
        <a class="quick-item" href="{{ route('siswa.riwayat') }}"><span class="bk-ic" aria-hidden="true">📅</span><span><b>Riwayat absen</b><small>@if($rekap){{ $rekap['H'] }} hadir dari {{ $rekap['hari'] }} hari kerja @endif</small></span></a>
    </div>
    @include('siswa._kartu', ['p' => $p])
@endif
@endsection
