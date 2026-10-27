<?php $__env->startSection('content'); ?>
<div class="head"><div><h1>Periksa Absensi &amp; Jurnal</h1><small>Absen di luar lokasi DUDI, laporan izin/sakit, dan jurnal yang diajukan siswa.</small></div></div>

<form method="post" action="<?php echo e(route('bimbingan.verifikasi.absensi')); ?>" class="card card-tight"><?php echo csrf_field(); ?>
    <div style="padding:14px 16px;border-bottom:1px solid var(--line2)"><h2>Absensi (<?php echo e($absensi->count()); ?>)</h2></div>
    <?php $__empty_1 = true; $__currentLoopData = $absensi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <label class="stu" style="cursor:pointer"><input type="checkbox" name="id[]" value="<?php echo e($a->id); ?>" class="chk-in" style="width:20px;height:20px">
            <span class="nm"><b><?php echo e($a->penempatan->siswa->nama ?? '-'); ?></b>
                <small><?php echo e(\App\Support\Dates::human($a->tanggal->format('Y-m-d'))); ?> · <?php echo e($a->penempatan->dudi->nama ?? ''); ?> · <?php echo e(\App\Models\Absensi::STATUS[$a->status]); ?><?php if($a->jam_masuk): ?> <?php echo e($a->jam_masuk); ?><?php endif; ?>
                    <?php if($a->perlu_dicek): ?> · <?php echo e($a->lat === null ? 'lokasi tidak terbaca' : 'jarak '.$a->jarak.' m dari DUDI'); ?><?php endif; ?>
                    <?php if($a->keterangan): ?> · <?php echo e($a->keterangan); ?><?php endif; ?></small>
                <small><?php if($a->lat !== null): ?><a href="https://www.google.com/maps?q=<?php echo e($a->lat); ?>,<?php echo e($a->lng); ?>" target="_blank" rel="noopener">🗺 Peta</a> <?php endif; ?> <?php echo $__env->make('bimbingan._foto', ['row' => $a, 'jenis' => 'absensi', 'kolom' => ['foto_masuk' => 'Selfie', 'foto_pulang' => 'Pulang', 'bukti' => 'Bukti']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></small></span>
            <span class="lg <?php echo e($a->status); ?>"><?php echo e($a->status); ?></span></label>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="empty">Tidak ada absensi yang perlu diperiksa. 👍</div>
    <?php endif; ?>
    <?php if($absensi->isNotEmpty()): ?>
        <div class="sticky-save"><span class="mut">Centang lalu pilih:</span><div class="row"><button class="btn" name="hasil" value="tolak">✕ Tolak</button><button class="btn btn-pri" name="hasil" value="ok">✓ Setujui</button></div></div>
    <?php endif; ?>
</form>

<form method="post" action="<?php echo e(route('bimbingan.verifikasi.jurnal')); ?>" class="card card-tight"><?php echo csrf_field(); ?>
    <div style="padding:14px 16px;border-bottom:1px solid var(--line2)"><h2>Jurnal diajukan (<?php echo e($jurnal->count()); ?>)</h2></div>
    <?php $__empty_1 = true; $__currentLoopData = $jurnal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <label class="stu" style="cursor:pointer;align-items:flex-start"><input type="checkbox" name="id[]" value="<?php echo e($j->id); ?>" style="width:20px;height:20px">
            <span class="nm"><b><?php echo e($j->penempatan->siswa->nama ?? '-'); ?> · <?php echo e(\App\Support\Dates::human($j->tanggal->format('Y-m-d'))); ?></b>
                <small style="white-space:pre-line"><?php echo e($j->kegiatan); ?></small>
                <?php if($j->foto): ?><small><a href="<?php echo e(route('berkas', ['jurnal', $j->id, 'foto'])); ?>" target="_blank" rel="noopener">📷 Foto</a></small><?php endif; ?></span></label>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="empty">Tidak ada jurnal yang menunggu. 👍</div>
    <?php endif; ?>
    <?php if($jurnal->isNotEmpty()): ?>
        <div style="padding:12px 16px"><div class="field"><label for="cat">Catatan (wajib bila minta revisi)</label><input id="cat" type="text" name="catatan" maxlength="500"></div>
            <div class="row"><button class="btn" name="hasil" value="revisi">↺ Minta revisi</button><button class="btn btn-pri" name="hasil" value="disetujui">✓ Setujui</button></div></div>
    <?php endif; ?>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'Periksa'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/claude/pkl-pusat/resources/views/bimbingan/verifikasi.blade.php ENDPATH**/ ?>