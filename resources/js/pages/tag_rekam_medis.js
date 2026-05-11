/**
 * Medis/Medical History Tags Management Page Script
 * Handles CRUD operations for medical history tags with custom pagination
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


// --- INIT SCRIPT ---
const setupMedisPage = () => {
    const config = window.medisConfig;
    const page = document.getElementById("medisPage");
    if (!config || !page || typeof window.$ === "undefined") return;
    const $ = window.$;
    const swalLib = window.Swal || window.swal;
    let currentPage = 1;
    let pageLength = 25;
    let totalRecords = 0;
    let filteredRecords = 0;
    let searchValue = "";
    let ajaxRequest = null;
    let isNameInvalid = false;
    let originalName = "";
    let originalDesc = "";
    let originalId = "";


    // --- UPDATE CRSF ---
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
            const activeClass = pageNum === currentPage
                ? "bg-teal-600 border-teal-600 text-white font-semibold shadow-md shadow-teal-600/30"
                : "border border-slate-300 bg-white text-slate-700 font-semibold transition hover:bg-slate-100 hover:border-slate-400";
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


    // --- LOAD DATA / TABLE DATA ---
    const renderEmptyState = (message) => {
        $("#table-medhis tbody").html(`<tr class="hover:bg-slate-50 transition"><td colspan="5" class="px-6 py-12 text-center text-slate-400 italic text-sm"><i class="fas fa-inbox mr-2 text-slate-300"></i>${message}</td></tr>`);
        $("#mobile-medhis-container").html(`<div class="px-6 py-12 text-center text-slate-400 italic text-sm"><i class="fas fa-inbox mr-2 text-slate-300"></i>${message}</div>`);
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
                if (response.csrf_hash) {
                    updateCsrf(response.csrf_hash);
                }
                currentPage = pageNumber;
                totalRecords = Number(response.recordsTotal || 0);
                filteredRecords = Number(response.recordsFiltered || totalRecords);
                const tbody = $("#table-medhis tbody");
                const mContainer = $("#mobile-medhis-container");
                tbody.empty();
                mContainer.empty();

                if (!response.data || response.data.length === 0) {
                    renderEmptyState("Data tag riwayat medis belum tersedia");
                    updatePaginationInfo();
                    updatePaginationUI();
                    return;
                }

                response.data.forEach((row) => {
                    const trimNameTags = row.nama ? row.nama.toLowerCase().replace(/\b\w/g, s => s.toUpperCase()) : "-";
                    const deskripsiRaw = row.deskripsi || "Tidak ada deskripsi";
                    const deskripsi = row.deskripsi
                        ? `<span class="text-slate-600">${row.deskripsi}</span>`
                        : `<span class="text-slate-400 italic text-xs">Tidak ada deskripsi</span>`;

                    // --- RENDER DESKTOP ROW ---
                    const tr = $(`<tr class="hover:bg-slate-50 transition border-b border-slate-100"></tr>`);
                    tr.append(`<td class="px-6 py-3.5">${row.no || "-"}</td>`);
                    tr.append(`<td class="px-6 py-3.5 font-medium text-slate-800">${trimNameTags}</td>`);
                    tr.append(`<td class="px-6 py-3.5 text-slate-500">${deskripsi}</td>`);
                    tr.append(`<td class="px-6 py-3.5 text-center">${row.jumlah || "0"}</td>`);
                    tr.append(`<td class="px-6 py-3.5 text-center">${row.action || "-"}</td>`);
                    tbody.append(tr);

                    // --- RENDER MOBILE CARD ---
                    const mCard = $(`
                        <div class="p-4 bg-white hover:bg-slate-50/50 transition-colors">
                            <div class="flex items-start justify-between gap-4 mb-3">
                                <div class="flex flex-col min-w-0">
                                    <h4 class="text-[15px] font-black text-slate-900 tracking-tight leading-tight mb-1 truncate">${trimNameTags}</h4>
                                    <p class="text-[11px] text-slate-500 font-medium line-clamp-2">${deskripsiRaw}</p>
                                </div>
                                <div class="shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 border border-indigo-100 text-[11px] font-black tracking-wide">
                                        ${row.jumlah || 0} Data
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-3 border-t border-slate-50">
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">No. ${row.no}</span>
                                <div class="flex items-center gap-2">
                                    ${row.action || ""}
                                </div>
                            </div>
                        </div>
                    `);
                    mContainer.append(mCard);
                });

                updatePaginationInfo();
                updatePaginationUI();
            },
            error: () => {
                renderEmptyState("Gagal memuat data tag riwayat medis");
                filteredRecords = 0;
                updatePaginationInfo();
                updatePaginationUI();
            },
        });
    };


    // --- VALIDASI NAMA ---
    const setInvalid = (inputId, feedbackElement, btnId, msg) => {
        $(inputId).addClass('border-red-500 focus:border-red-500 focus:ring-red-500/15');
        $(inputId).removeClass('border-teal-500 focus:border-teal-500 focus:ring-teal-500/15');
        $(feedbackElement).removeClass('hidden').text(msg).css('color', '#ef4444');
        $(btnId).prop('disabled', true);
    };

    const setValid = (inputId, feedbackElement, btnId, enable) => {
        $(inputId).removeClass('border-red-500 focus:border-red-500 focus:ring-red-500/15');
        $(inputId).addClass('border-teal-500 focus:border-teal-500 focus:ring-teal-500/15');
        $(feedbackElement).addClass('hidden').text('');
        $(btnId).prop('disabled', !enable);
    };

    const resetValidation = (inputId, feedbackElement, btnId) => {
        $(inputId).removeClass('border-red-500 border-teal-500 focus:border-red-500 focus:border-teal-500');
        $(feedbackElement).addClass('hidden').text('');
        $(btnId).prop('disabled', true);
    };


    // --- VALIDASI NAMA DUPLICATE ---
    const validateInput = (inputId, submitBtnId, feedbackId, originalValue, originalId, descInputId, originalDesc) => {
        let debounceTimer;
        $(`${inputId}, ${descInputId}`).off('input').on('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const isEdit = inputId === '#edit_name';
                const currentName = ($(inputId).val() || '').trim();
                const currentDesc = ($(descInputId).val() || '').trim();
                const origName = isEdit ? String($(inputId).attr('data-original') || '').trim() : '';
                const origDesc = isEdit ? String($(descInputId).attr('data-original') || '').trim() : '';
                const origId = isEdit ? String($('#editMedhisForm').attr('data-id') || '') : '';
                if (currentName === '') {
                    setInvalid(inputId, feedbackId, submitBtnId, 'Nama tag tidak boleh kosong');
                    return;
                }
                if (isEdit && currentName.toLowerCase() === origName.toLowerCase()) {
                    if (currentDesc === origDesc) {
                        setValid(inputId, feedbackId, submitBtnId, false);
                    } else {
                        setValid(inputId, feedbackId, submitBtnId, true);
                    }
                    return;
                }

                if (ajaxRequest) ajaxRequest.abort();
                $(feedbackId).removeClass('hidden').text('Memeriksa nama...').css('color', '#64748b');
                ajaxRequest = $.ajax({
                    url: config.checkNameUrl,
                    type: 'POST',
                    data: {
                        [config.csrfName]: config.csrfHash,
                        name: currentName,
                        id: origId
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.csrf_hash) {
                            updateCsrf(response.csrf_hash);
                        }
                        if (response.exists) {
                            isNameInvalid = true;
                            setInvalid(inputId, feedbackId, submitBtnId, 'Nama tag sudah ada, gunakan nama lain');
                        } else {
                            isNameInvalid = false;
                            setValid(inputId, feedbackId, submitBtnId, true);
                        }
                    },
                    error: function () {
                        setInvalid(inputId, feedbackId, submitBtnId, 'Gagal memeriksa nama');
                    }
                });
            }, 300);
        });
    };


    // --- SEARCH HANDLERS ---
    const searchHandler = debounce((value) => {
        searchValue = value;
        currentPage = 1;
        loadTableData(1);
    }, 400);


    // --- EVENT LISTENER ---
    $("#searchInput").off('input') .on("input", function () {
        const keyword = $(this).val();
        searchHandler(keyword);
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


    // --- MODAL TAMBAH DAN EDIT FORM ---
    $('#addMedhisForm, #editMedhisForm').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const btnSubmit = form.find('button[type="submit"]');
        const url = form.attr('action');
        const isEdit = form.attr('id') === 'editMedhisForm';
        const originalBtnText = isEdit ? 'Simpan Perubahan' : 'Simpan';
        btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
        $.ajax({
            url: url,
            type: "POST",
            data: form.serialize(),
            dataType: "json",
            success: function (response) {
                btnSubmit.prop('disabled', false).text(originalBtnText);
                if (response.csrf_hash) {
                    updateCsrf(response.csrf_hash);
                }
                if (response.status || response.success) {
                    const targetModal = document.getElementById(isEdit ? "modalEdit" : "modalTambah");
                    if (targetModal) closeModal(targetModal);
                    loadTableData(currentPage);
                    if (typeof swalLib !== 'undefined' && swalLib?.fire) {
                        swalLib.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Data berhasil disimpan!',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        alert(response.message || 'Data berhasil disimpan!');
                    }
                    setTimeout(() => {
                        if (typeof loadTableData === 'function') {
                            loadTableData(typeof currentPage !== 'undefined' ? currentPage : 1);
                        } else {
                            console.error("Fungsi loadTableData tidak ditemukan, melakukan hard-reload...");
                            window.location.reload();
                        }
                    });
                } else {
                    if (typeof swalLib !== 'undefined' && swalLib?.fire) {
                        swalLib.fire({
                            icon: 'error',
                            title: 'Gagal Simpan',
                            text: response.message || 'Terjadi kesalahan sistem.'
                        });
                    } else {
                        alert(response.message || 'Terjadi kesalahan sistem.');
                    }
                }
            },
            error: function () {
                btnSubmit.prop('disabled', false).text(originalBtnText);
                if (typeof swalLib !== 'undefined' && swalLib?.fire) {
                    swalLib.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Token CSRF mungkin kadaluarsa atau koneksi terputus.'
                    });
                } else {
                    alert('Error: Token CSRF mungkin kadaluarsa atau koneksi terputus.');
                }
            }
        });
    });


    // --- MODAL DELETE ---
    $('#deleteMedhisForm').on('submit', function (e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        const btnSubmit = form.find('button[type="submit"]');
        btnSubmit.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menghapus...');
        $.ajax({
            url: url,
            type: "POST",
            data: form.serialize(),
            dataType: "json",
            success: function (response) {
                if (response.csrf_hash) {
                    updateCsrf(response.csrf_hash);
                }
                if (response.status || response.success) {
                    closeModal(document.getElementById("modalDelete"));
                    loadTableData(currentPage);

                    if (swalLib?.fire) {
                        swalLib.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Data berhasil dihapus!',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        alert(response.message || 'Data berhasil dihapus!');
                    }
                } else {
                    if (swalLib?.fire) {
                        swalLib.fire({
                            icon: 'error',
                            title: 'Gagal Hapus',
                            text: response.message || 'Terjadi kesalahan sistem.'
                        });
                    } else {
                        alert(response.message || 'Terjadi kesalahan sistem.');
                    }
                    btnSubmit.prop('disabled', false).text('Ya, Hapus');
                }
            },
            error: function () {
                if (swalLib?.fire) {
                    swalLib.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Token CSRF mungkin kadaluarsa atau koneksi terputus.'
                    });
                } else {
                    alert('Error: Token CSRF mungkin kadaluarsa atau koneksi terputus.');
                }
                btnSubmit.prop('disabled', false).text('Ya, Hapus');
            }
        });
    });


    // --- BUTTON EDIT ---
    $(document).on('click', '.btn_edit', function () {
        const button = $(this);
        const id = button.data('id');
        const name = button.data('name');
        const description = button.data('description');
        const href = button.data('href');
        if (id && name) {
            originalName = name;
            originalDesc = description || '';
            originalId = id;
            $('#edit_name').val(name).attr('data-original', name);
            $('#edit_deskripsi').val(description || '').attr('data-original', description || '');

            $('#editMedhisForm').attr('action', href).attr('data-id', id);
            resetValidation('#edit_name', '.edit-name-feedback', '#edit_submitBtn');
            openModal(document.getElementById("modalEdit"));
        }
    });


    // --- BUTTON DELETE ---
    $(document).on('click', '.btn_delete', function () {
        const href = $(this).data('href');
        $("#deleteMedhisForm").attr("action", href);
        openModal(document.getElementById("modalDelete"));
    });


    // --- MODAL HANDLER ---
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


    // --- RESET FORM DAN VALIDASI SAAT MODAL TUTUP ---
    $('#modalTambah').on('click', '[data-modal-close]', function () {
        $('#addMedhisForm')[0].reset();
        resetValidation('#add_name', '.name-feedback', '#add_submitBtn');
    });

    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.target.classList.contains('flex')) {
                if (mutation.target.id === 'modalTambah') {
                    $('#addMedhisForm')[0].reset();
                    resetValidation('#add_name', '.name-feedback', '#add_submitBtn');
                    validateInput('#add_name', '#add_submitBtn', '.name-feedback', '', '', '#add_deskripsi', '');
                }
            }
        });
    });

    const modalTambah = document.getElementById('modalTambah');
    if (modalTambah) {
        observer.observe(modalTambah, { attributes: true, attributeFilter: ['class'] });
    }
    validateInput('#edit_name', '#edit_submitBtn', '.edit-name-feedback', originalName, originalId, '#edit_deskripsi', originalDesc);
    loadTableData(1);
};


// Initialize page
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupMedisPage);
} else {
    setupMedisPage();
}
