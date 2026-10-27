<?php $__env->startSection('content'); ?>
<div class="head"><div><h1>Absen PKL</h1><small><?php echo e(\App\Support\Dates::long($hari)); ?></small></div></div>
<?php if($tertunda >= max(1, (int) config('pkl.jurnal_tertunda_hari'))): ?><div class="alert alert-warn"><span class="alert-ic" aria-hidden="true">!</span><span class="alert-txt">Jurnal <b><?php echo e($tertunda); ?> hari kerja</b> terakhir belum diisi. <a href="<?php echo e(route('siswa.jurnal', ['tanggal' => date('Y-m-d', strtotime($hari.' -1 day'))])); ?>"><b>Isi jurnal</b></a></span></div><?php endif; ?>
<?php if($revisi): ?><div class="alert alert-warn"><span class="alert-ic" aria-hidden="true">!</span><span class="alert-txt"><?php echo e($revisi); ?> jurnal perlu diperbaiki. <a href="<?php echo e(route('siswa.jurnal')); ?>"><b>Buka jurnal</b></a></span></div><?php endif; ?>

<?php if(! $p): ?>
    <div class="card"><div class="empty">
        <?php if($terakhir): ?>
            Hari ini bukan masa PKL Anda (<?php echo e(\App\Support\Dates::human($terakhir->mulai->format('Y-m-d'))); ?> – <?php echo e(\App\Support\Dates::human($terakhir->selesai->format('Y-m-d'))); ?><?php if($terakhir->status !== 'aktif'): ?>, status <?php echo e(\App\Models\Penempatan::STATUS[$terakhir->status]); ?><?php endif; ?>).
        <?php else: ?>
            Anda belum memiliki penempatan PKL. Hubungi koordinator PKL.
        <?php endif; ?>
    </div></div>
    <?php if($terakhir): ?><?php echo $__env->make('siswa._kartu', ['p' => $terakhir], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php endif; ?>
<?php else: ?>
    <?php ($kerja = in_array(\App\Support\Dates::weekday($hari) ?: 7, $p->dudi->hariKerja(), true)); ?>
    <div class="card selfcheck">
        <?php if(! $absen): ?>
            <?php if (! ($kerja)): ?><p class="mut" style="margin-bottom:10px">Hari ini bukan hari kerja <?php echo e($p->dudi->nama); ?>. Absen tetap bisa dilakukan bila Anda masuk.</p><?php endif; ?>
            <form method="post" action="<?php echo e(route('siswa.absen')); ?>" enctype="multipart/form-data" data-geo="1" data-geo-opsional>
                <?php echo csrf_field(); ?> <input type="hidden" name="aksi" value="masuk"><input type="hidden" name="lat"><input type="hidden" name="lng">
                <h2>Absen masuk</h2>
                <p class="mut">Batas tepat waktu pukul <b><?php echo e($batas); ?></b>. Ambil selfie di tempat PKL; lokasi HP akan dibaca otomatis.</p>
                <div class="field"><label for="foto">Foto selfie</label><input id="foto" type="file" name="foto" accept="image/*" capture="user" required></div>
                <button class="btn btn-pri" name="aksi" value="masuk" style="width:100%">📍 Absen Masuk</button>
            </form>
            <details style="margin-top:14px"><summary><b>Tidak masuk hari ini? Lapor izin / sakit</b></summary>
                <form method="post" action="<?php echo e(route('siswa.absen')); ?>" enctype="multipart/form-data" style="margin-top:10px">
                    <?php echo csrf_field(); ?> <input type="hidden" name="aksi" value="lapor">
                    <div class="field"><label>Jenis</label><?php echo $__env->make('partials.select', ['name' => 'status', 'options' => ['I' => 'Izin', 'S' => 'Sakit']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
                    <div class="field"><label for="ket">Alasan</label><textarea id="ket" name="keterangan" rows="2" maxlength="500" required placeholder="mis. demam, sudah ke puskesmas"></textarea></div>
                    <div class="field"><label for="bukti">Bukti (foto surat dokter/izin, opsional)</label><input id="bukti" type="file" name="bukti" accept="image/*,application/pdf"></div>
                    <button class="btn">Kirim laporan</button>
                </form>
            </details>
        <?php elseif($absen->status === 'H' && ! $absen->jam_pulang): ?>
            <p>✅ Masuk pukul <b><?php echo e($absen->jam_masuk); ?></b><?php if($absen->terlambat): ?> <span class="badge b-warn">Terlambat</span><?php endif; ?> <?php if($absen->perlu_dicek): ?><span class="badge b-warn">Lokasi diperiksa pembimbing</span><?php endif; ?></p>
            <form method="post" action="<?php echo e(route('siswa.absen')); ?>" enctype="multipart/form-data" data-geo="1" data-geo-opsional style="margin-top:12px">
                <?php echo csrf_field(); ?> <input type="hidden" name="aksi" value="pulang"><input type="hidden" name="lat"><input type="hidden" name="lng">
                <h2>Absen pulang</h2>
                <p class="mut">Jam pulang <?php echo e($p->dudi->jam_pulang); ?>.</p>
                <div class="field"><label for="foto2">Foto (opsional)</label><input id="foto2" type="file" name="foto" accept="image/*" capture="user"></div>
                <button class="btn btn-pri" name="aksi" value="pulang" style="width:100%">🏠 Absen Pulang</button>
            </form>
        <?php elseif($absen->status === 'H'): ?>
            <p>✅ Hari ini selesai: masuk <b><?php echo e($absen->jam_masuk); ?></b>, pulang <b><?php echo e($absen->jam_pulang); ?></b>.<?php if($absen->terlambat): ?> <span class="badge b-warn">Terlambat</span><?php endif; ?></p>
        <?php else: ?>
            <p>Hari ini tercatat <span class="lg <?php echo e($absen->status); ?>"><?php echo e($absen->status); ?></span> <b><?php echo e(\App\Models\Absensi::STATUS[$absen->status]); ?></b> — <?php echo e($absen->keterangan); ?>

                <?php if($absen->verifikasi === 'ok'): ?><span class="badge b-ok">Disetujui</span><?php elseif($absen->verifikasi === 'tolak'): ?><span class="badge b-bad">Ditolak</span><?php else: ?><span class="badge">Menunggu pembimbing</span><?php endif; ?></p>
        <?php endif; ?>
    </div>

    <div class="quick">
        <a class="quick-item <?php echo e($jurnalHariIni ? '' : 'primary'); ?>" href="<?php echo e(route('siswa.jurnal')); ?>"><span class="bk-ic" aria-hidden="true">📝</span><span><b>Jurnal hari ini</b><small><?php echo e($jurnalHariIni ? \App\Models\Jurnal::STATUS[$jurnalHariIni->status] : 'Belum diisi'); ?></small></span></a>
        <a class="quick-item" href="<?php echo e(route('siswa.riwayat')); ?>"><span class="bk-ic" aria-hidden="true">🗓</span><span><b>Riwayat absen</b><small><?php if($rekap): ?><?php echo e($rekap['H']); ?> hadir dari <?php echo e($rekap['hari']); ?> hari kerja <?php endif; ?></small></span></a>
    </div>
    <?php echo $__env->make('siswa._kartu', ['p' => $p], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'Absen PKL'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/claude/pkl-pusat/resources/views/siswa/beranda.blade.php ENDPATH**/ ?>