<?php $__env->startSection('content'); ?>
<div class="head"><div><h1>Riwayat Absen PKL</h1><small><?php echo e($p?->dudi?->nama); ?></small></div></div>
<?php if(! $p): ?>
    <div class="card"><div class="empty">Anda belum memiliki penempatan PKL.</div></div>
<?php else: ?>
    <div class="kpis kpis-4">
        <div class="kpi"><small>Hadir</small><div class="n ok"><?php echo e($rekap['H']); ?></div><small><?php echo e($rekap['persen'] === null ? '-' : $rekap['persen'].'%'); ?> dari <?php echo e($rekap['hari']); ?> hari kerja</small></div>
        <div class="kpi"><small>Izin / Sakit</small><div class="n"><?php echo e($rekap['I'] + $rekap['S']); ?></div></div>
        <div class="kpi"><small>Alpa / tanpa keterangan</small><div class="n <?php echo e($rekap['A'] + $rekap['kosong'] ? 'bad' : 'ok'); ?>"><?php echo e($rekap['A'] + $rekap['kosong']); ?></div></div>
        <div class="kpi"><small>Terlambat</small><div class="n <?php echo e($rekap['T'] ? 'warn' : 'ok'); ?>"><?php echo e($rekap['T']); ?></div></div>
    </div>
    <div class="card card-tight scroll"><table class="tbl tbl-cards">
        <thead><tr><th>Tanggal</th><th>Status</th><th>Masuk</th><th>Pulang</th><th>Keterangan</th></tr></thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr><td class="card-title"><?php echo e(\App\Support\Dates::human($a->tanggal->format('Y-m-d'))); ?></td>
                <td data-label="Status"><span class="lg <?php echo e($a->status); ?>"><?php echo e($a->status); ?></span> <?php if($a->terlambat): ?><span class="badge b-warn">Terlambat</span><?php endif; ?> <?php if($a->verifikasi === 'tolak'): ?><span class="badge b-bad">Ditolak</span><?php elseif($a->perlu_dicek): ?><span class="badge b-warn">Diperiksa</span><?php endif; ?></td>
                <td data-label="Masuk" class="mono"><?php echo e($a->jam_masuk ?: ''); ?></td><td data-label="Pulang" class="mono"><?php echo e($a->jam_pulang ?: ''); ?></td>
                <td data-label="Ket"><?php echo e($a->keterangan); ?></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="5"><div class="empty">Belum ada catatan absen.</div></td></tr>
        <?php endif; ?>
        </tbody></table></div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'Riwayat Absen'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/claude/pkl-pusat/resources/views/siswa/riwayat.blade.php ENDPATH**/ ?>