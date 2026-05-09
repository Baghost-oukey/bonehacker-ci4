(function () {
  const dayEl = document.getElementById("clock-day");
  const timeEl = document.getElementById("clock-time");

  if (!dayEl || !timeEl) return;

  const hari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];

  const bulan = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember",
  ];

  function updateClock() {
    const now = new Date();

    const h = String(now.getHours()).padStart(2, "0");
    const m = String(now.getMinutes()).padStart(2, "0");
    const s = String(now.getSeconds()).padStart(2, "0");

    const hariStr = hari[now.getDay()];
    const tanggal = now.getDate();
    const bulanStr = bulan[now.getMonth()];
    const tahun = now.getFullYear();

    timeEl.textContent = `${h}:${m}:${s}`;
    dayEl.textContent = `${hariStr}, ${tanggal} ${bulanStr} ${tahun}`;
  }

  // update pertama
  updateClock();

  // update tiap detik
  setInterval(updateClock, 1000);
})();
