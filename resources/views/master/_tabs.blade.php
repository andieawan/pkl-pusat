<nav class="seg seg-scroll" aria-label="Data PKL" style="margin-bottom:14px">
    @foreach(['master.dudi' => 'DUDI', 'master.pembimbing' => 'Pembimbing Industri', 'master.periode' => 'Periode', 'master.penempatan' => 'Penempatan', 'master.komponen' => 'Komponen Nilai'] as $r => $l)
        <a href="{{ route($r) }}" @class(['on' => request()->routeIs($r)])>{{ $l }}</a>
    @endforeach
</nav>
