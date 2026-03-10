<?php
    $uri = service('request')->getUri();
    // Mengambil segment pertama, jika kosong (home) maka default ke string kosong
    $current_segment = $uri->getTotalSegments() > 0 ? $uri->getSegment(1) : ''; 
    $url = base_url() . '/';
?>

<aside class="main-sidebar sidebar-style-2 elevation-4">
    <div id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="<?= base_url() ?>">Bonehacker</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="<?= base_url() ?>">Bn</a>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-header">Dashboard</li>
            
            <li class="<?= in_array($current_segment, ['beranda', '']) ? 'active' : '' ?>">
                <a class="nav-link" href="<?= $url ?>">
                    <i class="fas fa-home"></i>
                    <span>Beranda</span>
                </a>
            </li>

            <li class="<?= $current_segment == 'antrean' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= $url ?>antrean">
                    <i class="fas fa-pencil-ruler"></i>
                    <span>Antrean</span>
                </a>
            </li>

            <li class="<?= $current_segment == 'dashboard' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= $url ?>dashboard">
                    <i class="fas fa-file-medical"></i>
                    <span>Rekam Medis</span>
                </a>
            </li>

            <?php if (isset($role) && $role == 'superadmin'): ?>
                <li class="<?= $current_segment == 'region' ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= $url ?>region">
                        <i class="fas fa-map"></i>
                        <span>Wilayah</span>
                    </a>
                </li>
            <?php endif; ?>

            <li class="<?= $current_segment == 'journal' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= $url ?>journal">
                    <i class="fas fa-book"></i>
                    <span>Jurnal</span>
                </a>
            </li>

            <li class="nav-item dropdown <?= in_array($current_segment, ['statistiktag', 'statistik', 'statresult', 'statistikgender', 'statsdaerah']) ? 'active' : '' ?>">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                    <i class="fas fa-chart-line"></i>
                    <span>Statistik</span>
                </a>
                <ul class="dropdown-menu">
                    <li class="<?= $current_segment == 'statistiktag' ? 'active' : '' ?>"><a class="nav-link" href="<?= $url ?>statistiktag">Keluhan & Medis</a></li>
                    <li class="<?= $current_segment == 'statistik' ? 'active' : '' ?>"><a class="nav-link" href="<?= $url ?>statistik">Riwayat Pasien</a></li>
                    <li class="<?= $current_segment == 'statresult' ? 'active' : '' ?>"><a class="nav-link" href="<?= $url ?>statresult">Hasil Pemeriksaan</a></li>
                    <li class="<?= $current_segment == 'statistikgender' ? 'active' : '' ?>"><a class="nav-link" href="<?= $url ?>statistikgender">Jenis Kelamin</a></li>
                    <li class="<?= $current_segment == 'statsdaerah' ? 'active' : '' ?>"><a class="nav-link" href="<?= $url ?>statsdaerah">Daerah</a></li>
                </ul>
            </li>

            <?php if (isset($role) && $role == 'superadmin'): ?>
                <li class="menu-header">Administrator</li>
                
                <li class="nav-item dropdown <?= in_array($current_segment, ['complaint', 'medis', 'result']) ? 'active' : '' ?>">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-tags"></i> <span>Tags</span></a>
                    <ul class="dropdown-menu">
                        <li class="<?= $current_segment == 'complaint' ? 'active' : '' ?>"><a class="nav-link" href="<?= $url ?>complaint">Tag Keluhan</a></li>
                        <li class="<?= $current_segment == 'medis' ? 'active' : '' ?>"><a class="nav-link" href="<?= $url ?>medis">Tag Rekam Medis</a></li>
                        <li class="<?= $current_segment == 'result' ? 'active' : '' ?>"><a class="nav-link" href="<?= $url ?>result">Tag Pemeriksaan</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown <?= in_array($current_segment, ['logs', 'whatsapp', 'log_whatsapp', 'jabatan', 'greeting']) ? 'active' : '' ?>">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-cog"></i> <span>Pengaturan</span></a>
                    <ul class="dropdown-menu">
                        <li class="<?= $current_segment == 'logs' ? 'active' : '' ?>"><a class="nav-link" href="<?= $url ?>logs">System Logs</a></li>
                        <li class="<?= $current_segment == 'whatsapp' ? 'active' : '' ?>"><a class="nav-link" href="<?= $url ?>whatsapp">Config WhatsApp</a></li>
                        <li class="<?= $current_segment == 'log_whatsapp' ? 'active' : '' ?>"><a class="nav-link" href="<?= $url ?>log_whatsapp">Log WhatsApp</a></li>
                        <li class="<?= $current_segment == 'jabatan' ? 'active' : '' ?>"><a class="nav-link" href="<?= $url ?>jabatan">Data Jabatan</a></li>
                        <li class="<?= $current_segment == 'greeting' ? 'active' : '' ?>"><a class="nav-link" href="<?= $url ?>greeting">Greetings</a></li>
                    </ul>
                </li>

                <li class="<?= $current_segment == 'users' ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= $url ?>users"><i class="fas fa-users"></i> <span>Manajemen User</span></a>
                </li>
                <li class="<?= $current_segment == 'terapis' ? 'active' : '' ?>">
                    <a class="nav-link" href="<?= $url ?>terapis"><i class="fas fa-user-md"></i> <span>Data Terapis</span></a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</aside>