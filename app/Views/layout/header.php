<?php
$role = session()->get('role');
$activeRegion = session()->get('active_region');
$activeRegionName = session()->get('active_region_name');
$listRegions = session()->get('list_regions_global') ?? [];
?>

<header id="appHeader" data-csrf-refresh-url="<?= site_url('auth/get_csrf') ?>"
    class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
    <div class="mx-auto flex h-14 max-w-screen-2xl items-center justify-between gap-4 px-4 md:px-6">

        <?= $this->include('App\Views\components\breadcrumbs') ?>

        <div class="flex items-center gap-3">
            <div class="hidden md:flex items-center gap-3">
                <!-- Global Region Filter -->
                <div class="min-w-[180px]">
                    <select id="globalRegionFilter" class="w-full text-sm rounded-md border-slate-300">
                        <?php if (in_array($role, ['superadmin', 'owner']) && (count($listRegions) > 1 || $role === 'superadmin')): ?>
                            <option value="all" <?= $activeRegion === 'all' ? 'selected' : '' ?>>Semua Wilayah</option>
                        <?php endif; ?>
                        
                        <?php foreach ($listRegions as $region): ?>
                            <?php 
                                // Only show regions the user is allowed to see, unless superadmin
                                $allowed = session()->get('region_patient_allowed');
                                $isAllowed = ($role === 'superadmin') || (is_array($allowed) && in_array($region['id'], $allowed)) || ($allowed == $region['id']);
                                
                                if ($isAllowed):
                            ?>
                            <option value="<?= $region['id'] ?>" <?= $activeRegion == $region['id'] ? 'selected' : '' ?>>
                                <?= esc($region['name']) ?>
                            </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?= $this->include('App\Views\components\clock') ?>

                <span class="mx-1 h-6 w-px shrink-0 bg-slate-300/90"></span>
            </div>

            <?= $this->include('App\Views\components\profile') ?>
        </div>
    </div>
</header>