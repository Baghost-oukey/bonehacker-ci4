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
            <?= $this->include('App\Views\components\clock') ?>

            <span class="mx-1 h-6 w-px shrink-0 bg-slate-300/90"></span>

            <?= $this->include('App\Views\components\profile') ?>
        </div>
    </div>
</header>