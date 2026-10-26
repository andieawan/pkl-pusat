@foreach($kolom as $k => $lbl)
    @if($row->{$k})<a href="{{ route('berkas', [$jenis, $row->id, $k]) }}" target="_blank" rel="noopener">📷 {{ $lbl }}</a> @endif
@endforeach
