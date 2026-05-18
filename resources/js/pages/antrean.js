/**
 * Antrean Management Page Script
 * Custom pagination with empty state fallback (SYNCED WITH HERMAWAN DATATABLES)
 */

const MODAL_VISIBLE_CLASS = "flex";
const MODAL_HIDDEN_CLASS = "hidden";

const getCsrfPayload = (config) => ({
  [config.csrfName]: config.csrfHash,
});

const openModal = (modal) => {
  if (!modal) return;
  modal.classList.remove(MODAL_HIDDEN_CLASS);
  modal.classList.add(MODAL_VISIBLE_CLASS);
  modal.style.display = 'flex'; // Ensure it overrides any fadeOut inline style
  
  // Robust opacity & scale reset to support cross-module styles
  modal.classList.remove("opacity-0");
  modal.classList.add("opacity-100");
  const content = modal.querySelector('.transform');
  if (content) {
    content.classList.remove("scale-95");
    content.classList.add("scale-100");
  }
};
window.openModal = openModal;

const closeModal = (modal) => {
  if (!modal) return;
  modal.classList.remove(MODAL_VISIBLE_CLASS);
  modal.classList.add(MODAL_HIDDEN_CLASS);
  modal.style.display = 'none'; // Sync with hidden class
};
window.closeModal = closeModal;

const setupAntreanPage = () => {
  const config = window.antreanConfig;
  const page = document.getElementById("antreanPage");
  if (!config || !page || typeof window.$ === "undefined") return;
  const $ = window.$;
  const swalLib = window.Swal || window.swal;

  const debounce = (fn, delay = 400) => {
    let timerId;
    return (...args) => {
      clearTimeout(timerId);
      timerId = setTimeout(() => fn(...args), delay);
    };
  };
  const injectStyle = () => {
    const style = document.createElement('style');
    style.innerHTML = `
            .dataTables_filter input { border: 1px solid #e2e8f0; border-radius: 0.375rem; padding: 0.25rem 0.75rem; height: 2.25rem; font-size: 0.875rem; outline: none; }
            .dataTables_filter input:focus { border-color: #0d9488; box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #0d9488; }
            .dataTables_length select { border: 1px solid #e2e8f0; border-radius: 0.375rem; padding: 0.25rem 0.5rem; height: 2.25rem; font-size: 0.875rem; outline: none; }
            .dataTables_length select:focus { border-color: #0d9488; }
        `;
    document.head.appendChild(style);
  }
  injectStyle();

  let addToQueueBaseUrl = config.fetchUrl.replace(
    "fetchDataTable",
    "addToQueue",
  );

  $.ajaxSetup({
    data: getCsrfPayload(config)
  });

  // --- INIT TABLE 1 (TABLE ANTREAN) ---
  const table1 = $('#table-1').DataTable({
    processing: false,
    serverSide: true,
    order: [],
    dom: '<"flex flex-col sm:flex-row items-center justify-between px-6 py-4 gap-4 border-b border-slate-100"<"flex items-center gap-4"l>>t<"flex flex-col md:flex-row items-center justify-between p-5 bg-slate-50/50 border-t border-slate-200 gap-4"<"text-xs font-medium text-slate-500"i><"flex items-center justify-end"p>>',
    language: {
      search: "",
      searchPlaceholder: "Cari pasien...",
      lengthMenu: "Tampilkan _MENU_",
      paginate: {
        previous: '<i class="fas fa-arrow-left"></i>',
        next: '<i class="fas fa-arrow-right"></i>'
      }
    },
    ajax: {
      url: config.fetchUrl,
      type: "POST",
      data: function (d) {
        d[config.csrfName] = config.csrfHash;
        d.start_date = $('#startDate').val();
        d.end_date = $('#endDate').val();
        d.region = '';
      },
      dataSrc: function (json) {
        if (json.new_token || json.csrf_hash) {
          config.csrfHash = json.new_token || json.csrf_hash;
          $(`input[name="${config.csrfName}"]`).val(config.csrfHash);
        }
        return json.data;
      }
    },
    columns: [
      { data: 'queue_number', class: 'px-6 py-3.5 text-center font-bold text-lg text-slate-800', sortable: true, searchable: true },
      { data: 'date', class: 'px-6 py-3.5 text-left text-xs text-slate-600', sortable: false, searchable: false },
      { data: 'name', class: 'px-6 py-3.5 text-left font-bold text-slate-800', sortable: true, searchable: true },
      { data: 'age', class: 'px-6 py-3.5 text-left text-slate-600', sortable: true, searchable: false },
      { data: 'address', class: 'px-6 py-3.5 text-left text-xs text-slate-500 max-w-[200px] truncate', sortable: false, searchable: false },
      { data: 'description', class: 'px-6 py-3.5 text-center', sortable: false, searchable: false },
      { data: 'action', class: 'px-6 py-3.5 text-center', sortable: false, searchable: false }
    ],
    rowCallback: function (row) { $(row).addClass('hover:bg-slate-50 transition border-b border-slate-100'); },
    initComplete: function () {
      $('.dataTables_filter label').contents().filter(function () { return this.nodeType === 3; }).remove();
      $('.dataTables_filter input').addClass('w-full');
    },
    drawCallback: function (settings) {
      if (window.innerWidth < 768) {
        // --- LOGIKA MOBILE KITA ---
        $("#iconSearch1").show();
        $("#iconSpinner1").hide();

        const api = this.api();
        const data = api.rows({ page: 'current' }).data();
        const $cardContainer = $('#mobile-card-container');
        $cardContainer.empty();

        if (data.length === 0) {
          $cardContainer.append('<div class="p-12 text-center text-slate-400 italic text-sm">Tidak ada data antrean.</div>');
        } else {
          data.each(function (row) {
            const statusClass = row.description.includes('Menunggu') ? 'bg-amber-50 text-amber-600 border-amber-100' :
              row.description.includes('Proses') ? 'bg-teal-50 text-teal-600 border-teal-100' :
                'bg-slate-50 text-slate-600 border-slate-100';

            const cardHtml = `
              <div class="p-4 space-y-4 bg-white">
                  <div class="flex items-center justify-between">
                      <div class="flex items-center gap-3 min-w-0 flex-1">
                          <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-100 text-slate-800 font-black text-xl border border-slate-200 shadow-sm">
                               ${row.queue_number}
                          </div>
                          <div class="flex flex-col min-w-0">
                              <span class="text-base font-black text-slate-900 truncate uppercase tracking-tight">${row.name}</span>
                              <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">${row.date}</span>
                          </div>
                      </div>
                      <div class="shrink-0 px-3 py-1 rounded-full border text-[9px] font-black uppercase tracking-tighter ${statusClass}">
                          ${row.description}
                      </div>
                  </div>
                  
                  <div class="grid grid-cols-2 gap-4 bg-slate-50/50 rounded-xl p-3 border border-slate-100">
                      <div class="flex flex-col gap-1">
                          <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest">Usia</span>
                          <span class="text-xs text-slate-700 font-black">${row.age || '-'}</span>
                      </div>
                      <div class="flex flex-col gap-1 border-l border-slate-200 pl-4">
                          <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest">Alamat</span>
                          <span class="text-xs text-slate-700 font-black truncate">${row.address || '-'}</span>
                      </div>
                  </div>

                  <div class="pt-2">
                      <div class="w-full flex items-center gap-2 [&>a]:flex-1 [&>button]:flex-1 [&>a]:flex [&>a]:items-center [&>a]:justify-center [&>a]:h-10 [&>a]:rounded-xl [&>a]:text-xs [&>a]:font-bold [&>button]:h-10 [&>button]:rounded-xl [&>button]:text-xs [&>button]:font-bold">
                          ${row.action}
                      </div>
                  </div>
              </div>
            `;
            $cardContainer.append(cardHtml);
          });
        }
        $('.dataTables_paginate').addClass('!flex !flex-row !items-center !justify-center gap-1 w-full mt-4');
      } else {
        // --- LOGIKA DESKTOP REMOTE ---
        $('.dataTables_paginate').addClass('!flex !flex-row !items-center !justify-end gap-1');
      }

      // --- STYLING PAGINATION HASIL PULL (TABLE 1) ---
      $('.dataTables_paginate > span').addClass('!flex !flex-row !items-center gap-1');
      $('.paginate_button').addClass('!inline-flex items-center justify-center min-w-[32px] h-8 rounded-lg border border-slate-300 text-xs font-semibold text-slate-700 cursor-pointer hover:bg-slate-100 transition-colors !m-0 !p-0');
      $('.paginate_button.current').addClass('!bg-teal-600 !text-white !border-teal-600 hover:!bg-teal-700').removeClass('bg-white text-slate-700');
      $('.paginate_button.disabled').addClass('!opacity-50 cursor-not-allowed shadow-none hover:bg-white hover:text-slate-700');
    }
  });

  $("#startDate, #endDate").on("change", () => {
    table1.ajax.reload();
  });


  // --- INIT  TABLE 2 (MODAL PASIEN) ---
  let table2Init = false;
  let isInfiniteScrollLoading = false;
  let mobileCurrentPage = 0;
  let totalPages = 1;

  // Deteksi scroll untuk mobile infinite scroll
  $('#mobile-patient-list').on('scroll', function() {
    if (window.innerWidth >= 768) return;
    if (!table2Init || !$.fn.DataTable.isDataTable('#table-2')) return;
    
    const container = this;
    // Jika mendekati bagian paling bawah (selisih 30px)
    if (container.scrollTop + container.clientHeight >= container.scrollHeight - 30) {
      if (!isInfiniteScrollLoading && mobileCurrentPage < totalPages - 1) {
        isInfiniteScrollLoading = true;
        const table2 = $('#table-2').DataTable();
        table2.page(mobileCurrentPage + 1).draw('page');
      }
    }
  });

  const initTable2 = () => {
    if (!table2Init) {
      table2Init = true;
      $('#table-2').DataTable({
        processing: false,
        serverSide: true,
        // Sembunyikan pagination/footer datatables di mobile memakai hidden md:flex
        dom: 't<"hidden md:flex items-center justify-between p-4 bg-slate-50/50 border-t border-slate-200"<"text-xs text-slate-500"i><"flex items-end"p>>',
        language: { search: "", searchPlaceholder: "Ketik Nama atau Nomor WhatsApp...", paginate: { previous: '<i class="fas fa-chevron-left text-[10px]"></i>', next: '<i class="fas fa-chevron-right text-[10px]"></i>' } },
        ajax: {
          url: config.fetchPatientUrl,
          type: "POST",
          data: function (d) {
            d[config.csrfName] = config.csrfHash;
            d.region = $("#region_id_new").val() || "";
          },
          dataSrc: function (json) {
            if (json.new_token || json.csrf_hash) {
              config.csrfHash = json.new_token || json.csrf_hash;
              $(`input[name="${config.csrfName}"]`).val(config.csrfHash);
            }
            return json.data;
          }
        },
        columns: [
          { data: 'patient_id', class: 'px-4 py-3 text-left font-mono text-xs font-semibold text-slate-500', sortable: true },
          { data: 'name', class: 'px-4 py-3 text-left text-sm', sortable: true },
          { data: 'address', class: 'px-4 py-3 text-left text-xs', sortable: false },
          { data: 'description', class: 'px-4 py-3 text-center', sortable: false },
          { data: 'action', class: 'px-4 py-3 text-center', sortable: false }
        ],
        rowCallback: function (row) { $(row).addClass('hover:bg-slate-50 transition border-b border-slate-100'); },
        initComplete: function () {
          $('.dataTables_filter label').contents().filter(function () { return this.nodeType === 3; }).remove();
          $('.dataTables_filter input').addClass('w-full min-w-[300px]');
        },
        drawCallback: function (settings) {
          if (window.innerWidth < 768) {
            // --- LOGIKA MOBILE DENGAN INFINITE SCROLL ---
            $("#iconSearch2").removeClass("hidden");
            $("#iconSpinner2").addClass("hidden");

            const api = this.api();
            const data = api.rows({ page: 'current' }).data();
            const info = api.page.info();
            
            totalPages = info.pages;
            mobileCurrentPage = info.page;

            const $cardContainer = $('#mobile-patient-list');
            
            // Jika halaman pertama (halaman 0), bersihkan container untuk pencarian/filter baru
            if (mobileCurrentPage === 0) {
              $cardContainer.empty();
            }

            // Hapus spinner/loader scroll yang ada sebelumnya agar diposisikan di paling bawah
            $('#mobile-scroll-loader').remove();

            if (data.length === 0 && mobileCurrentPage === 0) {
              $cardContainer.append('<div class="p-8 text-center text-slate-400 italic text-sm"><i class="fas fa-search mr-2"></i> Tidak ada data pasien.</div>');
            } else {
              data.each(function (row) {
                const statusClass = row.description.includes('Lama') ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-teal-50 text-teal-600 border-teal-100';
                const card = $(`
                  <div class="p-4 space-y-3 bg-white">
                    <div class="flex items-center justify-between">
                      <span class="text-[10px] font-mono font-bold text-slate-400">#${row.patient_id}</span>
                      <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider ${statusClass}">
                        ${row.description}
                      </span>
                    </div>
                    
                    <div class="space-y-1">
                      <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight">${row.name}</h4>
                      <p class="text-[11px] text-slate-500 font-medium leading-relaxed">
                        <i class="fas fa-map-marker-alt mr-1"></i> ${row.address || "-"}
                      </p>
                    </div>

                    <div class="pt-1">
                      ${row.action.replace('btn-sm', 'w-full h-10 rounded-xl text-xs font-black uppercase tracking-widest flex items-center justify-center gap-2')}
                    </div>
                  </div>
                `);
                $cardContainer.append(card);
              });

              // Jika masih ada halaman berikutnya, tampilkan loader animasi estetik di bawah
              if (mobileCurrentPage < totalPages - 1) {
                $cardContainer.append(`
                  <div id="mobile-scroll-loader" class="p-4 text-center text-slate-400 text-xs font-black uppercase tracking-widest flex items-center justify-center gap-2 bg-slate-50/50 border-t border-slate-100 shrink-0">
                    <i class="fas fa-circle-notch fa-spin text-teal-600"></i> Memuat pasien...
                  </div>
                `);
              }
            }
            
            isInfiniteScrollLoading = false;
            $('.dataTables_paginate').addClass('!flex !flex-row !items-center !justify-center gap-1 w-full mt-4');
          } else {
            // --- LOGIKA DESKTOP REMOTE ---
            $('.dataTables_paginate').addClass('!flex !flex-row !items-center !justify-end gap-1');
          }

          // --- STYLING PAGINATION HASIL PULL (TABLE 2 / MODAL) ---
          $('.dataTables_paginate > span').addClass('!flex !flex-row !items-center gap-1');
          $('.paginate_button').addClass('!inline-flex items-center justify-center min-w-[28px] h-7 rounded text-[11px] font-semibold text-slate-600 cursor-pointer hover:bg-slate-100 transition-colors !m-0 !p-0 border border-slate-300');
          $('.paginate_button.current').addClass('!bg-slate-900 !text-white hover:!bg-slate-800 border-0').removeClass('bg-white text-slate-600');
          $('.paginate_button.disabled').addClass('!opacity-50 cursor-not-allowed shadow-none');
        }
      });
    }
  };

  // --- MODAL PILIH PASIEN ---
  $(document)
    .off("click", '[data-modal-open="exampleModal"]')
    .on("click", '[data-modal-open="exampleModal"]', function (e) {
      if (!$(this).closest(".modal-wrapper").length) {
        setTimeout(() => initTable2(), 200);
      }
    });

  // --- TAMBAH ANTREAN ---
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
            table1.ajax.reload(null, false);
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

  // --- SUBMIT PASIEN BARU (TAMBAH CEK WA) ---
  $("#submitBtnNew").on("click", function (e) {
    e.preventDefault();
    const form = $("#formTambahPasien");

    if (!form[0].checkValidity()) {
      form.addClass("was-validated");
      form.find(".invalid-feedback").removeClass("hidden");
      return;
    }

    const phone = $('#phone_new').val();

    $.ajax({
      url: config.checkPhoneUrl,
      type: 'POST',
      data: { phone: phone, [config.csrfName]: config.csrfHash },
      dataType: 'json',
      success: function (response) {
        if (response.new_token) {
          config.csrfHash = response.new_token;
          $(`input[name='${config.csrfName}']`).val(response.new_token);
        }

        if (response.exists) {
          Swal.fire({
            target: document.getElementById("modalnewpatient"),
            title: "Gagal!",
            text: "Nomor WhatsApp sudah terdaftar di sistem. Gunakan nomor lain.",
            icon: "warning",
          });
        } else {
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

                    if (table2Init) {
                      $('#table-2').DataTable().ajax.reload(null, false);
                    }

                    setTimeout(() => {
                      closeModal(document.getElementById("modalnewpatient"));
                      form[0].reset();
                      form.removeClass("was-validated");
                    }, 500);

                    table1.ajax.reload(null, false);
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
        }
      },
      error: function () {
        Swal.fire({ target: document.getElementById("modalnewpatient"), title: 'Error', text: 'Gagal memverifikasi nomor WhatsApp.', icon: 'error' });
      }
    });
  });

  $(document).on("click", "[data-modal-open]", function (event) {
    const targetId = $(this).attr("data-modal-open");
    const targetModal = document.getElementById(targetId);
    if (!targetModal) return;

    // Special logic for new patient modal opened from selection modal
    if (targetId === "modalnewpatient") {
      // We don't necessarily close exampleModal here if we want them stacked,
      // but we increased z-index of modalnewpatient in PHP to handle this.
    }

    openModal(targetModal);

    if (targetId === "modalnewpatient") {
      setTimeout(() => {
        if (typeof initSelect2Desa === "function") initSelect2Desa();
        const ni = targetModal.querySelector('input[name="name"]');
        if (ni) ni.focus();
      }, 300);
    }
  });

  $(document).on("click", "[data-modal-close]", function (e) {
    e.preventDefault();
    closeModal($(this).closest(".modal-wrapper")[0]);
  });

  $(document).on("click", ".modal-wrapper", function (e) {
    if (e.target === this) {
      closeModal(this);
    }
  });

  // Toggle pasien rentan
  $(document).on("change", "#isSuspectiveCheckbox", function () {
    const k = $("#keterangan_rentan");
    $(this).is(":checked")
      ? k.stop().slideDown(300).removeClass("hidden")
      : k.stop().slideUp(300);
  });

  $(document).on("click", ".modal-wrapper > div", (e) => e.stopPropagation());

  // --- SEARCH TABLE 1 ---
  const searchInputDebounce = debounce((val) => {
    table1.search(val).draw();
  }, 400);

  $("#searchInput").on("keyup", function () {
    const val = $(this).val();
    searchInputDebounce(val);
  });

  // --- SEARCH TABLE 2 ---
  const searchDebounce = debounce((val) => {
    if ($.fn.DataTable.isDataTable('#table-2')) {
      $('#table-2').DataTable().search(val).draw();
    }
  }, 400);

  $("#searchPatientList").off("keyup").on("keyup", function () {
    const val = $(this).val();
    searchDebounce(val);
  });

  // --- Export buttons ---
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

// FUNGSI PREVIEW GAMBAR/FILE PASIEN
window.previewFiles = function () {
  const previewContainer = document.getElementById('file-previews');
  const files = document.getElementById('userfiles').files;
  previewContainer.innerHTML = '';
  if (files) {
    Array.from(files).forEach(file => {
      const reader = new FileReader();
      const div = document.createElement('div');
      div.className = 'relative h-16 w-16 rounded-md border border-slate-200 overflow-hidden shadow-sm';
      reader.onload = function (e) {
        if (file.type.startsWith('image/')) {
          const img = document.createElement('img');
          img.src = e.target.result;
          img.className = 'h-full w-full object-cover';
          div.appendChild(img);
        } else {
          const icon = document.createElement('div');
          icon.className = 'flex h-full w-full items-center justify-center bg-slate-50 text-slate-500 text-[10px] font-bold uppercase';
          icon.textContent = file.name.split('.').pop();
          div.appendChild(icon);
        }
      };

      reader.readAsDataURL(file);
      previewContainer.appendChild(div);
    });
  }
};