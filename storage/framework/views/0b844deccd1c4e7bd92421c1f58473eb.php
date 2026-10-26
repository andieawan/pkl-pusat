<?php ($tgl = fn ($ymd) => \App\Support\Dates::long($ymd)); ?>
<?php $__env->startSection('toolbar'); ?>
    <input type="hidden" name="dudi" value="<?php echo e($dudi->id); ?>"><input type="hidden" name="periode" value="<?php echo e($per->id); ?>">
    <label>Nomor surat<input type="text" name="nomor" value="<?php echo e($nomor); ?>" placeholder="mis. 012/PKL/<?php echo e(config('pkl.kode_surat')); ?>/X/2026"></label>
    <label>Tempat surat<input type="text" name="tempat" value="<?php echo e($tempat); ?>"></label>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('letter'); ?>
<?php echo $__env->make('surat._kop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<table style="margin-top:10px"><tr><td>Nomor</td><td>:</td><td><?php echo e($nomor ?: '........................'); ?></td></tr>
    <tr><td>Lampiran</td><td>:</td><td>-</td></tr>
    <tr><td>Perihal</td><td>:</td><td><b><?php echo e($jenis === 'pengantar' ? 'Permohonan Penempatan Siswa PKL' : 'Penarikan Siswa PKL'); ?></b></td></tr></table>
<p style="margin-top:14px">Kepada Yth.<br>Pimpinan <?php echo e($dudi->nama); ?><br><?php echo e($dudi->alamat); ?><br>di Tempat</p>
<p>Dengan hormat,</p>
<?php if($jenis === 'pengantar'): ?>
    <p>Sehubungan dengan pelaksanaan Praktik Kerja Lapangan (PKL) <?php echo e($cfg['nama']); ?>, bersama ini kami menyerahkan siswa berikut untuk melaksanakan PKL di instansi/perusahaan Bapak/Ibu pada tanggal <?php echo e($tgl($per->mulai->format('Y-m-d'))); ?> sampai dengan <?php echo e($tgl($per->selesai->format('Y-m-d'))); ?>:</p>
<?php else: ?>
    <p>Sehubungan dengan berakhirnya masa Praktik Kerja Lapangan (PKL) <?php echo e($cfg['nama']); ?> pada tanggal <?php echo e($tgl($per->selesai->format('Y-m-d'))); ?>, bersama ini kami bermaksud menarik kembali siswa berikut:</p>
<?php endif; ?>
<table class="tbl" style="border:1px solid #000;margin:8px 0">
    <thead><tr><th style="border:1px solid #000">No</th><th style="border:1px solid #000">Nama</th><th style="border:1px solid #000">NIS</th><th style="border:1px solid #000">Kelas</th></tr></thead>
    <tbody><?php $__empty_1 = true; $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td style="border:1px solid #000"><?php echo e($i + 1); ?></td><td style="border:1px solid #000"><?php echo e($p->siswa->nama ?? '-'); ?></td><td style="border:1px solid #000"><?php echo e($p->siswa->nis ?? ''); ?></td><td style="border:1px solid #000"><?php echo e($p->siswa->kelas->nama ?? ''); ?></td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="4">Belum ada siswa ditempatkan.</td></tr><?php endif; ?></tbody></table>
<?php if($jenis === 'pengantar'): ?>
    <p>Kami mohon bimbingan dan pengawasan Bapak/Ibu selama siswa melaksanakan PKL. Guru pembimbing dari sekolah: <?php echo e($list->pluck('guru.nama')->filter()->unique()->implode(', ') ?: '........................'); ?>.</p>
<?php else: ?>
    <p>Kami mengucapkan terima kasih atas kesempatan, bimbingan, dan kerja sama yang telah diberikan selama siswa kami melaksanakan PKL. Mohon kesediaan Bapak/Ibu mengisi penilaian siswa.</p>
<?php endif; ?>
<p>Demikian surat ini kami sampaikan. Atas perhatian dan kerja samanya kami ucapkan terima kasih.</p>
<p style="text-align:right;margin-top:24px"><?php echo e($tempat ?: '........................'); ?>, <?php echo e($tgl(\App\Support\Dates::today())); ?></p>
<div class="ttd"><div><?php if(config('pkl.koordinator')): ?>Koordinator PKL,<div class="sp"></div><b><u><?php echo e(config('pkl.koordinator')); ?></u></b><?php endif; ?></div>
    <div>Kepala Sekolah,<div class="sp"></div><b><u><?php echo e($cfg['kepsek']); ?></u></b><br>NUPTK. <?php echo e($cfg['kepsek_nuptk']); ?></div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('surat.layout', ['title' => 'Surat '.ucfirst($jenis).' PKL'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/claude/pkl-pusat/resources/views/laporan/surat.blade.php ENDPATH**/ ?>