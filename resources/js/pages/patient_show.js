const PatientShowPage = {
  init() {
    this.initTabs();
  },

  // --- INIT TABLE ---
  initTabs() {
    const buttons = document.querySelectorAll(".tab-btn");
    const contents = document.querySelectorAll(".tab-content");
    if (!buttons.length || !contents.length) return;
    buttons.forEach((btn) => {
      btn.addEventListener("click", () => {
        const target = btn.dataset.tab;

        // reset semua tab button
        buttons.forEach((b) => {
          b.classList.remove("border-teal-600", "text-teal-600");
          b.classList.add("border-transparent", "text-slate-500");
        });

        // aktifkan tab sekarang
        btn.classList.remove("border-transparent", "text-slate-500");
        btn.classList.add("border-teal-600", "text-teal-600");

        // hide semua content
        contents.forEach((c) => c.classList.add("hidden"));

        // tampilkan target
        const activeTab = document.getElementById(`tab-${target}`);
        if (activeTab) activeTab.classList.remove("hidden");
        if (target === "riwayat" && window.$) {
          setTimeout(() => {
            if ($.fn.DataTable.isDataTable("#table-2")) {
              $("#table-2").DataTable().columns.adjust().draw();
            }
          }, 200);
        }
      });
    });
  },
};

// document.addEventListener("DOMContentLoaded", () => {
//   PatientShowPage.init();
// });

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => PatientShowPage.init());
} else {
  PatientShowPage.init();
}
