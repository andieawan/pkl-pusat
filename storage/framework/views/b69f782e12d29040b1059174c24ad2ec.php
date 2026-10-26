<?php ($pi ??= null); ?>
<div class="grid g2">
    <div class="field"><label>DUDI</label><?php echo $__env->make('partials.select', ['name' => 'dudi_id', 'options' => ['' => '– pilih –'] + $dudi->pluck('nama', 'id')->all(), 'selected' => $pi->dudi_id ?? $dudiId, 'attrs' => 'required'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
    <div class="field"><label>Nama</label><input type="text" name="nama" value="<?php echo e($pi->nama ?? ''); ?>" required maxlength="191"></div>
    <div class="field"><label>Jabatan</label><input type="text" name="jabatan" value="<?php echo e($pi->jabatan ?? ''); ?>"></div>
    <div class="field"><label>Telepon / WA</label><input type="tel" name="telp" value="<?php echo e($pi->telp ?? ''); ?>" maxlength="30"></div>
    <div class="field"><label>Username login</label><input type="text" name="username" value="<?php echo e($pi?->user?->username); ?>" pattern="[a-z0-9._\-]{3,60}" autocapitalize="none" placeholder="<?php echo e($pi?->user ? '' : 'kosongkan bila tanpa akun'); ?>"></div>
</div>
<?php if($pi?->user): ?><label class="chk"><input type="checkbox" name="reset_password" value="1"> Buat password baru</label><?php endif; ?>
<button class="btn btn-pri" style="margin-top:8px">Simpan</button>
<?php /**PATH /home/claude/pkl-pusat/resources/views/master/_pembimbing-form.blade.php ENDPATH**/ ?>