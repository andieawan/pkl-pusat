<?php $__env->startSection('content'); ?>
<?php ($me = auth()->user()); ?>
<?php ($hadir = $absen->where('status', 'H')->count()); ?>
<?php ($belum = $aktif->count() - $absen->count()); ?>
<div class="head"><div><h1>Ringkasan PKL</h1><small><?php echo e(\App\Support\Dates::long($hari)); ?> · <?php echo e($me->isPengelola() ? 'Semua siswa PKL' : 'Siswa bimbingan Anda'); ?></small></div></div>

<div class="kpis kpis-4">
    <div class="kpi"><small>Sedang PKL hari ini</small><div class="n"><?php echo e($aktif->count()); ?></div><small>dari <?php echo e($totalPenempatan); ?> penempatan</small></div>
    <div class="kpi"><small>Sudah absen masuk</small><div class="n ok"><?php echo e($hadir); ?></div><small><?php echo e($absen->whereIn('status', ['I', 'S'])->count()); ?> izin/sakit · <?php echo e($absen->where('terlambat', true)->count()); ?> terlambat</small></div>
    <div class="kpi"><small>Belum absen</small><div class="n <?php echo e($belum ? 'warn' : 'ok'); ?>"><?php echo e($belum); ?></div></div>
    <div class="kpi"><small>Perlu diperiksa</small><div class="n <?php echo e($perluDicek + $jurnalMenunggu ? 'warn' : 'ok'); ?>"><?php echo e($perluDicek + $jurnalMenunggu); ?></div><small><?php echo e($perluDicek); ?> absensi · <?php echo e($jurnalMenunggu); ?> jurnal <?php if($tertunda->isNotEmpty()): ?> · <?php echo e($tertunda->count()); ?> siswa jurnal tertunda <?php endif; ?></small></div>
</div>

<div class="quick">
    <?php if($perluDicek + $jurnalMenunggu): ?><a class="quick-item primary" href="<?php echo e(route('bimbingan.verifikasi')); ?>"><span class="bk-ic" aria-hidden="true">✅</span><span><b>Periksa absensi &amp; jurnal</b><small><?php echo e($perluDicek + $jurnalMenunggu); ?> menunggu</small></span></a><?php endif; ?>
    <a class="quick-item" href="<?php echo e(route('bimbingan.index')); ?>"><span class="bk-ic" aria-hidden="true">👥</span><span><b>Daftar siswa PKL</b><small>Detail, kunjungan, nilai</small></span></a>
    <a class="quick-item" href="<?php echo e(route('laporan.index')); ?>"><span class="bk-ic" aria-hidden="true">📊</span><span><b>Rekap &amp; laporan</b><small>Absensi, nilai, Excel</small></span></a>
    <?php if($me->isPengelola()): ?><a class="quick-item" href="<?php echo e(route('master.penempatan')); ?>"><span class="bk-ic" aria-hidden="true">🏢</span><span><b>Penempatan siswa</b><small>Tempatkan siswa ke DUDI</small></span></a><?php endif; ?>
</div>

<div class="card card-tight">
    <div style="padding:14px 16px;border-bottom:1px solid var(--line2)"><h2>Kehadiran hari ini</h2></div>
    <?php $__empty_1 = true; $__currentLoopData = $aktif; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php ($a = $absen[$p->id] ?? null); ?>
        <a class="stu" style="color:inherit" href="<?php echo e(route('bimbingan.show', $p)); ?>">
            <span class="nm"><b><?php echo e($p->siswa->nama ?? '-'); ?></b><small><?php echo e($p->siswa->kelas->nama ?? ''); ?> · <?php echo e($p->dudi->nama ?? '-'); ?><?php if($a?->jam_masuk): ?> · masuk <?php echo e($a->jam_masuk); ?><?php endif; ?> <?php if($a?->jam_pulang): ?>– pulang <?php echo e($a->jam_pulang); ?><?php endif; ?></small></span>
            <?php if(isset($tertunda[$p->id])): ?><span class="badge b-warn" title="Hari kerja berturut-turut tanpa jurnal">Jurnal <?php echo e($tertunda[$p->id]); ?> hari</span><?php endif; ?>
            <?php if($a): ?>
                <?php if($a->perlu_dicek): ?><span class="badge b-warn" title="Lokasi di luar area DUDI atau tidak terbaca">Cek lokasi</span><?php endif; ?>
                <?php if($a->terlambat): ?><span class="badge b-warn">Terlambat</span><?php endif; ?>
                <span class="lg <?php echo e($a->status); ?>" title="<?php echo e(\App\Models\Absensi::STATUS[$a->status]); ?>"><?php echo e($a->status); ?></span>
            <?php else: ?>
                <span class="badge">Belum absen</span>
            <?php endif; ?>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="empty">Tidak ada siswa yang sedang PKL hari ini.<?php if($me->isPengelola()): ?> Tambahkan periode dan penempatan di menu <a href="<?php echo e(route('master.dudi')); ?>">Data PKL</a>.<?php endif; ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'Ringkasan PKL'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/claude/pkl-pusat/resources/views/beranda.blade.php ENDPATH**/ ?>