/**
 * Jabatan Management Page Script
 * Handles Server-Side DataTables, AJAX Validation, and CRUD modals
 */

// === 1. GLOBAL HELPER FUNCTIONS ===
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
    const config = window.jabatanConfig;
    if (!config || typeof window.$ === "undefined") return;

    const $ = window.$;
    const swalLib = window.Swal || window.swal;

    let table = null;

    const updateCsrf = (newToken) => {
        if (!newToken) return;
        config.csrfHash = newToken;
        $(`input[name='${config.csrfName}']`).val(newToken);
    };

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
                    d[config.csrfName] = config.csrfHash;
                },
                dataSrc: function (json) {
                    if (json.csrfHash) {
                        updateCsrf(json.csrfHash);
                    }
                    return json.data;
                }
            },
            columns: [
                { data: "no", width: "5%", sortable: false, searchable: false, className: "px-6 py-4 text-xs font-mono text-slate-400 border-0" },
                { data: "nama_jabatan", width: "25%", className: "px-6 py-4 text-sm font-semibold text-slate-700 border-0" },
                { data: "deskripsi", width: "50%", className: "px-6 py-4 text-xs text-slate-500 border-0 font-medium" },
                { data: "action", class: "text-right px-6 py-4 border-0", width: "20%", sortable: false, searchable: false }
            ],
            dom: '<"w-full"t><"clear">',
            language: { search: "_INPUT_" },
            initComplete: function () {
                const searchInput = $('.dataTables_filter input');
                searchInput
                    .attr('placeholder', 'Cari jabatan...')
                    .addClass('w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-700 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 transition-all outline-none block shadow-inner');

                searchInput.appendTo($('#search-container'));
                $('.dataTables_filter').hide();
            },
            drawCallback: function (settings) {
                const api = this.api();
                const data = api.rows({ page: 'current' }).data();
                const mobileContainer = $('#mobile-jabatan-container');
                const footer = $('#table-footer');

                // --- MOBILE CARD RENDERING ---
                mobileContainer.empty();
                if (data.length === 0) {
                    mobileContainer.append(`
                        <div class="py-16 text-center opacity-30">
                            <i class="fas fa-id-badge text-4xl text-slate-300 mb-3"></i>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-widest">Jabatan Kosong</p>
                        </div>
                    `);
                } else {
                    data.each(function (row) {
                        mobileContainer.append(`
                            <div class="p-5 space-y-4 hover:bg-slate-50/50 transition-colors">
                                <div class="flex justify-between items-start">
                                    <div class="space-y-1">
                                        <p class="text-[9px] font-bold text-slate-300 uppercase tracking-widest italic">#${row.no}</p>
                                        <h3 class="text-sm font-semibold text-slate-800 uppercase tracking-tight">${row.nama_jabatan}</h3>
                                    </div>
                                    <div class="flex gap-2">
                                        ${row.action}
                                    </div>
                                </div>
                                <div class="bg-slate-50/80 p-3.5 rounded-2xl border border-slate-100 shadow-inner">
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1.5 opacity-60">Deskripsi Pekerjaan</p>
                                    <p class="text-xs font-bold text-slate-600 leading-relaxed">${row.deskripsi || '<span class="italic opacity-30">Tidak ada deskripsi.</span>'}</p>
                                </div>
                            </div>
                        `);
                    });
                }

                // --- PAGINATION RENDERING ---
                footer.empty();
                const info = api.page.info();
                const pagination = $(`
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest">
                            Menampilkan ${info.start + 1} - ${info.end} dari ${info.recordsTotal} Jabatan
                        </div>
                        <div class="flex items-center gap-2" id="custom-pagination"></div>
                    </div>
                `);

                footer.append(pagination);
                
                // Manual pagination for premium feel
                const paginateContainer = $('#custom-pagination');
                const prevDisabled = info.page === 0 ? 'opacity-30 pointer-events-none' : '';
                const nextDisabled = info.page === info.pages - 1 ? 'opacity-30 pointer-events-none' : '';

                paginateContainer.append(`
                    <button class="px-3 py-2 rounded-xl border border-slate-200 text-slate-400 hover:bg-slate-50 transition-all ${prevDisabled}" onclick="window.jabatanTable.page('previous').draw('page')">
                        <i class="fas fa-chevron-left text-[10px]"></i>
                    </button>
                    <div class="flex items-center bg-slate-50 rounded-xl px-1 border border-slate-100 shadow-inner">
                        <span class="px-3 py-2 text-[11px] font-semibold text-slate-700">${info.page + 1} / ${info.pages}</span>
                    </div>
                    <button class="px-3 py-2 rounded-xl border border-slate-200 text-slate-400 hover:bg-slate-50 transition-all ${nextDisabled}" onclick="window.jabatanTable.page('next').draw('page')">
                        <i class="fas fa-chevron-right text-[10px]"></i>
                    </button>
                `);

                window.jabatanTable = api; // Global reference for pagination buttons
            }
        });
    };

    // --- FUNGSI VALIDASI AJAX ---
    const setupValidation = (inputId, submitBtnId, errorId, originalName, originalId) => {
        let timer;
        $(inputId).on('input', function () {
            clearTimeout(timer);
            const name = $(this).val();
            if (name.trim() === "") { $(submitBtnId).prop('disabled', true); return; }
            if (name === originalName) { $(inputId).removeClass('is-invalid'); $(submitBtnId).prop('disabled', false); return; }

            timer = setTimeout(() => {
                $.ajax({
                    url: config.checkNameUrl,
                    type: "POST",
                    data: { name: name, id: originalId, [config.csrfName]: config.csrfHash },
                    success: function (res) {
                        if (res.exists) {
                            $(inputId).addClass('is-invalid');
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
        // Modal Open / Close delegation listeners
        $(document).on("click", "[data-modal-open]", function (event) {
            const targetId = $(this).attr("data-modal-open");
            const targetModal = document.getElementById(targetId);
            if (targetModal) {
                openModal(targetModal);
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

        $(document).on('click', '.btn_edit', function (e) {
            e.preventDefault();
            const btn = $(this);
            const modal = document.getElementById('modal_edit_jabatan');
            openModal(modal);

            $('#edit_name').val(btn.data('name'));
            $('#edit_deskripsi').val(btn.data('description'));
            $('#editjabatanForm').attr("action", btn.data('href'));
            $('#edit_submitBtn').prop('disabled', false);
            $('#edit_name').removeClass('is-invalid');

            const originalName = $('#edit_name').val();
            const urlParts = $('#editjabatanForm').attr("action").split('/');
            const originalId = urlParts[urlParts.length - 1];
            setupValidation('#edit_name', '#edit_submitBtn', '#edit_nameError', originalName, originalId);
        });

        $(document).on('click', '.btn_delete', function (e) {
            e.preventDefault();
            const href = $(this).data('href');
            swalLib.fire({
                title: 'Hapus Jabatan ?',
                text: "Jabatan Akan dihapus dari daftar",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'bg-red-600 text-white px-6 py-2.5 rounded-xl font-bold ml-2',
                    cancelButton: 'bg-slate-100 text-slate-500 px-6 py-2.5 rounded-xl font-bold'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(href, { [config.csrfName]: config.csrfHash }, function (res) {
                        if (res && res.csrfHash) updateCsrf(res.csrfHash);
                        swalLib.fire({
                            icon: res.status,
                            title: res.status === 'success' ? 'Berhasil!' : 'Gagal!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => { if (table) table.ajax.reload(null, false); });
                    }, 'json');
                }
            });
        });

        // Handle Add Form AJAX Submit
        $('#addJabatanForm').on('submit', function (e) {
            e.preventDefault();
            const form = $(this);
            const actionUrl = form.attr('action');
            const data = form.serialize();

            swalLib.fire({
                title: 'Menyimpan...',
                text: 'Harap tunggu sebentar.',
                allowOutsideClick: false,
                didOpen: () => {
                    swalLib.showLoading();
                }
            });

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function (res) {
                    if (res && res.csrfHash) updateCsrf(res.csrfHash);
                    if (res.status === 'success') {
                        swalLib.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            closeModal(document.getElementById('jabatanModal'));
                            form[0].reset();
                            $('#add_submitBtn').prop('disabled', true);
                            if (table) table.ajax.reload(null, false);
                        });
                    } else {
                        swalLib.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: res.message,
                            confirmButtonText: 'Oke'
                        });
                    }
                },
                error: function (xhr) {
                    const res = xhr.responseJSON;
                    if (res && res.csrfHash) updateCsrf(res.csrfHash);
                    swalLib.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: (res && res.message) ? res.message : 'Terjadi kesalahan pada server.',
                        confirmButtonText: 'Oke'
                    });
                }
            });
        });

        // Handle Edit Form AJAX Submit
        $('#editjabatanForm').on('submit', function (e) {
            e.preventDefault();
            const form = $(this);
            const actionUrl = form.attr('action');
            const data = form.serialize();

            swalLib.fire({
                title: 'Memperbarui...',
                text: 'Harap tunggu sebentar.',
                allowOutsideClick: false,
                didOpen: () => {
                    swalLib.showLoading();
                }
            });

            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function (res) {
                    if (res && res.csrfHash) updateCsrf(res.csrfHash);
                    if (res.status === 'success') {
                        swalLib.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            closeModal(document.getElementById('modal_edit_jabatan'));
                            if (table) table.ajax.reload(null, false);
                        });
                    } else {
                        swalLib.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: res.message,
                            confirmButtonText: 'Oke'
                        });
                    }
                },
                error: function (xhr) {
                    const res = xhr.responseJSON;
                    if (res && res.csrfHash) updateCsrf(res.csrfHash);
                    swalLib.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: (res && res.message) ? res.message : 'Terjadi kesalahan pada server.',
                        confirmButtonText: 'Oke'
                    });
                }
            });
        });

        // Initialize Add Form validation on page load
        setupValidation('#add_name', '#add_submitBtn', '#add_nameError', '', '');
    };

    checkFlashdata();
    initTable();
    initEvents();
};

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupJabatanPage);
} else {
    setupJabatanPage();
}