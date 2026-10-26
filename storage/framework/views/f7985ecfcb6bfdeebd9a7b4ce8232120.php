<?php $__env->startSection('content'); ?>
<div class="head"><div><h1>Jurnal Kegiatan</h1><small>Tulis kegiatan setiap hari kerja, lalu ajukan ke pembimbing.</small></div></div>
<?php if(! $p): ?>
    <div class="card"><div class="empty">Anda belum memiliki penempatan PKL.</div></div>
<?php else: ?>
    <?php ($kunci = $ini && $ini->status === 'disetujui'); ?>
    <form method="get" class="card filter-card"><div class="fields">
        <div class="field"><label for="tgl">Tanggal</label><input id="tgl" type="date" name="tanggal" value="<?php echo e($tanggal); ?>" min="<?php echo e($p->mulai->format('Y-m-d')); ?>" max="<?php echo e(min($hari, $p->selesai->format('Y-m-d'))); ?>" onchange="this.form.submit()"></div>
    </div></form>
    <form method="post" action="<?php echo e(route('siswa.jurnal.simpan')); ?>" enctype="multipart/form-data" class="card" data-unsaved>
        <?php echo csrf_field(); ?> <input type="hidden" name="tanggal" value="<?php echo e($tanggal); ?>">
        <h2><?php echo e(\App\Support\Dates::long($tanggal)); ?> <?php if($ini): ?><span class="badge <?php echo e(['disetujui' => 'b-ok', 'revisi' => 'b-bad', 'diajukan' => 'b-warn'][$ini->status] ?? ''); ?>"><?php echo e(\App\Models\Jurnal::STATUS[$ini->status]); ?></span><?php endif; ?></h2>
        <?php if($ini?->catatan): ?><div class="alert alert-warn"><span class="alert-txt"><b>Catatan pembimbing:</b> <?php echo e($ini->catatan); ?></span></div><?php endif; ?>
        <div class="field"><label for="keg">Kegiatan / pekerjaan hari ini</label>
            <textarea id="keg" name="kegiatan" rows="6" maxlength="5000" required <?php if($kunci): echo 'disabled'; endif; ?> placeholder="mis. Membantu instalasi jaringan LAN di ruang administrasi, crimping 12 kabel..."><?php echo e(old('kegiatan', $ini->kegiatan ?? '')); ?></textarea></div>
        <?php if($ini?->foto): ?><p><a href="<?php echo e(route('berkas', ['jurnal', $ini->id, 'foto'])); ?>" target="_blank">📷 Lihat foto kegiatan</a></p><?php endif; ?>
        <?php if (! ($kunci)): ?>
            <div class="field"><label for="fj">Foto kegiatan (opsional)</label><input id="fj" type="file" name="foto" accept="image/*"></div>
            <div class="row"><button class="btn" name="aksi" value="draft">Simpan draf</button><button class="btn btn-pri" name="aksi" value="ajukan">Ajukan ke pembimbing</button></div>
        <?php endif; ?>
    </form>
    <div class="card card-tight">
        <div style="padding:14px 16px;border-bottom:1px solid var(--line2)"><h2>Jurnal sebelumnya</h2></div>
        <?php $__empty_1 = true; $__currentLoopData = $daftar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a class="stu" style="color:inherit" href="<?php echo e(route('siswa.jurnal', ['tanggal' => $j->tanggal->format('Y-m-d')])); ?>">
                <span class="nm"><b><?php echo e(\App\Support\Dates::human($j->tanggal->format('Y-m-d'))); ?></b><small><?php echo e(\Illuminate\Support\Str::limit($j->kegiatan, 90)); ?></small></span>
                <span class="badge <?php echo e(['disetujui' => 'b-ok', 'revisi' => 'b-bad', 'diajukan' => 'b-warn'][$j->status] ?? ''); ?>"><?php echo e(\App\Models\Jurnal::STATUS[$j->status]); ?></span></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="empty">Belum ada jurnal.</div>
        <?php endif; ?>
        <?php echo $__env->make('partials.pager', ['p' => $daftar], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'Jurnal PKL'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/claude/pkl-pusat/resources/views/siswa/jurnal.blade.php ENDPATH**/ ?>