/**
 * Antrean Management Page Script
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

const setupAntreanPage = () => {
  const config = window.antreanConfig;
  const page = document.getElementById("antreanPage");

  if (!config || !page || typeof window.$ === "undefined") return;

  const $ = window.$;
  const swalLib = window.Swal || window.swal;

  let currentPage = 1;
  let pageLength = 25;
  let totalRecords = 0;
  let filteredRecords = 0;
  let searchValue = "";
  let patientSearchValue = "";
  let addToQueueBaseUrl = config.fetchUrl.replace(
    "fetchDataTable",
    "addToQueue",
  );

  // Update CSRF token
  const updateCsrf = (newToken) => {
    if (!newToken) return;
    config.csrfHash = newToken;
    $("meta[name='csrf-token']").attr("content", newToken);
    $(`input[name='${config.csrfName}']`).val(newToken);
  };

  // Update pagination info text
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

  // Render pagination buttons
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

  // Render empty/loading state for table-1
  const renderTable1State = (message, isLoading = false) => {
    const icon = isLoading
      ? '<i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>'
      : '<i class="fas fa-inbox mr-2 text-slate-300"></i>';
    $("#table-1 tbody").html(
      `<tr class="hover:bg-slate-50 transition"><td colspan="8" class="px-6 py-12 text-center text-slate-400 italic text-sm">${icon}${message}</td></tr>`,
    );
  };

  // Render empty/loading state for table-2
  const renderTable2State = (message, isLoading = false) => {
    const icon = isLoading
      ? '<i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>'
      : '<i class="fas fa-inbox mr-2 text-slate-300"></i>';
    $("#patientListBody").html(
      `<tr><td colspan="5" class="px-6 py-12 text-center text-slate-400 italic text-sm">${icon}${message}</td></tr>`,
    );
  };

  // Load table-1 (antrean)
  const loadTableData = (pageNumber = 1) => {
    const startDate = $("#startDate").val();
    const endDate = $("#endDate").val();

    renderTable1State("Memuat data antrean...", true);

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
        start_date: startDate,
        end_date: endDate,
      },
      success: (response) => {
        if (response.new_token) updateCsrf(response.new_token);

        currentPage = pageNumber;
        totalRecords = Number(response.recordsTotal || 0);
        filteredRecords = Number(response.recordsFiltered || totalRecords);

        const tbody = $("#table-1 tbody");
        tbody.empty();

        if (!response.data || response.data.length === 0) {
          renderTable1State("Data antrean belum tersedia");
          updatePaginationInfo();
          updatePaginationUI();
          return;
        }

        response.data.forEach((row) => {
          const tr = $(
            `<tr class="hover:bg-slate-50 transition border-b border-slate-100"></tr>`,
          );
          tr.append(
            `<td class="px-6 py-3.5 text-center font-mono text-xs text-slate-500">${row.queue_id || row.no || "-"}</td>`,
          );
          tr.append(
            `<td class="px-6 py-3.5 text-xs text-slate-600">${row.date || "-"}</td>`,
          );
          tr.append(
            `<td class="px-6 py-3.5 font-bold text-slate-800">${row.name || "-"}</td>`,
          );
          tr.append(
            `<td class="px-6 py-3.5 text-slate-600">${row.age || "-"}</td>`,
          );
          tr.append(
            `<td class="px-6 py-3.5 text-xs text-slate-500">${row.address || "-"}</td>`,
          );
          tr.append(
            `<td class="px-6 py-3.5 text-slate-600">${row.phone || "-"}</td>`,
          );
          tr.append(
            `<td class="px-6 py-3.5 text-center">${row.description || "-"}</td>`,
          );
          tr.append(
            `<td class="px-6 py-3.5 text-center">${row.action || "-"}</td>`,
          );
          tbody.append(tr);
        });

        updatePaginationInfo();
        updatePaginationUI();
      },
      error: () => {
        renderTable1State("Gagal memuat data antrean");
        filteredRecords = 0;
        updatePaginationInfo();
        updatePaginationUI();
      },
    });
  };

  // Load table-2 (pasien di modal)
  const loadPatientData = () => {
    const tableEl = document.getElementById("table-2");
    if (!tableEl) return;

    renderTable2State("Memuat data pasien...", true);

    $.ajax({
      url: config.fetchPatientUrl,
      type: "POST",
      dataType: "json",
      data: {
        [config.csrfName]: config.csrfHash,
        draw: 1,
        start: 0,
        length: 100,
        search: { value: patientSearchValue },
        region: $("#region_id_new").val() || "",
      },
      success: (response) => {
        if (response && response.new_token) {
          updateCsrf(response.new_token);
        }

        const data = response && response.data ? response.data : [];
        let html = "";

        if (data.length > 0) {
          data.forEach((row, index) => {
            html += `
              <tr class="hover:bg-slate-50 transition border-b border-slate-100">
                <td class="px-4 py-3 text-left">
                  <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">
                    ${String(row.patient_id || index + 1).padStart(2, "0")}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <div class="text-sm font-semibold text-slate-800">${row.name || "-"}</div>
                  ${row.phone ? `<div class="text-xs text-slate-400 mt-0.5"><i class="fab fa-whatsapp mr-1"></i>${row.phone}</div>` : ""}
                </td>
                <td class="px-4 py-3">
                  <div class="text-xs text-slate-600 max-w-xs truncate">${row.address || row.desa_nama || "-"}</div>
                </td>
                <td class="px-4 py-3 text-center">
                  ${row.description || "-"}
                </td>
                <td class="px-4 py-3 text-center">
                  <button type="button" onclick="tambahKeAntrean(${row.patient_id})" 
                    class="inline-flex items-center gap-1 rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-teal-700 transition">
                    Pilih <i class="fas fa-arrow-right text-[10px]"></i>
                  </button>
                </td>
              </tr>
            `;
          });
        } else {
          renderTable2State("Tidak ada data pasien");
          return;
        }

        $("#patientListBody").html(html);
      },
      error: () => {
        renderTable2State("Gagal memuat data pasien");
      },
    });
  };

  // Search handler untuk table-2
  const patientSearchHandler = debounce((value) => {
    patientSearchValue = value;
    loadPatientData();
  }, 500);

  $("#searchPatientList")
    .off("keyup")
    .on("keyup", function () {
      patientSearchHandler($(this).val());
    });

  // Tambah ke antrean
  window.tambahKeAntrean = (patientId) => {
    Swal.fire({
      target: document.getElementById("exampleModal"),
      title: "Konfirmasi",
      text: "Tambahkan pasien ini ke antrean?",
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#0d9488",
      cancelButtonColor: "#64748b",
      confirmButtonText: "Ya, Tambahkan",
      cancelButtonText: "Batal",
      reverseButtons: true,
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: "Memproses...",
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading(),
        });

        $.ajax({
          url: addToQueueBaseUrl + "/" + patientId,
          type: "GET",
          dataType: "json",
          success: (res) => {
            Swal.fire({
              icon: "success",
              title: "Berhasil!",
              text: res.message,
              timer: 1500,
              showConfirmButton: false,
            });
            closeModal(document.getElementById("exampleModal"));
            loadTableData(currentPage);
          },
          error: (xhr) => {
            const err = xhr.responseJSON;
            Swal.fire(
              "Gagal!",
              err ? err.message : "Terjadi kesalahan server.",
              "error",
            );
          },
        });
      }
    });
  };

  // Tambah pasien baru
  $("#submitBtnNew").on("click", function (e) {
    e.preventDefault();
    const form = $("#formTambahPasien");

    if (!form[0].checkValidity()) {
      form.addClass("was-validated");
      form.find(".invalid-feedback").removeClass("hidden");
      return;
    }

    Swal.fire({
      target: document.getElementById("modalnewpatient"),
      title: "Konfirmasi Data",
      text: "Apakah Anda yakin ingin menyimpan data pasien ini?",
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: "#0d9488",
      cancelButtonColor: "#64748b",
      confirmButtonText: "Ya, Simpan",
      cancelButtonText: "Batal",
      reverseButtons: true,
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          target: document.getElementById("modalnewpatient"),
          title: "Menyimpan...",
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading(),
        });

        $.ajax({
          url: form.attr("action"),
          type: "POST",
          data: form.serialize(),
          dataType: "json",
          success: (res) => {
            if (res.success || res.status === "success") {
              Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "Berhasil Disimpan",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                target: document.getElementById("modalnewpatient"),
              });

              if ($("#exampleModal").hasClass("flex")) {
                loadPatientData();
              }

              setTimeout(() => {
                closeModal(document.getElementById("modalnewpatient"));
                form[0].reset();
                form.removeClass("was-validated");
              }, 500);
            } else {
              Swal.fire({
                target: document.getElementById("modalnewpatient"),
                title: "Gagal!",
                text: res.message || "Terjadi kesalahan.",
                icon: "warning",
              });
            }
          },
          error: () => {
            Swal.fire({
              target: document.getElementById("modalnewpatient"),
              title: "Error!",
              text: "Gagal mengirim data ke server.",
              icon: "error",
            });
          },
        });
      }
    });
  });

  // Search handler for table-1
  const searchHandler = debounce((value) => {
    searchValue = value;
    currentPage = 1;
    loadTableData(1);
  }, 400);

  // Event listeners
  $("#searchInput").on("keyup", function () {
    searchHandler($(this).val());
  });

  $("#startDate, #endDate").on("change", () => {
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

  // Modal pilih pasien
  $(document)
    .off("click", '[data-modal-open="exampleModal"]')
    .on("click", '[data-modal-open="exampleModal"]', function (e) {
      if (!$(this).closest(".modal-wrapper").length) {
        setTimeout(() => loadPatientData(), 200);
      }
    });

  // Modal open/close handlers
  document.addEventListener("click", (event) => {
    const openTrigger = event.target.closest("[data-modal-open]");
    if (openTrigger) {
      const targetId = openTrigger.getAttribute("data-modal-open");
      const targetModal = document.getElementById(targetId);
      if (targetId === "modalnewpatient") {
        const old = document.getElementById("exampleModal");
        if (old) closeModal(old);
      }
      openModal(targetModal);
      if (targetId === "modalnewpatient") {
        setTimeout(() => {
          if (typeof initSelect2Desa === "function") initSelect2Desa();
          const ni = targetModal.querySelector('input[name="name"]');
          if (ni) ni.focus();
        }, 300);
      }
      return;
    }
    const closeTrigger = event.target.closest("[data-modal-close]");
    if (closeTrigger) closeModal(closeTrigger.closest(".modal-wrapper"));
  });

  // Click outside modal
  $(document).on("click", ".modal-wrapper", function (e) {
    if (e.target === this) {
      $(this).fadeOut(200, function () {
        $(this).addClass("hidden").removeClass("flex").removeAttr("style");
      });
    }
  });

  // Close button
  $(document).on("click", "[data-modal-close]", function (e) {
    e.preventDefault();
    $(this)
      .closest(".modal-wrapper")
      .fadeOut(200, function () {
        $(this).addClass("hidden").removeClass("flex").removeAttr("style");
      });
  });

  // Toggle pasien rentan
  $(document).on("change", "#isSuspectiveCheckbox", function () {
    const k = $("#keterangan_rentan");
    $(this).is(":checked")
      ? k.stop().slideDown(300).removeClass("hidden")
      : k.stop().slideUp(300);
  });

  $(document).on("click", ".modal-wrapper > div", (e) => e.stopPropagation());

  // Export buttons
  $("#btnPdf").on("click", () => {
    const sd = $("#startDate").val();
    const ed = $("#endDate").val();
    window.open(`${config.pdfUrl}?start_date=${sd}&end_date=${ed}`, "_blank");
  });

  $("#btnExcel").on("click", () => {
    const sd = $("#startDate").val();
    const ed = $("#endDate").val();
    window.location.href = `${config.excelUrl}?start_date=${sd}&end_date=${ed}`;
  });

  // Init
  loadTableData(1);
};

// Select2 Desa
function initSelect2Desa() {
  if (!$("#desa_id").length) return;

  if ($("#desa_id").hasClass("select2-hidden-accessible")) {
    $("#desa_id").select2("destroy");
  }

  $("#desa_id").select2({
    placeholder: "Temukan Desa",
    dropdownParent: $("#modalnewpatient"),
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

// Toggle domestik
$(document).on("change", 'input[name="domestic"]', function () {
  const v = $(this).val();
  if (v === "luar_negeri") {
    $("#local-fields").addClass("hidden");
    $("#country-fields").removeClass("hidden");
    $("#desa_id").val(null).trigger("change");
  } else {
    $("#local-fields").removeClass("hidden");
    $("#country-fields").addClass("hidden");
    $("#country_id").val("");
  }
});

// Init page
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", setupAntreanPage);
} else {
  setupAntreanPage();
}
