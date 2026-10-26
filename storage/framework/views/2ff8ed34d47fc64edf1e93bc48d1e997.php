<?php $__env->startSection('content'); ?>
<div class="head"><div><h1>Nilai PKL</h1><small><?php echo e($p?->dudi?->nama); ?></small></div></div>
<?php if(! $p): ?>
    <div class="card"><div class="empty">Anda belum memiliki penempatan PKL.</div></div>
<?php else: ?>
    <div class="kpis"><div class="kpi kpi-main"><small>Nilai akhir</small><div class="n"><?php echo e($n['akhir'] ?? '–'); ?></div><small><?php echo e($n['lengkap'] ? $n['huruf'].' · '.\App\Services\NilaiService::predikat($n['akhir']) : 'Belum lengkap, menunggu pembimbing'); ?></small></div></div>
    <div class="card card-tight"><table class="tbl">
        <thead><tr><th>Komponen</th><th>Penilai</th><th class="c">Bobot</th><th class="c">Nilai</th></tr></thead>
        <tbody><?php $__currentLoopData = $n['rincian']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr><td><?php echo e($r['komponen']->nama); ?></td><td><?php echo e(\App\Models\KomponenNilai::SUMBER[$r['komponen']->sumber]); ?></td><td class="c"><?php echo e($r['komponen']->bobot); ?>%</td><td class="c"><b><?php echo e($r['skor'] ?? '–'); ?></b></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></tbody></table></div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'Nilai PKL'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/claude/pkl-pusat/resources/views/siswa/nilai.blade.php ENDPATH**/ ?>