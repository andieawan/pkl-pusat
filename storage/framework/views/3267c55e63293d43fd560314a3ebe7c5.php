<?php $__env->startSection('content'); ?>
<div class="head"><div><h1>Data PKL</h1><small>Tempatkan beberapa siswa sekaligus ke satu DUDI.</small></div></div>
<?php echo $__env->make('master._tabs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php if($periode->isEmpty()): ?>
    <div class="card"><div class="empty">Buat <a href="<?php echo e(route('master.periode')); ?>">periode PKL</a> dulu.</div></div>
<?php else: ?>
<form method="get" class="card filter-card"><div class="fields">
    <div class="field"><label>Periode</label><?php echo $__env->make('partials.select', ['name' => 'periode', 'options' => $periode->pluck('nama', 'id')->all(), 'selected' => $periodeId, 'attrs' => 'onchange="this.form.submit()"'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
    <div class="field"><label>Kelas</label><?php echo $__env->make('partials.select', ['name' => 'kelas', 'options' => ['' => '– pilih kelas –'] + $kelas->pluck('nama', 'id')->all(), 'selected' => $kelasId ?: '', 'attrs' => 'onchange="this.form.submit()"'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
</div></form>
<?php if($kelasId): ?>
<form method="post" action="<?php echo e(route('master.penempatan.simpan')); ?>" class="card"><?php echo csrf_field(); ?> <input type="hidden" name="periode_id" value="<?php echo e($periodeId); ?>">
    <div class="grid g2">
        <div class="field"><label>DUDI</label><select name="dudi_id" required><option value="">– pilih –</option><?php $__currentLoopData = $dudi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($d->id); ?>"><?php echo e($d->nama); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div class="field"><label>Pembimbing industri</label><select name="pembimbing_industri_id"><option value="">–</option><?php $__currentLoopData = $dudi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php $__currentLoopData = $d->pembimbing; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($pi->id); ?>"><?php echo e($pi->nama); ?> (<?php echo e($d->nama); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
        <div class="field"><label>Guru pembimbing</label><?php echo $__env->make('partials.select', ['name' => 'guru_id', 'options' => ['' => '–'] + $guru->pluck('nama', 'id')->all()], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
        <div class="grid g2"><div class="field"><label>Mulai</label><input type="date" name="mulai" placeholder="bawaan periode"></div><div class="field"><label>Selesai</label><input type="date" name="selesai"></div></div>
    </div>
    <small>Tanggal kosong = mengikuti periode.</small>
    <h2 style="margin-top:14px">Siswa</h2>
    <?php $__empty_1 = true; $__currentLoopData = $siswa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php ($ada = array_key_exists($s->id, $sudah)); ?>
        <label class="chk"><input type="checkbox" name="siswa[]" value="<?php echo e($s->id); ?>" <?php if($ada): echo 'disabled'; endif; ?>> <?php echo e($s->nama); ?> <small class="mono"><?php echo e($s->nis); ?></small><?php if($ada): ?> <span class="badge">sudah ditempatkan</span><?php endif; ?></label>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="mut">Tidak ada siswa aktif di kelas ini.</p>
    <?php endif; ?>
    <button class="btn btn-pri" style="margin-top:10px">Tempatkan siswa terpilih</button>
</form>
<?php endif; ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'Penempatan'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/claude/pkl-pusat/resources/views/master/penempatan.blade.php ENDPATH**/ ?>