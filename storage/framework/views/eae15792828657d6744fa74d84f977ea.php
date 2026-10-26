<?php $__env->startSection('content'); ?>
<div class="head"><div><h1>Data PKL</h1><small>Komponen penilaian dan bobotnya. Total bobot komponen aktif harus 100.</small></div></div>
<?php echo $__env->make('master._tabs', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<form method="post" action="<?php echo e(route('master.komponen.simpan')); ?>" class="card card-tight"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
    <div class="scroll"><table class="tbl">
        <thead><tr><th>Nama komponen</th><th>Diisi oleh</th><th class="c">Bobot (%)</th><th class="c">Aktif</th></tr></thead>
        <tbody>
        <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr><td><input type="text" name="komponen[<?php echo e($k->id); ?>][nama]" value="<?php echo e($k->nama); ?>" maxlength="191"></td>
                <td><?php echo $__env->make('partials.select', ['name' => "komponen[{$k->id}][sumber]", 'options' => \App\Models\KomponenNilai::SUMBER, 'selected' => $k->sumber], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                <td class="c"><input type="number" name="komponen[<?php echo e($k->id); ?>][bobot]" value="<?php echo e($k->bobot); ?>" min="0" max="100" style="width:80px"></td>
                <td class="c"><input type="checkbox" name="komponen[<?php echo e($k->id); ?>][aktif]" value="1" <?php if($k->aktif): echo 'checked'; endif; ?> style="width:20px;height:20px"></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php $__currentLoopData = ['b1', 'b2']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr><td><input type="text" name="komponen[<?php echo e($b); ?>][nama]" placeholder="+ komponen baru" maxlength="191"></td>
                <td><?php echo $__env->make('partials.select', ['name' => "komponen[{$b}][sumber]", 'options' => \App\Models\KomponenNilai::SUMBER], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
                <td class="c"><input type="number" name="komponen[<?php echo e($b); ?>][bobot]" value="0" min="0" max="100" style="width:80px"></td>
                <td class="c"><input type="checkbox" name="komponen[<?php echo e($b); ?>][aktif]" value="1" checked style="width:20px;height:20px"></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody></table></div>
    <div style="padding:12px 16px"><small>Komponen nonaktif tidak dihitung dan tidak muncul di form nilai; nilai yang sudah diisi tetap tersimpan.</small><br><button class="btn btn-pri" style="margin-top:8px">Simpan komponen</button></div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'Komponen Nilai'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/claude/pkl-pusat/resources/views/master/komponen.blade.php ENDPATH**/ ?>