/**
 * Region Management Page Script
 * Handles CRUD operations for region/cabang data
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

const setupRegionPage = () => {
  const config = window.regionConfig;
  const page = document.getElementById("regionPage");

  if (!config || !page || typeof window.$ === "undefined") return;

  const $ = window.$;
  const swalLib = window.Swal || window.swal;

  let currentPage = 1;
  let pageLength = 25;
  let totalRecords = 0;
  let filteredRecords = 0;
  let searchValue = "";
  let deleteUrl = null;

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
    const colCount = config.role === 'superadmin' ? 5 : 4;
    $("#table-region tbody").html(`<tr class="hover:bg-slate-50 transition"><td colspan="${colCount}" class="px-6 py-12 text-center text-slate-400 italic text-sm"><i class="fas fa-inbox mr-2 text-slate-300"></i>${message}</td></tr>`);
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
        search: { value: searchValue },
      },
      success: (response) => {
        updateCsrf(response.new_token);
        $('#datatable-loader').addClass('hidden');

        currentPage = pageNumber;
        totalRecords = Number(response.recordsTotal || 0);
        filteredRecords = Number(response.recordsFiltered || totalRecords);

        const tbody = $("#table-region tbody");
        tbody.empty();

        if (!response.data || response.data.length === 0) {
          renderEmptyState("Data cabang belum tersedia");
          updatePaginationInfo();
          updatePaginationUI();
          return;
        }

        response.data.forEach((row) => {
          const tr = $(`<tr class="hover:bg-slate-50 transition border-b border-slate-100"></tr>`);
          tr.append(`<td class="px-6 py-3.5">${row.id || "-"}</td>`);
          tr.append(`<td class="px-6 py-3.5 font-medium text-slate-800">${row.name || "-"}</td>`);
          tr.append(`<td class="px-6 py-3.5 text-slate-500">${row.created_at || "-"}</td>`);
          tr.append(`<td class="px-6 py-3.5 text-slate-500">${row.updated_at || "-"}</td>`);
          
          if (config.role === 'superadmin') {
            tr.append(`<td class="px-6 py-3.5 text-right">${row.action || "-"}</td>`);
          }
          
          tbody.append(tr);
        });

        updatePaginationInfo();
        updatePaginationUI();
      },
      error: () => {
        renderEmptyState("Gagal memuat data cabang");
        $('#datatable-loader').addClass('hidden');

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


  // --- Event Listeners ---
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
    if (!Number.isNaN(pageNum)) loadTableData(pageNum);
  });

  $("#paginationPrev").on("click", () => {
    $('#datatable-loader').removeClass('hidden');
    if (currentPage > 1) loadTableData(currentPage - 1);
  });

  $("#paginationNext").on("click", () => {
    const totalPages = Math.max(1, Math.ceil(filteredRecords / pageLength));
    if (currentPage < totalPages) {
      $('#datatable-loader').removeClass('hidden');
      loadTableData(currentPage + 1);
    }
  });

  // Validasi Text Field Cabang 
  const formRegion = $("#formTambahRegion");
  const regionInput = formRegion.find('input[name="name"]');

  $('[data-modal-target="modalTambahRegion"]').on('click', function () {
    regionInput.val('');
  });

  regionInput.on('input', function () {
    if (!$(this).val()) {
      $(this).val();
    }
  });


  // --- Submit form tambah ---
  $("#formTambahRegion").on("submit", function (e) {
    e.preventDefault();
    const form = this;
    const $form = $(form);
    const btn = $("#btnSimpanRegion");
    const inputValue = regionInput.val().trim();
    if (!form.checkValidity() || inputValue === 'Cabang' || inputValue === '') {
      regionInput.removeClass('border-slate-300 focus:border-teal-500 focus:ring-teal-500')
        .addClass('border-red-500 focus:border-red-500 focus:ring-red-500 text-red-600 bg-red-50');
      $form.find(".invalid-feedback").text("Nama Cabang tidak boleh kosong!").removeClass("hidden");
      return;
    } else {
      regionInput.removeClass('border-red-500 focus:border-red-500 focus:ring-red-500 text-red-600 bg-red-50')
        .addClass('border-slate-300 focus:border-teal-500 focus:ring-teal-500');
      $form.find(".invalid-feedback").addClass("hidden");
    }

    const formData = new FormData(form);
    formData.append(config.csrfName, config.csrfHash);

    $.ajax({
      url: $form.attr("action"),
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      dataType: "json",
      beforeSend: () => {
        btn.prop("disabled", true).text("Menyimpan...");
        if (swalLib?.fire) {
          swalLib.fire({
            target: document.getElementById('modalTambahRegion'),
            title: 'Menyimpan...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
          });
        }
      },
      success: (response) => {
        if (swalLib) swalLib.close();
        if (response.new_token) {
          if (typeof config !== 'undefined') config.csrfHash = response.new_token;

          if (typeof updateCsrf === 'function') updateCsrf(response.new_token);
        }

        if (response.status === "success") {
          setTimeout(() => {
            closeModal(document.getElementById("modalTambahRegion"));
            form.reset();
            $form.removeClass("was-validated");
            $('#modalTambahRegion input[name="name"]').val('Cabang ');
            if (typeof loadTableData === 'function') loadTableData(currentPage);

            if (swalLib?.fire) {
              swalLib.fire({
                title: "Berhasil",
                text: response.message,
                icon: "success",
                timer: 2000,
                showConfirmButton: false
              });
            }
          }, 100);
        } else {
          setTimeout(() => {
            if (swalLib?.fire) {
              swalLib.fire({
                target: document.getElementById('modalTambahRegion'),
                title: "Gagal",
                text: response.message || "Gagal menyimpan data",
                icon: "error",
              });
            }
          }, 100);
        }
      },
      error: () => {
        if (swalLib) swalLib.close();

        let msg = "Terjadi kegagalan sistem";
        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;

        setTimeout(() => {
          if (swalLib?.fire) {
            swalLib.fire({
              target: document.getElementById('modalTambahRegion'),
              title: "Error",
              text: msg,
              icon: "error"
            });
          }
        }, 100);
      },
      complete: () => {
        btn.prop("disabled", false).text("Simpan");
      },
    });
  });


  // --- Edit region ---
  const editRegionInput = $('#editName');
  $(document).on("click", ".btn_edit", function (e) {
    e.preventDefault();
    const href = $(this).data("href");
    const name = $(this).data("name");
    $("#formEditRegion").attr("action", href);
    $("#editName").val(name);
    openModal(document.getElementById("modalEditRegion"));
  });
  // --- Edit region: Submit form edit ---
  $("#formEditRegion").on("submit", function (e) {
    e.preventDefault();
    const form = this;
    const $form = $(form);
    const btn = $("#btnUpdateRegion");
    const inputValue = editRegionInput.val().trim();

    if (!form.checkValidity() || inputValue.toLowerCase() === 'cabang' || inputValue === '') {
      editRegionInput.removeClass('border-slate-300 focus:border-teal-500 focus:ring-teal-500')
        .addClass('border-red-500 focus:border-red-500 focus:ring-red-500 text-red-600 bg-red-50');

      $form.find(".invalid-feedback").text("Nama Cabang tidak boleh kosong!").removeClass("hidden");
      return;
    } else {
      editRegionInput.removeClass('border-red-500 focus:border-red-500 focus:ring-red-500 text-red-600 bg-red-50')
        .addClass('border-slate-300 focus:border-teal-500 focus:ring-teal-500');
      $form.find(".invalid-feedback").addClass("hidden");
    }

    const formData = new FormData(form);
    if (typeof config !== 'undefined' && config.csrfName) {
      formData.append(config.csrfName, config.csrfHash);
    }
    $.ajax({
      url: $form.attr("action"),
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      dataType: "json",
      beforeSend: () => {
        btn.prop("disabled", true).text("Menyimpan...");
      },
      success: (response) => {
        updateCsrf(response.new_token);
        if (swalLib?.fire) {
          swalLib.fire({
            target: document.getElementById('modalEditRegion'),
            title: 'Menyimpan Perubahan...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
          });
        }
      },
      success: (response) => {
        if (swalLib) swalLib.close();
        if (response.new_token) {
          if (typeof config !== 'undefined') config.csrfHash = response.new_token;
          if (typeof updateCsrf === 'function') updateCsrf(response.new_token);
        }

        if (response.status === "success") {
          setTimeout(() => {
            closeModal(document.getElementById("modalEditRegion"));
            form.reset();
            $form.removeClass("was-validated");
            editRegionInput.removeClass('border-red-500').addClass('border-slate-300');

            if (typeof loadTableData === 'function') loadTableData(currentPage);

            if (swalLib?.fire) {
              swalLib.fire({
                title: "Berhasil",
                text: response.message,
                icon: "success",
                timer: 2000,
                showConfirmButton: false
              });
            }
          }, 100);
        } else {
          setTimeout(() => {
            if (swalLib?.fire) {
              swalLib.fire({
                target: document.getElementById('modalEditRegion'),
                title: "Gagal Mengupdate",
                text: response.message || "Gagal mengupdate data",
                icon: "error",
                confirmButtonText: 'Tutup <i class="fas fa-times ml-1"></i>',
                confirmButtonColor: '#ef4444'
              });
            }
          }, 100);
        }
      },
      error: (xhr) => {
        if (swalLib) swalLib.close();
        let msg = "Terjadi kegagalan sistem";
        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;

        setTimeout(() => {
          if (swalLib?.fire) {
            swalLib.fire({
              target: document.getElementById('modalEditRegion'),
              title: "Error Server",
              text: msg,
              icon: "error",
              confirmButtonText: 'Tutup <i class="fas fa-times ml-1"></i>',
              confirmButtonColor: '#ef4444'
            });
          }
        }, 100);
      },
      complete: () => {
        btn.prop("disabled", false).text("Simpan Perubahan");
      },
    });
  });


  // --- Delete region ---
  // let deleteUrl = null;

  $(document).on("click", ".btn_delete", function (e) {
    e.preventDefault();
    deleteUrl = $(this).data("href");
    if (!deleteUrl) {
      console.error("URL tidak ditemukan pada atribut data-href!");
      return;
    }
    openModal(document.getElementById("modalDeleteRegion"));
  });


  // Delete Region : Submit form delete
  $("#formDeleteRegion").on("submit", function (e) {
    e.preventDefault();
    const form = this;
    const $form = $(form);
    const btn = $("#btnConfirmDelete");

    const formData = new FormData(form);

    if (typeof config !== 'undefined' && config.csrfName) {
      formData.append(config.csrfName, config.csrfHash);
    }
    // formData.append(config.csrfName, config.csrfHash);
    // formData.append("_method", "DELETE");

    $.ajax({
      url: deleteUrl,
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      dataType: "json",
      beforeSend: () => {
        btn.prop("disabled", true).text("Menghapus...");
        if (swalLib?.fire) {
          swalLib.fire({
            target: document.getElementById('modalDeleteRegion'),
            title: 'Menghapus...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
          });
        }
      },
      success: (response) => {
        updateCsrf(response.new_token);
        if (swalLib) swalLib.close();
        if (response.new_token) {
          if (typeof config !== 'undefined') config.csrfHash = response.new_token;
          if (typeof updateCsrf === 'function') updateCsrf(response.new_token);
        }

        if (response.status === "success") {
          setTimeout(() => {
            closeModal(document.getElementById("modalDeleteRegion"));
            deleteUrl = null;
            if (typeof loadTableData === 'function') loadTableData(currentPage);

            if (swalLib?.fire) {
              swalLib.fire({
                title: "Berhasil",
                text: response.message,
                icon: "success",
                timer: 2000,
                showConfirmButton: false
              });
            }
          }, 100);
        } else {
          setTimeout(() => {
            if (swalLib?.fire) {
              swalLib.fire({
                target: document.getElementById('modalDeleteRegion'),
                title: "Gagal",
                text: response.message || "Gagal menghapus data",
                icon: "error",
                confirmButtonText: 'Tutup <i class="fas fa-times ml-1"></i>',
                confirmButtonColor: '#ef4444'
              });
            }
          }, 100);
        }
      },
      error: (xhr) => {
        if (swalLib) swalLib.close();
        let msg = "Terjadi kegagalan sistem";
        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;

        setTimeout(() => {
          if (swalLib?.fire) {
            swalLib.fire({
              target: document.getElementById('modalDeleteRegion'),
              title: "Error Server",
              text: msg,
              icon: "error",
              confirmButtonText: 'Tutup <i class="fas fa-times ml-1"></i>',
              confirmButtonColor: '#ef4444'
            });
          }
        }, 100);
      },
      complete: () => {
        btn.prop("disabled", false).text("Ya, Hapus");
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


  // Reset form saat modal tambah ditutup
  $("#modalTambahRegion").on("click", "[data-modal-close]", function () {
    $("#formTambahRegion")[0].reset();
    $("#formTambahRegion").removeClass("was-validated");
    $("#formTambahRegion .invalid-feedback").addClass("hidden");
  });


  // Reset form saat modal edit ditutup
  $("#modalEditRegion").on("click", "[data-modal-close]", function () {
    $("#formEditRegion").removeClass("was-validated");
    $("#formEditRegion .invalid-feedback").addClass("hidden");
  });
  // Initial load
  loadTableData(1);
};


// Initialize page
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", setupRegionPage);
} else {
  setupRegionPage();
}
