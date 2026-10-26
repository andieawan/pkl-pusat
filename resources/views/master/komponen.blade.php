@extends('layouts.app', ['title' => 'Komponen Nilai'])
@section('content')
<div class="head"><div><h1>Data PKL</h1><small>Komponen penilaian dan bobotnya. Total bobot komponen aktif harus 100.</small></div></div>
@include('master._tabs')
<form method="post" action="{{ route('master.komponen.simpan') }}" class="card card-tight">@csrf @method('PUT')
    <div class="scroll"><table class="tbl">
        <thead><tr><th>Nama komponen</th><th>Diisi oleh</th><th class="c">Bobot (%)</th><th class="c">Aktif</th></tr></thead>
        <tbody>
        @foreach($list as $k)
            <tr><td><input type="text" name="komponen[{{ $k->id }}][nama]" value="{{ $k->nama }}" maxlength="191"></td>
                <td>@include('partials.select', ['name' => "komponen[{$k->id}][sumber]", 'options' => \App\Models\KomponenNilai::SUMBER, 'selected' => $k->sumber])</td>
                <td class="c"><input type="number" name="komponen[{{ $k->id }}][bobot]" value="{{ $k->bobot }}" min="0" max="100" style="width:80px"></td>
                <td class="c"><input type="checkbox" name="komponen[{{ $k->id }}][aktif]" value="1" @checked($k->aktif) style="width:20px;height:20px"></td></tr>
        @endforeach
        @foreach(['b1', 'b2'] as $b)
            <tr><td><input type="text" name="komponen[{{ $b }}][nama]" placeholder="+ komponen baru" maxlength="191"></td>
                <td>@include('partials.select', ['name' => "komponen[{$b}][sumber]", 'options' => \App\Models\KomponenNilai::SUMBER])</td>
                <td class="c"><input type="number" name="komponen[{{ $b }}][bobot]" value="0" min="0" max="100" style="width:80px"></td>
                <td class="c"><input type="checkbox" name="komponen[{{ $b }}][aktif]" value="1" checked style="width:20px;height:20px"></td></tr>
        @endforeach
        </tbody></table></div>
    <div style="padding:12px 16px"><small>Komponen nonaktif tidak dihitung dan tidak muncul di form nilai; nilai yang sudah diisi tetap tersimpan.</small><br><button class="btn btn-pri" style="margin-top:8px">Simpan komponen</button></div>
</form>
@endsection
