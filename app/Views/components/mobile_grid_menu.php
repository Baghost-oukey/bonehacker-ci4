<?php
$role = session()->get('role');
$uri = service('request')->getUri();
$current_segment = $uri->getSegment(1);

$menus = [];

// Menu Khusus TERAPIS (User)
if ($role === 'user' && !empty(session()->get('terapis_id'))) {
    $menus[] = ['label' => 'Beranda', 'url' => site_url('beranda'), 'icon' => 'fa-home', 'color' => 'bg-slate-800'];
    $menus[] = ['label' => 'Profil Saya', 'url' => site_url('karyawan/profil_saya'), 'icon' => 'fa-user-circle', 'color' => 'bg-indigo-500'];
    $menus[] = ['label' => 'Gaji Saya', 'url' => site_url('gaji/monitor'), 'icon' => 'fa-wallet', 'color' => 'bg-emerald-500'];
}

// Menu untuk SUPERADMIN
if ($role === 'superadmin') {
    // Dashboard Section
    $menus[] = ['label' => 'Beranda', 'url' => site_url('beranda'), 'icon' => 'fa-home', 'color' => 'bg-slate-800'];
    $menus[] = ['label' => 'Antrean', 'url' => site_url('antrean'), 'icon' => 'fa-pencil-ruler', 'color' => 'bg-orange-500'];
    $menus[] = ['label' => 'Statistik', 'url' => site_url('statistik'), 'icon' => 'fa-chart-pie', 'color' => 'bg-indigo-600'];
    $menus[] = ['label' => 'Rekam Medis', 'url' => site_url('rekam-medis'), 'icon' => 'fa-file-medical', 'color' => 'bg-rose-500'];
    $menus[] = ['label' => 'Cabang', 'url' => site_url('region'), 'icon' => 'fa-map', 'color' => 'bg-cyan-500'];
    $menus[] = ['label' => 'Kalender Kerja', 'url' => site_url('kalender'), 'icon' => 'fa-calendar-days', 'color' => 'bg-amber-500'];
    $menus[] = ['label' => 'Jurnal', 'url' => site_url('journal'), 'icon' => 'fa-book', 'color' => 'bg-violet-500'];

    // Kas Section
    $menus[] = ['label' => 'Master Kategori Kas', 'url' => site_url('kas/categories'), 'icon' => 'fa-credit-card', 'color' => 'bg-slate-600'];

    // Manajemen Section
    $menus[] = ['label' => 'Manajemen Tags', 'url' => site_url('tag-keluhan'), 'icon' => 'fa-tags', 'color' => 'bg-teal-600'];
    $menus[] = ['label' => 'Logs', 'url' => site_url('logs'), 'icon' => 'fa-cog', 'color' => 'bg-slate-700'];
    $menus[] = ['label' => 'WhatsApp', 'url' => site_url('whatsapp'), 'icon' => 'fa-cog', 'color' => 'bg-green-600'];
    $menus[] = ['label' => 'Log WhatsApp', 'url' => site_url('log_whatsapp'), 'icon' => 'fa-cog', 'color' => 'bg-green-700'];
    $menus[] = ['label' => 'Jabatan', 'url' => site_url('jabatan'), 'icon' => 'fa-cog', 'color' => 'bg-blue-700'];
    $menus[] = ['label' => 'Rank Terapis', 'url' => site_url('rank-terapis'), 'icon' => 'fa-star', 'color' => 'bg-amber-600'];
    $menus[] = ['label' => 'Greetings', 'url' => site_url('greeting'), 'icon' => 'fa-cog', 'color' => 'bg-purple-600'];
    $menus[] = ['label' => 'Manajemen Karyawan', 'url' => site_url('karyawan'), 'icon' => 'fa-user-friends', 'color' => 'bg-blue-500'];
    $menus[] = ['label' => 'Statistik Tag', 'url' => site_url('statistiktag'), 'icon' => 'fa-chart-bar', 'color' => 'bg-purple-600'];
}

// Menu untuk OWNER
if ($role === 'owner') {
    // Dashboard Section
    $menus[] = ['label' => 'Beranda', 'url' => site_url('beranda'), 'icon' => 'fa-home', 'color' => 'bg-slate-800'];
    $menus[] = ['label' => 'Antrean', 'url' => site_url('antrean'), 'icon' => 'fa-pencil-ruler', 'color' => 'bg-orange-500'];
    $menus[] = ['label' => 'Rekam Medis', 'url' => site_url('rekam-medis'), 'icon' => 'fa-file-medical', 'color' => 'bg-rose-500'];
    $menus[] = ['label' => 'Jurnal', 'url' => site_url('journal'), 'icon' => 'fa-book', 'color' => 'bg-violet-500'];
    $menus[] = ['label' => 'Transaksi', 'url' => site_url('transaksi'), 'icon' => 'fa-money-bill-wave', 'color' => 'bg-green-500'];

    $menus[] = ['label' => 'Presensi Karyawan', 'url' => site_url('kehadiran/tambah'), 'icon' => 'fa-user-clock', 'color' => 'bg-sky-600'];
    $menus[] = ['label' => 'Kalender Kerja', 'url' => site_url('kalender'), 'icon' => 'fa-calendar-days', 'color' => 'bg-amber-500'];
   
    // Kelola Gaji Section
    $menus[] = ['label' => 'Kelola Gaji', 'url' => site_url('gaji'), 'icon' => 'fa-dollar-sign', 'color' => 'bg-emerald-600'];
     
    // Jasa Pelayanan Section
    $menus[] = ['label' => 'Jasa Pelayanan', 'url' => site_url('jasa-pelayanan/reguler'), 'icon' => 'fa-hospital-user', 'color' => 'bg-pink-600'];
     
    // Kas Section
    $menus[] = ['label' => 'Keuangan', 'url' => site_url('kas'), 'icon' => 'fa-credit-card', 'color' => 'bg-slate-600'];
    $menus[] = ['label' => 'Statistik', 'url' => site_url('statistik'), 'icon' => 'fa-chart-pie', 'color' => 'bg-indigo-600'];
    $menus[] = ['label' => 'Manajemen Tags', 'url' => site_url('tag-keluhan'), 'icon' => 'fa-tags', 'color' => 'bg-teal-500'];
    $menus[] = ['label' => 'Manajemen Karyawan', 'url' => site_url('karyawan'), 'icon' => 'fa-user-friends', 'color' => 'bg-blue-500'];
}

// Menu untuk ADMIN
if ($role === 'admin') {
    // Dashboard Section
    $menus[] = ['label' => 'Beranda', 'url' => site_url('beranda'), 'icon' => 'fa-home', 'color' => 'bg-slate-800'];
    $menus[] = ['label' => 'Antrean', 'url' => site_url('antrean'), 'icon' => 'fa-pencil-ruler', 'color' => 'bg-orange-500'];
    $menus[] = ['label' => 'Rekam Medis', 'url' => site_url('rekam-medis'), 'icon' => 'fa-file-medical', 'color' => 'bg-rose-500'];


    $menus[] = ['label' => 'Jurnal', 'url' => site_url('journal'), 'icon' => 'fa-book', 'color' => 'bg-violet-500'];
    $menus[] = ['label' => 'Transaksi', 'url' => site_url('transaksi'), 'icon' => 'fa-money-bill-wave', 'color' => 'bg-green-500'];
    $menus[] = ['label' => 'Presensi Karyawan', 'url' => site_url('kehadiran/tambah'), 'icon' => 'fa-user-clock', 'color' => 'bg-sky-600'];
    
    // Jasa Pelayanan Section
        $menus[] = ['label' => 'Kalender Kerja', 'url' => site_url('kalender'), 'icon' => 'fa-calendar-days', 'color' => 'bg-amber-500'];
    $menus[] = ['label' => 'Jasa Pelayanan', 'url' => site_url('jasa-pelayanan/reguler'), 'icon' => 'fa-hospital-user', 'color' => 'bg-pink-600'];
    
    // Manajemen Section
    $menus[] = ['label' => 'Statistik', 'url' => site_url('statistik'), 'icon' => 'fa-chart-pie', 'color' => 'bg-indigo-600'];
    $menus[] = ['label' => 'Manajemen Tags', 'url' => site_url('tag-keluhan'), 'icon' => 'fa-tags', 'color' => 'bg-teal-500'];
    $menus[] = ['label' => 'Manajemen Karyawan', 'url' => site_url('karyawan'), 'icon' => 'fa-user-friends', 'color' => 'bg-blue-500'];
}
?>


<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:hidden">
    <?php foreach ($menus as $menu): ?>
        <a href="<?= $menu['url'] ?>" class="flex flex-col items-center justify-center gap-3 rounded-2xl bg-white p-6 shadow-sm transition-all active:scale-95 hover:shadow-md border border-slate-100">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl <?= $menu['color'] ?> text-white shadow-lg shadow-<?= str_replace('bg-', '', $menu['color']) ?>/20">
                <i class="fas <?= $menu['icon'] ?> text-lg"></i>
            </div>
            <span class="text-center text-xs font-bold text-slate-700"><?= $menu['label'] ?></span>
        </a>
    <?php endforeach; ?>
</div>