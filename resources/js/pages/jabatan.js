/**
 * Jabatan Management Page Script
 * Handles Server-Side DataTables, AJAX Validation, and CRUD modals
 */

// === 1. GLOBAL HELPER FUNCTIONS ===
// (Fungsi bawaan standar untuk konsistensi dengan modul lain)
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


// === 2. MAIN PAGE SETUP FUNCTION ===
const setupJabatanPage = () => {
    // Ambil konfigurasi dari View
    const config = window.jabatanConfig;

    // Sabuk pengaman: Pastikan jQuery dan Config tersedia
    if (!config || typeof window.$ === "undefined") return;

    const $ = window.$;
    const swalLib = window.Swal || window.swal;

    // --- STATE VARIABLES ---
    let table = null;

    // --- FUNGSI CEK FLASHDATA ---
    const checkFlashdata = () => {
        if (config.flashSuccess) {
            swalLib.fire({
                icon: 'success',
                title: 'Mantap !',
                text: config.flashSuccess,
                timer: 2500,
                showConfirmButton: false
            });
        }

        if (config.flashError) {
            swalLib.fire({
                icon: 'error',
                title: 'Oops!',
                text: config.flashError,
                confirmButtonText: 'Oke'
            });
        }
    };

    // --- INISIALISASI DATATABLES ---
    const initTable = () => {
        table = $('#table-jabatan').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: config.fetchUrl,
                type: "POST",
                data: function (d) {
                    d[config.csrfName] = config.csrfHash; // Kirim CSRF Token
                }
            },
            columns: [
                { data: "no", width: "5%", sortable: false, searchable: false, className: "px-6 py-4 text-sm text-slate-500 border-0" },
                { data: "nama_jabatan", width: "25%", className: "px-6 py-4 text-sm font-bold text-slate-700 border-0" },
                { data: "deskripsi", width: "50%", className: "px-6 py-4 text-xs text-slate-500 border-0" },
                { data: "action", class: "text-right px-6 py-4 border-0", width: "20%", sortable: false, searchable: false }
            ],
            // DOM Layout untuk integrasi Tailwind
            dom: '<"w-full"t><"flex flex-col md:flex-row items-center justify-between p-6 bg-white border-t border-slate-100 gap-4"<"flex items-center gap-4"li>p><"clear">',
            language: { search: "_INPUT_" },
            initComplete: function () {
                const searchInput = $('.dataTables_filter input');
                searchInput
                    .attr('placeholder', 'Cari jabatan...')
                    .addClass('w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all outline-none block');

                searchInput.appendTo($('#search-container'));
                $('.dataTables_filter').hide();
            },
            drawCallback: function () {
                // Styling Pagination
                $('.pagination').addClass('flex flex-row items-center mb-0');
                $('.page-item').addClass('mx-0.5');
                $('.page-link').addClass('px-3 py-1 rounded-lg border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors');
                $('.active .page-link').addClass('bg-teal-600 text-white border-teal-600 hover:bg-teal-700').removeClass('text-slate-600');
                $('.dataTables_length select').addClass('border border-slate-200 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 bg-white focus:outline-none focus:border-teal-500 mx-2');
            }
        });
    };

    // --- FUNGSI VALIDASI AJAX ---
    const setupValidation = (inputId, submitBtnId, errorId, originalName, originalId) => {
        let timer;
        $(inputId).on('input', function () {
            clearTimeout(timer);
            const name = $(this).val();

            if (name.trim() === "") {
                $(submitBtnId).prop('disabled', true);
                return;
            }

            if (name === originalName) {
                $(inputId).removeClass('is-invalid');
                $(submitBtnId).prop('disabled', false);
                return;
            }

            timer = setTimeout(() => {
                $.ajax({
                    url: config.checkNameUrl,
                    type: "POST",
                    data: {
                        name: name,
                        id: originalId,
                        [config.csrfName]: config.csrfHash
                    },
                    success: function (res) {
                        if (res.exists) {
                            $(inputId).addClass('is-invalid');
                            $(errorId).text('Nama jabatan sudah digunakan.');
                            $(submitBtnId).prop('disabled', true);
                        } else {
                            $(inputId).removeClass('is-invalid');
                            $(submitBtnId).prop('disabled', false);
                        }
                    }
                });
            }, 500);
        });
    };

    // --- SETUP EVENT LISTENERS ---
    const initEvents = () => {
        // Event Klik Edit (Dari Tabel Server-Side)
        $(document).on('click', '.btn_edit', function (e) {
            e.preventDefault();
            const btn = $(this);

            $('#modal_edit_jabatan').modal('show');
            $('#edit_name').val(btn.data('name'));
            $('#edit_deskripsi').val(btn.data('description'));
            $('#editjabatanForm').attr("action", btn.data('href'));

            $('#edit_submitBtn').prop('disabled', false);
            $('#edit_name').removeClass('is-invalid');
        });

        // Event Klik Delete (AJAX Post)
        $(document).on('click', '.btn_delete', function (e) {
            e.preventDefault();
            const href = $(this).data('href');

            swalLib.fire({
                title: 'Hapus Jabatan ?',
                text: "Jabatan Akan dihapus dari daftar",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(href, {
                        [config.csrfName]: config.csrfHash
                    }, function (res) {
                        swalLib.fire({
                            icon: res.status,
                            title: res.status === 'success' ? 'Mantap!' : 'Oops!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }, 'json');
                }
            });
        });

        // Trigger Validasi saat Modal Tambah Terbuka
        $('#jabatanModal').on('shown.bs.modal', function () {
            setupValidation('#add_name', '#add_submitBtn', '#add_nameError', '', '');
        });

        // Trigger Validasi saat Modal Edit Terbuka
        $('#modal_edit_jabatan').on('shown.bs.modal', function () {
            const originalName = $('#edit_name').val();
            const urlParts = $('#editjabatanForm').attr("action").split('/');
            const originalId = urlParts[urlParts.length - 1];

            setupValidation('#edit_name', '#edit_submitBtn', '#edit_nameError', originalName, originalId);
        });
    };

    // ==========================================
    // 4. EKSEKUSI FUNGSI (RUN)
    // ==========================================
    checkFlashdata();
    initTable();
    initEvents();
};

// === 3. AUTO-RUN SAAT HALAMAN DIMUAT ===
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupJabatanPage);
} else {
    setupJabatanPage();
}