{{-- Ringkas info penempatan siswa --}}
<div class="card">
    <h2 style="margin-bottom:6px">{{ $p->dudi->nama ?? '-' }}</h2>
    <small>{{ $p->dudi->alamat ?? '' }}</small>
    <table class="kv" style="margin-top:8px;font-size:14px">
        <tr><td>Masa PKL</td><td>{{ \App\Support\Dates::human($p->mulai->format('Y-m-d')) }} – {{ \App\Support\Dates::human($p->selesai->format('Y-m-d')) }}</td></tr>
        <tr><td>Jam kerja</td><td>{{ $p->dudi->jam_masuk ?? '-' }} – {{ $p->dudi->jam_pulang ?? '-' }}</td></tr>
        <tr><td>Pembimbing sekolah</td><td>{{ $p->guru->nama ?? '-' }}</td></tr>
        <tr><td>Pembimbing industri</td><td>{{ $p->pembimbingIndustri->nama ?? '-' }}</td></tr>
        @if($p->status !== 'aktif')<tr><td>Status</td><td><span class="badge">{{ \App\Models\Penempatan::STATUS[$p->status] }}</span></td></tr>@endif
    </table>
</div>
