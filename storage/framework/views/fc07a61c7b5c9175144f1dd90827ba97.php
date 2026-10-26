<?php $__currentLoopData = $kolom; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if($row->{$k}): ?><a href="<?php echo e(route('berkas', [$jenis, $row->id, $k])); ?>" target="_blank" rel="noopener">📷 <?php echo e($lbl); ?></a> <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH /home/claude/pkl-pusat/resources/views/bimbingan/_foto.blade.php ENDPATH**/ ?>