/**
 * Terapis Management Page Script
 * Handles CRUD operations for terapis data with custom pagination
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

const setupTerapisPage = () => {
    const config = window.terapisConfig;
    const page = document.getElementById("terapisPage");

    if (!config || !page || typeof window.$ === "undefined") return;

    const $ = window.$;
    const swalLib = window.Swal || window.swal;

    let currentPage = 1;
    let pageLength = 25;
    let totalRecords = 0;
    let filteredRecords = 0;
    let searchValue = "";
    let statusUrl = null;
    let statusType = null;

    // Initialize Select2
    const initSelect2 = () => {
        $('#region_id, #jabatan_id').select2({
            width: '100%',
            dropdownParent: $('#modalTambah')
        });
    };

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

    const renderEmptyState = (message) => {
        $("#table-terapis tbody").html(`<tr class="hover:bg-slate-50 transition"><td colspan="7" class="px-6 py-12 text-center text-slate-400 italic text-sm"><i class="fas fa-inbox mr-2 text-slate-300"></i>${message}</td></tr>`);
    };

    const loadTableData = (pageNumber = 1) => {
        const regionFilter = $('#region_filter').val();

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
                region: regionFilter
            },
            success: (response) => {
                if (response.csrfHash) {
                    updateCsrf(response.csrfHash);
                }

                currentPage = pageNumber;
                totalRecords = Number(response.recordsTotal || 0);
                filteredRecords = Number(response.recordsFiltered || totalRecords);

                const tbody = $("#table-terapis tbody");
                tbody.empty();

                if (!response.data || response.data.length === 0) {
                    renderEmptyState("Data terapis belum tersedia");
                    updatePaginationInfo();
                    updatePaginationUI();
                    return;
                }

                response.data.forEach((row) => {
                    const tr = $(`<tr class="hover:bg-slate-50 transition border-b border-slate-100"></tr>`);
                    tr.append(`<td class="px-6 py-3.5">${row.no || "-"}</td>`);
                    tr.append(`<td class="px-6 py-3.5 font-medium text-slate-800">${row.nama || "-"}</td>`);
                    tr.append(`<td class="px-6 py-3.5">${row.region_name || "-"}</td>`);
                    tr.append(`<td class="px-6 py-3.5 text-slate-500 max-w-xs truncate">${row.alamat || "-"}</td>`);
                    tr.append(`<td class="px-6 py-3.5 text-center font-semibold">${row.jml_tindakan || "0"}</td>`);
                    tr.append(`<td class="px-6 py-3.5 text-center">${row.is_active || "-"}</td>`);
                    tr.append(`<td class="px-6 py-3.5 text-center">${row.action || "-"}</td>`);
                    tbody.append(tr);
                });

                updatePaginationInfo();
                updatePaginationUI();
            },
            error: () => {
                renderEmptyState("Gagal memuat data terapis");
                filteredRecords = 0;
                updatePaginationInfo();
                updatePaginationUI();
            },
        });
    };

    // Check ID availability
    const checkTerapisId = (id, feedbackElement, submitBtn) => {
        if (id.length < 7) {
            $(feedbackElement).removeClass('hidden').text('ID terlalu pendek (minimal 7 karakter)').css('color', '#ef4444');
            $(submitBtn).prop('disabled', true);
            return;
        }

        $.ajax({
            url: config.checkIdUrl,
            type: "POST",
            data: {
                terapis_id: id,
                [config.csrfName]: config.csrfHash
            },
            dataType: "json",
            success: function(res) {
                if (res.exists) {
                    $(feedbackElement).removeClass('hidden').text('ID sudah digunakan terapis lain').css('color', '#ef4444');
                    $(submitBtn).prop('disabled', true);
                } else {
                    $(feedbackElement).removeClass('hidden').text('ID tersedia').css('color', '#10b981');
                    $(submitBtn).prop('disabled', false);
                }
            },
            error: function() {
                $(feedbackElement).removeClass('hidden').text('Gagal memeriksa ID').css('color', '#ef4444');
                $(submitBtn).prop('disabled', true);
            }
        });
    };

    // Search handler with debounce
    const searchHandler = debounce((value) => {
        searchValue = value;
        currentPage = 1;
        loadTableData(1);
    }, 400);

    // Event Listeners
    $("#searchInput").on("keyup", function () {
        searchHandler($(this).val());
    });

    $('#region_filter').on('change', function() {
        currentPage = 1;
        loadTableData(1);
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

    // ID validation
    const debouncedCheckId = debounce((id) => {
        checkTerapisId(id, '#formTambahTerapis .id-feedback', '#btnSimpan');
    }, 500);

    $('#terapis_id').on('input', function() {
        const id = $(this).val().trim();
        debouncedCheckId(id);
    });

    // Submit form tambah
    $('#formTambahTerapis').on('submit', function(e) {
        e.preventDefault();

        const form = this;
        const $form = $(form);
        const btn = $('#btnSimpan');

        if (!form.checkValidity()) {
            $form.addClass('was-validated');
            $form.find('.invalid-feedback').removeClass('hidden');
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        const formData = new FormData(form);
        formData.append(config.csrfName, config.csrfHash);

        $.ajax({
            url: $form.attr('action'),
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(res) {
                updateCsrf(res.csrfHash);

                if (res.status === 'success') {
                    closeModal(document.getElementById("modalTambah"));
                    form.reset();
                    $form.removeClass('was-validated');
                    $('#preview').addClass('hidden');
                    $('#btnSimpan').prop('disabled', true);
                    loadTableData(currentPage);

                    if (swalLib?.fire) {
                        swalLib.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        alert(res.message);
                    }
                } else {
                    if (swalLib?.fire) {
                        swalLib.fire('Gagal!', res.message, 'error');
                    } else {
                        alert(res.message);
                    }
                    btn.prop('disabled', false).text('Simpan Data');
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                if (swalLib?.fire) {
                    swalLib.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                } else {
                    alert('Terjadi kesalahan sistem.');
                }
                btn.prop('disabled', false).text('Simpan Data');
            }
        });
    });

    // Status button handler
    $(document).on('click', '.btn_status', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const btn = $(this);
        statusUrl = btn.data('href');
        statusType = btn.data('type');

        const isDelete = (statusType === 'delete');
        const modalTitle = isDelete ? 'Nonaktifkan Terapis?' : 'Aktifkan Terapis?';
        const modalText = isDelete ? 'Terapis ini tidak akan muncul dalam daftar tindakan.' : 'Terapis akan kembali aktif di sistem.';
        const confirmText = isDelete ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan';
        const confirmColor = isDelete ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700';

        $('#modalStatusTitle').text(modalTitle);
        $('#modalStatusText').text(modalText);
        $('#btnConfirmStatus').text(confirmText).removeClass('bg-red-600 bg-blue-600 hover:bg-red-700 hover:bg-blue-700').addClass(confirmColor);

        openModal(document.getElementById("modalStatus"));
    });

    // Confirm status change
    $('#btnConfirmStatus').on('click', function() {
        if (!statusUrl) return;

        const btn = $(this);
        btn.prop('disabled', true).text('Memproses...');

        $.ajax({
            url: statusUrl,
            type: "POST",
            data: {
                [config.csrfName]: config.csrfHash
            },
            dataType: "json",
            success: function(res) {
                if (res.csrfHash) {
                    updateCsrf(res.csrfHash);
                }

                if (res.status === 'success') {
                    closeModal(document.getElementById("modalStatus"));
                    statusUrl = null;
                    loadTableData(currentPage);

                    if (swalLib?.fire) {
                        swalLib.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        alert(res.message);
                    }
                } else {
                    if (swalLib?.fire) {
                        swalLib.fire('Gagal!', res.message, 'error');
                    } else {
                        alert(res.message);
                    }
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                if (swalLib?.fire) {
                    swalLib.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                } else {
                    alert('Terjadi kesalahan sistem.');
                }
            },
            complete: function() {
                btn.prop('disabled', false).text(
                    statusType === 'delete' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'
                );
            }
        });
    });

    // Detail button handler
    $(document).on('click', '.btn_detail_terapis', function() {
        const userId = $(this).data('userid');
        window.location.href = `${config.detailUrl}/${userId}`;
    });

    // Modal handlers
    document.addEventListener("click", (event) => {
        const openTrigger = event.target.closest("[data-modal-open]");
        if (openTrigger) {
            const targetId = openTrigger.getAttribute("data-modal-open");
            const modal = document.getElementById(targetId);
            openModal(modal);

            // Re-initialize Select2 when modal opens
            if (targetId === 'modalTambah') {
                setTimeout(initSelect2, 100);
            }
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

    // Reset form when modal is closed
    $("#modalTambah").on("click", "[data-modal-close]", function() {
        $('#formTambahTerapis')[0].reset();
        $('#formTambahTerapis').removeClass('was-validated');
        $('#formTambahTerapis .invalid-feedback').addClass('hidden');
        $('#formTambahTerapis .id-feedback').addClass('hidden');
        $('#preview').addClass('hidden');
        $('#btnSimpan').prop('disabled', true);
        $('#region_id, #jabatan_id').val(null).trigger('change');
    });

    // Initialize
    initSelect2();
    loadTableData(1);
};

// Initialize page
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupTerapisPage);
} else {
    setupTerapisPage();
}
