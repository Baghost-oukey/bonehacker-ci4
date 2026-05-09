/**
 * Rekam Medis Management Page Script
 * Custom pagination with empty state fallback
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

// --- INIT || SETUP SCRIPT ---
const setupRekamMedisPage = () => {
  const config = window.rekamMedisConfig;
  const page = document.getElementById("rekamMedisPage");

  if (!config || !page || typeof window.$ === "undefined") return;

  const $ = window.$;
  const swalLib = window.Swal || window.swal;

  let currentPage = 1;
  let pageLength = 25;
  let totalRecords = 0;
  let filteredRecords = 0;
  let searchValue = "";
  let deleteId = null;

  // --- UPDATE CRSF TOKEN ---
  const updateCsrf = (newToken) => {
    if (!newToken) return;
    config.csrfHash = newToken;
    $("meta[name='csrf-token']").attr("content", newToken);
    $(`input[name='${config.csrfName}']`).val(newToken);
  };


  // --- PAGINATION ---
  const updatePaginationInfo = () => {
    if (filteredRecords <= 0) {
      $("#paginationInfo").text("Menampilkan 0 sampai 0 dari 0 data");
      return;
    }
    const start = (currentPage - 1) * pageLength + 1;
    const end = Math.min(currentPage * pageLength, filteredRecords);
    $("#paginationInfo").text(
      `Menampilkan ${start} sampai ${end} dari ${filteredRecords} data`,
    );
  };

  const updatePaginationUI = () => {
    const totalPages = Math.max(1, Math.ceil(filteredRecords / pageLength));
    const container = $("#paginationNumbers");
    container.empty();

    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
      container.append(
        `<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400" data-page="1">1</button>`,
      );
      if (startPage > 2) {
        container.append('<span class="px-1 text-slate-300">...</span>');
      }
    }

    for (let pageNum = startPage; pageNum <= endPage; pageNum += 1) {
      const activeClass =
        pageNum === currentPage
          ? "bg-teal-600 border-teal-600 text-white font-semibold shadow-md shadow-teal-600/30"
          : "border border-slate-300 bg-white text-slate-700 font-semibold transition hover:bg-slate-100 hover:border-slate-400";
      container.append(
        `<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg ${activeClass} text-xs" data-page="${pageNum}">${pageNum}</button>`,
      );
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        container.append('<span class="px-1 text-slate-300">...</span>');
      }
      container.append(
        `<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400" data-page="${totalPages}">${totalPages}</button>`,
      );
    }

    $("#paginationPrev").prop("disabled", currentPage <= 1);
    $("#paginationNext").prop("disabled", currentPage >= totalPages);
  };


  // --- DATA FETCHING & RENDERING ---
  const renderTableState = (message, isLoading = false) => {
    const icon = isLoading
      ? '<i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>'
      : '<i class="fas fa-inbox mr-2 text-slate-300"></i>';
    $("#table-RekamMedis tbody").html(
      `<tr class="hover:bg-slate-50 transition"><td colspan="7" class="px-6 py-12 text-center text-slate-400 italic text-sm">${icon}${message}</td></tr>`,
    );
  };

  const loadTableData = (pageNumber = 1) => {
    const region = $("#region").length ? $("#region").val() : config.filterRegion;
    renderTableState("Memuat data pasien...", true);

    $.ajax({
      url: config.fetchUrl,
      type: "POST",
      dataType: "json",
      data: {
        [config.csrfName]: config.csrfHash,
        draw: 1,
        start: (pageNumber - 1) * pageLength,
        length: pageLength,
        search: { value: searchValue },
        region: region,
      },
      success: (response) => {
        if (response.new_token) updateCsrf(response.new_token);
        currentPage = pageNumber;
        totalRecords = Number(response.recordsTotal || 0);
        filteredRecords = Number(response.recordsFiltered || totalRecords);

        const tbody = $("#table-RekamMedis tbody");
        tbody.empty();

        if (!response.data || response.data.length === 0) {
          renderTableState("Data pasien belum tersedia");
          updatePaginationInfo();
          updatePaginationUI();
          return;
        }

        response.data.forEach((row) => {
          // --- INJECT STYLING UNTUK DELETE ---
          const isDeleted = row.is_delete == 1 || row.is_delete === "1";
          const textStyle = isDeleted
            ? "text-red-500 line-through decoration-red-500"
            : "";
          const nameStyle = isDeleted
            ? "text-red-500 line-through decoration-red-500"
            : "text-slate-800 font-medium";


          // --- BUTTON AKSI ---
          const actionBtns = `
            <div class="flex items-center justify-center gap-2">
              <a href="/patient/show/${row.id}"
                title="Lihat Detail"
                class="group flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm transition-all hover:border-teal-200 hover:bg-teal-50 hover:text-teal-600">
                <i class="fas fa-eye text-xs transition-transform group-hover:scale-110"></i>
              </a>
              ${config.isSuperadmin && !isDeleted
              ? `
              <button type="button"
                data-id="${row.id}"
                title="Hapus Data"
                class="group flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm transition-all hover:border-red-200 hover:bg-red-50 hover:text-red-600 btn-delete">
                <i class="fas fa-trash text-xs transition-transform group-hover:scale-110"></i>
              </button>
              `
              : ""
            }
            </div>
          `;

          const tr = $(
            `<tr class="hover:bg-slate-50 transition border-b border-slate-100 ${isDeleted ? 'bg-red-50/30' : ''}"></tr>`,
          );
          tr.append(
            `<td class="px-6 py-3.5 text-xs ${isDeleted ? 'text-red-500 line-through' : 'text-slate-500'}">${row.id || "-"}</td>`,
          );
          tr.append(
            `<td class="px-6 py-3.5 ${nameStyle}">${row.name || "-"}</td>`,
          );
          tr.append(
            `<td class="px-6 py-3.5 ${isDeleted ? 'text-red-500 line-through' : 'text-slate-700'}">${row.name_region || "-"}</td>`
          );
          tr.append(
            `<td class="px-6 py-3.5 text-xs max-w-xs truncate ${isDeleted ? 'text-red-500 line-through' : 'text-slate-500'}">${row.address || "-"}</td>`,
          );
          tr.append(
            `<td class="px-6 py-3.5 text-center text-xs ${isDeleted ? 'text-red-500 line-through' : ''}">${row.date || "-"}</td>`,
          );
          tr.append(
            `<td class="px-6 py-3.5 text-center ${isDeleted ? 'text-red-500 line-through' : ''}">${row.visit_count || 0}</td>`,
          );
          tr.append(`<td class="px-6 py-3.5">${actionBtns}</td>`);
          tbody.append(tr);
        });

        updatePaginationInfo();
        updatePaginationUI();
      },
      error: () => {
        renderTableState("Gagal memuat data pasien");
        filteredRecords = 0;
        updatePaginationInfo();
        updatePaginationUI();
      },
    });
  };

  const searchHandler = debounce((value) => {
    searchValue = value;
    currentPage = 1;
    loadTableData(1);
  }, 400);

  $("#searchInput").on("keyup", function () {
    searchHandler($(this).val());
  });

  $("#region").on("change", function () {
    currentPage = 1;
    loadTableData(1);
  });

  $("#paginationLength").on("change", function () {
    pageLength = parseInt($(this).val(), 10);
    currentPage = 1;
    loadTableData(1);
  });

  $(document).on("click", ".pagination-btn", function () {
    const p = parseInt($(this).data("page"), 10);
    if (!isNaN(p)) loadTableData(p);
  });

  $("#paginationPrev").on("click", () => {
    if (currentPage > 1) loadTableData(currentPage - 1);
  });
  $("#paginationNext").on("click", () => {
    const tp = Math.max(1, Math.ceil(filteredRecords / pageLength));
    if (currentPage < tp) loadTableData(currentPage + 1);
  });


  // --- KETERANGAN RENTAN --
  $(document).on("change", "#isSuspectiveCheckbox", function () {
    const k = $("#keterangan_rentan");
    $(this).is(":checked")
      ? k.stop().slideDown(300).removeClass("hidden")
      : k.stop().slideUp(300);
  });

  // --- TOMBOL DOMESTIK ---
  $(document).on("change", 'input[name="domestic"]', function () {
    const v = $(this).val();
    if (v === "luar_negeri") {
      $("#desa-group, #region-group").addClass("hidden");
      $("#country-group").removeClass("hidden");
      $("#desa_id").val(null).trigger("change");
    } else {
      $("#desa-group, #region-group").removeClass("hidden");
      $("#country-group").addClass("hidden");
      $("#country_id").val("");
    }
  });


  // --- SIMPAN PASIEN ---
  $("#submitBtn").on("click", function (e) {
    e.preventDefault();
    const form = $("#formTambahPasien");

    if (!form[0].checkValidity()) {
      form.addClass("was-validated");
      form.find(".invalid-feedback").removeClass("hidden");
      return;
    }

    if (swalLib?.fire) {
      swalLib.fire({
        target: document.getElementById("exampleModal"),
        title: "Menyimpan...",
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
      });
    }

    $.ajax({
      url: form.attr("action"),
      type: "POST",
      data: form.serialize(),
      dataType: "json",
      success: (res) => {
        if (swalLib) swalLib.close();
        if (res.success || res.status === "success") {
          closeModal(document.getElementById("exampleModal"));
          form[0].reset();
          form.removeClass("was-validated");
          loadTableData(currentPage);
          if (swalLib?.fire) {
            swalLib.fire({
              icon: "success",
              title: "Berhasil!",
              text: res.message || "Data tersimpan",
              timer: 2000,
              showConfirmButton: false,
            });
          }
        } else {
          if (swalLib?.fire) {
            swalLib.fire({
              target: document.getElementById("exampleModal"),
              title: "Gagal!",
              text: res.message || "Error",
              icon: "error",
            });
          }
        }
      },
      error: () => {
        if (swalLib) swalLib.close();
        if (swalLib?.fire) {
          swalLib.fire({
            target: document.getElementById("exampleModal"),
            title: "Error!",
            text: "Gagal mengirim data",
            icon: "error",
          });
        }
      },
    });
  });

  // --- RANGE DATEPICKER UNTUK EXPORT ---
  $("#periodeSelect").on("change", function () {
    const customDateContainer = $("#customDateRange");
    if ($(this).val() === "custom") {
      customDateContainer.removeClass("hidden").hide().slideDown(200);
    } else {
      customDateContainer.slideUp(200, function () {
        $(this).addClass("hidden");
      });
    }
  });


  // --- DELETE HANDLER ---
  $(document).on("click", ".btn-delete", function () {
    deleteId = $(this).data("id");
    openModal(document.getElementById("modalDelete"));
  });

  $("#confirmDelete").on("click", () => {
    if (!deleteId) return;

    $.ajax({
      url: `${config.destroyBaseUrl}/${deleteId}`,
      type: "POST",
      dataType: "json",
      data: getCsrfPayload(config),
      success: (response) => {
        updateCsrf(response.new_token);
        if (response && response.status) {
          closeModal(document.getElementById("modalDelete"));
          deleteId = null;
          loadTableData(currentPage);
        }
      },
      error: () => {
        if (swalLib?.fire) {
          swalLib.fire("Error!", "Gagal menghapus data.", "error");
        }
      },
    });
  });


  // --- MODAL HANDLERS ---
  document.addEventListener("click", (event) => {
    const openTrigger = event.target.closest("[data-modal-open]");
    if (openTrigger) {
      const targetId = openTrigger.getAttribute("data-modal-open");
      openModal(document.getElementById(targetId));
      if (targetId === "exampleModal") {
        setTimeout(() => {
          if (typeof initSelect2Desa === "function") initSelect2Desa();
        }, 300);
      }
      return;
    }
    const closeTrigger = event.target.closest("[data-modal-close]");
    if (closeTrigger) closeModal(closeTrigger.closest(".modal-wrapper"));
    if (
      event.target.classList &&
      event.target.classList.contains("modal-wrapper")
    ) {
      closeModal(event.target);
    }
  });

  $(document).on("click", ".modal-wrapper", function (e) {
    if (e.target === this) {
      $(this).fadeOut(200, function () {
        $(this).addClass("hidden").removeClass("flex").removeAttr("style");
      });
    }
  });

  $(document).on("click", "[data-modal-close]", function (e) {
    e.preventDefault();
    $(this)
      .closest(".modal-wrapper")
      .fadeOut(200, function () {
        $(this).addClass("hidden").removeClass("flex").removeAttr("style");
      });
  });

  loadTableData(1);
};


// --- SELECT DESA ---
function initSelect2Desa() {
  if (!$("#desa_id").length) return;
  if ($("#desa_id").hasClass("select2-hidden-accessible"))
    $("#desa_id").select2("destroy");

  $("#desa_id").select2({
    placeholder: "Temukan Desa",
    dropdownParent: $("#exampleModal"),
    width: "100%",
    ajax: {
      url: "https://wilayah.smartsociety.id/public/desa",
      dataType: "json",
      delay: 250,
      data: (p) => ({ search: p.term, page: p.page || 1 }),
      processResults: (data) => {
        let opts = [];
        if (data.data?.data) {
          $.each(data.data.data, (i, item) => {
            const kec = item.kecamatan?.kecNama || "";
            const kab = item.kecamatan?.kabupaten?.kabNama || "";
            const sub = kec ? `Kec. ${kec}, ${kab}` : "";
            opts.push({
              id: item.desIdDesa,
              text: `<strong>${item.desNama}</strong><br><small>${sub}</small>`,
              full_data: item,
            });
          });
        }
        return {
          results: opts,
          pagination: { more: !!data.data?.next_page_url },
        };
      },
    },
    minimumInputLength: 1,
    escapeMarkup: (m) => m,
    templateResult: (i) => i.text,
    templateSelection: (i) =>
      i.text
        ? i.text.replace(/<br\s*\/?>/gi, " ").replace(/<\/?[^>]+(>|$)/g, "")
        : i.text,
  });
}

$(document).on("select2:select", "#desa_id", function (e) {
  const d = e.params.data.full_data;
  $("#desa_nama").val(d?.desNama || "");
  $("#kecamatan_id").val(d?.kecamatan?.kecIdKecamatan || "");
  $("#kecamatan_nama").val(d?.kecamatan?.kecNama || "");
  $("#kabupaten_id").val(d?.kecamatan?.kabupaten?.kabIdKabupaten || "");
  $("#kabupaten_nama").val(d?.kecamatan?.kabupaten?.kabNama || "");
  $("#provinsi_id").val(
    d?.kecamatan?.kabupaten?.provinsi?.provIdProvinsi || "",
  );
  $("#provinsi_nama").val(d?.kecamatan?.kabupaten?.provinsi?.provNama || "");
});

$(document).on("select2:open", "#desa_id", () => {
  setTimeout(() => {
    const sf = document.querySelector(".select2-search__field");
    if (sf) sf.focus();
  }, 100);
});

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", setupRekamMedisPage);
} else {
  setupRekamMedisPage();
}
