<?php $__env->startSection('content'); ?>
<?php ($me = auth()->user()); ?>
<div class="head"><div><h1>Siswa PKL</h1><small><?php echo e($me->isPengelola() ? 'Semua penempatan' : 'Siswa bimbingan Anda'); ?></small></div>
    <?php if($me->isPengelola()): ?><a class="btn btn-pri" href="<?php echo e(route('master.penempatan')); ?>">+ Tempatkan siswa</a><?php endif; ?></div>
<form method="get" class="card filter-card"><div class="fields">
    <div class="field"><label>Periode</label><?php echo $__env->make('partials.select', ['name' => 'periode', 'options' => ['' => 'Semua periode'] + $periode->pluck('nama', 'id')->all(), 'selected' => $periodeId ?: ''], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
    <?php if($dudi->isNotEmpty()): ?><div class="field"><label>DUDI</label><?php echo $__env->make('partials.select', ['name' => 'dudi', 'options' => ['' => 'Semua DUDI'] + $dudi->pluck('nama', 'id')->all(), 'selected' => $dudiId ?: ''], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div><?php endif; ?>
    <div class="field"><label>Status</label><?php echo $__env->make('partials.select', ['name' => 'status', 'options' => ['' => 'Semua'] + \App\Models\Penempatan::STATUS, 'selected' => $status], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
    <div class="field"><label for="q">Cari siswa</label><input id="q" type="search" name="q" value="<?php echo e($q); ?>" placeholder="Nama / NIS"></div>
    <div class="field"><button class="btn">Tampilkan</button></div>
</div></form>
<div class="card card-tight scroll"><table class="tbl tbl-cards">
    <thead><tr><th>Siswa</th><th>Kelas</th><th>DUDI</th><th>Pembimbing</th><th>Masa</th><th>Status</th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr><td class="card-title"><a href="<?php echo e(route('bimbingan.show', $p)); ?>"><b><?php echo e($p->siswa->nama ?? '-'); ?></b></a><br><small class="mono"><?php echo e($p->siswa->nis ?? ''); ?></small></td>
            <td data-label="Kelas"><?php echo e($p->siswa->kelas->nama ?? '-'); ?></td>
            <td data-label="DUDI"><?php echo e($p->dudi->nama ?? '-'); ?></td>
            <td data-label="Pembimbing"><small><?php echo e($p->guru->nama ?? '-'); ?><br><?php echo e($p->pembimbingIndustri->nama ?? ''); ?></small></td>
            <td data-label="Masa"><small><?php echo e(\App\Support\Dates::human($p->mulai->format('Y-m-d'))); ?> – <?php echo e(\App\Support\Dates::human($p->selesai->format('Y-m-d'))); ?></small></td>
            <td data-label="Status"><span class="badge <?php echo e($p->status === 'aktif' ? 'b-ok' : ($p->status === 'ditarik' ? 'b-bad' : '')); ?>"><?php echo e(\App\Models\Penempatan::STATUS[$p->status]); ?></span></td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="6"><div class="empty">Belum ada siswa PKL<?php echo e($q !== '' ? ' yang cocok' : ''); ?>.</div></td></tr>
    <?php endif; ?>
    </tbody></table>
    <?php echo $__env->make('partials.pager', ['p' => $list], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'Siswa PKL'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/claude/pkl-pusat/resources/views/bimbingan/index.blade.php ENDPATH**/ ?>