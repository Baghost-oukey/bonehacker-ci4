<?php
$summaryPayload = [
    'today' => (int) ($pasien_today ?? 0),
    'yesterday' => (int) ($pasien_yesterday ?? 0),
    'thismonth' => (int) ($pasien_thismonth ?? 0),
    'lastmonth' => (int) ($pasien_lastmonth ?? 0),
    'thisyear' => (int) ($pasien_thisyear ?? 0),
    'lastyear' => (int) ($pasien_lastyear ?? 0),
];

$summaryJson = htmlspecialchars(json_encode($summaryPayload), ENT_QUOTES, 'UTF-8');
?>

<div id="patientSummaryChartRoot" data-summary="<?= $summaryJson ?>" class="rounded-2xl bg-white p-5 shadow-sm">
    <div class="mb-3 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-700">Perbandingan Pasien</h3>
        <span class="text-xs text-slate-500">Harian, Bulanan, Tahunan</span>
    </div>

    <div class="h-76">
        <canvas id="patientSummaryChart"></canvas>
    </div>
</div>
