/**
 * Journal Management Page Script
 * Custom pagination implementation
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
    document.body.classList.add('overflow-hidden');
};

const closeModal = (modal) => {
    if (!modal) return;
    modal.classList.remove(MODAL_VISIBLE_CLASS);
    modal.classList.add(MODAL_HIDDEN_CLASS);
    document.body.classList.remove('overflow-hidden');
};

const setupJournalPage = () => {
    const config = window.journalConfig;
    const tableEl = document.getElementById("table-journal");

    if (!config || !tableEl || typeof window.$ === "undefined") return;

    const $ = window.$;

    let currentPage = 1;
    let pageLength = 25;
    let totalRecords = 0;
    let filteredRecords = 0;

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
        $("#paginationInfo").text("Menampilkan " + start + " sampai " + end + " dari " + filteredRecords + " data");
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
            const activeClass = pageNum === currentPage
                ? "bg-teal-600 border-teal-600 text-white font-semibold shadow-md shadow-teal-600/30"
                : "border border-slate-300 bg-white text-slate-700 font-semibold transition hover:bg-slate-100 hover:border-slate-400";
            container.append('<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg ' + activeClass + ' text-xs" data-page="' + pageNum + '">' + pageNum + '</button>');
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                container.append('<span class="px-1 text-slate-300">...</span>');
            }
            container.append('<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400" data-page="' + totalPages + '">' + totalPages + '</button>');
        }

        $("#paginationPrev").prop("disabled", currentPage <= 1);
        $("#paginationNext").prop("disabled", currentPage >= totalPages);
    };

    const renderEmptyState = (message) => {
        $("#table-journal tbody").html('<tr class="hover:bg-slate-50 transition"><td colspan="8" class="px-6 py-12 text-center text-slate-400 italic text-sm"><i class="fas fa-inbox mr-2 text-slate-300"></i>' + message + '</td></tr>');
    };

    const loadTableData = (pageNumber) => {
        if (!pageNumber) pageNumber = 1;
        
        const searchValue = $('#customSearch').val() || '';
        const region = $('#region').val() || '';
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();

        // Show loading
        renderEmptyState('<i class="fas fa-spinner fa-spin mr-2 text-teal-500"></i> Memuat data...');

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
                region: region,
                start_date: startDate,
                end_date: endDate
            },
            success: function (response) {
                if (response.new_token) {
                    updateCsrf(response.new_token);
                }

                currentPage = pageNumber;
                totalRecords = Number(response.recordsTotal || 0);
                filteredRecords = Number(response.recordsFiltered || totalRecords);

                const tbody = $("#table-journal tbody");
                tbody.empty();

                if (!response.data || response.data.length === 0) {
                    renderEmptyState("Data jurnal pemeriksaan belum tersedia");
                    updatePaginationInfo();
                    updatePaginationUI();
                    return;
                }

                response.data.forEach(function (row) {
                    const tr = $('<tr class="hover:bg-slate-50 transition border-b border-slate-100"></tr>');
                    tr.append('<td class="px-6 py-3.5 text-center text-xs text-slate-500">' + (row.no || "-") + '</td>');
                    tr.append('<td class="px-6 py-3.5 text-xs text-slate-600 font-medium">' + (row.tanggal || "-") + '</td>');
                    tr.append('<td class="px-6 py-3.5 font-semibold text-slate-800 text-xs uppercase">' + (row.nama || "-") + '</td>');
                    tr.append('<td class="px-6 py-3.5 text-center text-xs font-semibold">' + (row.status || "-") + '</td>');
                    tr.append('<td class="px-6 py-3.5 text-xs text-slate-500 max-w-xs truncate">' + (row.alamat || "-") + '</td>');
                    tr.append('<td class="px-6 py-3.5 text-xs text-slate-600">' + (row.result_names || "-") + '</td>');
                    tr.append('<td class="px-6 py-3.5 text-xs text-slate-600">' + (row.measures || "-") + '</td>');
                    tr.append('<td class="px-6 py-3.5 text-center">' + (row.action || "-") + '</td>');
                    tbody.append(tr);
                });

                updatePaginationInfo();
                updatePaginationUI();
            },
            error: function () {
                renderEmptyState("Gagal memuat data jurnal pemeriksaan");
                filteredRecords = 0;
                updatePaginationInfo();
                updatePaginationUI();
            }
        });
    };

    // Event Listeners
    const initEvents = () => {
        // Initialize Select2 jika diperlukan
        if ($.fn.select2) {
            $('#export_region').select2({
                width: '100%',
                dropdownParent: $("#modalExportJournal")
            });
        }

        // Search input
        $('#customSearch').on('keyup', function () {
            currentPage = 1;
            loadTableData(1);
        });

        // Search button
        $('#btn-search').on('click', function() {
            currentPage = 1;
            loadTableData(1);
        });

        // Filter perubahan
        $('#region, #start_date, #end_date').on('change', function () {
            currentPage = 1;
            loadTableData(1);
        });

        // Pagination length
        $('#paginationLength').on('change', function () {
            pageLength = parseInt($(this).val(), 10);
            currentPage = 1;
            loadTableData(1);
        });

        // Pagination buttons
        $(document).on('click', '.pagination-btn', function () {
            const pageNum = parseInt($(this).data('page'), 10);
            if (!isNaN(pageNum)) {
                loadTableData(pageNum);
            }
        });

        $('#paginationPrev').on('click', function () {
            if (currentPage > 1) {
                loadTableData(currentPage - 1);
            }
        });

        $('#paginationNext').on('click', function () {
            const totalPages = Math.max(1, Math.ceil(filteredRecords / pageLength));
            if (currentPage < totalPages) {
                loadTableData(currentPage + 1);
            }
        });

        // Reset filter
        $('#btn-reset').on('click', function () {
            $('#customSearch').val('');
            $('#start_date').val('');
            $('#end_date').val('');
            $('#region').val('').trigger('change');
            currentPage = 1;
            pageLength = 25;
            $('#paginationLength').val(25);
            loadTableData(1);
        });

        // Buka Modal Export
        $('#btnOpenExport').on('click', function (e) {
            e.preventDefault();
            if ($('#export_region').length && $('#region').length) {
                $('#export_region').val($('#region').val()).trigger('change');
            }
            openModal(document.getElementById('modalExportJournal'));
        });

        // Tutup Modal Export
        $('.btn-close-modal').on('click', function (e) {
            e.preventDefault();
            closeModal(document.getElementById('modalExportJournal'));
        });

        // Klik di luar modal untuk menutup
        $(document).on('click', '.modal-wrapper.flex', function(e) {
            if (e.target === this) {
                closeModal(this);
            }
        });

        // Tombol Escape untuk menutup modal
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                const visibleModal = document.querySelector('.modal-wrapper.flex');
                if (visibleModal) {
                    closeModal(visibleModal);
                }
            }
        });

        // Period picker di modal export
        $('#period_picker').on('change', function () {
            const period = $(this).val();
            const today = new Date();
            let start = new Date();
            let end = new Date();

            if (period === 'yesterday') {
                start.setDate(today.getDate() - 1);
                end.setDate(today.getDate() - 1);
            } else if (period === 'last_month') {
                start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                end = new Date(today.getFullYear(), today.getMonth(), 0);
            } else if (period === 'last_year') {
                start = new Date(today.getFullYear() - 1, 0, 1);
                end = new Date(today.getFullYear() - 1, 11, 31);
            } else if (period === 'all') {
                start = new Date(2000, 0, 1);
                end = today;
            }

            if (period === 'custom') {
                $('#custom_date_container').slideDown(300);
                $('#exp_start_date').val('').prop('required', true);
                $('#exp_end_date').val('').prop('required', true);
            } else {
                $('#custom_date_container').slideUp(300);
                const startDateString = start.toISOString().split('T')[0];
                const endDateString = end.toISOString().split('T')[0];
                $('#exp_start_date').val(startDateString).prop('required', false);
                $('#exp_end_date').val(endDateString).prop('required', false);
            }
        });
    };

    // Jalankan semua setup
    initEvents();
    loadTableData(1);
};

// Jalankan inisialisasi saat DOM sudah siap
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupJournalPage);
} else {
    setupJournalPage();
}