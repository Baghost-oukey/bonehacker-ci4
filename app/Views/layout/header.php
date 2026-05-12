<?php
$role = session()->get('role');
$activeRegion = session()->get('active_region');
$activeRegionName = session()->get('active_region_name');
$listRegions = session()->get('list_regions_global') ?? [];
?>

<header id="appHeader" data-csrf-refresh-url="<?= site_url('auth/get_csrf') ?>"
    class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur-md">
    <div class="mx-auto flex h-14 max-w-screen-2xl items-center justify-between gap-1 px-3 md:px-6">
        
        <div class="flex items-center gap-1.5 min-w-0">
            <!-- MENU BUTTON MOBILE -->
            <a href="<?= base_url('menu') ?>" 
                class="lg:hidden inline-flex items-center justify-center rounded-xl p-1.5 text-slate-600 hover:bg-slate-100 active:scale-95 transition-all">
                <i class="fas fa-th-large text-base"></i>
            </a>

            <!-- TOGGLE BUTTON DESKTOP -->
            <button id="sidebarToggle"
                class="hidden lg:inline-flex items-center justify-center rounded-xl p-1.5 text-slate-600 hover:bg-slate-100 active:scale-95 transition-all">
                <i class="fas fa-bars text-base"></i>
            </button>

            <!-- BREADCRUMBS (Hidden on small mobile) -->
            <div class="hidden sm:block">
                <?= $this->include('App\Views\components\breadcrumbs') ?>
            </div>

            <!-- Page Title Mobile (Only shown on very small screens) -->
            <div class="sm:hidden font-bold text-slate-900 truncate max-w-[100px] text-xs">
                <?= $title ?? 'BoneHacker' ?>
            </div>
        </div>

        <div class="flex items-center gap-1.5 md:gap-4 shrink-0">

            <!-- Global Region Filter - Custom Dropdown -->
            <?php 
                $allowed = session()->get('region_patient_allowed');
                $hasMultipleRegions = is_array($allowed) && count($allowed) > 1;
                $showAllOption = ($role === 'superadmin') || ($role === 'owner' && $hasMultipleRegions);
            ?>
            <div class="relative" id="regionDropdownWrapper">
                <!-- Trigger Button -->
                <button id="regionDropdownBtn" type="button"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-100 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-teal-500/20 max-w-[140px]">
                    <i class="fas fa-map-marker-alt text-teal-500 text-[10px] shrink-0"></i>
                    <span id="regionDropdownLabel" class="truncate max-w-[90px]">
                        <?= esc($activeRegionName ?? 'Pilih Cabang') ?>
                    </span>
                    <i class="fas fa-chevron-down text-[9px] text-slate-400 shrink-0 transition-transform" id="regionDropdownChevron"></i>
                </button>

                <!-- Hidden select for form submission -->
                <select id="globalRegionFilter" class="hidden">
                    <?php if ($showAllOption): ?>
                        <option value="all" <?= $activeRegion === 'all' ? 'selected' : '' ?>>Semua Cabang</option>
                    <?php endif; ?>
                    <?php foreach ($listRegions as $region): ?>
                        <?php 
                            $isAllowed = ($role === 'superadmin') || 
                                         ($allowed === 'all') || 
                                         (is_array($allowed) && in_array($region['id'], $allowed)) || 
                                         ($allowed == $region['id']);
                            if ($isAllowed):
                        ?>
                        <option value="<?= $region['id'] ?>" <?= $activeRegion == $region['id'] ? 'selected' : '' ?>>
                            <?= esc($region['name']) ?>
                        </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>

                <!-- Dropdown Menu -->
                <div id="regionDropdownMenu"
                    class="absolute left-0 top-full mt-1.5 z-50 hidden w-56 rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60 overflow-hidden">
                    
                    <!-- Search -->
                    <div class="p-2 border-b border-slate-100">
                        <div class="relative">
                            <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                            <input type="text" id="regionSearchInput" placeholder="Cari cabang..."
                                class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-7 pr-3 py-1.5 text-xs text-slate-700 focus:outline-none focus:border-teal-400 focus:bg-white transition">
                        </div>
                    </div>

                    <!-- Options (scrollable) -->
                    <div id="regionOptionsList" class="overflow-y-auto max-h-60 py-1">
                    
                    <?php if ($showAllOption): ?>
                        <button type="button" data-value="all" data-label="Semua Cabang"
                            class="region-option flex w-full items-center gap-2.5 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition <?= $activeRegion === 'all' ? 'font-bold text-teal-600' : '' ?>">
                            <i class="fas fa-globe text-[11px] text-slate-400 w-3 shrink-0"></i>
                            <span class="truncate">Semua Cabang</span>
                            <?php if ($activeRegion === 'all'): ?>
                                <i class="fas fa-check ml-auto text-teal-500 text-[10px]"></i>
                            <?php endif; ?>
                        </button>
                        <div class="my-1 border-t border-slate-100"></div>
                    <?php endif; ?>

                    <?php foreach ($listRegions as $region): ?>
                        <?php 
                            $isAllowed = ($role === 'superadmin') || 
                                         ($allowed === 'all') || 
                                         (is_array($allowed) && in_array($region['id'], $allowed)) || 
                                         ($allowed == $region['id']);
                            if (!$isAllowed) continue;
                            $isActive = $activeRegion == $region['id'];
                        ?>
                        <button type="button" data-value="<?= $region['id'] ?>" data-label="<?= esc($region['name']) ?>"
                            class="region-option flex w-full items-center gap-2.5 px-4 py-2 text-sm transition hover:bg-slate-50 <?= $isActive ? 'font-bold text-teal-600' : 'text-slate-700' ?>">
                            <i class="fas fa-map-marker-alt text-[11px] <?= $isActive ? 'text-teal-500' : 'text-slate-300' ?> w-3 shrink-0"></i>
                            <span class="truncate"><?= esc($region['name']) ?></span>
                            <?php if ($isActive): ?>
                                <i class="fas fa-check ml-auto text-teal-500 text-[10px]"></i>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>

                    <!-- Empty state -->
                    <div id="regionEmptyState" class="hidden px-4 py-4 text-center text-xs text-slate-400 italic">
                        Cabang tidak ditemukan
                    </div>

                    </div>
                </div>
            </div>

            <!-- Clock (Hidden on mobile) -->
            <div class="hidden md:block">
                <?= $this->include('App\Views\components\clock') ?>
            </div>

            <span class="hidden md:block h-6 w-px bg-slate-200"></span>

            <?= $this->include('App\Views\components\profile') ?>
        </div>
    </div>
</header>
