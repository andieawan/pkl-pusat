<?php $__env->startSection('content'); ?>
<div class="head"><div><h1>Data PKL</h1><small>Pembimbing dari DUDI. Bila diberi username, ia bisa login untuk memeriksa absensi, jurnal, dan mengisi nilai.</small></div></div>
<?php echo $__env->make('master._tabs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php if($a = session('akun_baru')): ?>
    <div class="alert alert-info" role="status"><span class="alert-ic" aria-hidden="true">🔑</span><span class="alert-txt">
        Akun untuk <b><?php echo e($a['nama']); ?></b>: username <b class="mono"><?php echo e($a['username']); ?></b>, password <b class="mono"><?php echo e($a['password']); ?></b><br>
        Alamat login: <b><?php echo e(url('/login')); ?></b>. Catat sekarang — password ini hanya ditampilkan sekali.</span></div>
<?php endif; ?>
<details class="card" <?php if($list->isEmpty()): ?> open <?php endif; ?>><summary><b>+ Tambah pembimbing industri</b></summary>
    <form method="post" action="<?php echo e(route('master.pembimbing.simpan')); ?>" style="margin-top:12px"><?php echo csrf_field(); ?> <?php echo $__env->make('master._pembimbing-form', ['pi' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></form></details>
<form method="get" class="card filter-card"><div class="fields"><div class="field"><label>DUDI</label><?php echo $__env->make('partials.select', ['name' => 'dudi', 'options' => ['' => 'Semua DUDI'] + $dudi->pluck('nama', 'id')->all(), 'selected' => $dudiId ?: '', 'attrs' => 'onchange="this.form.submit()"'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div></div></form>
<div class="card card-tight">
<?php $__empty_1 = true; $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <details class="stu" style="display:block">
        <summary style="display:flex;gap:10px;align-items:center;cursor:pointer"><span class="nm" style="flex:1"><b><?php echo e($pi->nama); ?></b><small><?php echo e($pi->dudi->nama ?? '-'); ?><?php echo e($pi->jabatan ? ' · '.$pi->jabatan : ''); ?><?php echo e($pi->telp ? ' · '.$pi->telp : ''); ?></small></span>
            <?php if($pi->user): ?><span class="badge b-ok"><?php echo e('@'.$pi->user->username); ?></span><?php else: ?><span class="badge">Tanpa akun</span><?php endif; ?></summary>
        <form method="post" action="<?php echo e(route('master.pembimbing.ubah', $pi)); ?>" style="margin-top:12px"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?> <?php echo $__env->make('master._pembimbing-form', ['pi' => $pi], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></form>
    </details>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="empty">Belum ada pembimbing industri.</div>
<?php endif; ?>
<?php echo $__env->make('partials.pager', ['p' => $list], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'Pembimbing Industri'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/claude/pkl-pusat/resources/views/master/pembimbing.blade.php ENDPATH**/ ?>