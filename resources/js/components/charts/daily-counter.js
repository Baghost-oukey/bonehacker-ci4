function parseDmyToDate(dmy) {
  const [day, month, year] = String(dmy).split("/").map(Number);
  if (!day || !month || !year) return null;
  return new Date(year, month - 1, day);
}

function toDmy(date) {
  const dd = String(date.getDate()).padStart(2, "0");
  const mm = String(date.getMonth() + 1).padStart(2, "0");
  const yyyy = date.getFullYear();
  return `${dd}/${mm}/${yyyy}`;
}

function getMonday(date) {
  const base = new Date(date.getFullYear(), date.getMonth(), date.getDate());
  const day = base.getDay();
  const diff = day === 0 ? -6 : 1 - day;
  base.setDate(base.getDate() + diff);
  return base;
}

function initDailyCounterChart() {
  const root = document.getElementById("dailyCounterChartRoot");
  const canvas = document.getElementById("weeklyChart");
  if (!root || !canvas || typeof Chart === "undefined") return;

  let weeklyData = [];
  try {
    weeklyData = JSON.parse(root.dataset.dailyCounts || "[]");
  } catch (_error) {
    weeklyData = [];
  }

  const flatDays = weeklyData.flat();
  const countByDate = new Map(
    flatDays.map((item) => [
      String(item.formatted_date),
      Number(item.daily_count || 0),
    ]),
  );

  const dayLabels = ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"];
  const monday = getMonday(new Date());

  const labels = [];
  const data = [];

  for (let i = 0; i < 7; i += 1) {
    const current = new Date(monday);
    current.setDate(monday.getDate() + i);

    const dmy = toDmy(current);
    labels.push(`${dayLabels[i]}\n${dmy}`);
    data.push(countByDate.get(dmy) ?? 0);
  }

  const rangeStart = toDmy(monday);
  const sunday = new Date(monday);
  sunday.setDate(monday.getDate() + 6);
  const rangeEnd = toDmy(sunday);

  const rangeText = document.getElementById("weeklyRangeText");
  if (rangeText) {
    rangeText.textContent = `${rangeStart} - ${rangeEnd}`;
  }

  const ctx = canvas.getContext("2d");

  new Chart(ctx, {
    type: "bar",
    data: {
      labels,
      datasets: [
        {
          label: "Jumlah Kunjungan",
          data,

          // 🔥 BRAND COLOR
          backgroundColor: "#10b981", // emerald-500
          hoverBackgroundColor: "#059669", // emerald-600

          borderRadius: 10,
          barThickness: 24,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: "#111827",
          titleColor: "#f9fafb",
          bodyColor: "#e5e7eb",
        },
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: {
            color: "#64748b",
            font: { size: 11, weight: 500 },
          },
        },
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0,
            color: "#64748b", 
          },
          grid: {
            color: "#e5e7eb",
          },
        },
      },
    },
  });
}

document.addEventListener("DOMContentLoaded", initDailyCounterChart);
