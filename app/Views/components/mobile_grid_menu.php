<?php
$role = session()->get('role');
$uri = service('request')->getUri();

$menus = [];

// DASHBOARD - Semua Role
$menus[] = ['label' => 'Dashboard', 'url' => site_url('beranda'), 'icon' => 'fa-chart-line', 'color' => 'bg-blue-600'];

// Menu Khusus TERAPIS (User)
if ($role === 'user') {
    $menus[] = ['label' => 'Profil Saya', 'url' => site_url('terapis/profil_saya'), 'icon' => 'fa-user-circle', 'color' => 'bg-indigo-500'];
    $menus[] = ['label' => 'Gaji Saya', 'url' => site_url('gaji/monitor'), 'icon' => 'fa-wallet', 'color' => 'bg-emerald-500'];
}

// Menu untuk ADMIN, OWNER, SUPERADMIN
if ($role !== 'user') {
    $menus[] = ['label' => 'Antrean', 'url' => site_url('antrean'), 'icon' => 'fa-pencil-ruler', 'color' => 'bg-orange-500'];
    $menus[] = ['label' => 'Rekam Medis', 'url' => site_url('rekam-medis'), 'icon' => 'fa-file-medical', 'color' => 'bg-rose-500'];

    if ($role === 'superadmin') {
        $menus[] = ['label' => 'Cabang', 'url' => site_url('region'), 'icon' => 'fa-map', 'color' => 'bg-cyan-500'];
    }
    
    $menus[] = ['label' => 'Kalender Kerja', 'url' => site_url('kalender'), 'icon' => 'fa-calendar-days', 'color' => 'bg-amber-500'];
    $menus[] = ['label' => 'Jurnal', 'url' => site_url('journal'), 'icon' => 'fa-book', 'color' => 'bg-violet-500'];

    // Transaksi & Presensi Karyawan (Owner, Admin, Superadmin)
    if (in_array($role, ['owner', 'admin', 'superadmin'])) {
        $menus[] = ['label' => 'Transaksi', 'url' => site_url('transaksi'), 'icon' => 'fa-money-bill-wave', 'color' => 'bg-green-500'];
        $menus[] = ['label' => 'Presensi Karyawan', 'url' => site_url('kehadiran'), 'icon' => 'fa-user-clock', 'color' => 'bg-sky-500'];

        if ($role === 'superadmin') {
            $menus[] = ['label' => 'Keuangan', 'url' => site_url('kas/categories'), 'icon' => 'fa-credit-card', 'color' => 'bg-slate-600'];
        }
    }

    // Kelola Gaji & Kasbon (Hanya Owner)
    if ($role === 'owner') {
        $menus[] = ['label' => 'Kelola Gaji', 'url' => site_url('gaji'), 'icon' => 'fa-dollar-sign', 'color' => 'bg-emerald-600'];
        $menus[] = ['label' => 'Kasbon', 'url' => site_url('kasbon'), 'icon' => 'fa-hand-holding-dollar', 'color' => 'bg-yellow-600'];
        $menus[] = ['label' => 'Arus Kas', 'url' => site_url('kas'), 'icon' => 'fa-credit-card', 'color' => 'bg-slate-600'];
    }

    if (in_array($role, ['owner', 'admin'])) {
        $menus[] = ['label' => 'Jasa Pelayanan', 'url' => site_url('jasa-pelayanan/reguler'), 'icon' => 'fa-hospital-user', 'color' => 'bg-pink-500'];
    }

    // Statistik & Analitik
    $menus[] = ['label' => 'Statistik', 'url' => site_url('statistiktag'), 'icon' => 'fa-chart-pie', 'color' => 'bg-indigo-600'];
}

// Manajemen Tags, Karyawan & Pengaturan
if ($role === 'superadmin') {
    $menus[] = ['label' => 'Tags', 'url' => site_url('tag-keluhan'), 'icon' => 'fa-tags', 'color' => 'bg-teal-500'];
    $menus[] = ['label' => 'Pengaturan', 'url' => site_url('logs'), 'icon' => 'fa-cog', 'color' => 'bg-slate-700'];
    $menus[] = ['label' => 'Users', 'url' => site_url('users'), 'icon' => 'fa-users', 'color' => 'bg-blue-600'];
    $menus[] = ['label' => 'Karyawan', 'url' => site_url('terapis'), 'icon' => 'fa-user-md', 'color' => 'bg-blue-500'];
} else if ($role === 'owner') {
    $menus[] = ['label' => 'Tags', 'url' => site_url('tag-keluhan'), 'icon' => 'fa-tags', 'color' => 'bg-teal-500'];
    $menus[] = ['label' => 'Karyawan', 'url' => site_url('terapis'), 'icon' => 'fa-user-md', 'color' => 'bg-blue-500'];
}
?>


<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:hidden">
    <?php foreach ($menus as $menu): ?>
        <a href="<?= $menu['url'] ?>" class="flex flex-col items-center justify-center gap-3 rounded-2xl bg-white p-6 shadow-sm transition-all active:scale-95 border border-slate-100">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl <?= $menu['color'] ?> text-white shadow-lg shadow-<?= str_replace('bg-', '', $menu['color']) ?>/20">
                <i class="fas <?= $menu['icon'] ?> text-lg"></i>
            </div>
            <span class="text-center text-xs font-bold text-slate-700"><?= $menu['label'] ?></span>
        </a>
    <?php endforeach; ?>
</div>
