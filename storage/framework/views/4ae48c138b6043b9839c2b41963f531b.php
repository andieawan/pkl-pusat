<?php $__env->startSection('content'); ?>
<div class="head"><div><h1>Data PKL</h1><small>Dunia Usaha / Dunia Industri tempat siswa PKL.</small></div></div>
<?php echo $__env->make('master._tabs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<details class="card" <?php if($list->isEmpty()): ?> open <?php endif; ?>><summary><b>+ Tambah DUDI</b></summary>
    <form method="post" action="<?php echo e(route('master.dudi.simpan')); ?>" style="margin-top:12px"><?php echo csrf_field(); ?> <?php echo $__env->make('master._dudi-form', ['d' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></form></details>
<form method="get" class="card filter-card"><div class="fields"><div class="field"><label for="q">Cari</label><input id="q" type="search" name="q" value="<?php echo e($q); ?>" placeholder="Nama / bidang"></div><div class="field"><button class="btn">Cari</button></div></div></form>
<div class="card card-tight">
<?php $__empty_1 = true; $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <details class="stu" style="display:block">
        <summary style="display:flex;gap:10px;align-items:center;cursor:pointer"><span class="nm" style="flex:1"><b><?php echo e($d->nama); ?></b>
            <small><?php echo e($d->bidang ?: '-'); ?> · <?php echo e($d->jam_masuk); ?>–<?php echo e($d->jam_pulang); ?> · <?php echo e($d->aktif_count); ?> siswa aktif<?php echo e($d->kuota ? ' / kuota '.$d->kuota : ''); ?></small></span>
            <?php if (! ($d->punyaLokasi())): ?><span class="badge b-warn" title="Absen tidak dicek jaraknya">Tanpa lokasi</span><?php endif; ?>
            <?php if (! ($d->aktif)): ?><span class="badge">Nonaktif</span><?php endif; ?></summary>
        <form method="post" action="<?php echo e(route('master.dudi.ubah', $d)); ?>" style="margin-top:12px"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?> <?php echo $__env->make('master._dudi-form', ['d' => $d], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></form>
        <p style="margin-top:8px"><a href="<?php echo e(route('laporan.surat', ['pengantar', 'dudi' => $d->id])); ?>" target="_blank">✉ Surat pengantar</a> · <a href="<?php echo e(route('laporan.surat', ['penarikan', 'dudi' => $d->id])); ?>" target="_blank">✉ Surat penarikan</a> · <a href="<?php echo e(route('master.pembimbing', ['dudi' => $d->id])); ?>">Pembimbing</a></p>
    </details>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="empty">Belum ada DUDI.</div>
<?php endif; ?>
<?php echo $__env->make('partials.pager', ['p' => $list], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'DUDI'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/claude/pkl-pusat/resources/views/master/dudi.blade.php ENDPATH**/ ?>