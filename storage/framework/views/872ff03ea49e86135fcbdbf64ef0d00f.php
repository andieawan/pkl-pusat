<?php $__env->startSection('content'); ?>
<div class="card login">
    <img class="logo-login" src="<?php echo e(asset('icons/icon-192.png')); ?>" alt="" width="56" height="56">
    <h1>Manajemen PKL</h1>
    <p class="mut"><?php echo e(config('pkl.school_name')); ?><br>Guru &amp; siswa: masuk memakai akun Pusat Sekolah. Pembimbing industri: masuk dengan username &amp; password dari sekolah.</p>
    <?php if(\App\Services\PusatKlien::aktif()): ?><a class="btn btn-pri" style="width:100%;text-align:center;display:block" href="<?php echo e(route('sso.login')); ?>">Masuk lewat Pusat</a><?php endif; ?>
    <?php if(config('pkl.login_lokal')): ?>
    <h2 style="margin:18px 0 6px;font-size:15px">Pembimbing industri</h2>
    <form method="post" action="<?php echo e(route('login')); ?>" autocomplete="off">
        <?php echo csrf_field(); ?>
        <div class="field"><label for="u">Username</label><input id="u" type="text" name="username" value="<?php echo e(old('username')); ?>" autocapitalize="none" autocomplete="username" required></div>
        <div class="field"><label for="pw">Password</label><input id="pw" type="password" name="password" autocomplete="current-password" required></div>
        <button class="btn" style="width:100%" data-busy="Memeriksa…">Masuk</button>
    </form>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => 'Masuk', 'bare' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/claude/pkl-pusat/resources/views/auth/login.blade.php ENDPATH**/ ?>