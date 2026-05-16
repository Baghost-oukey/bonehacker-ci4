/**
 * Patient Biodata Card Script
 * Handles form toggles and Select2 instances
 */

const PatientBiodataPage = {
  init() {
    this.initSuspectiveToggle();
    this.initDomesticToggle();
    this.initSelect2Instances();
    this.initEnterKeyPrevention();
  },

  initSuspectiveToggle() {
    const checkbox = document.getElementById("isSuspectiveCheckbox");
    const info = document.getElementById("keterangan_rentan");
    if (!checkbox || !info) return;

    if (checkbox.checked) info.classList.remove("hidden");

    checkbox.addEventListener("change", function () {
      info.classList.toggle("hidden", !this.checked);
    });
  },

  initDomesticToggle() {
    const radios = document.querySelectorAll('input[name="domestic"]');
    if (!radios.length) return;

    const update = () => {
      const selected = document.querySelector('input[name="domestic"]:checked');
      const isDalam = selected && selected.value === "dalam_negeri";

      const countryGroup = document.getElementById("country-group");
      const desaGroup = document.getElementById("desa-group");
      const regionGroup = document.getElementById("region-group");

      if (countryGroup) countryGroup.classList.toggle("hidden", isDalam);
      if (desaGroup) desaGroup.classList.toggle("hidden", !isDalam);
      if (regionGroup) regionGroup.classList.toggle("hidden", !isDalam);
    };

    radios.forEach((r) => r.addEventListener("change", update));
    update();
  },

  initSelect2Instances() {
    if (typeof window.$ === "undefined") return;

    $("#region_id, #region_history").select2({
      placeholder: "PILIH",
      width: "100%",
    });

    // Destroy existing Select2 instance
    if ($("#desa_id").hasClass("select2-hidden-accessible")) {
      $("#desa_id").select2("destroy");
    }

    // Clear the value to prevent "same value" bug
    $("#desa_id").val(null);

    $("#desa_id").select2({
      placeholder: "Temukan Desa",
      allowClear: true,
      width: "100%",
      ajax: {
        url: "https://wilayah.smartsociety.id/public/desa",
        dataType: "json",
        delay: 250,
        data: (p) => ({ search: p.term, page: p.page || 1 }),
        processResults: (data) => {
          let options = [];
          if (data.data?.data) {
            $.each(data.data.data, (i, item) => {
              const sub = item.kecamatan
                ? `Kec. ${item.kecamatan.kecNama}, ${item.kecamatan.kabupaten?.kabNama || ""}`
                : "";
              options.push({
                id: item.desIdDesa,
                text: `<strong>${item.desNama}</strong><br><small style="color:#64748b">${sub}</small>`,
                full_data: item, // Changed from 'data' to 'full_data'
              });
            });
          }
          return {
            results: options,
            pagination: { more: !!data.data?.next_page_url },
          };
        },
        cache: true,
      },
      minimumInputLength: 1,
      escapeMarkup: (m) => m,
      templateResult: (i) => i.text,
      templateSelection: (i) => (i.text ? i.text.split("<br>")[0] : i.text),
    });
  },

  initEnterKeyPrevention() {
    if (typeof window.$ === "undefined") return;
    $(document).on("keypress", ".select2-search__field", function (e) {
      if (e.which === 13) e.preventDefault();
    });
  },
};

// Global event handler for desa select (survives re-init)
$(document).on("select2:select", "#desa_id", function (e) {
  console.log("Desa selected (global handler):", e.params.data);
  
  const item = e.params.data.full_data;
  if (!item) {
    console.warn("No full_data in select event");
    return;
  }

  console.log("Full data:", item);

  // Fill hidden inputs
  $("#desa_nama").val(item.desNama || "");
  $("#kecamatan_id").val(item.kecamatan?.kecIdKecamatan || "");
  $("#kecamatan_nama").val(item.kecamatan?.kecNama || "");
  $("#kabupaten_id").val(item.kecamatan?.kabupaten?.kabIdKabupaten || "");
  $("#kabupaten_nama").val(item.kecamatan?.kabupaten?.kabNama || "");
  $("#provinsi_id").val(item.kecamatan?.kabupaten?.provinsi?.provIdProvinsi || "");
  $("#provinsi_nama").val(item.kecamatan?.kabupaten?.provinsi?.provNama || "");

  // Fill readonly display fields
  const kecEl = document.getElementById("kecamatan_nama");
  const kabEl = document.getElementById("kabupaten_nama");
  const provEl = document.getElementById("provinsi_nama");

  if (kecEl) kecEl.value = item.kecamatan?.kecNama || "";
  if (kabEl) kabEl.value = item.kecamatan?.kabupaten?.kabNama || "";
  if (provEl) provEl.value = item.kecamatan?.kabupaten?.provinsi?.provNama || "";

  console.log("Fields updated:", {
    kecamatan: item.kecamatan?.kecNama,
    kabupaten: item.kecamatan?.kabupaten?.kabNama,
    provinsi: item.kecamatan?.kabupaten?.provinsi?.provNama,
  });
});

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () =>
    PatientBiodataPage.init(),
  );
} else {
  PatientBiodataPage.init();
}

// Expose to window for external access
window.PatientBiodataPage = PatientBiodataPage;
