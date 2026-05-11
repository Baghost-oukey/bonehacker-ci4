<?php
$role = session()->get('role');
$activeRegion = session()->get('active_region');
$activeRegionName = session()->get('active_region_name');
$listRegions = session()->get('list_regions_global') ?? [];
?>

<header id="appHeader" data-csrf-refresh-url="<?= site_url('auth/get_csrf') ?>"
    class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur-md">
    <div class="mx-auto flex h-14 max-w-screen-2xl items-center justify-between gap-2 px-4 md:px-6">
        
        <div class="flex items-center gap-2">
            <!-- TOGGLE BUTTON MOBILE -->
            <button id="sidebarToggle"
                class="lg:hidden inline-flex items-center justify-center rounded-xl p-2 text-slate-600 hover:bg-slate-100 active:scale-95 transition-all">
                <i class="fas fa-bars text-lg"></i>
            </button>

            <!-- BREADCRUMBS (Hidden on small mobile) -->
            <div class="hidden sm:block">
                <?= $this->include('App\Views\components\breadcrumbs') ?>
            </div>

            <!-- Page Title Mobile (Only shown on very small screens) -->
            <div class="sm:hidden font-bold text-slate-900 truncate max-w-30">
                <?= $title ?? 'BoneHacker' ?>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-4">
            <!-- Global Region Filter (Mobile Optimized) -->
            <div class="relative group">
                <select id="globalRegionFilter" 
                    class="block w-32 md:w-48 appearance-none bg-slate-50 border border-slate-200 text-slate-700 text-[11px] md:text-sm rounded-xl py-1.5 pl-3 pr-8 focus:outline-none focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 transition-all cursor-pointer">
                    <?php 
                        $allowed = session()->get('region_patient_allowed');
                        $hasMultipleRegions = is_array($allowed) && count($allowed) > 1;
                        $showAllOption = ($role === 'superadmin') || ($role === 'owner' && $hasMultipleRegions);
                    ?>
                    <?php if ($showAllOption): ?>
                        <option value="all" <?= $activeRegion === 'all' ? 'selected' : '' ?>>Semua Wilayah</option>
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
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                    <i class="fas fa-chevron-down text-[10px]"></i>
                </div>
            </div>

            <!-- Clock (Hidden on mobile to save space) -->
            <div class="hidden md:block">
                <?= $this->include('App\Views\components\clock') ?>
            </div>

            <span class="hidden md:block h-6 w-px bg-slate-200"></span>

            <?= $this->include('App\Views\components\profile') ?>
        </div>
    </div>
</header>