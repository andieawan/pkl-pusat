@php($d ??= null) @php($sfx = $d?->id ?? 'baru')
<div class="grid g2">
    <div class="field"><label>Nama DUDI</label><input type="text" name="nama" value="{{ $d->nama ?? '' }}" required maxlength="191"></div>
    <div class="field"><label>Bidang usaha</label><input type="text" name="bidang" value="{{ $d->bidang ?? '' }}" maxlength="191" placeholder="mis. Jaringan komputer"></div>
</div>
<div class="field"><label>Alamat</label><input type="text" name="alamat" value="{{ $d->alamat ?? '' }}" maxlength="500"></div>
<div class="grid g3">
    <div class="field"><label>Kontak</label><input type="text" name="kontak_nama" value="{{ $d->kontak_nama ?? '' }}"></div>
    <div class="field"><label>Telepon</label><input type="tel" name="telp" value="{{ $d->telp ?? '' }}" maxlength="30"></div>
    <div class="field"><label>Email</label><input type="email" name="email" value="{{ $d->email ?? '' }}"></div>
</div>
<div class="grid g3">
    <div class="field"><label>Latitude</label><input id="lat-{{ $sfx }}" type="text" inputmode="decimal" name="lat" value="{{ $d->lat ?? '' }}" placeholder="-8.15"></div>
    <div class="field"><label>Longitude</label><input id="lng-{{ $sfx }}" type="text" inputmode="decimal" name="lng" value="{{ $d->lng ?? '' }}" placeholder="113.76"></div>
    <div class="field"><label>Radius absen (meter)</label><input type="number" name="radius" value="{{ $d->radius ?? config('pkl.radius_bawaan') }}" min="20" max="5000"></div>
</div>
<p><button type="button" class="btn btn-sm" data-geo-fill="#lat-{{ $sfx }},#lng-{{ $sfx }}">📍 Isi dengan lokasi saya sekarang</button> <small>atau salin koordinat dari Google Maps (klik kanan pada peta).</small></p>
<div class="grid g3">
    <div class="field"><label>Jam masuk</label><input type="time" name="jam_masuk" value="{{ $d->jam_masuk ?? '08:00' }}"></div>
    <div class="field"><label>Jam pulang</label><input type="time" name="jam_pulang" value="{{ $d->jam_pulang ?? '16:00' }}"></div>
    <div class="field"><label>Kuota siswa</label><input type="number" name="kuota" value="{{ $d->kuota ?? '' }}" min="0"></div>
</div>
<div class="field"><label>Hari kerja</label><div class="row">
    @foreach(\App\Models\Dudi::HARI as $n => $h)<label class="chk"><input type="checkbox" name="hari_kerja[]" value="{{ $n }}" @checked(in_array($n, $d?->hariKerja() ?? [1, 2, 3, 4, 5, 6], true))>{{ $h }}</label>@endforeach
</div></div>
@if($d)<label class="chk"><input type="checkbox" name="aktif" value="1" @checked($d->aktif)> Aktif (bisa dipilih untuk penempatan)</label>@endif
<button class="btn btn-pri" style="margin-top:8px">Simpan</button>
