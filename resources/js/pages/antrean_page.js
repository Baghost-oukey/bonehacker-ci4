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
    let searchValue = "";
    let pageLength = 25;
    let filteredRecords = 0;
    let currentPage = 1;

    // --- Tabel Utama ---
    let tableInstance = null;

    const loadTableData = () => {
        const config = window.antreanConfig;

        tableInstance = $('#table-1').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            pageLength: 25,
            paging: true,
            bPaginate: false,
            info: true,
            ajax: {
                url: config.fetchUrl,
                type: "POST",
                data: function (d) {
                    d[config.csrfName] = config.csrfHash;
                    d.region = $('#region_id').val() || '';
                    d.start_date = $('#startDate').val();
                    d.end_date = $('#endDate').val();
                }
            },
            columns: [
                { data: 'queue_id', className: 'px-6 py-3.5 text-center font-mono text-xs text-slate-500' },
                { data: 'date', className: 'px-6 py-3.5 text-xs text-slate-600' },
                { data: 'name', className: 'px-6 py-3.5 font-bold text-slate-800' },
                { data: 'age', className: 'px-6 py-3.5 text-slate-600' },
                { data: 'address', className: 'px-6 py-3.5 text-xs text-slate-500' },
                { data: 'phone', className: 'px-6 py-3.5 text-slate-600' },
                { data: 'description', className: 'px-6 py-3.5 text-center' },
                { data: 'action', className: 'px-6 py-3.5 text-right' }
            ],
            dom: '<"p-0"rt><"flex items-center justify-between p-4 border-t border-slate-100"i>',
            language: {
                processing: '<i class="fas fa-circle-notch fa-spin text-indigo-500"></i>',
                emptyTable: "Belum ada antrean masuk",
                info: "Menampilkan <span class='font-bold text-slate-800'>_START_</span> sampai <span class='font-bold text-slate-800'>_END_</span> dari <span class='font-bold text-indigo-600'>_TOTAL_</span> data",
                // infoEmpty: "Menampilkan 0 data",
                // paginate: { previous: 'Prev', next: 'Next' }
            },

            drawCallback: function () {
                if (typeof updateCustomPagination === 'function') {
                    updateCustomPagination(this.api());
                }
            }
        });

        $('#searchPatient').on('keyup', function () {
            tableInstance.search(this.value).draw();
        });
    };
    // --- Table Paseien pada saat tambah Pasient ---
    let patientTableInstance = null;

    const loadPatientData = () => {
        const config = window.antreanConfig;
        $('#patientListBody').html('<tr><td colspan="5" class="animate-pulse bg-slate-50 h-20"></td></tr>');

        if ($.fn.DataTable.isDataTable('#table-2')) {
            patientTableInstance.ajax.reload();
            return;
        }
        patientTableInstance = $('#table-2').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            pageLength: 10,
            lengthMenu: [10, 25, 50],
            pagingType: "simple_numbers",
            ajax: {
                url: config.fetchPatientUrl,
                type: "POST",
                data: function (d) {
                    d[config.csrfName] = config.csrfHash;
                    d.region = $("#region_id").val() || "";
                }
            },
            columns: [
                {
                    data: 'patient_id',
                    className: 'px-6 py-4',
                    render: function (data) {
                        return `
                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-gradient-to-br from-slate-100 to-slate-200 border border-slate-200 flex items-center justify-center font-black text-slate-700 text-xs shadow-inner">
                        ${data.toString().slice(-2)}
                    </div>
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">ID PASIEN</span>
                        <span class="text-xs font-black text-indigo-600">#${data}</span>
                    </div>
                </div>`;
                    }
                },
                {
                    data: 'name',
                    className: 'px-6 py-4',
                    render: function (data, type, row) {
                        return `
                <div class="flex flex-col">
                    <span class="font-black text-slate-800 text-sm tracking-tight leading-tight uppercase">${data}</span>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[10px] font-bold border border-emerald-100">
                            <i class="fab fa-whatsapp"></i> ${row.phone || '-'}
                        </span>
                    </div>
                </div>`;
                    }
                },
                {
                    data: 'address',
                    className: 'px-6 py-4',
                    render: function (data, type, row) {
                        const primaryAddress = row.desa_nama || data || 'Alamat tidak lengkap';
                        const regionLabel = row.name_region || 'LOKASI UMUM';
                        return `
                    <div class="flex flex-col gap-0.5 text-left group">
                        <div class="text-xs font-black text-slate-700 truncate max-w-[180px] uppercase tracking-tight">
                            ${primaryAddress}
                        </div>
                        
                        <div class="flex items-center gap-1 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                            <i class="fas fa-map-marker-alt text-red-500 text-[8px] animate-bounce-slow"></i>
                            <span class="group-hover:text-indigo-500 transition-colors">${regionLabel}</span>
                        </div>
                    </div>`;
                    }
                },
                {
                    data: 'visit_count', // Ubah data ini jika fieldnya berbeda
                    className: 'px-6 py-4 text-center',
                    render: function (data, type, row) {
                        const isLama = row.visit_count > 0;
                        return isLama ?
                            `<div class="inline-flex flex-col items-center">
                    <span class="px-3 py-1 rounded-full bg-indigo-600 text-[9px] font-black text-white uppercase tracking-wider shadow-sm">
                        PASIEN LAMA
                    </span>
                    <span class="text-[9px] font-bold text-slate-400 mt-1 italic">${row.visit_count} Kunjungan</span>
                </div>` :
                            `<span class="px-3 py-1 rounded-full bg-white text-[9px] font-black text-slate-400 uppercase tracking-wider border border-slate-200">
                    BARU
                </span>`;
                    }
                },
                {
                    data: 'patient_id',
                    className: 'px-6 py-4 text-right',
                    searchable: false,
                    orderable: false,
                    render: function (data) {
                        return `
                <button type="button" onclick="tambahKeAntrean(${data})" 
                    class="h-10 px-5 rounded-xl bg-slate-900 text-[11px] font-black text-white hover:bg-indigo-600 transition-all active:scale-95 shadow-md flex items-center gap-2 ml-auto group">
                    PILIH <i class="fas fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                </button>`;
                    }
                }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari pasien...",
                processing: '<i class="fas fa-spinner fa-spin text-indigo-500"></i> Memuat data...'
            },
            dom: '<"overflow-x-auto"rt><"flex flex-col md:flex-row items-center justify-between px-8 py-4 gap-4 border-t border-slate-100 [<_.pagination]:flex [<_.pagination]:gap-1 [<_.page-item]:list-none"ip>',
            drawCallback: function () {
                $('.pagination').addClass('flex flex-row items-center mb-0');
                $('.page-item').addClass('mx-0.5');
                $('.page-link').addClass('px-3 py-1 rounded-lg border border-slate-200 text-xs text-slate-600 hover:bg-slate-50');
                $('.active .page-link').addClass('bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700');
            }
        });

        // Hubungkan Search Custom kita ke DataTable
        $("#searchPatientList").on("keyup", function () {
            patientTableInstance.search(this.value).draw();
        });
    };

    window.tambahKeAntrean = (patientId) => {
        Swal.fire({
            title: 'Konfirmasi',
            text: "Tambahkan pasien ini ke antrean?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d9488',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Tambahkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Beri feedback loading ke user
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: `${window.location.origin}/antrean/addToQueue/${patientId}`,
                    type: "GET",
                    dataType: "json",
                    success: function (response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        closeModal(document.getElementById("exampleModal"));
                        if (tableInstance) {
                            tableInstance.ajax.reload();
                        }
                    },
                    error: function (xhr) {
                        const err = xhr.responseJSON;
                        Swal.fire('Gagal!', err ? err.message : 'Terjadi kesalahan server.', 'error');
                    }
                });
            }
        });
    };

    // --- Button Pagination ---
    const updateCustomPagination = (api) => {
        const info = api.page.info();
        const $prevBtn = $('#paginationPrev');
        const $nextBtn = $('#paginationNext');
        const $numbersContainer = $('#paginationNumbers');

        // 1. Atur Status tombol Prev & Next
        $prevBtn.prop('disabled', info.page === 0);
        $nextBtn.prop('disabled', info.page >= info.pages - 1);

        // 2. Clear angka halaman lama
        $numbersContainer.empty();

        // 3. Gambar Angka Halaman (Logika sederhana: tampilkan semua atau batasi)
        for (let i = 0; i < info.pages; i++) {
            const activeClass = i === info.page
                ? 'bg-indigo-600 text-white border-indigo-600'
                : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50';

            const btnNumber = `
            <button onclick="changePage(${i})" 
                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border ${activeClass} text-xs font-semibold transition">
                ${i + 1}
            </button>`;

            // Hanya tampilkan jika halaman < 5 (atau kamu bisa buat logika ellipsis ...)
            if (info.pages <= 5 || (i >= info.page - 2 && i <= info.page + 2)) {
                $numbersContainer.append(btnNumber);
            }
        }
    };

    // Fungsi untuk pindah halaman
    window.changePage = (pageIndex) => {
        if (tableInstance) {
            tableInstance.page(pageIndex).draw('page');
        }
    };

    // --- EVENT LISTENERS ---
    //  Modal Pilih Pasien (Table 2)
    $(document).on('click', '[data-modal-open="exampleModal"]', function (e) {
        if (!$(this).closest('.modal-wrapper').length) {
            loadPatientData("");
        }
    });

    $("#searchPatientList").on("keyup", debounce(function () {
        loadPatientData($(this).val());
    }, 500));

    $("#startDate, #endDate, #region_id").on("change", () => loadTableData(1));
    $("#searchInput").on("keyup", debounce((e) => {
        searchValue = e.target.value;
        loadTableData(1);
    }, 400));

    document.addEventListener("click", (event) => {
        const openTrigger = event.target.closest("[data-modal-open]");
        if (openTrigger) {
            const targetId = openTrigger.getAttribute("data-modal-open");
            const targetModal = document.getElementById(targetId);

            if (targetId === "modalFormBaru" || targetId === "modalnewpatient") {
                const oldModal = document.getElementById("exampleModal");
                if (oldModal) closeModal(oldModal);
            }

            openModal(targetModal);

            if (targetId === "modalnewpatient" || targetId === "modalFormBaru") {
                // Kasih jeda sedikit agar modal selesai muncul (penting buat Select2)
                setTimeout(() => {
                    initSelect2Desa();

                    // Paksa fokus ke input nama agar user nyaman
                    const nameInput = targetModal.querySelector('input[name="name"]');
                    if (nameInput) nameInput.focus();
                }, 300);
            }
        }


        const closeTrigger = event.target.closest("[data-modal-close]");
        if (closeTrigger) {
            closeModal(closeTrigger.closest(".modal-wrapper"));
        }
    });


    $(document).on('click', '.modal-wrapper', function (e) {
        if (e.target === this) {
            const $modal = $(this);
            $modal.fadeOut(200, function () {
                $(this).addClass('hidden').removeClass('flex').removeAttr('style');
                $('body').removeClass('overflow-hidden');
            });
        }
    });

    $(document).on('click', '[data-modal-close]', function (e) {
        e.preventDefault();
        const $modal = $(this).closest('.modal-wrapper');
        $modal.fadeOut(200, function () {
            $(this).addClass('hidden').removeClass('flex').removeAttr('style');
            $('body').removeClass('overflow-hidden');
        });
    });

    $(document).on('change', '#isSuspectiveCheckbox', function () {
        const $ket = $('#keterangan_rentan');
        if ($(this).is(':checked')) {
            $ket.stop().slideDown(300).removeClass('hidden');
        } else {
            $ket.stop().slideUp(300);
        }
    });

    $(document).on('click', '.modal-wrapper > div', function (e) {
        e.stopPropagation();
    });
    // Menambahkan Modal Baru End

    // 3. Auto-fill dari Desa (Gunakan delegasi agar aman)
    $(document).on('select2:select', '#desa_id', function (e) {
        const d = e.params.data.full_data;
        $('#desa_nama').val(d.desNama || '');
        $('#kecamatan_id').val(d.kecamatan?.kecIdKecamatan || '');
        $('#kecamatan_nama').val(d.kecamatan?.kecNama || '');
        $('#kabupaten_id').val(d.kecamatan?.kabupaten?.kabIdKabupaten || '');
        $('#kabupaten_nama').val(d.kecamatan?.kabupaten?.kabNama || '');
        $('#provinsi_id').val(d.kecamatan?.kabupaten?.provinsi?.provIdProvinsi || '');
        $('#provinsi_nama').val(d.kecamatan?.kabupaten?.provinsi?.provNama || '');
    });

    
    // Tombol Sebelumnya
    $('#paginationPrev').on('click', function () {
        if (tableInstance) {
            tableInstance.page('previous').draw('page');
        }
    });

    // Tombol Berikutnya
    $('#paginationNext').on('click', function () {
        if (tableInstance) {
            tableInstance.page('next').draw('page');
        }
    });
    loadTableData(1);
};

$(document).on('focusin', function (e) {
    if ($(e.target).closest(".select2-container").length) {
        e.stopImmediatePropagation();
    }
});

// Pastikan kursor masuk ke search field saat Select2 dibuka
$(document).on('select2:open', '#desa_id', function () {
    setTimeout(() => {
        const searchField = document.querySelector('.select2-search__field');
        if (searchField) searchField.focus();
    }, 100);
});

$(document).on('focusin', function (e) {
    if ($(e.target).closest(".select2-container").length) {
        e.stopImmediatePropagation();
    }
});

// Jalankan Inisialisasi
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupAntreanPage);
} else {
    setupAntreanPage();
}
function initSelect2Desa() {
    console.log("--- Debug: Inisialisasi Select2 Desa Dimulai ---");
    
    const $el = $('#desa_id'); 

    if ($el.data('select2')) {
        $el.select2('destroy');
        console.log("Debug: Instance lama dihancurkan");
    }

    $el.select2({
        placeholder: "Temukan Desa",
        dropdownParent: $('#desa_id'),
        width: '100%',
        dropdownAutoWidth: true,
        ajax: {
            url: 'https://wilayah.smartsociety.id/public/desa',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term,
                    page: params.page || 1
                };
            },
            processResults: function (data) {
                let options = [];
                if (data.data && data.data.data) {
                    $.each(data.data.data, function (index, item) {
                        let subText = item.kecamatan ? `Kec. ${item.kecamatan.kecNama}, ${item.kecamatan.kabupaten.kabNama}` : '';
                        options.push({
                            id: item.desIdDesa,
                            text: `<strong>${item.desNama}</strong><br><small>${subText}</small>`,
                            full_data: item
                        });
                    });
                }
                return { results: options, pagination: { more: data.data?.next_page_url ? true : false } };
            }
        },
        minimumInputLength: 1,
        escapeMarkup: m => m,
        templateResult: i => i.text,
        templateSelection: i => i.text ? i.text.replace(/<br\s*\/?>/gi, ' ').replace(/<\/?[^>]+(>|$)/g, "") : i.text
    });

    // PENTING: Gunakan variabel $el yang sudah dibuat tadi
    $el.on('select2:open', function() {
        setTimeout(() => {
            const searchField = document.querySelector('.select2-search__field');
            if (searchField) searchField.focus();
        }, 100);
    });
}