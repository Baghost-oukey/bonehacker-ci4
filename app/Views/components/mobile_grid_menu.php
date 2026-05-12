<?php
$role = session()->get('role');
$uri = service('request')->getUri();

$menus = [];

// DASHBOARD (Kembali ke halaman statistik/beranda asli)
$menus[] = ['label' => 'Dashboard', 'url' => site_url('beranda'), 'icon' => 'fa-chart-line', 'color' => 'bg-blue-600'];

if ($role === 'user') {
    $menus[] = ['label' => 'Profil Saya', 'url' => site_url('terapis/profil_saya'), 'icon' => 'fa-user-circle', 'color' => 'bg-indigo-500'];
    $menus[] = ['label' => 'Gaji Saya', 'url' => site_url('gaji/monitor'), 'icon' => 'fa-wallet', 'color' => 'bg-emerald-500'];
}

if ($role !== 'user') {
    $menus[] = ['label' => 'Antrean', 'url' => site_url('antrean'), 'icon' => 'fa-pencil-ruler', 'color' => 'bg-orange-500'];
    $menus[] = ['label' => 'Rekam Medis', 'url' => site_url('rekam-medis'), 'icon' => 'fa-file-medical', 'color' => 'bg-rose-500'];

    if ($role === 'superadmin') {
        $menus[] = ['label' => 'Cabang', 'url' => site_url('region'), 'icon' => 'fa-map', 'color' => 'bg-cyan-500'];
    }
    
    $menus[] = ['label' => 'Kalender Kerja', 'url' => site_url('kalender'), 'icon' => 'fa-calendar-days', 'color' => 'bg-amber-500'];
    $menus[] = ['label' => 'Jurnal', 'url' => site_url('journal'), 'icon' => 'fa-book', 'color' => 'bg-violet-500'];

    if (in_array($role, ['owner', 'admin'])) {
        $menus[] = ['label' => 'Transaksi', 'url' => site_url('transaksi'), 'icon' => 'fa-money-bill-wave', 'color' => 'bg-green-500'];
        $menus[] = ['label' => 'Kehadiran', 'url' => site_url('kehadiran'), 'icon' => 'fa-user-clock', 'color' => 'bg-sky-500'];
    }

    if ($role === 'owner') {
        $menus[] = ['label' => 'Gaji Karyawan', 'url' => site_url('gaji'), 'icon' => 'fa-dollar-sign', 'color' => 'bg-emerald-600'];
        $menus[] = ['label' => 'Kasbon', 'url' => site_url('kasbon'), 'icon' => 'fa-hand-holding-dollar', 'color' => 'bg-yellow-600'];
    }

    if (in_array($role, ['owner', 'admin'])) {
        $menus[] = ['label' => 'Jasa Pelayanan', 'url' => site_url('jasa-pelayanan/reguler'), 'icon' => 'fa-hospital-user', 'color' => 'bg-pink-500'];
    }
}

if ($role === 'owner' || $role === 'superadmin') {
    $menus[] = ['label' => 'Arus Kas', 'url' => site_url('kas'), 'icon' => 'fa-credit-card', 'color' => 'bg-slate-600'];
}

if ($role !== 'user') {
    $menus[] = ['label' => 'Statistik', 'url' => site_url('statistik'), 'icon' => 'fa-chart-line', 'color' => 'bg-indigo-600'];
}

if ($role === 'superadmin' || $role === 'owner') {
    $menus[] = ['label' => 'Tag Keluhan', 'url' => site_url('tag-keluhan'), 'icon' => 'fa-tags', 'color' => 'bg-teal-500'];
}

if ($role === 'superadmin') {
    $menus[] = ['label' => 'Logs', 'url' => site_url('logs'), 'icon' => 'fa-terminal', 'color' => 'bg-slate-800'];
    $menus[] = ['label' => 'Users', 'url' => site_url('users'), 'icon' => 'fa-users', 'color' => 'bg-blue-600'];
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
