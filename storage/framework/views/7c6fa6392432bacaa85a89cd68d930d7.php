<?php $__env->startSection('content'); ?>
<?php ($me = auth()->user()); ?>
<a class="back-link" href="<?php echo e(route('bimbingan.index')); ?>">‹ Daftar siswa PKL</a>
<div class="head"><div><h1><?php echo e($p->siswa->nama ?? '-'); ?></h1><small><?php echo e($p->siswa->nis ?? ''); ?> · <?php echo e($p->siswa->kelas->nama ?? '-'); ?> · <?php echo e($p->periode->nama ?? ''); ?><?php if($p->siswa?->no_hp): ?> · 📞 <?php echo e($p->siswa->no_hp); ?><?php endif; ?></small></div>
    <?php if($nilai['lengkap']): ?><a class="btn" href="<?php echo e(route('laporan.sertifikat', $p)); ?>" target="_blank">🎓 Sertifikat</a><?php endif; ?></div>

<div class="split">
<div>
    <div class="kpis kpis-4">
        <div class="kpi"><small>Hadir</small><div class="n ok"><?php echo e($rekap['H']); ?></div><small><?php echo e($rekap['persen'] === null ? '-' : $rekap['persen'].'%'); ?> dari <?php echo e($rekap['hari']); ?> hari</small></div>
        <div class="kpi"><small>Izin / Sakit</small><div class="n"><?php echo e($rekap['I'] + $rekap['S']); ?></div></div>
        <div class="kpi"><small>Alpa / kosong</small><div class="n <?php echo e($rekap['A'] + $rekap['kosong'] ? 'bad' : 'ok'); ?>"><?php echo e($rekap['A'] + $rekap['kosong']); ?></div></div>
        <div class="kpi"><small>Nilai akhir</small><div class="n"><?php echo e($nilai['akhir'] ?? '–'); ?></div><small><?php echo e($nilai['lengkap'] ? $nilai['huruf'] : 'belum lengkap'); ?></small></div>
    </div>

    <div class="card card-tight" id="absensi">
        <div style="padding:14px 16px;border-bottom:1px solid var(--line2)"><h2>Absensi</h2></div>
        <div class="scroll"><table class="tbl tbl-cards">
            <thead><tr><th>Tanggal</th><th>Status</th><th>Jam</th><th>Lokasi</th><th>Bukti</th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $absensi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr><td class="card-title"><?php echo e(\App\Support\Dates::human($a->tanggal->format('Y-m-d'))); ?><?php if($a->sumber === 'koreksi'): ?> <small class="mut">(koreksi)</small><?php endif; ?></td>
                    <td data-label="Status"><span class="lg <?php echo e($a->status); ?>"><?php echo e($a->status); ?></span> <?php if($a->terlambat): ?><span class="badge b-warn">Terlambat</span><?php endif; ?>
                        <?php if($a->verifikasi === 'ok'): ?><span class="badge b-ok">OK</span><?php elseif($a->verifikasi === 'tolak'): ?><span class="badge b-bad">Ditolak</span><?php elseif($a->perlu_dicek || in_array($a->status, ['I', 'S'])): ?><span class="badge b-warn">Perlu dicek</span><?php endif; ?>
                        <?php if($a->keterangan): ?><br><small><?php echo e($a->keterangan); ?></small><?php endif; ?></td>
                    <td data-label="Jam" class="mono"><?php echo e($a->jam_masuk); ?><?php echo e($a->jam_pulang ? '–'.$a->jam_pulang : ''); ?></td>
                    <td data-label="Lokasi"><?php if($a->lat !== null): ?><a href="https://www.google.com/maps?q=<?php echo e($a->lat); ?>,<?php echo e($a->lng); ?>" target="_blank" rel="noopener"><?php echo e($a->jarak !== null ? $a->jarak.' m' : 'peta'); ?></a><?php else: ?><span class="mut">–</span><?php endif; ?></td>
                    <td data-label="Bukti"><?php echo $__env->make('bimbingan._foto', ['row' => $a, 'jenis' => 'absensi', 'kolom' => ['foto_masuk' => 'Masuk', 'foto_pulang' => 'Pulang', 'bukti' => 'Bukti']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5"><div class="empty">Belum ada absensi.</div></td></tr>
            <?php endif; ?>
            </tbody></table></div>
        <?php if($me->isPengelola()): ?>
            <details style="padding:12px 16px"><summary><b>Koreksi absensi</b> (siswa lupa absen, HP rusak, dsb.)</summary>
                <form method="post" action="<?php echo e(route('bimbingan.koreksi', $p)); ?>" class="fields" style="margin-top:10px"><?php echo csrf_field(); ?>
                    <div class="field"><label>Tanggal</label><input type="date" name="tanggal" required min="<?php echo e($p->mulai->format('Y-m-d')); ?>" max="<?php echo e(min($hari, $p->selesai->format('Y-m-d'))); ?>"></div>
                    <div class="field"><label>Status</label><?php echo $__env->make('partials.select', ['name' => 'status', 'options' => \App\Models\Absensi::STATUS], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
                    <div class="field"><label>Keterangan</label><input type="text" name="keterangan" maxlength="500" placeholder="alasan koreksi"></div>
                    <div class="field"><button class="btn">Simpan koreksi</button></div>
                </form></details>
        <?php endif; ?>
    </div>

    <div class="card card-tight" id="jurnal">
        <div style="padding:14px 16px;border-bottom:1px solid var(--line2)"><h2>Jurnal kegiatan</h2></div>
        <?php $__empty_1 = true; $__currentLoopData = $jurnal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $j): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="stu"><span class="nm"><b><?php echo e(\App\Support\Dates::human($j->tanggal->format('Y-m-d'))); ?></b> <span class="badge <?php echo e(['disetujui' => 'b-ok', 'revisi' => 'b-bad', 'diajukan' => 'b-warn'][$j->status] ?? ''); ?>"><?php echo e(\App\Models\Jurnal::STATUS[$j->status]); ?></span>
                <small style="white-space:pre-line"><?php echo e($j->kegiatan); ?></small><?php if($j->catatan): ?><small class="warn">Catatan: <?php echo e($j->catatan); ?></small><?php endif; ?>
                <?php if($j->foto): ?><small><a href="<?php echo e(route('berkas', ['jurnal', $j->id, 'foto'])); ?>" target="_blank" rel="noopener">📷 Foto</a></small><?php endif; ?></span>
                <?php if($pembimbing && $j->status === 'diajukan'): ?>
                    <form method="post" action="<?php echo e(route('bimbingan.verifikasi.jurnal')); ?>"><?php echo csrf_field(); ?><input type="hidden" name="id[]" value="<?php echo e($j->id); ?>"><button class="btn btn-sm" name="hasil" value="disetujui">✓ Setujui</button></form>
                <?php endif; ?></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="empty">Belum ada jurnal.</div>
        <?php endif; ?>
    </div>
</div>

<div>
    <div class="card">
        <h2><?php echo e($p->dudi->nama ?? '-'); ?></h2>
        <small><?php echo e($p->dudi->alamat ?? ''); ?></small>
        <table class="kv" style="margin-top:8px;font-size:14px">
            <tr><td>Masa</td><td><?php echo e(\App\Support\Dates::human($p->mulai->format('Y-m-d'))); ?> – <?php echo e(\App\Support\Dates::human($p->selesai->format('Y-m-d'))); ?></td></tr>
            <tr><td>Status</td><td><?php echo e(\App\Models\Penempatan::STATUS[$p->status]); ?></td></tr>
            <tr><td>Guru pembimbing</td><td><?php echo e($p->guru->nama ?? '-'); ?></td></tr>
            <tr><td>Pembimbing industri</td><td><?php echo e($p->pembimbingIndustri->nama ?? '-'); ?><?php if($p->pembimbingIndustri?->telp): ?><br><small><?php echo e($p->pembimbingIndustri->telp); ?></small><?php endif; ?></td></tr>
            <?php if($p->catatan): ?><tr><td>Catatan</td><td><?php echo e($p->catatan); ?></td></tr><?php endif; ?>
        </table>
        <?php if($me->isPengelola()): ?>
            <details style="margin-top:10px"><summary><b>Ubah penempatan</b></summary>
                <form method="post" action="<?php echo e(route('master.penempatan.ubah', $p)); ?>" style="margin-top:10px"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <?php ($dudiAll = \App\Models\Dudi::orderBy('nama')->with('pembimbing')->get()); ?>
                    <div class="field"><label>DUDI</label><?php echo $__env->make('partials.select', ['name' => 'dudi_id', 'options' => $dudiAll->pluck('nama', 'id')->all(), 'selected' => $p->dudi_id], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
                    <div class="field"><label>Pembimbing industri</label><select name="pembimbing_industri_id"><option value="">–</option>
                        <?php $__currentLoopData = $dudiAll; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php $__currentLoopData = $d->pembimbing; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($pi->id); ?>" <?php if($pi->id === $p->pembimbing_industri_id): echo 'selected'; endif; ?>><?php echo e($pi->nama); ?> (<?php echo e($d->nama); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
                    <div class="field"><label>Guru pembimbing</label><?php echo $__env->make('partials.select', ['name' => 'guru_id', 'options' => ['' => '–'] + \App\Models\Pegawai::aktif()->orderBy('nama')->pluck('nama', 'id')->all(), 'selected' => $p->guru_id], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
                    <div class="grid g2"><div class="field"><label>Mulai</label><input type="date" name="mulai" value="<?php echo e($p->mulai->format('Y-m-d')); ?>"></div><div class="field"><label>Selesai</label><input type="date" name="selesai" value="<?php echo e($p->selesai->format('Y-m-d')); ?>"></div></div>
                    <div class="field"><label>Status</label><?php echo $__env->make('partials.select', ['name' => 'status', 'options' => \App\Models\Penempatan::STATUS, 'selected' => $p->status], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
                    <div class="field"><label>Catatan</label><input type="text" name="catatan" value="<?php echo e($p->catatan); ?>" maxlength="500" placeholder="mis. alasan penarikan"></div>
                    <button class="btn btn-pri">Simpan</button>
                </form></details>
        <?php endif; ?>
    </div>

    <div class="card" id="nilai">
        <h2>Nilai</h2>
        <?php ($bisa = in_array(true, $bolehNilai, true)); ?>
        <form method="post" action="<?php echo e(route('bimbingan.nilai', $p)); ?>"><?php echo csrf_field(); ?>
            <table class="tbl" style="font-size:14px"><tbody>
            <?php $__currentLoopData = $nilai['rincian']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php ($k = $r['komponen']); ?>
                <tr><td><?php echo e($k->nama); ?><br><small><?php echo e(\App\Models\KomponenNilai::SUMBER[$k->sumber]); ?> · <?php echo e($k->bobot); ?>%</small></td>
                    <td class="r" style="width:90px"><?php if($bolehNilai[$k->id] ?? false): ?><input type="number" name="skor[<?php echo e($k->id); ?>]" value="<?php echo e($r['skor']); ?>" min="0" max="100" step="0.01" inputmode="decimal" style="width:80px"><?php else: ?><b><?php echo e($r['skor'] ?? '–'); ?></b><?php endif; ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr><td><b>Nilai akhir</b></td><td class="r"><b><?php echo e($nilai['akhir'] ?? '–'); ?></b> <?php echo e($nilai['lengkap'] ? $nilai['huruf'] : ''); ?></td></tr>
            </tbody></table>
            <?php if($bisa): ?><button class="btn btn-pri" style="margin-top:10px">Simpan nilai</button><?php endif; ?>
        </form>
    </div>

    <div class="card" id="kunjungan">
        <h2>Kunjungan / monitoring</h2>
        <?php $__empty_1 = true; $__currentLoopData = $kunjungan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div style="border-bottom:1px solid var(--line2);padding:8px 0"><b><?php echo e(\App\Support\Dates::human($k->tanggal->format('Y-m-d'))); ?></b> <small><?php echo e($k->pencatat->nama ?? ''); ?></small>
                <div style="white-space:pre-line;font-size:14px"><?php echo e($k->catatan); ?></div>
                <?php if($k->foto): ?><small><a href="<?php echo e(route('berkas', ['kunjungan', $k->id, 'foto'])); ?>" target="_blank" rel="noopener">📷 Foto</a></small><?php endif; ?></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="mut">Belum ada catatan kunjungan.</p>
        <?php endif; ?>
        <?php if($guruIni): ?>
            <form method="post" action="<?php echo e(route('bimbingan.kunjungan', $p)); ?>" enctype="multipart/form-data" style="margin-top:10px"><?php echo csrf_field(); ?>
                <div class="field"><label>Tanggal kunjungan</label><input type="date" name="tanggal" value="<?php echo e($hari); ?>" max="<?php echo e($hari); ?>" required></div>
                <div class="field"><label>Hasil kunjungan</label><textarea name="catatan" rows="3" maxlength="5000" required placeholder="Kondisi siswa, masukan dari DUDI, tindak lanjut"></textarea></div>
                <div class="field"><label>Foto (opsional)</label><input type="file" name="foto" accept="image/*"></div>
                <button class="btn">Simpan kunjungan</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['title' => $p->siswa->nama ?? 'Siswa PKL'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/claude/pkl-pusat/resources/views/bimbingan/show.blade.php ENDPATH**/ ?>