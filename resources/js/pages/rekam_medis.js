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
  if (!modal) {
    return;
  }

  modal.classList.remove(MODAL_HIDDEN_CLASS);
  modal.classList.add(MODAL_VISIBLE_CLASS);
};

const closeModal = (modal) => {
  if (!modal) {
    return;
  }

  modal.classList.remove(MODAL_VISIBLE_CLASS);
  modal.classList.add(MODAL_HIDDEN_CLASS);
};

const setupRekamMedisPage = () => {
  const config = window.rekamMedisConfig;
  const page = document.getElementById("rekamMedisPage");

  if (!config || !page || typeof window.$ === "undefined") {
    return;
  }

  const $ = window.$;
  const swalLib = window.Swal || window.swal;

  let currentPage = 1;
  let pageLength = 25;
  let totalRecords = 0;
  let filteredRecords = 0;
  let searchValue = "";
  let deleteId = null;

  const updateCsrf = (newToken) => {
    if (!newToken) {
      return;
    }

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
      container.append('<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400" data-page="1">1</button>');
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
    $("#table-1 tbody").html(`<tr class="hover:bg-slate-50 transition"><td colspan="7" class="px-6 py-12 text-center text-slate-400 italic text-sm"><i class="fas fa-inbox mr-2 text-slate-300"></i>${message}</td></tr>`);
  };

  const loadTableData = (pageNumber = 1) => {
    $.ajax({
      url: config.fetchUrl,
      type: "POST",
      dataType: "json",
      data: {
        ...getCsrfPayload(config),
        draw: 1,
        start: (pageNumber - 1) * pageLength,
        length: pageLength,
        region: $("#region").val() || "",
        search: { value: searchValue },
      },
      success: (response) => {
        updateCsrf(response.new_token);

        currentPage = pageNumber;
        totalRecords = Number(response.recordsTotal || 0);
        filteredRecords = Number(response.recordsFiltered || totalRecords);

        const tbody = $("#table-1 tbody");
        tbody.empty();

        if (!response.data || response.data.length === 0) {
          renderEmptyState("Data pasien belum tersedia");
          updatePaginationInfo();
          updatePaginationUI();
          return;
        }

        response.data.forEach((row) => {
          const tr = $("<tr class='hover:bg-slate-50 transition border-b border-slate-100'></tr>");
          tr.append(`<td class="px-6 py-3.5 text-center">${row.id || "-"}</td>`);
          tr.append(`<td class="px-6 py-3.5">${row.name || "-"}</td>`);
          tr.append(`<td class="px-6 py-3.5">${row.name_region || "-"}</td>`);
          tr.append(`<td class="px-6 py-3.5">${row.address || "-"}</td>`);
          tr.append(`<td class="px-6 py-3.5">${row.date || "-"}</td>`);
          tr.append(`<td class="px-6 py-3.5 text-center">${row.visit_count || 0}</td>`);
          tr.append(`<td class="px-6 py-3.5 text-right">${row.action || "-"}</td>`);

          if (row.is_delete === "1") {
            tr.css({
              color: "red",
              textDecoration: "line-through",
            });
          }

          tbody.append(tr);
        });

        updatePaginationInfo();
        updatePaginationUI();
      },
      error: () => {
        renderEmptyState("Gagal memuat data pasien");
        filteredRecords = 0;
        updatePaginationInfo();
        updatePaginationUI();
      },
    });
  };

  const toggleSuspectiveNote = () => {
    const checkbox = document.getElementById("isSuspectiveCheckbox");
    const note = document.getElementById("keterangan_rentan");

    if (!checkbox || !note) {
      return;
    }

    if (checkbox.checked) {
      note.classList.remove("hidden");
      return;
    }

    note.classList.add("hidden");
  };

  const toggleCountryField = () => {
    const domestic = document.querySelector("input[name='domestic']:checked");
    const countryGroup = document.getElementById("country-group");
    const desaGroup = document.getElementById("desa-group");
    const regionGroup = document.getElementById("region-group");

    if (!domestic || !countryGroup || !desaGroup || !regionGroup) {
      return;
    }

    if (domestic.value === "luar_negeri") {
      countryGroup.classList.remove("hidden");
      desaGroup.classList.add("hidden");
      regionGroup.classList.add("hidden");
      return;
    }

    countryGroup.classList.add("hidden");
    desaGroup.classList.remove("hidden");
    regionGroup.classList.remove("hidden");
  };

  const simpanPasien = (btn, formElement, $form) => {
    const formData = new FormData(formElement);
    const csrfHeader = $("meta[name='csrf-header']").attr("content");
    const csrfHash = $("meta[name='csrf-token']").attr("content");

    if (csrfHeader && csrfHash) {
      formData.append(csrfHeader, csrfHash);
    }

    $.ajax({
      url: $form.attr("action"),
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      dataType: "json",
      headers: csrfHeader && csrfHash ? { [csrfHeader]: csrfHash } : {},
      beforeSend: () => {
        btn.prop("disabled", true).addClass("btn-progress").text("Proses Simpan...");
      },
      success: (response) => {
        updateCsrf(response.new_token);

        if (response.status === "success") {
          closeModal(document.getElementById("exampleModal"));
          formElement.reset();
          $form.removeClass("was-validated");
          toggleSuspectiveNote();
          toggleCountryField();
          loadTableData(currentPage);

          if (swalLib && typeof swalLib.fire === "function") {
            swalLib.fire({
              title: "Berhasil",
              text: response.message,
              icon: "success",
              confirmButtonText: "OK",
            });
          }
          return;
        }

        if (swalLib && typeof swalLib.fire === "function") {
          swalLib.fire({
            title: "Gagal",
            text: response.message || "Gagal menyimpan data",
            icon: "error",
            confirmButtonText: "Oke",
          });
        }

        btn.prop("disabled", false).removeClass("btn-progress").text("Simpan");
      },
      error: () => {
        if (swalLib && typeof swalLib.fire === "function") {
          swalLib.fire({
            title: "Error",
            text: "Terjadi kegagalan sistem",
            icon: "error",
          });
        }

        btn.prop("disabled", false).removeClass("btn-progress").text("Simpan");
      },
      complete: () => {
        btn.prop("disabled", false).removeClass("btn-progress").text("Simpan");
      },
    });
  };

  $("#region_id").select2({
    dropdownParent: $("#exampleModal"),
  });

  $("#desa_id")
    .select2({
      placeholder: "Temukan Desa",
      dropdownParent: $("#exampleModal"),
      ajax: {
        url: "https://wilayah.smartsociety.id/public/desa",
        dataType: "json",
        delay: 250,
        data: (params) => ({
          search: params.term,
          page: params.page || 1,
        }),
        processResults: (data) => {
          const options = [];

          if (data.data && data.data.data) {
            $.each(data.data.data, (_index, item) => {
              let optionText = item.desNama;
              if (item.kecamatan && item.kecamatan.kabupaten) {
                optionText += `<br><small>Kec. ${item.kecamatan.kecNama} - ${item.kecamatan.kabupaten.kabNama}</small>`;
              }

              options.push({
                id: item.desIdDesa,
                text: optionText,
                data: item,
              });
            });
          }

          return {
            results: options,
            pagination: {
              more: Boolean(data.data && data.data.next_page_url),
            },
          };
        },
        cache: true,
      },
      minimumInputLength: 1,
      escapeMarkup: (markup) => markup,
      templateResult: (item) => item.text,
      templateSelection: (item) =>
        item.text
          ? item.text
              .replace(/<br\s*\/?>/gi, " ")
              .replace(/<small>/gi, "")
              .replace(/<\/small>/gi, "")
          : item.text,
    })
    .on("select2:select", (e) => {
      const item = e.params.data.data || {};
      $("#desa_nama").val(item.desNama || "");
      $("#kecamatan_id").val(item.kecIdKecamatan || "");
      $("#kecamatan_nama").val(item.kecamatan ? item.kecamatan.kecNama : "");
      $("#kabupaten_id").val(item.kecamatan ? item.kecamatan.kabIdKabupaten : "");
      $("#kabupaten_nama").val(item.kecamatan && item.kecamatan.kabupaten ? item.kecamatan.kabupaten.kabNama : "");
      $("#provinsi_id").val(item.kecamatan && item.kecamatan.kabupaten ? item.kecamatan.kabupaten.provIdProvinsi : "");
      $("#provinsi_nama").val(item.kecamatan && item.kecamatan.kabupaten && item.kecamatan.kabupaten.provinsi ? item.kecamatan.kabupaten.provinsi.provNama : "");
    });

  if (config.isSuperadmin && typeof window.moment !== "undefined" && $("#export_date").length) {
    $("#export_date").daterangepicker({
      locale: { format: "YYYY-MM-DD" },
      ranges: {
        "Hari Ini": [window.moment(), window.moment()],
        "Bulan Ini": [window.moment().startOf("month"), window.moment().endOf("month")],
        "Tahun Ini": [window.moment().startOf("year"), window.moment().endOf("year")],
      },
    });

    $("#export_date").on("apply.daterangepicker", function (_ev, picker) {
      $(this).val(`${picker.startDate.format("YYYY-MM-DD")} - ${picker.endDate.format("YYYY-MM-DD")}`);
    });
  }

  const searchHandler = debounce((value) => {
    searchValue = value;
    currentPage = 1;
    loadTableData(1);
  }, 400);

  $("#searchInput").on("keyup", function () {
    searchHandler($(this).val());
  });

  $("#paginationLength").on("change", function () {
    pageLength = parseInt($(this).val(), 10);
    currentPage = 1;
    loadTableData(1);
  });

  $(document).on("click", ".pagination-btn", function () {
    const pageNum = parseInt($(this).data("page"), 10);
    if (!Number.isNaN(pageNum)) {
      loadTableData(pageNum);
    }
  });

  $("#paginationPrev").on("click", () => {
    if (currentPage > 1) {
      loadTableData(currentPage - 1);
    }
  });

  $("#paginationNext").on("click", () => {
    const totalPages = Math.max(1, Math.ceil(filteredRecords / pageLength));
    if (currentPage < totalPages) {
      loadTableData(currentPage + 1);
    }
  });

  $("#submitBtn").on("click", function (event) {
    event.preventDefault();
    event.stopPropagation();

    const btn = $(this);
    const $form = btn.closest("form");
    const formElement = $form[0];

    if (!formElement.checkValidity()) {
      $form.addClass("was-validated");
      return;
    }

    const phone = $("#phone").val();
    if (!phone) {
      simpanPasien(btn, formElement, $form);
      return;
    }

    $.ajax({
      url: config.checkPhoneUrl,
      type: "POST",
      dataType: "json",
      data: {
        phone,
        ...getCsrfPayload(config),
      },
      success: (response) => {
        updateCsrf(response.new_token);

        if (!response.exists) {
          simpanPasien(btn, formElement, $form);
          return;
        }

        if (swalLib && typeof swalLib.fire === "function") {
          swalLib
            .fire({
              title: "Nomor Sudah Ada",
              text: "Nomor telepon sudah terdaftar. Tetap simpan data baru?",
              icon: "warning",
              showCancelButton: true,
              confirmButtonText: "Ya, Simpan",
              cancelButtonText: "Batal",
            })
            .then((result) => {
              if (result.isConfirmed) {
                simpanPasien(btn, formElement, $form);
              }
            });
        }
      },
    });
  });

  window.destroy = (id) => {
    deleteId = id;
    openModal(document.getElementById("modalDelete"));
  };

  $("#confirmDelete").on("click", () => {
    if (!deleteId) {
      return;
    }

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
        window.alert("Gagal menghapus data.");
      },
    });
  });

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

  $("#isSuspectiveCheckbox").on("change", toggleSuspectiveNote);
  $("input[name='domestic']").on("change", toggleCountryField);

  toggleSuspectiveNote();
  toggleCountryField();
  loadTableData(1);
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", setupRekamMedisPage);
} else {
  setupRekamMedisPage();
}
