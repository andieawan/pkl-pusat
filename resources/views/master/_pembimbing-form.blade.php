@php($pi ??= null)
<div class="grid g2">
    <div class="field"><label>DUDI</label>@include('partials.select', ['name' => 'dudi_id', 'options' => ['' => '– pilih –'] + $dudi->pluck('nama', 'id')->all(), 'selected' => $pi->dudi_id ?? $dudiId, 'attrs' => 'required'])</div>
    <div class="field"><label>Nama</label><input type="text" name="nama" value="{{ $pi->nama ?? '' }}" required maxlength="191"></div>
    <div class="field"><label>Jabatan</label><input type="text" name="jabatan" value="{{ $pi->jabatan ?? '' }}"></div>
    <div class="field"><label>Telepon / WA</label><input type="tel" name="telp" value="{{ $pi->telp ?? '' }}" maxlength="30"></div>
    <div class="field"><label>Username login</label><input type="text" name="username" value="{{ $pi?->user?->username }}" pattern="[a-z0-9._\-]{3,60}" autocapitalize="none" placeholder="{{ $pi?->user ? '' : 'kosongkan bila tanpa akun' }}"></div>
</div>
@if($pi?->user)<label class="chk"><input type="checkbox" name="reset_password" value="1"> Buat password baru</label>@endif
<button class="btn btn-pri" style="margin-top:8px">Simpan</button>
