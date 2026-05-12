<?php
$uri = service('request')->getUri();
$current_segment = $uri->getTotalSegments() > 0 ? $uri->getSegment(1) : '';
$role = session()->get('role');
$realname = session()->get('realname') ?? 'User';
$userInitial = strtoupper(substr($realname, 0, 1));
?>


<div class="flex flex-col gap-2 p-2 border-b border-slate-200">
    <a href="<?= base_url() ?>" class="group/menu-button flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all hover:bg-slate-100 hover:text-slate-900">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-slate-900 text-[11px] font-bold text-white shadow-sm">B</span>
        <span class="truncate font-semibold tracking-tight text-slate-900">Bonehacker</span>
    </a>
</div>

<!-- DASHBOAR | ADMIN - SUPERADMIN - OWNER -->
<div class="no-scrollbar flex min-h-0 flex-1 flex-col gap-0 overflow-y-auto">
    <div class="relative flex w-full min-w-0 flex-col p-2">
        <div class="sticky top-0 z-10 flex h-8 shrink-0 items-center bg-white px-2 text-xs font-semibold uppercase tracking-wider text-slate-500/80">
            Dashboard
        </div>

        <ul class="flex w-full min-w-0 flex-col gap-1">
            <li>
                <a href="<?= base_url('beranda') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= in_array($current_segment, ['beranda', '']) ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                    <i class="fas fa-home w-4 text-center shrink-0"></i>
                    <span class="truncate">Beranda</span>
                </a>
            </li>
            <?php if ($role === 'user'): ?>
                <li>
                    <a href="<?= base_url('terapis/profil_saya') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'terapis' && $uri->getSegment(2) == 'profil_saya' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <i class="fas fa-user-circle w-4 text-center shrink-0"></i>
                        <span class="truncate">Profil Saya</span>
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('gaji/monitor') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'gaji' && $uri->getSegment(2) == 'monitor' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <i class="fas fa-wallet w-4 text-center shrink-0"></i>
                        <span class="truncate">Gaji Saya</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($role !== 'user'): ?>
                <li>
                    <a href="<?= base_url('antrean') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'antrean' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <i class="fas fa-pencil-ruler w-4 text-center shrink-0"></i>
                        <span class="truncate">Antrean</span>
                    </a>
                </li>
                <li>
                    <a href="<?= base_url('rekam-medis') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'rekam-medis' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <i class="fas fa-file-medical w-4 text-center shrink-0"></i>
                        <span class="truncate">Rekam Medis</span>
                    </a>
                </li>

                <?php if ($role === 'superadmin'): ?>
                    <li>
                        <a href="<?= base_url('region') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'region' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                            <i class="fas fa-map w-4 text-center shrink-0"></i>
                            <span class="truncate">Cabang</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('kalender') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'kalender' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                            <i class="fa-solid fa-calendar-days w-4 text-center shrink-0"></i>
                            <span class="truncate">Kalender Kerja</span>
                        </a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="<?= base_url('journal') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'journal' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <i class="fas fa-book w-4 text-center shrink-0"></i>
                        <span class="truncate">Jurnal</span>
                    </a>
                </li>

                <?php if (in_array($role, ['owner', 'admin', 'superadmin'])): ?>
                    <li>
                        <a href="<?= base_url('transaksi') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'transaksi' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                            <i class="fas fa-money-bill-wave w-4 text-center shrink-0"></i>
                            <span class="truncate">Transaksi</span>
                        </a>
                    </li>
                    <li>
                        <details class="group" <?= $current_segment == 'kehadiran' ? 'open' : '' ?>>
                            <summary class="flex w-full cursor-pointer list-none items-center justify-between rounded-md p-2 text-left text-sm transition-all text-slate-600 hover:bg-slate-100 hover:text-slate-900 [&::-webkit-details-marker]:hidden">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-user-clock w-4 text-center shrink-0"></i>
                                    <span class="truncate font-medium">Presensi Karyawan</span>
                                </span>
                                <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 group-open:rotate-180 shrink-0"></i>
                            </summary>
                            <ul class="mx-3.5 mt-1 flex min-w-0 translate-x-px flex-col gap-1 border-l border-slate-200 px-2.5 py-0.5">
                                <li>
                                    <a href="<?= base_url('kehadiran/tambah') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'kehadiran' && $uri->getSegment(2) == 'tambah' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                        <span class="truncate">Input Presensi</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= base_url('kehadiran') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'kehadiran' && ($uri->getSegment(2) == '' || $uri->getSegment(2) == 'index') ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                        <span class="truncate">Rekap Presensi</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= base_url('kehadiran/cuti') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'kehadiran' && $uri->getSegment(2) == 'cuti' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                        <span class="truncate">Cuti Karyawan</span>
                                    </a>
                                </li>
                            </ul>
                        </details>
                    </li>
                    <?php if ($role !== 'superadmin'): ?>
                        <li>
                            <a href="<?= base_url('kalender') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'kalender' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                <i class="fa-solid fa-calendar-days w-4 text-center shrink-0"></i>
                                <span class="truncate">Kalender Kerja</span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (in_array($role, ['owner'])): ?>
                    <li>
                        <details class="group" <?= in_array($current_segment, ['gaji', 'transaksi-tunjangan', 'tunjangan-karyawan', 'kasbon']) ? 'open' : '' ?>>
                            <summary class="flex w-full cursor-pointer list-none items-center justify-between rounded-md p-2 text-left text-sm transition-all text-slate-600 hover:bg-slate-100 hover:text-slate-900 [&::-webkit-details-marker]:hidden">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-dollar-sign w-4 text-center shrink-0"></i>

                                    <span class="truncate font-medium">Kelola Gaji</span>
                                </span>
                                <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 group-open:rotate-180 shrink-0"></i>
                            </summary>
                            <ul class="mx-3.5 mt-1 flex min-w-0 translate-x-px flex-col gap-1 border-l border-slate-200 px-2.5 py-0.5">
                                <li>
                                    <a href="<?= base_url('gaji') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'gaji' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                        <span class="truncate">Gaji Karyawan</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= base_url('transaksi-tunjangan') ?>"
                                        class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'transaksi-tunjangan' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                        <span class="truncate">Tunjangan Terapis</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= base_url('tunjangan-karyawan') ?>"
                                        class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'tunjangan-karyawan' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                        <span class="truncate">Master Tunjangan</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="<?= base_url('kasbon') ?>"
                                        class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'kasbon' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                        <span class="truncate">Kasbon Karyawan</span>
                                    </a>
                                </li>
                            </ul>
                        </details>
                    </li>
                <?php endif; ?>

                <?php if (in_array($role, ['owner', 'admin'])): ?>
                    <li>

                        <details class="group" <?= $current_segment == 'jasa-pelayanan' ? 'open' : '' ?>>
                            <summary class="flex w-full cursor-pointer list-none items-center justify-between rounded-md p-2 text-left text-sm transition-all text-slate-600 hover:bg-slate-100 hover:text-slate-900 [&::-webkit-details-marker]:hidden">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-hospital-user w-4 text-center shrink-0"></i>

                                    <span class="truncate font-medium">Jasa Pelayanan</span>
                                </span>
                                <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 group-open:rotate-180 shrink-0"></i>
                            </summary>
                            <ul class="mx-3.5 mt-1 flex min-w-0 translate-x-px flex-col gap-1 border-l border-slate-200 px-2.5 py-0.5">
                                <li>
                                    <a href="<?= base_url('jasa-pelayanan/reguler') ?>"
                                        class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= ($uri->getTotalSegments() >= 2 && $uri->getSegment(2) == 'reguler') ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                        <span class="truncate">Pasien Reguler</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="<?= base_url('jasa-pelayanan/kejantanan') ?>"
                                        class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= ($uri->getTotalSegments() >= 2 && $uri->getSegment(2) == 'kejantanan') ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                        <span class="truncate">Pasien Kejantanan</span>
                                    </a>
                                </li>
                            </ul>
                        </details>
                    </li>
                <?php endif; ?>
            <?php endif; ?>

        </ul>
    </div>


    <!-- KEUANGAN | OWNER & SUPERADMIN -->
    <?php if ($role === 'owner' || $role === 'superadmin'): ?>
        <div class="relative flex w-full min-w-0 flex-col p-2">
            <div class="sticky top-0 z-10 flex h-8 shrink-0 items-center bg-white px-2 text-xs font-semibold uppercase tracking-wider text-slate-500/80">
                Kas
            </div>

            <ul class="flex w-full min-w-0 flex-col gap-1">
                <li>
                    <details class="group" <?= in_array($current_segment, ['kas', 'gaji', 'statistikkeuangan']) || ($current_segment == 'kas' && $uri->getSegment(2) == 'categories') ? 'open' : '' ?>>
                        <summary class="flex w-full cursor-pointer list-none items-center justify-between rounded-md p-2 text-left text-sm transition-all text-slate-600 hover:bg-slate-100 hover:text-slate-900 [&::-webkit-details-marker]:hidden">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-credit-card w-4 text-center shrink-0"></i>
                                <span class="truncate font-medium">Keuangan</span>
                            </span>
                            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 group-open:rotate-180 shrink-0"></i>
                        </summary>

                        <ul class="mx-3.5 mt-1 flex min-w-0 translate-x-px flex-col gap-1 border-l border-slate-200 px-2.5 py-0.5">
                            <?php if ($role === 'owner'): ?>
                                <li>
                                    <a href="<?= base_url('kas') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'kas' && $uri->getSegment(2) == '' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                        <span class="truncate">Arus Kas</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= base_url('statistikkeuangan') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'statistikkeuangan' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                        <span class="truncate">Statistik Keuangan</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <a href="<?= base_url('kas/categories') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'kas' && $uri->getSegment(2) == 'categories' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                    <span class="truncate">Master Kategori</span>
                                </a>
                            </li>
                        </ul>
                    </details>
                </li>
            </ul>
        </div>
    <?php endif; ?>

    <!-- STATISTIK | ADMIN - SUPERADMIN - OWNER -->
    <div class="relative flex w-full min-w-0 flex-col p-2">
        <div class="sticky top-0 z-10 flex h-8 shrink-0 items-center bg-white px-2 text-xs font-semibold uppercase tracking-wider text-slate-500/80">
            Analitik
        </div>
        <ul class="flex w-full min-w-0 flex-col gap-1">
            <?php if ($role !== 'user'): ?>
                <li>
                    <details class="group" <?= in_array($current_segment, ['statistiktag', 'statistik', 'statistikresource', 'statistikresult', 'statistikgender', 'statistikdaerah']) ? 'open' : '' ?>>
                        <summary class="flex w-full cursor-pointer list-none items-center justify-between rounded-md p-2 text-left text-sm transition-all text-slate-600 hover:bg-slate-100 hover:text-slate-900 [&::-webkit-details-marker]:hidden">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-chart-line w-4 text-center shrink-0"></i>
                                <span class="truncate font-medium">Statistik</span>
                            </span>
                            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 group-open:rotate-180 shrink-0"></i>
                        </summary>

                        <ul class="mx-3.5 mt-1 flex min-w-0 translate-x-px flex-col gap-1 border-l border-slate-200 px-2.5 py-0.5">
                            <li>
                                <a href="<?= base_url('statistiktag') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'statistiktag' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                    <span class="truncate">Keluhan & Medis</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('statistik') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'statistik' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                    <span class="truncate">Riwayat Pasien</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('statistikresource') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'statistikresource' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                    <span class="truncate">Sosial Media</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('statistikresult') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'statistikresult' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                    <span class="truncate">Hasil Pemeriksaan</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('statistikgender') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'statistikgender' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                    <span class="truncate">Jenis Kelamin</span>
                                </a>
                            </li>
                            <li>
                                <a href="<?= base_url('statistikdaerah') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'statistikdaerah' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                                    <span class="truncate">Daerah</span>
                                </a>
                            </li>
                        </ul>
                    </details>
                </li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- TAGIFY & MANAGE USER | SUPERADMIN - OWNER -->
    <?php if ($role === 'superadmin' || $role === 'owner'): ?>
        <div class="relative flex w-full min-w-0 flex-col p-2">
            <div class="sticky top-0 z-10 flex h-8 shrink-0 items-center bg-white px-2 text-xs font-semibold uppercase tracking-wider text-slate-500/80">
                Manajemen
            </div>

            <ul class="flex w-full min-w-0 flex-col gap-1">
                <li>
                    <details class="group" <?= in_array($current_segment, ['complaint', 'medis', 'result']) ? 'open' : '' ?>>
                        <summary class="flex w-full cursor-pointer list-none items-center justify-between rounded-md p-2 text-left text-sm transition-all text-slate-600 hover:bg-slate-100 hover:text-slate-900 [&::-webkit-details-marker]:hidden">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-tags w-4 text-center shrink-0"></i>
                                <span class="truncate font-medium">Tags</span>
                            </span>
                            <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 group-open:rotate-180 shrink-0"></i>
                        </summary>
                        <ul class="mx-3.5 mt-1 flex min-w-0 translate-x-px flex-col gap-1 border-l border-slate-200 px-2.5 py-0.5">
                            <li><a href="<?= base_url('tag-keluhan') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'tag-keluhan' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>"><span class="truncate">Tag Keluhan</span></a></li>
                            <li><a href="<?= base_url('tag-rekam-medis') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'tag-rekam-medis' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>"><span class="truncate">Tag Rekam Medis</span></a></li>
                            <li><a href="<?= base_url('tag-pemeriksaan') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'tag-pemeriksaan' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>"><span class="truncate">Tag Pemeriksaan</span></a></li>
                        </ul>
                    </details>
                </li>


                <?php if ($role === 'superadmin'): ?>
                    <li>
                        <details class="group" <?= in_array($current_segment, ['logs', 'whatsapp', 'log_whatsapp', 'jabatan', 'greeting']) ? 'open' : '' ?>>
                            <summary class="flex w-full cursor-pointer list-none items-center justify-between rounded-md p-2 text-left text-sm transition-all text-slate-600 hover:bg-slate-100 hover:text-slate-900 [&::-webkit-details-marker]:hidden">
                                <span class="flex items-center gap-2">
                                    <i class="fas fa-cog w-4 text-center shrink-0"></i>
                                    <span class="truncate font-medium">Pengaturan</span>
                                </span>
                                <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-200 group-open:rotate-180 shrink-0"></i>
                            </summary>
                            <ul class="mx-3.5 mt-1 flex min-w-0 translate-x-px flex-col gap-1 border-l border-slate-200 px-2.5 py-0.5">
                                <li><a href="<?= base_url('logs') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'logs' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>"><span class="truncate">Logs</span></a></li>
                                <li><a href="<?= base_url('whatsapp') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'whatsapp' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>"><span class="truncate">WhatsApp</span></a></li>
                                <li><a href="<?= base_url('log_whatsapp') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'log_whatsapp' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>"><span class="truncate">Log WhatsApp</span></a></li>
                                <li><a href="<?= base_url('jabatan') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'jabatan' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>"><span class="truncate">Jabatan</span></a></li>
                                <li><a href="<?= base_url('greeting') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'greeting' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>"><span class="truncate">Greetings</span></a></li>
                            </ul>
                        </details>
                    </li>
                <?php endif; ?>


                <?php if ($role === 'superadmin'): ?>
                    <li>
                        <a href="<?= base_url('users') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'users' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                            <i class="fas fa-users w-4 text-center shrink-0"></i>
                            <span class="truncate">Users</span>
                        </a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="<?= base_url('terapis') ?>" class="flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm transition-all <?= $current_segment == 'terapis' ? 'bg-slate-100 font-medium text-slate-900' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <i class="fas fa-user-md w-4 text-center shrink-0"></i>
                        <span class="truncate">Karyawan</span>
                    </a>
                </li>

            </ul>
        </div>
    <?php endif; ?>

</div>

<div class="sticky bottom-0 z-20 mt-auto border-t border-slate-200 bg-white p-2">
    <?= $this->include('App\Views\layout\footer') ?>
</div>