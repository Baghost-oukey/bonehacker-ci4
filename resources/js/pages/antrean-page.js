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
            dom: '<"p-0"rt><"flex items-center justify-between p-4 border-t border-slate-100"ip>',
            language: {
                processing: '<i class="fas fa-circle-notch fa-spin text-indigo-500"></i>',
                emptyTable: "Belum ada antrean masuk",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_",
                paginate: { previous: 'Prev', next: 'Next' }
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
                { data: 'patient_id', className: 'px-8 py-4 font-mono text-xs font-bold text-indigo-600' },
                { data: 'name', className: 'px-8 py-4 font-bold text-slate-800' },
                { data: 'address', className: 'px-8 py-4 text-xs text-slate-500' },
                { data: 'description', className: 'px-8 py-4 text-center' },
                {
                    data: 'patient_id', 
                    className: 'px-8 py-4 text-right',
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                    <button type="button" 
                        onclick="tambahKeAntrean(${data})" 
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 text-xs font-bold text-white hover:bg-teal-700 transition active:scale-95 shadow-sm">
                        <i class="fas fa-plus text-[10px]"></i> Tambah
                    </button>
                `;
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

    // --- EVENT LISTENERS ---

    // 1. Pemicu Modal Pilih Pasien (Table 2)
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

            if (targetId === "modalFormBaru") {
                closeModal(document.getElementById("exampleModal"));
            }

            openModal(targetModal);
        }

        const closeTrigger = event.target.closest("[data-modal-close]");
        if (closeTrigger) {
            closeModal(closeTrigger.closest(".modal-wrapper"));
        }
    });

    const updatePaginationUI = () => {
    };

    loadTableData(1);
};

// Jalankan Inisialisasi
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupAntreanPage);
} else {
    setupAntreanPage();
}