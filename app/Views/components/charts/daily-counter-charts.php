<?php
$chartDataJson = htmlspecialchars(json_encode($daily_counts ?? []), ENT_QUOTES, 'UTF-8');
?>

<div id="dailyCounterChartRoot" data-daily-counts="<?= $chartDataJson ?>"
    class="rounded-2xl bg-white shadow-sm border border-slate-100 overflow-hidden">

    <!-- HEADER -->
    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
        <h3 class="text-sm font-semibold text-slate-700">
            Grafik Minggu Berjalan
        </h3>
        <span id="weeklyRangeText" class="text-xs text-slate-500"></span>
    </div>

    <!-- CHART -->
    <div class="p-5">
        <div class="h-72">
            <canvas id="weeklyChart"></canvas>
        </div>
    </div>
</div>