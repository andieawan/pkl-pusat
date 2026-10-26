<nav class="seg seg-scroll" aria-label="Data PKL" style="margin-bottom:14px">
    <?php $__currentLoopData = ['master.dudi' => 'DUDI', 'master.pembimbing' => 'Pembimbing Industri', 'master.periode' => 'Periode', 'master.penempatan' => 'Penempatan', 'master.komponen' => 'Komponen Nilai']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route($r)); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['on' => request()->routeIs($r)]); ?>"><?php echo e($l); ?></a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</nav>
<?php /**PATH /home/claude/pkl-pusat/resources/views/master/_tabs.blade.php ENDPATH**/ ?>