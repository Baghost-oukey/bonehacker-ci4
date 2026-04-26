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

    if ($("#desa_id").hasClass("select2-hidden-accessible")) {
      $("#desa_id").select2("destroy");
    }

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
                data: {
                  desa_nama: item.desNama,
                  kecamatan_id: item.kecamatan?.kecIdKecamatan || "",
                  kecamatan_nama: item.kecamatan?.kecNama || "",
                  kabupaten_id: item.kecamatan?.kabupaten?.kabIdKabupaten || "",
                  kabupaten_nama: item.kecamatan?.kabupaten?.kabNama || "",
                  provinsi_id:
                    item.kecamatan?.kabupaten?.provinsi?.provIdProvinsi || "",
                  provinsi_nama:
                    item.kecamatan?.kabupaten?.provinsi?.provNama || "",
                },
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

    $("#desa_id").on("select2:select", function (e) {
      const data = e.params.data.data;
      if (!data) return;
      $("#desa_nama").val(data.desa_nama || "");
      $("#kecamatan_id").val(data.kecamatan_id || "");
      $("#kecamatan_nama").val(data.kecamatan_nama || "");
      $("#kabupaten_id").val(data.kabupaten_id || "");
      $("#kabupaten_nama").val(data.kabupaten_nama || "");
      $("#provinsi_id").val(data.provinsi_id || "");
      $("#provinsi_nama").val(data.provinsi_nama || "");

      const updates = {
        kecamatan_nama: data.kecamatan_nama,
        kabupaten_nama: data.kabupaten_nama,
        provinsi_nama: data.provinsi_nama,
      };
      Object.entries(updates).forEach(([id, val]) => {
        const el = document.getElementById(id);
        if (el && val) el.value = val;
      });
    });
  },

  initEnterKeyPrevention() {
    if (typeof window.$ === "undefined") return;
    $(document).on("keypress", ".select2-search__field", function (e) {
      if (e.which === 13) e.preventDefault();
    });
  },
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () =>
    PatientBiodataPage.init(),
  );
} else {
  PatientBiodataPage.init();
}
