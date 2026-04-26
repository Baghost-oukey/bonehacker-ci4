/**
 * Patient History Card Script
 * Custom pagination, modals, tagify, and CRUD operations
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
  currentPage: 1,
  pageLength: 25,
  totalRecords: 0,
  filteredRecords: 0,
  searchValue: "",

  init() {
    this.initTagify();
    this.initEventListeners();
    this.loadTableData(1);
    this.checkUrlParams();
  },

  formatDateForInput(dateTime) {
    if (!dateTime) return "";
    const d = new Date(dateTime);
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
  },

  // =============================================
  // PAGINATION
  // =============================================
  updatePaginationInfo() {
    if (this.filteredRecords <= 0) {
      $("#paginationInfo").text("Menampilkan 0 sampai 0 dari 0 data");
      return;
    }
    const start = (this.currentPage - 1) * this.pageLength + 1;
    const end = Math.min(
      this.currentPage * this.pageLength,
      this.filteredRecords,
    );
    $("#paginationInfo").text(
      `Menampilkan ${start} sampai ${end} dari ${this.filteredRecords} data`,
    );
  },

  updatePaginationUI() {
    const totalPages = Math.max(
      1,
      Math.ceil(this.filteredRecords / this.pageLength),
    );
    const container = $("#paginationNumbers");
    container.empty();

    const startPage = Math.max(1, this.currentPage - 2);
    const endPage = Math.min(totalPages, this.currentPage + 2);

    if (startPage > 1) {
      container.append(
        `<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400" data-page="1">1</button>`,
      );
      if (startPage > 2)
        container.append('<span class="px-1 text-slate-300">...</span>');
    }

    for (let p = startPage; p <= endPage; p++) {
      const active =
        p === this.currentPage
          ? "bg-teal-600 border-teal-600 text-white font-semibold shadow-md shadow-teal-600/30"
          : "border border-slate-300 bg-white text-slate-700 font-semibold transition hover:bg-slate-100 hover:border-slate-400";
      container.append(
        `<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg ${active} text-xs" data-page="${p}">${p}</button>`,
      );
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1)
        container.append('<span class="px-1 text-slate-300">...</span>');
      container.append(
        `<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400" data-page="${totalPages}">${totalPages}</button>`,
      );
    }

    $("#paginationPrev").prop("disabled", this.currentPage <= 1);
    $("#paginationNext").prop("disabled", this.currentPage >= totalPages);
  },

  renderTableState(message, isLoading = false) {
    const icon = isLoading
      ? '<i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>'
      : '<i class="fas fa-inbox mr-2 text-slate-300"></i>';
    $("#table-2 tbody").html(
      `<tr class="hover:bg-slate-50 transition"><td colspan="6" class="px-6 py-12 text-center text-slate-400 italic text-sm">${icon}${message}</td></tr>`,
    );
  },

  // =============================================
  // LOAD TABLE DATA
  // =============================================
  loadTableData(pageNumber = 1) {
    const self = this;
    this.renderTableState("Memuat data riwayat...", true);

    $.ajax({
      url: window.historyFetchUrl,
      type: "POST",
      dataType: "json",
      data: {
        [window.csrfTokenName]: window.csrfHash,
        draw: 1,
        start: (pageNumber - 1) * self.pageLength,
        length: self.pageLength,
        search: { value: self.searchValue },
      },
      success: function (response) {
        if (response.new_token) {
          window.csrfHash = response.new_token;
          $('input[name="' + window.csrfTokenName + '"]').val(
            response.new_token,
          );
        }

        self.currentPage = pageNumber;
        self.totalRecords = Number(response.recordsTotal || 0);
        self.filteredRecords = Number(
          response.recordsFiltered || self.totalRecords,
        );

        const tbody = $("#table-2 tbody");
        tbody.empty();

        if (!response.data || response.data.length === 0) {
          self.renderTableState("Belum ada data riwayat");
          self.updatePaginationInfo();
          self.updatePaginationUI();
          return;
        }

        response.data.forEach(function (row) {
          const tr = $(
            '<tr class="hover:bg-slate-50 transition border-b border-slate-100"></tr>',
          );
          tr.append(
            `<td class="px-6 py-3.5 text-center text-xs text-slate-500">${row.no || "-"}</td>`,
          );
          tr.append(
            `<td class="px-6 py-3.5 text-xs text-slate-700">${row.complaint || "-"}</td>`,
          );
          tr.append(
            `<td class="px-6 py-3.5 text-xs text-slate-700">${row.medhis || "-"}</td>`,
          );
          tr.append(
            `<td class="px-6 py-3.5 text-xs text-slate-600">${row.date || "-"}</td>`,
          );
          tr.append(`<td class="px-6 py-3.5 text-xs">${row.type || "-"}</td>`);
          tr.append(
            `<td class="px-6 py-3.5 text-center">${row.action || "-"}</td>`,
          );
          tbody.append(tr);
        });

        self.updatePaginationInfo();
        self.updatePaginationUI();
      },
      error: function () {
        self.renderTableState("Gagal memuat data riwayat");
        self.filteredRecords = 0;
        self.updatePaginationInfo();
        self.updatePaginationUI();
      },
    });
  },

  // =============================================
  // TAGIFY
  // =============================================
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

  // =============================================
  // CRUD
  // =============================================
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

    $("#history-info").hide();
    $("#save-button").show();
    document.getElementById("date").value = new Date()
      .toISOString()
      .split("T")[0];
    $(".terapis").prop("disabled", false).val([]).trigger("change");
  },

  show(id) {
    const self = this;
    const form = document.getElementById("save_data");
    if (!form) return;
    form.reset();

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
        modal.querySelector(".modal-title").textContent =
          "Detail Riwayat Pasien";
        form.setAttribute("action", window.historyStoreUrl);

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

        $("#save-button").show();
      },
      error: () => alert("Gagal memuat data"),
    });
  },

  destroy(id) {
    deleteId = id;
    openModal(document.getElementById("deleteModal"));
  },

  checkUrlParams() {
    const params = new URLSearchParams(window.location.search);
    if (params.get("openModalRiwayat") === "true") {
      const hId = params.get("history_id");
      if (hId && hId !== "undefined" && hId !== "")
        setTimeout(() => this.show(hId), 500);
    }
  },

  // =============================================
  // EVENT LISTENERS
  // =============================================
  initEventListeners() {
    const self = this;

    // Tambah Riwayat button
    document
      .getElementById("btn-add-history")
      ?.addEventListener("click", () => self.add());

    // Select2
    $("#region_history, .terapis").select2({
      dropdownParent: $("#exampleModal"),
      width: "100%",
    });

    // Pagination
    $("#paginationLength").on("change", function () {
      self.pageLength = parseInt($(this).val(), 10);
      self.currentPage = 1;
      self.loadTableData(1);
    });
    $(document).on("click", ".pagination-btn", function () {
      const p = parseInt($(this).data("page"), 10);
      if (!isNaN(p)) self.loadTableData(p);
    });
    $("#paginationPrev").on("click", () => {
      if (self.currentPage > 1) self.loadTableData(self.currentPage - 1);
    });
    $("#paginationNext").on("click", () => {
      const tp = Math.max(1, Math.ceil(self.filteredRecords / self.pageLength));
      if (self.currentPage < tp) self.loadTableData(self.currentPage + 1);
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
            self.loadTableData(self.currentPage);
            if (window.Swal?.fire)
              window.Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: res.message,
                timer: 2000,
                showConfirmButton: false,
              });
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

    // Delete button
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
            self.loadTableData(self.currentPage);
          } else alert("Gagal: " + res.message);
        },
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
      if (e.target.classList.contains("modal-wrapper")) closeModal(e.target);
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

window.show = (id) => PatientHistoryPage.show(id);
window.destroy = (id) => PatientHistoryPage.destroy(id);