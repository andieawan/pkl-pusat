
<div class="card">
    <h2 style="margin-bottom:6px"><?php echo e($p->dudi->nama ?? '-'); ?></h2>
    <small><?php echo e($p->dudi->alamat ?? ''); ?></small>
    <table class="kv" style="margin-top:8px;font-size:14px">
        <tr><td>Masa PKL</td><td><?php echo e(\App\Support\Dates::human($p->mulai->format('Y-m-d'))); ?> – <?php echo e(\App\Support\Dates::human($p->selesai->format('Y-m-d'))); ?></td></tr>
        <tr><td>Jam kerja</td><td><?php echo e($p->dudi->jam_masuk ?? '-'); ?> – <?php echo e($p->dudi->jam_pulang ?? '-'); ?></td></tr>
        <tr><td>Pembimbing sekolah</td><td><?php echo e($p->guru->nama ?? '-'); ?></td></tr>
        <tr><td>Pembimbing industri</td><td><?php echo e($p->pembimbingIndustri->nama ?? '-'); ?></td></tr>
        <?php if($p->status !== 'aktif'): ?><tr><td>Status</td><td><span class="badge"><?php echo e(\App\Models\Penempatan::STATUS[$p->status]); ?></span></td></tr><?php endif; ?>
    </table>
</div>
<?php /**PATH /home/claude/pkl-pusat/resources/views/siswa/_kartu.blade.php ENDPATH**/ ?>