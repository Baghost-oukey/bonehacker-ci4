/**
 * Patient History Card Script
 * Handles DataTable, modals, tagify, and CRUD operations
 */

const MODAL_VISIBLE_CLASS = "flex";
const MODAL_HIDDEN_CLASS = "hidden";

const openModal = (modal) => {
  if (!modal) return;
  modal.classList.remove(MODAL_HIDDEN_CLASS);
  modal.classList.add(MODAL_VISIBLE_CLASS);
  document.body.style.overflow = "hidden";
};

const closeModal = (modal) => {
  if (!modal) return;
  modal.classList.remove(MODAL_VISIBLE_CLASS);
  modal.classList.add(MODAL_HIDDEN_CLASS);
  document.body.style.overflow = "";
};

let complaintTagify = null;
let medhisTagify = null;
let resultTagify = null;
let deleteId = null;
let activeTerapis = window.activeTerapis || [];

const PatientHistoryPage = {
  init() {
    this.initDataTable();
    this.initTagify();
    this.initEventListeners();

    window.add = this.add.bind(this);
    window.show = this.show.bind(this);
    window.destroy = this.destroy.bind(this);
    window.toggleTerapiForm = this.toggleTerapiForm;

    this.checkGender();
    this.checkUrlParams();
  },

  formatDateForInput(dateTime) {
    if (!dateTime) return "";
    const d = new Date(dateTime);
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
  },

  checkGender() {
    const gender = document.getElementById("gender")?.value;
    const el = document.getElementById("terapi-kejantanan");
    if (el) el.style.display = gender === "Man" ? "flex" : "none";
  },

  toggleTerapiForm() {
    const cb = document.getElementById("kejantanan");
    ["terapi-form", "pemeriksaan"].forEach((id) => {
      const el = document.getElementById(id);
      if (el) el.style.display = cb?.checked ? "block" : "none";
    });
  },

  // DataTable
  initDataTable() {
    if (typeof window.$ === "undefined") return;

    if ($.fn.DataTable.isDataTable("#table-2")) {
      $("#table-2").DataTable().destroy();
      $("#table-2 tbody").empty();
    }

    $("#table-2").DataTable({
      processing: true,
      serverSide: true,
      destroy: true,
      order: [[3, "desc"]],
      language: {
        processing: '<i class="fas fa-spinner fa-spin text-slate-300"></i>',
        emptyTable:
          '<div class="py-8 text-center"><i class="fas fa-inbox text-3xl text-slate-300 mb-2"></i><p class="text-slate-500 text-sm">Belum ada data riwayat</p></div>',
        info: "Menampilkan _START_ - _END_ dari _TOTAL_",
        infoEmpty: "Menampilkan 0 - 0 dari 0",
        lengthMenu: "Tampilkan _MENU_ per halaman",
        search: "",
        searchPlaceholder: "Cari data riwayat...",
        paginate: {
          previous: '<i class="fas fa-chevron-left text-xs"></i>',
          next: '<i class="fas fa-chevron-right text-xs"></i>',
        },
      },
      columns: [
        { data: "no", width: "5%" },
        { data: "complaint", width: "25%" },
        { data: "medhis", width: "25%" },
        { data: "date", width: "15%" },
        { data: "type", width: "15%" },
        { data: "action", width: "15%", orderable: false },
      ],
      ajax: {
        url: window.historyFetchUrl,
        type: "POST",
        data: function (d) {
          d[window.csrfTokenName] = window.csrfHash;
        },
        dataSrc: function (json) {
          return json.data || [];
        },
        error: function (xhr) {
          console.error("History fetch error:", xhr.status, xhr.responseText);
        },
      },
      rowCallback: function (row, data) {
        $(row).addClass("hover:bg-slate-50 transition");
        if (data.is_delete === "1") {
          $(row).find("td").addClass("text-red-500 line-through opacity-70");
        }
      },
      drawCallback: function () {
        $(".dataTables_length select").addClass(
          "rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15",
        );
        $(".dataTables_filter input").addClass(
          "rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15 w-full sm:w-64",
        );
        $(".paginate_button").addClass(
          "inline-flex h-8 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400 mx-0.5",
        );
        $(".paginate_button.current").addClass(
          "bg-teal-600 border-teal-600 text-white shadow-md shadow-teal-600/30 hover:bg-teal-700",
        );
        $(".paginate_button.disabled").addClass(
          "opacity-50 cursor-not-allowed hover:bg-white hover:border-slate-300",
        );
        $(".dataTables_info").addClass("text-xs font-medium text-slate-600");
      },
    });
  },

  // Tagify
  initTagify() {
    complaintTagify = this.initTagifyWithServer(
      "complaint",
      window.complaintTagsUrl,
    );
    medhisTagify = this.initTagifyWithServer("medhis", window.medisTagsUrl);
    resultTagify = this.initTagifyWithServer("results", window.resultTagsUrl);
  },

  initTagifyWithServer(inputName, url) {
    const textarea = document.querySelector(`textarea[name="${inputName}"]`);
    if (!textarea) return null;
    const tagify = new Tagify(textarea, { whitelist: [] });
    let controller;
    tagify.on("input", (e) => {
      const value = e.detail.value;
      tagify.whitelist = null;
      if (controller) controller.abort();
      controller = new AbortController();
      tagify.loading(true);
      fetch(`${url}?query=${encodeURIComponent(value)}`, {
        signal: controller.signal,
      })
        .then((res) => res.json())
        .then((list) => {
          tagify.whitelist = list;
          tagify.loading(false).dropdown.show(value);
        })
        .catch(() => tagify.loading(false));
    });
    return tagify;
  },

  // CRUD
  add() {
    const modal = document.getElementById("exampleModal");
    const form = document.getElementById("save_data");
    if (!modal || !form) return;

    openModal(modal);
    modal.querySelector(".modal-title").textContent = "Tambah Riwayat Pasien";
    form.setAttribute("action", window.historyStoreUrl);
    form.reset();
    form.querySelector('input[name="patient_id"]').value = window.patientId;
    form.querySelector('input[name="queue_id"]').value = window.queueId;
    $(form).find(":input").prop("readonly", false);
    $(form).find(":checkbox").prop("disabled", false);

    [complaintTagify, medhisTagify, resultTagify].forEach((t) => {
      if (t) {
        t.removeAllTags();
        t.setReadonly(false);
      }
    });

    $("#terapi-kejantanan").show();
    $("#kejantanan").prop("checked", false);
    $("#history-info").hide();
    $("#save-button").show();
    $("#region_history").prop("disabled", false);

    document.getElementById("date").value = new Date()
      .toISOString()
      .split("T")[0];

    $(".terapis").prop("disabled", false).empty();
    if (typeof activeTerapis !== "undefined") {
      activeTerapis.forEach((t) =>
        $(".terapis").append(new Option(t.nama || t.name, t.id)),
      );
    }
    $(".terapis").val([]).trigger("change");
  },

  show(id) {
    const self = this;
    const form = document.getElementById("save_data");
    if (!form) return;
    form.reset();
    $('input[type="checkbox"]').prop("checked", false);
    $('input[type="radio"]').prop("checked", false);

    const showUrl = window.historyFetchUrl
      .replace(/\/\d+$/, "/" + id)
      .replace("fetch", "show");

    $.ajax({
      url: showUrl,
      type: "GET",
      dataType: "json",
      success: function (data) {
        const modal = document.getElementById("exampleModal");
        openModal(modal);
        form.setAttribute(
          "action",
          window.historyStoreUrl.replace("store", "update"),
        );
        modal.querySelector(".modal-title").textContent =
          "Detail Riwayat Pasien";

        $("#notif-wa").hide();
        document.getElementById("terapi-kejantanan").style.display = "flex";
        document.getElementById("kejantanan").checked =
          data.kejantanan === "ya";
        self.toggleTerapiForm();

        form.querySelector('input[name="id"]').value = data.id || "";
        form.querySelector('input[name="patient_id"]').value =
          data.patient_id || "";
        form.querySelector('input[name="date"]').value =
          self.formatDateForInput(data.date);

        if (complaintTagify) {
          complaintTagify.removeAllTags();
          if (data.complaint && data.complaint !== "-")
            complaintTagify.addTags(data.complaint.split(", "));
        }
        if (medhisTagify) {
          medhisTagify.removeAllTags();
          if (data.medhis && data.medhis !== "-")
            medhisTagify.addTags(data.medhis.split(", "));
        }
        if (resultTagify) {
          resultTagify.removeAllTags();
          if (data.results && data.results !== "-")
            resultTagify.addTags(data.results.split(", "));
        }

        $("#history-info").show();
        $("#created_by").text(data.history_created_by || "-");
        if (data.history_updated_by && data.history_updated_by !== "-") {
          $("#updated_by").text(data.history_updated_by);
          $("#updated_info").show();
        } else {
          $("#updated_info").hide();
        }

        self.updateFormStatus(data);
      },
      error: () => alert("Gagal memuat data"),
    });
  },

  updateFormStatus(data) {
    const dayDiff = Math.ceil(
      Math.abs(new Date() - new Date(data.date_modified)) /
        (1000 * 60 * 60 * 24),
    );
    if (dayDiff > 1 && data.type !== "draft") {
      $("#exampleModal form :input").prop("readonly", true);
      $("#exampleModal form :checkbox, #exampleModal form :radio").prop(
        "disabled",
        true,
      );
      [complaintTagify, medhisTagify, resultTagify].forEach((t) => {
        if (t) t.setReadonly(true);
      });
      $(".terapis, #region_history").prop("disabled", true);
      $("#save-button").hide();
    } else {
      $("#save-button").show();
    }
  },

  destroy(id) {
    deleteId = id;
    openModal(document.getElementById("deleteModal"));
  },

  checkUrlParams() {
    const params = new URLSearchParams(window.location.search);
    if (params.get("openModalRiwayat") === "true") {
      const hId = params.get("history_id");
      if (hId && hId !== "undefined" && hId !== "") {
        setTimeout(() => this.show(hId), 500);
      }
    }
  },

  // Event Listeners
  initEventListeners() {
    const self = this;

    // ✅ Button Tambah Riwayat
    const addBtn = document.getElementById("btn-add-history");
    if (addBtn) {
      addBtn.addEventListener("click", () => {
        self.add();
      });
    }

    // Select2 di modal
    $("#region_history, .terapis").select2({
      dropdownParent: $("#exampleModal"),
      width: "100%",
    });

    // Save button
    $(document).on("click", "#save-button", function (e) {
      e.preventDefault();
      const btn = $(this);
      const formData = new FormData();

      $("#exampleModal form")
        .find("input[name], textarea[name], select[name]")
        .each(function () {
          const input = $(this);
          const name = input.attr("name");
          const value = input.val();
          if (["complaint", "medhis", "results"].includes(name)) return;

          if (input.is(":checkbox")) {
            if (input.is(":checked")) formData.append(name, value);
          } else if (input.is(":radio")) {
            if (input.is(":checked")) formData.set(name, value);
          } else {
            formData.set(name, value || "");
          }
        });

      if (complaintTagify)
        formData.set("complaint", JSON.stringify(complaintTagify.value || []));
      if (medhisTagify)
        formData.set("medhis", JSON.stringify(medhisTagify.value || []));
      if (resultTagify)
        formData.set("results", JSON.stringify(resultTagify.value || []));

      btn
        .prop("disabled", true)
        .html('<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...');

      $.ajax({
        url: window.historyStoreUrl,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: (res) => {
          if (res.status) {
            closeModal(document.getElementById("exampleModal"));
            if ($.fn.DataTable.isDataTable("#table-2"))
              $("#table-2").DataTable().ajax.reload(null, false);

            if (window.Swal?.fire) {
              window.Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: res.message,
                timer: 2000,
                showConfirmButton: false,
              });
            }
          } else {
            if (window.Swal?.fire)
              window.Swal.fire({
                icon: "error",
                title: "Gagal!",
                text: res.message,
              });

            btn
              .prop("disabled", false)
              .html('<i class="fas fa-save mr-2"></i> Simpan Data');
          }
        },
        error: () => {
          btn
            .prop("disabled", false)
            .html('<i class="fas fa-save mr-2"></i> Simpan Data');
        },
      });
    });

    // Delete
    $(document).on("click", "#confirmDeleteButton", function () {
      if (!deleteId) return;

      const btn = $(this);
      btn.prop("disabled", true).text("Memproses...");

      $.ajax({
        url: `${window.historyDestroyUrl}/${deleteId}`,
        type: "POST",
        dataType: "json",
        data: { [window.csrfTokenName]: window.csrfHash },
        success: (res) => {
          if (res.status) {
            closeModal(document.getElementById("deleteModal"));
            deleteId = null;

            if ($.fn.DataTable.isDataTable("#table-2"))
              $("#table-2").DataTable().ajax.reload(null, false);
          } else {
            alert("Gagal: " + res.message);
          }
        },
        error: () => alert("Terjadi kesalahan"),
        complete: () => {
          btn.prop("disabled", false).text("Ya, Hapus");
          deleteId = null;
        },
      });
    });

    // Modal close
    document.addEventListener("click", (e) => {
      const closeBtn = e.target.closest("[data-modal-close]");
      if (closeBtn) closeModal(closeBtn.closest(".modal-wrapper"));

      if (e.target.classList.contains("modal-wrapper")) {
        closeModal(e.target);
      }
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        const m = document.querySelector(".modal-wrapper.flex");
        if (m) closeModal(m);
      }
    });
  },
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () =>
    PatientHistoryPage.init(),
  );
} else {
  PatientHistoryPage.init();
}
