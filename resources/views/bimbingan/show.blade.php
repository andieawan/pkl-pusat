@extends('layouts.app', ['title' => $p->siswa->nama ?? 'Siswa PKL'])
@section('content')
@php($me = auth()->user())
<a class="back-link" href="{{ route('bimbingan.index') }}">‹ Daftar siswa PKL</a>
<div class="head"><div><h1>{{ $p->siswa->nama ?? '-' }}</h1><small>{{ $p->siswa->nis ?? '' }} · {{ $p->siswa->kelas->nama ?? '-' }} · {{ $p->periode->nama ?? '' }}@if($p->siswa?->no_hp) · 📞 {{ $p->siswa->no_hp }}@endif</small></div>
    @if($nilai['lengkap'])<a class="btn" href="{{ route('laporan.sertifikat', $p) }}" target="_blank">🎓 Sertifikat</a>@endif</div>

<div class="split">
<div>
    <div class="kpis kpis-4">
        <div class="kpi"><small>Hadir</small><div class="n ok">{{ $rekap['H'] }}</div><small>{{ $rekap['persen'] === null ? '-' : $rekap['persen'].'%' }} dari {{ $rekap['hari'] }} hari</small></div>
        <div class="kpi"><small>Izin / Sakit</small><div class="n">{{ $rekap['I'] + $rekap['S'] }}</div></div>
        <div class="kpi"><small>Alpa / kosong</small><div class="n {{ $rekap['A'] + $rekap['kosong'] ? 'bad' : 'ok' }}">{{ $rekap['A'] + $rekap['kosong'] }}</div></div>
        <div class="kpi"><small>Nilai akhir</small><div class="n">{{ $nilai['akhir'] ?? '–' }}</div><small>{{ $nilai['lengkap'] ? $nilai['huruf'] : 'belum lengkap' }}</small></div>
    </div>

    <div class="card card-tight" id="absensi">
        <div style="padding:14px 16px;border-bottom:1px solid var(--line2)"><h2>Absensi</h2></div>
        <div class="scroll"><table class="tbl tbl-cards">
            <thead><tr><th>Tanggal</th><th>Status</th><th>Jam</th><th>Lokasi</th><th>Bukti</th></tr></thead>
            <tbody>
            @forelse($absensi as $a)
                <tr><td class="card-title">{{ \App\Support\Dates::human($a->tanggal->format('Y-m-d')) }}@if($a->sumber === 'koreksi') <small class="mut">(koreksi)</small>@endif</td>
                    <td data-label="Status"><span class="lg {{ $a->status }}">{{ $a->status }}</span> @if($a->terlambat)<span class="badge b-warn">Terlambat</span>@endif
                        @if($a->verifikasi === 'ok')<span class="badge b-ok">OK</span>@elseif($a->verifikasi === 'tolak')<span class="badge b-bad">Ditolak</span>@elseif($a->perlu_dicek || in_array($a->status, ['I', 'S']))<span class="badge b-warn">Perlu dicek</span>@endif
                        @if($a->keterangan)<br><small>{{ $a->keterangan }}</small>@endif</td>
                    <td data-label="Jam" class="mono">{{ $a->jam_masuk }}{{ $a->jam_pulang ? '–'.$a->jam_pulang : '' }}</td>
                    <td data-label="Lokasi">@if($a->lat !== null)<a href="https://www.google.com/maps?q={{ $a->lat }},{{ $a->lng }}" target="_blank" rel="noopener">{{ $a->jarak !== null ? $a->jarak.' m' : 'peta' }}</a>@else<span class="mut">–</span>@endif</td>
                    <td data-label="Bukti">@include('bimbingan._foto', ['row' => $a, 'jenis' => 'absensi', 'kolom' => ['foto_masuk' => 'Masuk', 'foto_pulang' => 'Pulang', 'bukti' => 'Bukti']])</td></tr>
            @empty
                <tr><td colspan="5"><div class="empty">Belum ada absensi.</div></td></tr>
            @endforelse
            </tbody></table></div>
        @if($me->isPengelola())
            <details style="padding:12px 16px"><summary><b>Koreksi absensi</b> (siswa lupa absen, HP rusak, dsb.)</summary>
                <form method="post" action="{{ route('bimbingan.koreksi', $p) }}" class="fields" style="margin-top:10px">@csrf
                    <div class="field"><label>Tanggal</label><input type="date" name="tanggal" required min="{{ $p->mulai->format('Y-m-d') }}" max="{{ min($hari, $p->selesai->format('Y-m-d')) }}"></div>
                    <div class="field"><label>Status</label>@include('partials.select', ['name' => 'status', 'options' => \App\Models\Absensi::STATUS])</div>
                    <div class="field"><label>Keterangan</label><input type="text" name="keterangan" maxlength="500" placeholder="alasan koreksi"></div>
                    <div class="field"><button class="btn">Simpan koreksi</button></div>
                </form></details>
        @endif
    </div>

    <div class="card card-tight" id="jurnal">
        <div style="padding:14px 16px;border-bottom:1px solid var(--line2)"><h2>Jurnal kegiatan</h2></div>
        @forelse($jurnal as $j)
            <div class="stu"><span class="nm"><b>{{ \App\Support\Dates::human($j->tanggal->format('Y-m-d')) }}</b> <span class="badge {{ ['disetujui' => 'b-ok', 'revisi' => 'b-bad', 'diajukan' => 'b-warn'][$j->status] ?? '' }}">{{ \App\Models\Jurnal::STATUS[$j->status] }}</span>
                <small style="white-space:pre-line">{{ $j->kegiatan }}</small>@if($j->catatan)<small class="warn">Catatan: {{ $j->catatan }}</small>@endif
                @if($j->foto)<small><a href="{{ route('berkas', ['jurnal', $j->id, 'foto']) }}" target="_blank" rel="noopener">📷 Foto</a></small>@endif</span>
                @if($pembimbing && $j->status === 'diajukan')
                    <form method="post" action="{{ route('bimbingan.verifikasi.jurnal') }}">@csrf<input type="hidden" name="id[]" value="{{ $j->id }}"><button class="btn btn-sm" name="hasil" value="disetujui">✓ Setujui</button></form>
                @endif</div>
        @empty
            <div class="empty">Belum ada jurnal.</div>
        @endforelse
    </div>
</div>

<div>
    <div class="card">
        <h2>{{ $p->dudi->nama ?? '-' }}</h2>
        <small>{{ $p->dudi->alamat ?? '' }}</small>
        <table class="kv" style="margin-top:8px;font-size:14px">
            <tr><td>Masa</td><td>{{ \App\Support\Dates::human($p->mulai->format('Y-m-d')) }} – {{ \App\Support\Dates::human($p->selesai->format('Y-m-d')) }}</td></tr>
            <tr><td>Status</td><td>{{ \App\Models\Penempatan::STATUS[$p->status] }}</td></tr>
            <tr><td>Guru pembimbing</td><td>{{ $p->guru->nama ?? '-' }}</td></tr>
            <tr><td>Pembimbing industri</td><td>{{ $p->pembimbingIndustri->nama ?? '-' }}@if($p->pembimbingIndustri?->telp)<br><small>{{ $p->pembimbingIndustri->telp }}</small>@endif</td></tr>
            @if($p->catatan)<tr><td>Catatan</td><td>{{ $p->catatan }}</td></tr>@endif
        </table>
        @if($me->isPengelola())
            <details style="margin-top:10px"><summary><b>Ubah penempatan</b></summary>
                <form method="post" action="{{ route('master.penempatan.ubah', $p) }}" style="margin-top:10px">@csrf @method('PUT')
                    @php($dudiAll = \App\Models\Dudi::orderBy('nama')->with('pembimbing')->get())
                    <div class="field"><label>DUDI</label>@include('partials.select', ['name' => 'dudi_id', 'options' => $dudiAll->pluck('nama', 'id')->all(), 'selected' => $p->dudi_id])</div>
                    <div class="field"><label>Pembimbing industri</label><select name="pembimbing_industri_id"><option value="">–</option>
                        @foreach($dudiAll as $d)@foreach($d->pembimbing as $pi)<option value="{{ $pi->id }}" @selected($pi->id === $p->pembimbing_industri_id)>{{ $pi->nama }} ({{ $d->nama }})</option>@endforeach @endforeach</select></div>
                    <div class="field"><label>Guru pembimbing</label>@include('partials.select', ['name' => 'guru_id', 'options' => ['' => '–'] + \App\Models\Pegawai::aktif()->orderBy('nama')->pluck('nama', 'id')->all(), 'selected' => $p->guru_id])</div>
                    <div class="grid g2"><div class="field"><label>Mulai</label><input type="date" name="mulai" value="{{ $p->mulai->format('Y-m-d') }}"></div><div class="field"><label>Selesai</label><input type="date" name="selesai" value="{{ $p->selesai->format('Y-m-d') }}"></div></div>
                    <div class="field"><label>Status</label>@include('partials.select', ['name' => 'status', 'options' => \App\Models\Penempatan::STATUS, 'selected' => $p->status])</div>
                    <div class="field"><label>Catatan</label><input type="text" name="catatan" value="{{ $p->catatan }}" maxlength="500" placeholder="mis. alasan penarikan"></div>
                    <button class="btn btn-pri">Simpan</button>
                </form></details>
        @endif
    </div>

    <div class="card" id="nilai">
        <h2>Nilai</h2>
        @php($bisa = in_array(true, $bolehNilai, true))
        <form method="post" action="{{ route('bimbingan.nilai', $p) }}">@csrf
            <table class="tbl" style="font-size:14px"><tbody>
            @foreach($nilai['rincian'] as $r)
                @php($k = $r['komponen'])
                <tr><td>{{ $k->nama }}<br><small>{{ \App\Models\KomponenNilai::SUMBER[$k->sumber] }} · {{ $k->bobot }}%</small></td>
                    <td class="r" style="width:90px">@if($bolehNilai[$k->id] ?? false)<input type="number" name="skor[{{ $k->id }}]" value="{{ $r['skor'] }}" min="0" max="100" step="0.01" inputmode="decimal" style="width:80px">@else<b>{{ $r['skor'] ?? '–' }}</b>@endif</td></tr>
            @endforeach
            <tr><td><b>Nilai akhir</b></td><td class="r"><b>{{ $nilai['akhir'] ?? '–' }}</b> {{ $nilai['lengkap'] ? $nilai['huruf'] : '' }}</td></tr>
            </tbody></table>
            @if($bisa)<button class="btn btn-pri" style="margin-top:10px">Simpan nilai</button>@endif
        </form>
    </div>

    <div class="card" id="kunjungan">
        <h2>Kunjungan / monitoring</h2>
        @forelse($kunjungan as $k)
            <div style="border-bottom:1px solid var(--line2);padding:8px 0"><b>{{ \App\Support\Dates::human($k->tanggal->format('Y-m-d')) }}</b> <small>{{ $k->pencatat->nama ?? '' }}</small>
                <div style="white-space:pre-line;font-size:14px">{{ $k->catatan }}</div>
                @if($k->foto)<small><a href="{{ route('berkas', ['kunjungan', $k->id, 'foto']) }}" target="_blank" rel="noopener">📷 Foto</a></small>@endif</div>
        @empty
            <p class="mut">Belum ada catatan kunjungan.</p>
        @endforelse
        @if($guruIni)
            <form method="post" action="{{ route('bimbingan.kunjungan', $p) }}" enctype="multipart/form-data" style="margin-top:10px">@csrf
                <div class="field"><label>Tanggal kunjungan</label><input type="date" name="tanggal" value="{{ $hari }}" max="{{ $hari }}" required></div>
                <div class="field"><label>Hasil kunjungan</label><textarea name="catatan" rows="3" maxlength="5000" required placeholder="Kondisi siswa, masukan dari DUDI, tindak lanjut"></textarea></div>
                <div class="field"><label>Foto (opsional)</label><input type="file" name="foto" accept="image/*"></div>
                <button class="btn">Simpan kunjungan</button>
            </form>
        @endif
    </div>
</div>
</div>
@endsection
