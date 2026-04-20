/**
 * Transaksi Management Page Script
 * Handles CRUD operations for transaction data with Chart.js visualization
 * Custom pagination implementation
 */

const MODAL_VISIBLE_CLASS = "flex";
const MODAL_HIDDEN_CLASS = "hidden";

const getCsrfPayload = (config) => ({
  [config.csrfName]: config.csrfHash,
});

const debounce = (fn, delay = 400) => {
  let timerId;
  return (...args) => {
    clearTimeout(timerId);
    timerId = setTimeout(() => fn(...args), delay);
  };
};

const openModal = (modal) => {
  if (!modal) return;
  modal.classList.remove(MODAL_HIDDEN_CLASS);
  modal.classList.add(MODAL_VISIBLE_CLASS);
};

const closeModal = (modal) => {
  if (!modal) return;
  modal.classList.remove(MODAL_VISIBLE_CLASS);
  modal.classList.add(MODAL_HIDDEN_CLASS);
};

const formatRupiah = (angka) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(angka);
};

const setupTransaksiPage = () => {
  const config = window.transaksiConfig;
  const page = document.getElementById("transaksiPage");

  if (!config || !page || typeof window.$ === "undefined") return;

  const $ = window.$;
  const swalLib = window.Swal || window.swal;
  const Chart = window.Chart;

  let currentPage = 1;
  let pageLength = 25;
  let totalRecords = 0;
  let filteredRecords = 0;
  let searchValue = "";
  let deleteId = null;
  let chartTransaksi = null;

  const updateCsrf = (newToken) => {
    if (!newToken) return;
    config.csrfHash = newToken;
    $("meta[name='csrf-token']").attr("content", newToken);
    $(`input[name='${config.csrfName}']`).val(newToken);
  };

  const updatePaginationInfo = () => {
    if (filteredRecords <= 0) {
      $("#paginationInfo").text("Menampilkan 0 sampai 0 dari 0 data");
      return;
    }
    const start = (currentPage - 1) * pageLength + 1;
    const end = Math.min(currentPage * pageLength, filteredRecords);
    $("#paginationInfo").text(`Menampilkan ${start} sampai ${end} dari ${filteredRecords} data`);
  };

  const updatePaginationUI = () => {
    const totalPages = Math.max(1, Math.ceil(filteredRecords / pageLength));
    const container = $("#paginationNumbers");
    container.empty();

    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
      container.append(`<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400" data-page="1">1</button>`);
      if (startPage > 2) {
        container.append('<span class="px-1 text-slate-300">...</span>');
      }
    }

    for (let pageNum = startPage; pageNum <= endPage; pageNum += 1) {
      const activeClass = pageNum === currentPage ? "bg-teal-600 border-teal-600 text-white font-semibold shadow-md shadow-teal-600/30" : "border border-slate-300 bg-white text-slate-700 font-semibold transition hover:bg-slate-100 hover:border-slate-400";
      container.append(`<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg ${activeClass} text-xs" data-page="${pageNum}">${pageNum}</button>`);
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        container.append('<span class="px-1 text-slate-300">...</span>');
      }
      container.append(`<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400" data-page="${totalPages}">${totalPages}</button>`);
    }

    $("#paginationPrev").prop("disabled", currentPage <= 1);
    $("#paginationNext").prop("disabled", currentPage >= totalPages);
  };

  const renderEmptyState = (message) => {
    $("#tableTransaksi tbody").html(`<tr class="hover:bg-slate-50 transition"><td colspan="7" class="px-6 py-12 text-center text-slate-400 italic text-sm"><i class="fas fa-inbox mr-2 text-slate-300"></i>${message}</td></tr>`);
  };

  const loadTableData = (pageNumber = 1) => {
    const filterDate = $("#filter_date").val();

    $.ajax({
      url: config.fetchUrl,
      type: "POST",
      dataType: "json",
      data: {
        ...getCsrfPayload(config),
        draw: 1,
        start: (pageNumber - 1) * pageLength,
        length: pageLength,
        search: { value: searchValue },
        date: filterDate,
      },
      success: (response) => {
        updateCsrf(response.new_token);

        currentPage = pageNumber;
        totalRecords = Number(response.recordsTotal || 0);
        filteredRecords = Number(response.recordsFiltered || totalRecords);

        const tbody = $("#tableTransaksi tbody");
        tbody.empty();

        if (!response.data || response.data.length === 0) {
          renderEmptyState("Data transaksi belum tersedia");
          updatePaginationInfo();
          updatePaginationUI();
          return;
        }

        response.data.forEach((row) => {
          const tr = $(`<tr class="hover:bg-slate-50 transition border-b border-slate-100"></tr>`);
          tr.append(`<td class="px-6 py-3.5">${row.no || "-"}</td>`);
          tr.append(`<td class="px-6 py-3.5 text-slate-600">${row.tanggal || "-"}</td>`);
          tr.append(`<td class="px-6 py-3.5 font-medium text-slate-800">${row.region_name || "-"}</td>`);
          tr.append(`<td class="px-6 py-3.5">${row.rentang_usia || "-"}</td>`);
          tr.append(`<td class="px-6 py-3.5">${row.metode_pembayaran || "-"}</td>`);

          const nominalColor = row.type === "expense" ? "text-rose-600" : "text-emerald-600";
          tr.append(`<td class="px-6 py-3.5 font-semibold ${nominalColor}">${row.nominal_format || "-"}</td>`);
          tr.append(`<td class="px-6 py-3.5 text-center">${row.aksi || "-"}</td>`);

          tbody.append(tr);
        });

        updatePaginationInfo();
        updatePaginationUI();
      },
      error: () => {
        renderEmptyState("Gagal memuat data transaksi");
        filteredRecords = 0;
        updatePaginationInfo();
        updatePaginationUI();
      },
    });
  };

  // Initialize Chart
  const initChart = () => {
    if (!Chart) return;

    const ctx = document.getElementById("chartTransaksi")?.getContext("2d");
    if (!ctx) return;

    if (chartTransaksi) {
      chartTransaksi.destroy();
    }

    const filterDate = $("#filter_date").val();

    $.ajax({
      url: config.chartDataUrl,
      type: "GET",
      data: { date: filterDate },
      dataType: "json",
      success: function (response) {
        const labels = response.labels || ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"];
        const incomeData = response.income || [0, 0, 0, 0, 0, 0, 0];
        const expenseData = response.expense || [0, 0, 0, 0, 0, 0, 0];

        chartTransaksi = new Chart(ctx, {
          type: "bar",
          data: {
            labels: labels,
            datasets: [
              {
                label: "Pendapatan",
                data: incomeData,
                backgroundColor: "rgba(16, 185, 129, 0.7)",
                borderColor: "rgb(16, 185, 129)",
                borderWidth: 1,
                borderRadius: 6,
                barPercentage: 0.6,
                categoryPercentage: 0.8,
              },
              {
                label: "Pengeluaran",
                data: expenseData,
                backgroundColor: "rgba(244, 63, 94, 0.7)",
                borderColor: "rgb(244, 63, 94)",
                borderWidth: 1,
                borderRadius: 6,
                barPercentage: 0.6,
                categoryPercentage: 0.8,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: {
                callbacks: {
                  label: function (context) {
                    let label = context.dataset.label || "";
                    if (label) label += ": ";
                    label += formatRupiah(context.raw);
                    return label;
                  },
                },
              },
            },
            scales: {
              y: {
                beginAtZero: true,
                grid: { color: "rgba(0, 0, 0, 0.05)" },
                ticks: {
                  callback: function (value) {
                    if (value >= 1000000) return (value / 1000000).toFixed(1) + "M";
                    if (value >= 1000) return (value / 1000).toFixed(0) + "K";
                    return value;
                  },
                },
              },
              x: { grid: { display: false } },
            },
          },
        });
      },
      error: function () {
        chartTransaksi = new Chart(ctx, {
          type: "bar",
          data: {
            labels: ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"],
            datasets: [
              {
                label: "Pendapatan",
                data: [1200000, 1900000, 1500000, 2100000, 1800000, 2500000, 1700000],
                backgroundColor: "rgba(16, 185, 129, 0.7)",
                borderRadius: 6,
              },
              {
                label: "Pengeluaran",
                data: [500000, 700000, 600000, 800000, 650000, 900000, 550000],
                backgroundColor: "rgba(244, 63, 94, 0.7)",
                borderRadius: 6,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              y: {
                beginAtZero: true,
                grid: { color: "rgba(0, 0, 0, 0.05)" },
                ticks: {
                  callback: function (value) {
                    if (value >= 1000000) return (value / 1000000).toFixed(1) + "M";
                    if (value >= 1000) return (value / 1000).toFixed(0) + "K";
                    return value;
                  },
                },
              },
            },
          },
        });
      },
    });
  };

  const updateTypeToggle = () => {
    const incomeRadio = $('input[name="type"][value="income"]');
    const labelIncome = $("#labelIncome");
    const labelExpense = $("#labelExpense");

    if (incomeRadio.is(":checked")) {
      labelIncome.addClass("bg-emerald-50 border border-emerald-200");
      labelExpense.removeClass("bg-rose-50 border border-rose-200");
    } else {
      labelExpense.addClass("bg-rose-50 border border-rose-200");
      labelIncome.removeClass("bg-emerald-50 border border-emerald-200");
    }
  };

  // Search handler with debounce
  const searchHandler = debounce((value) => {
    searchValue = value;
    currentPage = 1;
    loadTableData(1);
  }, 400);

  // Event Listeners
  $("#searchInput").on("keyup", function () {
    searchHandler($(this).val());
  });

  $("#filter_date").on("change", function () {
    currentPage = 1;
    loadTableData(1);
    initChart();
  });

  $("#paginationLength").on("change", function () {
    pageLength = parseInt($(this).val(), 10);
    currentPage = 1;
    loadTableData(1);
  });

  $(document).on("click", ".pagination-btn", function () {
    const pageNum = parseInt($(this).data("page"), 10);
    if (!Number.isNaN(pageNum)) loadTableData(pageNum);
  });

  $("#paginationPrev").on("click", () => {
    if (currentPage > 1) loadTableData(currentPage - 1);
  });

  $("#paginationNext").on("click", () => {
    const totalPages = Math.max(1, Math.ceil(filteredRecords / pageLength));
    if (currentPage < totalPages) loadTableData(currentPage + 1);
  });

  $('input[name="type"]').on("change", updateTypeToggle);

  // Submit form transaksi
  $("#formTransaksi").on("submit", function (e) {
    e.preventDefault();

    const btn = $("#btnSimpan");
    const form = this;
    const $form = $(form);

    if (!form.checkValidity()) {
      $form.addClass("was-validated");
      return;
    }

    btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');

    const formData = new FormData(form);
    formData.append(config.csrfName, config.csrfHash);

    $.ajax({
      url: config.storeUrl,
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (res) {
        updateCsrf(res.new_token);

        if (res.status === "success") {
          if (swalLib?.fire) {
            swalLib.fire({
              icon: "success",
              title: "Berhasil!",
              text: res.message,
              timer: 1500,
              showConfirmButton: false,
            });
          }

          closeModal(document.getElementById("modalTambah"));
          form.reset();
          $form.removeClass("was-validated");
          updateTypeToggle();

          setTimeout(() => location.reload(), 1500);
        } else {
          if (swalLib?.fire) {
            swalLib.fire("Gagal!", res.message, "error");
          }
          btn.prop("disabled", false).text("Simpan Transaksi");
        }
      },
      error: function (xhr) {
        if (swalLib?.fire) {
          swalLib.fire("Error!", "Terjadi kesalahan sistem atau session habis.", "error");
        }
        btn.prop("disabled", false).text("Simpan Transaksi");
        console.error(xhr.responseText);
      },
    });
  });

  // Rekap modal
  $("#btnRekap").on("click", function () {
    openModal(document.getElementById("modalRekap"));
  });

  $("#btnRekapPdf").on("click", function () {
    const tgl = $("#filter_date").val();
    window.open(`${config.exportPdfUrl}?date=${tgl}`, "_blank");
    closeModal(document.getElementById("modalRekap"));
  });

  $("#btnRekapExcel").on("click", function () {
    const tgl = $("#filter_date").val();
    window.location.href = `${config.exportExcelUrl}?date=${tgl}`;
    closeModal(document.getElementById("modalRekap"));
  });

  // Export buttons
  $("#btnExportPdf").on("click", function () {
    const tgl = $("#filter_date").val();
    window.open(`${config.exportPdfUrl}?date=${tgl}`, "_blank");
  });

  $("#btnExportExcel").on("click", function () {
    const tgl = $("#filter_date").val();
    window.location.href = `${config.exportExcelUrl}?date=${tgl}`;
  });

  // Delete transaction
  $(document).on("click", ".btn-delete", function () {
    deleteId = $(this).data("id");
    openModal(document.getElementById("modalDelete"));
  });

  $("#confirmDelete").on("click", function () {
    if (!deleteId) return;

    const btn = $(this);
    btn.prop("disabled", true).text("Menghapus...");

    $.ajax({
      url: config.deleteUrl,
      type: "POST",
      data: {
        id_transaksi: deleteId,
        [config.csrfName]: config.csrfHash,
      },
      dataType: "json",
      success: function (res) {
        updateCsrf(res.new_token);

        if (res.status === "success") {
          closeModal(document.getElementById("modalDelete"));
          deleteId = null;

          if (swalLib?.fire) {
            swalLib.fire({
              icon: "success",
              title: "Berhasil!",
              text: res.message,
              timer: 1500,
              showConfirmButton: false,
            });
          }

          setTimeout(() => location.reload(), 1500);
        } else {
          if (swalLib?.fire) {
            swalLib.fire("Gagal!", res.message, "error");
          }
          btn.prop("disabled", false).text("Hapus");
        }
      },
      error: function () {
        if (swalLib?.fire) {
          swalLib.fire("Error!", "Terjadi kesalahan sistem.", "error");
        }
        btn.prop("disabled", false).text("Hapus");
      },
    });
  });

  // Modal handlers
  document.addEventListener("click", (event) => {
    const openTrigger = event.target.closest("[data-modal-open]");
    if (openTrigger) {
      const targetId = openTrigger.getAttribute("data-modal-open");
      openModal(document.getElementById(targetId));
      return;
    }

    const closeTrigger = event.target.closest("[data-modal-close]");
    if (closeTrigger) {
      closeModal(closeTrigger.closest(".modal-wrapper"));
      return;
    }

    if (event.target.classList && event.target.classList.contains("modal-wrapper")) {
      closeModal(event.target);
    }
  });

  // Reset form when modal is closed
  $("#modalTambah").on("click", "[data-modal-close]", function () {
    $("#formTransaksi")[0].reset();
    $("#formTransaksi").removeClass("was-validated");
    $('input[name="type"][value="income"]').prop("checked", true);
    updateTypeToggle();
  });

  // Initialize
  loadTableData(1);
  initChart();
  updateTypeToggle();
};

// Initialize page
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", setupTransaksiPage);
} else {
  setupTransaksiPage();
}
