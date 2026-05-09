function initPatientSummaryChart() {
    const root = document.getElementById('patientSummaryChartRoot');
    const canvas = document.getElementById('patientSummaryChart');
    if (!root || !canvas || typeof Chart === 'undefined') return;

    let summary = {};
    try {
        summary = JSON.parse(root.dataset.summary || '{}');
    } catch (_error) {
        summary = {};
    }

    const labels = ['Harian', 'Bulanan', 'Tahunan'];

    const currentData = [
        Number(summary.today || 0),
        Number(summary.thismonth || 0),
        Number(summary.thisyear || 0),
    ];

    const previousData = [
        Number(summary.yesterday || 0),
        Number(summary.lastmonth || 0),
        Number(summary.lastyear || 0),
    ];

    const ctx = canvas.getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Periode Saat Ini',

                    // 🔥 PRIMARY (highlight)
                    data: currentData,
                    backgroundColor: 'rgba(16, 185, 129, 0.9)', // emerald
                    hoverBackgroundColor: 'rgba(5, 150, 105, 1)',

                    borderRadius: 10,
                    barThickness: 24,
                },
                {
                    label: 'Periode Sebelumnya',

                    // 🔥 SECONDARY (neutral)
                    data: previousData,
                    backgroundColor: '#cbd5f5', // soft gray-blue
                    hoverBackgroundColor: '#94a3b8',

                    borderRadius: 10,
                    barThickness: 24,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: '#475569', // neutral
                        boxWidth: 12,
                        boxHeight: 12,
                        usePointStyle: true,
                        pointStyle: 'rectRounded',
                    },
                },
                tooltip: {
                    backgroundColor: '#111827',
                    titleColor: '#f9fafb',
                    bodyColor: '#e5e7eb',
                },
            },

            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#64748b',
                        font: { size: 11, weight: 500 },
                    },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        color: '#64748b',
                    },
                    grid: {
                        color: '#f1f5f9',
                    },
                },
            },
        },
    });
}

document.addEventListener('DOMContentLoaded', initPatientSummaryChart);