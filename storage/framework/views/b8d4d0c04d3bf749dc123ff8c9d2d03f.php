<?php $__env->startSection('content'); ?>
<?php ($me = auth()->user()); ?>
<div class="head noprint"><div><h1>Rekap PKL</h1><small>Kehadiran dan nilai per siswa<?php echo e($per ? ' · '.$per->nama : ''); ?>.</small></div>
    <div class="row"><a class="btn" href="<?php echo e(route('laporan.unduh', request()->query())); ?>">⬇ Excel</a><button type="button" class="btn" onclick="window.print()">🖨 Cetak</button></div></div>
<form method="get" class="card filter-card noprint"><div class="fields">
    <div class="field"><label>Periode</label><?php echo $__env->make('partials.select', ['name' => 'periode', 'options' => $periode->pluck('nama', 'id')->all(), 'selected' => $per?->id], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
    <?php if($me->isPengelola()): ?><div class="field"><label>DUDI</label><?php echo $__env->make('partials.select', ['name' => 'dudi', 'options' => ['' => 'Semua DUDI'] + $dudi->pluck('nama', 'id')->all(), 'selected' => $dudiId ?: ''], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div><?php endif; ?>
    <div class="field"><button class="btn">Tampilkan</button></div>
</div></form>
<div class="card card-tight scroll">
    <h2 class="only-print" style="display:none;padding:10px">Rekap PKL <?php echo e($per->nama ?? ''); ?> — <?php echo e(config('pkl.school_name')); ?></h2>
    <table class="tbl" style="font-size:13px">
    <thead><tr><th>No</th><th>Siswa</th><th>Kelas</th><th>DUDI</th><th class="c">Hari</th><th class="c">H</th><th class="c">I</th><th class="c">S</th><th class="c">A</th><th class="c">Kosong</th><th class="c">Telat</th><th class="c">% Hadir</th>
        <?php $__currentLoopData = $komponen; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><th class="c" title="<?php echo e($k->nama); ?> (<?php echo e($k->bobot); ?>%)"><?php echo e(\Illuminate\Support\Str::limit($k->nama, 14)); ?></th><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><th class="c">Akhir</th><th></th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php ($p = $r['p']); ?> <?php ($a = $r['rekap']); ?>
        <tr><td><?php echo e($i + 1); ?></td><td><a href="<?php echo e(route('bimbingan.show', $p)); ?>"><?php echo e($p->siswa->nama ?? '-'); ?></a><?php if($p->status !== 'aktif'): ?> <small class="mut">(<?php echo e(\App\Models\Penempatan::STATUS[$p->status]); ?>)</small><?php endif; ?></td>
            <td><?php echo e($p->siswa->kelas->nama ?? ''); ?></td><td><?php echo e($p->dudi->nama ?? ''); ?></td>
            <td class="c"><?php echo e($a['hari']); ?></td><td class="c"><?php echo e($a['H']); ?></td><td class="c"><?php echo e($a['I']); ?></td><td class="c"><?php echo e($a['S']); ?></td><td class="c"><?php echo e($a['A']); ?></td>
            <td class="c <?php echo e($a['kosong'] ? 'bad' : ''); ?>"><?php echo e($a['kosong']); ?></td><td class="c"><?php echo e($a['T']); ?></td><td class="c"><?php echo e($a['persen'] === null ? '-' : $a['persen'].'%'); ?></td>
            <?php $__currentLoopData = $r['nilai']['rincian']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $x): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><td class="c"><?php echo e($x['skor'] ?? '–'); ?></td><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <td class="c"><b><?php echo e($r['nilai']['akhir'] ?? '–'); ?></b> <?php echo e($r['nilai']['lengkap'] ? $r['nilai']['huruf'] : ''); ?></td>
            <td class="noprint"><?php if($r['nilai']['lengkap']): ?><a href="<?php echo e(route('laporan.sertifikat', $p)); ?>" target="_blank" title="Sertifikat">🎓</a><?php endif; ?></td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="<?php echo e(14 + $komponen->count()); ?>"><div class="empty">Tidak ada data untuk periode ini.</div></td></tr>
    <?php endif; ?>
    </tbody></table>
</div>
<p class="mut noprint"><small>Kosong = hari kerja DUDI tanpa catatan absen apa pun. % hadir dihitung dari hari kerja yang sudah berlalu.</small></p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'Laporan PKL', 'width' => '1400px'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/claude/pkl-pusat/resources/views/laporan/index.blade.php ENDPATH**/ ?>