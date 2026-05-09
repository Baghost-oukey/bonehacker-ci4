<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<?php
$waitingList = array_values(array_filter($patient_queues ?? [], static fn($q) => empty($q->process_at) && empty($q->finish_at)));
$processingList = array_values(array_filter($patient_queues ?? [], static fn($q) => !empty($q->process_at) && empty($q->finish_at)));
$finishedList = array_values(array_filter($patient_queues ?? [], static fn($q) => !empty($q->finish_at)));
?>

<section id="monitoringPage" class="w-full space-y-6 p-4 md:p-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">
                Monitor Antrean
            </h1>
            <p class="text-sm text-slate-500">
                Monitoring antrean pasien secara real-time - <?= esc($regionName ?? 'Semua Wilayah') ?>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

        <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="h-12 w-12 flex items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                <i class="fas fa-clock text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500">Menunggu</p>
                <p class="text-3xl font-bold text-slate-900"><?= esc($waiting_queues ?? 0) ?></p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="h-12 w-12 flex items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                <i class="fas fa-user-md text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500">Sedang Terapi</p>
                <p class="text-3xl font-bold text-slate-900"><?= esc($processed_queues ?? 0) ?></p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="h-12 w-12 flex items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                <i class="fas fa-check-circle text-lg"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500">Selesai</p>
                <p class="text-3xl font-bold text-slate-900"><?= esc($finished_queues ?? 0) ?></p>
            </div>
        </div>

    </div>

    <div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">
                        Monitoring Status Antrean
                    </h3>
                    <p class="text-sm text-slate-500">
                        Tampilan daftar pasien yang sedang menunggu, diproses, dan selesai
                    </p>
                </div>
                <div
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-slate-400"></span>
                    <?= esc($regionName ?? 'Semua Wilayah') ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-100 bg-slate-50 px-5 py-3">
                    <div class="flex items-center justify-between">
                        <h2 class="flex items-center gap-2 text-sm font-semibold uppercase text-slate-600">
                            <i class="fas fa-clock text-slate-400"></i>
                            Menunggu
                        </h2>
                        <span
                            class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase text-slate-600"><?= count($waitingList) ?></span>
                    </div>
                </header>
                <div class="h-80 space-y-3 overflow-y-auto p-4">
                    <?php if (!empty($waitingList)): ?>
                        <?php foreach ($waitingList as $i => $q): ?>
                            <?php
                            $isHot = $i < 3;
                            $patientName = $q->patient_name ?? '-';
                            $patientPhone = $q->patient_phone ?? ($q->phone ?? '-');
                            ?>
                            <div
                                class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3 transition hover:border-slate-300 hover:bg-slate-50">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div
                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-700 text-xs font-bold text-white">
                                        <?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-800"><?= esc($patientName) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="flex h-full flex-col items-center justify-center text-center text-slate-500">
                            <p class="text-base font-semibold">Kosong</p>
                            <p class="text-sm">Tidak ada pasien menunggu</p>
                        </div>
                    <?php endif; ?>
                </div>
            </article>

            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-100 bg-slate-50 px-5 py-3">
                    <div class="flex items-center justify-between">
                        <h2 class="flex items-center gap-2 text-sm font-semibold uppercase text-slate-600">
                            <i class="fas fa-user-md text-slate-400"></i>
                            Sedang Terapi
                        </h2>
                        <span
                            class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase text-slate-600"><?= count($processingList) ?></span>
                    </div>
                </header>
                <div class="h-80 space-y-3 overflow-y-auto p-4">
                    <?php if (!empty($processingList)): ?>
                        <?php foreach ($processingList as $q): ?>
                            <?php
                            $patientName = $q->patient_name ?? '-';
                            $patientPhone = $q->patient_phone ?? ($q->phone ?? '-');
                            ?>
                            <div
                                class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3 transition hover:border-slate-300 hover:bg-slate-50">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600">
                                        <i class="fas fa-spinner animate-spin text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-800"><?= esc($patientName) ?></p>
                                    </div>
                                </div>
                                <span
                                    class="inline-flex h-4 w-4 animate-spin rounded-full border-2 border-slate-400 border-t-transparent"></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="flex h-full flex-col items-center justify-center text-center text-slate-500">
                            <p class="text-base font-semibold">Tidak ada aktivitas</p>
                            <p class="text-sm">Belum ada pasien yang sedang terapi</p>
                        </div>
                    <?php endif; ?>
                </div>
            </article>

            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <header class="border-b border-slate-100 bg-slate-50 px-5 py-3">
                    <div class="flex items-center justify-between">
                        <h2 class="flex items-center gap-2 text-sm font-semibold uppercase text-slate-600">
                            <i class="fas fa-check-circle text-slate-400"></i>
                            Selesai
                        </h2>
                        <span
                            class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase text-slate-600"><?= count($finishedList) ?></span>
                    </div>
                </header>
                <div class="h-80 space-y-3 overflow-y-auto p-4">
                    <?php if (!empty($finishedList)): ?>
                        <?php foreach ($finishedList as $q): ?>
                            <?php
                            $patientName = $q->patient_name ?? '-';
                            $patientPhone = $q->patient_phone ?? ($q->phone ?? '-');
                            ?>
                            <div
                                class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3 transition hover:border-slate-300 hover:bg-slate-50">
                                <div class="flex min-w-0 items-center gap-3">
                                    <div
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600">
                                        <i class="fas fa-check text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-800"><?= esc($patientName) ?></p>
                                    </div>
                                </div>
                                <span
                                    class="inline-flex items-center rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold uppercase text-slate-600">Selesai</span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="flex h-full flex-col items-center justify-center text-center text-slate-500">
                            <p class="text-base font-semibold">Belum ada yang selesai</p>
                            <p class="text-sm">Tunggu pasien menyelesaikan terapi</p>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="module">
    import '@/pages/monitoring.js';
</script>
<?= $this->endSection() ?>