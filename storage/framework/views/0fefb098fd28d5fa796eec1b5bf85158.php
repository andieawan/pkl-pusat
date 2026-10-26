<?php ($d ??= null); ?> <?php ($sfx = $d?->id ?? 'baru'); ?>
<div class="grid g2">
    <div class="field"><label>Nama DUDI</label><input type="text" name="nama" value="<?php echo e($d->nama ?? ''); ?>" required maxlength="191"></div>
    <div class="field"><label>Bidang usaha</label><input type="text" name="bidang" value="<?php echo e($d->bidang ?? ''); ?>" maxlength="191" placeholder="mis. Jaringan komputer"></div>
</div>
<div class="field"><label>Alamat</label><input type="text" name="alamat" value="<?php echo e($d->alamat ?? ''); ?>" maxlength="500"></div>
<div class="grid g3">
    <div class="field"><label>Kontak</label><input type="text" name="kontak_nama" value="<?php echo e($d->kontak_nama ?? ''); ?>"></div>
    <div class="field"><label>Telepon</label><input type="tel" name="telp" value="<?php echo e($d->telp ?? ''); ?>" maxlength="30"></div>
    <div class="field"><label>Email</label><input type="email" name="email" value="<?php echo e($d->email ?? ''); ?>"></div>
</div>
<div class="grid g3">
    <div class="field"><label>Latitude</label><input id="lat-<?php echo e($sfx); ?>" type="text" inputmode="decimal" name="lat" value="<?php echo e($d->lat ?? ''); ?>" placeholder="-8.15"></div>
    <div class="field"><label>Longitude</label><input id="lng-<?php echo e($sfx); ?>" type="text" inputmode="decimal" name="lng" value="<?php echo e($d->lng ?? ''); ?>" placeholder="113.76"></div>
    <div class="field"><label>Radius absen (meter)</label><input type="number" name="radius" value="<?php echo e($d->radius ?? config('pkl.radius_bawaan')); ?>" min="20" max="5000"></div>
</div>
<p><button type="button" class="btn btn-sm" data-geo-fill="#lat-<?php echo e($sfx); ?>,#lng-<?php echo e($sfx); ?>">📍 Isi dengan lokasi saya sekarang</button> <small>atau salin koordinat dari Google Maps (klik kanan pada peta).</small></p>
<div class="grid g3">
    <div class="field"><label>Jam masuk</label><input type="time" name="jam_masuk" value="<?php echo e($d->jam_masuk ?? '08:00'); ?>"></div>
    <div class="field"><label>Jam pulang</label><input type="time" name="jam_pulang" value="<?php echo e($d->jam_pulang ?? '16:00'); ?>"></div>
    <div class="field"><label>Kuota siswa</label><input type="number" name="kuota" value="<?php echo e($d->kuota ?? ''); ?>" min="0"></div>
</div>
<div class="field"><label>Hari kerja</label><div class="row">
    <?php $__currentLoopData = \App\Models\Dudi::HARI; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n => $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><label class="chk"><input type="checkbox" name="hari_kerja[]" value="<?php echo e($n); ?>" <?php if(in_array($n, $d?->hariKerja() ?? [1, 2, 3, 4, 5, 6], true)): echo 'checked'; endif; ?>><?php echo e($h); ?></label><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div></div>
<?php if($d): ?><label class="chk"><input type="checkbox" name="aktif" value="1" <?php if($d->aktif): echo 'checked'; endif; ?>> Aktif (bisa dipilih untuk penempatan)</label><?php endif; ?>
<button class="btn btn-pri" style="margin-top:8px">Simpan</button>
<?php /**PATH /home/claude/pkl-pusat/resources/views/master/_dudi-form.blade.php ENDPATH**/ ?>