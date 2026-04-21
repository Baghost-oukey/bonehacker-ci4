<?php
$uri = service('request')->getUri();
$current_segment = $uri->getTotalSegments() > 0 ? $uri->getSegment(1) : '';
$role = session()->get('role');
$realname = session()->get('realname') ?? 'User';
$userInitial = strtoupper(substr($realname, 0, 1));
?>

<aside id="appSidebar"
    class="fixed left-0 top-0 z-40 h-screen w-72 -translate-x-full border-r border-slate-200/90 bg-white/95 shadow-lg shadow-slate-900/5 backdrop-blur-sm transition-transform duration-300 lg:translate-x-0">
    <div class="flex h-full flex-col">
        <div class="border-b border-slate-200">
            <div class="relative flex h-14 shrink-0 items-center  px-2">
                <a href="<?= base_url() ?>"
                    class="flex h-8 w-max items-center gap-2 rounded-lg px-3 text-slate-800 transition hover:bg-slate-100">
                    <span
                        class="flex h-6 w-6 items-center justify-center rounded-md bg-slate-900 text-[11px] font-bold text-white">B</span>
                    <span class="text-sm font-semibold tracking-tight">Bonehacker</span>
                </a>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto px-2 pt-4">
            <nav class="space-y-4">
                <div class="space-y-1">
                    <p class="px-2 text-sm font-medium mb-2 text-slate-400">Dashboard</p>

                    <a href="<?= base_url('beranda') ?>"
                        class="flex h-9 items-center gap-3 rounded-lg px-3 text-sm font-medium transition <?= in_array($current_segment, ['beranda', '']) ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <i class="fas fa-home w-4 text-center"></i>
                        <span>Beranda</span>
                    </a>

                    <a href="<?= base_url('antrean') ?>"
                        class="flex h-9 items-center gap-3 rounded-lg px-3 text-sm font-medium transition <?= $current_segment == 'antrean' ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <i class="fas fa-pencil-ruler w-4 text-center"></i>
                        <span>Antrean</span>
                    </a>

                    <a href="<?= base_url('rekam-medis') ?>"
                        class="flex h-9 items-center gap-3 rounded-lg px-3 text-sm font-medium transition <?= $current_segment == 'rekam-medis' ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <i class="fas fa-file-medical w-4 text-center"></i>
                        <span>Rekam Medis</span>
                    </a>

                    <?php if ($role === 'superadmin' || $role === 'owner'): ?>
                        <a href="<?= base_url('region') ?>"
                            class="flex h-9 items-center gap-3 rounded-lg px-3 text-sm font-medium transition <?= $current_segment == 'region' ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' ?>">
                            <i class="fas fa-map w-4 text-center"></i>
                            <span>Cabang</span>
                        </a>
                    <?php endif; ?>

                    <a href="<?= base_url('journal') ?>"
                        class="flex h-9 items-center gap-3 rounded-lg px-3 text-sm font-medium transition <?= $current_segment == 'journal' ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <i class="fas fa-book w-4 text-center"></i>
                        <span>Jurnal</span>
                    </a>

                    <a href="<?= base_url('transaksi') ?>"
                        class="flex h-9 items-center gap-3 rounded-lg px-3 text-sm font-medium transition <?= $current_segment == 'transaksi' ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' ?>">
                        <i class="fas fa-money-bill-wave w-4 text-center"></i>
                        <span>Transaksi</span>
                    </a>
                </div>

                <div class="space-y-1">
                    <p class="px-2 text-sm font-medium mb-2 text-slate-400">Analitik</p>
                    <details class="group" <?= in_array($current_segment, ['statistiktag', 'statistik', 'statistikresource', 'statistikresult', 'statistikgender', 'statistikdaerah']) ? 'open' : '' ?>>
                        <summary
                            class="flex h-9 cursor-pointer list-none items-center justify-between rounded-lg px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100 hover:text-slate-900">
                            <span class="flex items-center gap-3">
                                <i class="fas fa-chart-line w-4 text-center"></i>
                                <span>Statistik</span>
                            </span>
                            <i
                                class="fas fa-chevron-down text-[10px] text-slate-400 transition group-open:rotate-180"></i>
                        </summary>
                        <div class="mt-1 space-y-1 pl-4">
                            <a href="<?= base_url('statistiktag') ?>"
                                class="block rounded-md px-3 py-2 text-sm transition <?= $current_segment == 'statistiktag' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">Keluhan
                                & Medis</a>
                            <a href="<?= base_url('statistik') ?>"
                                class="block rounded-md px-3 py-2 text-sm transition <?= $current_segment == 'statistik' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">Riwayat
                                Pasien</a>
                            <a href="<?= base_url('statistikresource') ?>"
                                class="block rounded-md px-3 py-2 text-sm transition <?= $current_segment == 'statistikresource' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">Sosial
                                Media</a>
                            <a href="<?= base_url('statistikresult') ?>"
                                class="block rounded-md px-3 py-2 text-sm transition <?= $current_segment == 'statistikresult' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">Hasil
                                Pemeriksaan</a>
                            <a href="<?= base_url('statistikgender') ?>"
                                class="block rounded-md px-3 py-2 text-sm transition <?= $current_segment == 'statistikgender' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">Jenis
                                Kelamin</a>
                            <a href="<?= base_url('statistikdaerah') ?>"
                                class="block rounded-md px-3 py-2 text-sm transition <?= $current_segment == 'statistikdaerah' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">Daerah</a>
                        </div>
                    </details>
                </div>

                <?php if ($role === 'superadmin' || $role === 'owner'): ?>
                    <div class="space-y-1">
                        <p class="px-2 text-sm font-medium mb-2 text-slate-400">Manajemen</p>

                        <details class="group" <?= in_array($current_segment, ['complaint', 'medis', 'result']) ? 'open' : '' ?>>
                            <summary
                                class="flex h-9 cursor-pointer list-none items-center justify-between rounded-lg px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100 hover:text-slate-900">
                                <span class="flex items-center gap-3">
                                    <i class="fas fa-tags w-4 text-center"></i>
                                    <span>Tags</span>
                                </span>
                                <i
                                    class="fas fa-chevron-down text-[10px] text-slate-400 transition group-open:rotate-180"></i>
                            </summary>
                            <div class="mt-1 space-y-1 pl-4">
                                <a href="<?= base_url('complaint') ?>"
                                    class="block rounded-md px-3 py-2 text-sm transition <?= $current_segment == 'complaint' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">Tag
                                    Keluhan</a>
                                <a href="<?= base_url('tag-rekam-medis') ?>"
                                    class="block rounded-md px-3 py-2 text-sm transition <?= $current_segment == 'tag-rekam-medis' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">Tag
                                    Rekam Medis</a>
                                <a href="<?= base_url('tag-pemeriksaan') ?>"
                                    class="block rounded-md px-3 py-2 text-sm transition <?= $current_segment == 'tag-pemeriksaan' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">Tag
                                    Pemeriksaan</a>
                            </div>
                        </details>

                        <details class="group" <?= in_array($current_segment, ['logs', 'whatsapp', 'log_whatsapp', 'jabatan', 'greeting']) ? 'open' : '' ?>>
                            <summary
                                class="flex h-9 cursor-pointer list-none items-center justify-between rounded-lg px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100 hover:text-slate-900">
                                <span class="flex items-center gap-3">
                                    <i class="fas fa-cog w-4 text-center"></i>
                                    <span>Pengaturan</span>
                                </span>
                                <i
                                    class="fas fa-chevron-down text-[10px] text-slate-400 transition group-open:rotate-180"></i>
                            </summary>
                            <div class="mt-1 space-y-1 pl-4">
                                <a href="<?= base_url('logs') ?>"
                                    class="block rounded-md px-3 py-2 text-sm transition <?= $current_segment == 'logs' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">Logs</a>
                                <a href="<?= base_url('whatsapp') ?>"
                                    class="block rounded-md px-3 py-2 text-sm transition <?= $current_segment == 'whatsapp' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">WhatsApp</a>
                                <a href="<?= base_url('log_whatsapp') ?>"
                                    class="block rounded-md px-3 py-2 text-sm transition <?= $current_segment == 'log_whatsapp' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">Log
                                    WhatsApp</a>
                                <a href="<?= base_url('jabatan') ?>"
                                    class="block rounded-md px-3 py-2 text-sm transition <?= $current_segment == 'jabatan' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">Jabatan</a>
                                <a href="<?= base_url('greeting') ?>"
                                    class="block rounded-md px-3 py-2 text-sm transition <?= $current_segment == 'greeting' ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">Greetings</a>
                            </div>
                        </details>

                        <a href="<?= base_url('users') ?>"
                            class="flex h-9 items-center gap-3 rounded-lg px-3 text-sm font-medium transition <?= $current_segment == 'users' ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' ?>">
                            <i class="fas fa-users w-4 text-center"></i>
                            <span>Users</span>
                        </a>

                        <a href="<?= base_url('terapis') ?>"
                            class="flex h-9 items-center gap-3 rounded-lg px-3 text-sm font-medium transition <?= $current_segment == 'terapis' ? 'bg-slate-100 text-slate-900' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900' ?>">
                            <i class="fas fa-user-md w-4 text-center"></i>
                            <span>Terapis</span>
                        </a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
        <?= $this->include('App\Views\layout\footer') ?>
    </div>
</aside>
