<?php
$role = session()->get('role');
$activeRegion = session()->get('active_region');
$activeRegionName = session()->get('active_region_name');
$listRegions = session()->get('list_regions_global') ?? [];
$totalBranches = count($listRegions);
?>

<?php if ($role === 'owner' || $role === 'superadmin'): ?>
    <div id="monitoringBranch" class="relative" data-switch-region-url="<?= site_url('auth/switch_region') ?>">
        <button type="button" data-menu-toggle="regionMenu"
            class="group flex h-9 items-center gap-2 rounded-lg border border-slate-200/90 bg-white pl-3 pr-10 text-sm text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50">
            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-slate-100 text-slate-500 transition group-hover:bg-slate-200 group-hover:text-slate-700">
                <i class="fas fa-map-marker-alt text-xs"></i>
            </span>

            <span class="max-w-32 truncate text-left text-[13px] font-medium leading-none">
                <?= ($activeRegion == 'all') ? 'Semua cabang' : esc($activeRegionName ?? 'Pilih cabang') ?>
            </span>

            <i class="fas fa-chevron-down text-[9px] text-slate-400 transition group-hover:text-slate-600"></i>
        </button>

        <div id="regionMenu"
            class="absolute right-0 z-30 mt-2 hidden w-72 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl ring-1 ring-black/5">
            <div class="border-b border-slate-100 px-4 py-3">
                <p class="text-xs font-medium text-slate-500">Pilih cabang pantauan</p>
                <p class="mt-0.5 text-xs text-slate-400"><?= $totalBranches ?> cabang tersedia</p>
            </div>
            <div class="max-h-80 overflow-y-auto p-2">

                <!-- Semua Cabang -->
                <button type="button" class="btn-switch-region flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition 
            <?= ($activeRegion == 'all') ? 'bg-sky-50 font-medium text-sky-700 ring-1 ring-sky-200' : 'text-slate-700 hover:bg-slate-50' ?>" data-id="all"
                    data-name="Semua cabang">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-globe text-slate-400"></i>
                        Semua cabang
                    </span>

                    <?php if ($activeRegion == 'all'): ?>
                        <i class="fas fa-check text-xs text-sky-600"></i>
                    <?php endif; ?>
                </button>

                <!-- List Cabang -->
                <?php foreach ($listRegions as $rg): ?>
                    <?php $isActive = ($activeRegion == $rg['id']); ?>

                    <button type="button" class="btn-switch-region flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm transition 
                <?= $isActive ? 'bg-sky-50 font-medium text-sky-700 ring-1 ring-sky-200' : 'text-slate-700 hover:bg-slate-50' ?>" data-id="<?= $rg['id'] ?>"
                        data-name="<?= esc($rg['name']) ?>">
                        <span class="flex items-center gap-2 truncate">
                            <i class="fas fa-building text-slate-400"></i>
                            <?= esc($rg['name']) ?>
                        </span>

                        <?php if ($isActive): ?>
                            <i class="fas fa-check text-xs text-sky-600"></i>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
<?php endif; ?>