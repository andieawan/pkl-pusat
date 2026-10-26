@extends('layouts.app', ['title' => 'Penempatan'])
@section('content')
<div class="head"><div><h1>Data PKL</h1><small>Tempatkan beberapa siswa sekaligus ke satu DUDI.</small></div></div>
@include('master._tabs')
@if($periode->isEmpty())
    <div class="card"><div class="empty">Buat <a href="{{ route('master.periode') }}">periode PKL</a> dulu.</div></div>
@else
<form method="get" class="card filter-card"><div class="fields">
    <div class="field"><label>Periode</label>@include('partials.select', ['name' => 'periode', 'options' => $periode->pluck('nama', 'id')->all(), 'selected' => $periodeId, 'attrs' => 'onchange="this.form.submit()"'])</div>
    <div class="field"><label>Kelas</label>@include('partials.select', ['name' => 'kelas', 'options' => ['' => '– pilih kelas –'] + $kelas->pluck('nama', 'id')->all(), 'selected' => $kelasId ?: '', 'attrs' => 'onchange="this.form.submit()"'])</div>
</div></form>
@if($kelasId)
<form method="post" action="{{ route('master.penempatan.simpan') }}" class="card">@csrf <input type="hidden" name="periode_id" value="{{ $periodeId }}">
    <div class="grid g2">
        <div class="field"><label>DUDI</label><select name="dudi_id" required><option value="">– pilih –</option>@foreach($dudi as $d)<option value="{{ $d->id }}">{{ $d->nama }}</option>@endforeach</select></div>
        <div class="field"><label>Pembimbing industri</label><select name="pembimbing_industri_id"><option value="">–</option>@foreach($dudi as $d)@foreach($d->pembimbing as $pi)<option value="{{ $pi->id }}">{{ $pi->nama }} ({{ $d->nama }})</option>@endforeach @endforeach</select></div>
        <div class="field"><label>Guru pembimbing</label>@include('partials.select', ['name' => 'guru_id', 'options' => ['' => '–'] + $guru->pluck('nama', 'id')->all()])</div>
        <div class="grid g2"><div class="field"><label>Mulai</label><input type="date" name="mulai" placeholder="bawaan periode"></div><div class="field"><label>Selesai</label><input type="date" name="selesai"></div></div>
    </div>
    <small>Tanggal kosong = mengikuti periode.</small>
    <h2 style="margin-top:14px">Siswa</h2>
    @forelse($siswa as $s)
        @php($ada = array_key_exists($s->id, $sudah))
        <label class="chk"><input type="checkbox" name="siswa[]" value="{{ $s->id }}" @disabled($ada)> {{ $s->nama }} <small class="mono">{{ $s->nis }}</small>@if($ada) <span class="badge">sudah ditempatkan</span>@endif</label>
    @empty
        <p class="mut">Tidak ada siswa aktif di kelas ini.</p>
    @endforelse
    <button class="btn btn-pri" style="margin-top:10px">Tempatkan siswa terpilih</button>
</form>
@endif
@endif
@endsection
