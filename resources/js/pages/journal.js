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

// --- INIT SCRIPT --- 
const setupJournalPage = () => {
    const config = window.journalConfig;
    const tableEl = document.getElementById("table-journal");

    if (!config || !tableEl || typeof window.$ === "undefined") return;
    const $ = window.$;
    let currentPage = 1;
    let pageLength = 25;
    let totalRecords = 0;
    let filteredRecords = 0;

    // --- UPDATE CRSF TOKEN ---
    const updateCsrf = (newToken) => {
        if (!newToken) return;
        config.csrfHash = newToken;
        $("meta[name='csrf-token']").attr("content", newToken);
        $(`input[name='${config.csrfName}']`).val(newToken);
    };

    // --- UPDATE PAGINATION ---
    const updatePaginationInfo = () => {
        if (filteredRecords <= 0) {
            $("#journalPaginationInfo").text("Menampilkan 0 sampai 0 dari 0 data");
            return;
        }
        const start = (currentPage - 1) * pageLength + 1;
        const end = Math.min(currentPage * pageLength, filteredRecords);
        $("#journalPaginationInfo").text("Menampilkan " + start + " sampai " + end + " dari " + filteredRecords + " data");
    };

    const updatePaginationUI = () => {
        const totalPages = Math.max(1, Math.ceil(filteredRecords / pageLength));
        const container = $("#journalPaginationNumbers");
        container.empty();

        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            container.append('<button class="journal-pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400" data-page="1">1</button>');
            if (startPage > 2) {
                container.append('<span class="px-1 text-slate-300">...</span>');
            }
        }

        for (let pageNum = startPage; pageNum <= endPage; pageNum += 1) {
            const activeClass = pageNum === currentPage
                ? "bg-teal-600 border-teal-600 text-white font-semibold shadow-md shadow-teal-600/30"
                : "border border-slate-300 bg-white text-slate-700 font-semibold transition hover:bg-slate-100 hover:border-slate-400";
            container.append('<button class="journal-pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg ' + activeClass + ' text-xs" data-page="' + pageNum + '">' + pageNum + '</button>');
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                container.append('<span class="px-1 text-slate-300">...</span>');
            }
            container.append('<button class="journal-pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400" data-page="' + totalPages + '">' + totalPages + '</button>');
        }

        $("#journalPaginationPrev").prop("disabled", currentPage <= 1);
        $("#journalPaginationNext").prop("disabled", currentPage >= totalPages);
    };


    // --- LOAD DAN RENDER DATA TABLE ---
    const renderEmptyState = (message) => {
        const icon = '<i class="fas fa-inbox mr-2 text-slate-300"></i>';
        $("#table-journal tbody").html('<tr class="hover:bg-slate-50 transition"><td colspan="9" class="px-6 py-12 text-center text-slate-400 italic text-sm">' + icon + message + '</td></tr>');
        $("#mobile-journal-container").html('<div class="p-12 text-center text-slate-400 italic text-sm">' + icon + message + '</div>');
    };
    const loadTableData = (pageNumber) => {
        if (!pageNumber) pageNumber = 1;
        const searchValue = $('#customSearch').val() || '';
        const region = $('#region').val() || '';
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
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
                    // updateCsrf(response.new_token);
                    config.csrfHash = response.new_token;
                    $('input[name="' + config.csrfName + '"]').val(response.new_token);
                }

                currentPage = pageNumber;
                totalRecords = Number(response.recordsTotal || 0);
                filteredRecords = Number(response.recordsFiltered || totalRecords);

                const tbody = $("#table-journal tbody");
                const mContainer = $("#mobile-journal-container");
                tbody.empty();
                mContainer.empty();

                if (!response.data || response.data.length === 0) {
                    renderEmptyState("Data jurnal pemeriksaan belum tersedia");
                    updatePaginationInfo();
                    updatePaginationUI();
                    return;
                }
                response.data.forEach(function (row) {
                    const isKejantanan = row.kejantanan === 'ya';
                    const rowClass = isKejantanan
                        ? 'hover:bg-blue-200 transition border-b border-blue-200 bg-blue-100'
                        : 'hover:bg-slate-50 transition border-b border-slate-100';
                    const tr = $('<tr class="' + rowClass + '"></tr>');

                    // 1. POLESAN BADGE STATUS
                    let statusBadge = '-';
                    if (row.status === 'Pasien Baru') {
                        statusBadge = '<span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Pasien Baru</span>';
                    } else if (row.status === 'Pasien Lama') {
                        statusBadge = '<span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">Pasien Lama</span>';
                    } else {
                        statusBadge = row.status || '-';
                    }

                    let typeBadge = '';
                    if (row.type === 'draft') {
                        typeBadge = '<span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20"><i class="fas fa-pencil-alt text-[10px]"></i> Draft</span>';
                    } else if (row.type === 'posted') {
                        typeBadge = '<span class="inline-flex items-center gap-1 rounded-md bg-teal-50 px-2 py-1 text-xs font-medium text-teal-700 ring-1 ring-inset ring-teal-600/20"><i class="fas fa-check text-[10px]"></i> Diterbitkan</span>';
                    } else {
                        typeBadge = '<span class="text-slate-400 text-xs">-</span>';
                    }

                    let hasilPemeriksaan = '-';
                    if (row.result_names && row.result_names !== '-' && row.result_names.trim() !== '') {
                        hasilPemeriksaan = '<div class="max-w-37.5 truncate" title="' + row.result_names + '">' + row.result_names + '</div>';
                    }

                    let tindakan = '-';
                    if (row.measures && row.measures !== '-') {
                        tindakan = '<div class="max-w-50 truncate cursor-help" title="' + row.measures + '">' + row.measures + '</div>';
                    }

                    // --- RENDER DESKTOP TABLE ---
                    tr.append('<td class="px-6 py-3.5 text-center text-xs text-slate-500">' + (row.no || "-") + '</td>');
                    tr.append('<td class="px-6 py-3.5 text-xs text-slate-600 font-medium">' + (row.tanggal || "-") + '</td>');
                    tr.append('<td class="px-6 py-3.5 font-semibold text-slate-800 text-xs uppercase">' + (row.nama || "-") + '</td>');
                    tr.append('<td class="px-6 py-3.5 text-center">' + statusBadge + '</td>');
                    tr.append('<td class="px-6 py-3.5 text-center">' + typeBadge + '</td>');
                    tr.append('<td class="px-6 py-3.5 text-xs text-slate-500 max-w-37.5 truncate" title="' + (row.alamat || "-") + '">' + (row.alamat || "-") + '</td>');
                    tr.append('<td class="px-6 py-3.5 text-xs text-slate-600">' + hasilPemeriksaan + '</td>');
                    tr.append('<td class="px-6 py-3.5 text-xs text-slate-600">' + tindakan + '</td>');
                    tr.append('<td class="px-6 py-3.5 text-center">' + (row.action || "-") + '</td>');
                    tbody.append(tr);

                    // --- RENDER MOBILE CARDS ---
                    const cardHtml = `
                        <div class="p-4 space-y-4 bg-white">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3 flex-1 min-w-0">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-400 font-mono text-sm border border-slate-200 shadow-sm mt-0.5">
                                        #${row.no}
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="text-[14px] font-black text-slate-900 leading-tight uppercase tracking-tight truncate">${row.nama}</span>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">${row.tanggal}</span>
                                            ${statusBadge}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-50/50 rounded-xl p-3 border border-slate-100 space-y-3">
                                <div class="flex flex-col gap-1">
                                    <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest">Hasil Pemeriksaan</span>
                                    <div class="text-[11px] text-slate-700 font-black line-clamp-2">${row.result_names || 'Belum diperiksa'}</div>
                                </div>
                                <div class="flex flex-col gap-1 border-t border-slate-100 pt-2">
                                    <span class="text-[9px] text-slate-400 font-black uppercase tracking-widest">Alamat</span>
                                    <div class="text-[11px] text-slate-700 font-black truncate">${row.alamat || '-'}</div>
                                </div>
                            </div>

                            <div class="pt-1">
                                <div class="w-full flex items-center justify-center gap-2 [&>a]:flex-1 [&>a]:flex [&>a]:items-center [&>a]:justify-center [&>a]:h-10 [&>a]:rounded-xl [&>a]:text-xs [&>a]:font-bold shadow-sm">
                                    ${row.action || ""}
                                </div>
                            </div>
                        </div>
                    `;
                    mContainer.append(cardHtml);
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

    // --- EVENT LISTENER ---
    const initEvents = () => {
        // --- SELECT REGION ---
        if ($.fn.select2) {
            $('#export_region').select2({
                width: '100%',
                dropdownParent: $("#modalExportJournal")
            });
        }

        // --- SEARCH INPUT ---
        let searchTimer;
        $('#customSearch').on('keyup', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                currentPage = 1;
                loadTableData(1);
            }, 800);
        });

        $('#region, #start_date, #end_date').on('change', function () {
            currentPage = 1;
            loadTableData(1);
        });

        $('#journalPaginationLength').on('change', function () {
            pageLength = parseInt($(this).val(), 10);
            currentPage = 1;
            loadTableData(1);
        });

        // --- EVENT LISTENER PAGINATION ---
        $(document).on('click', '.journal-pagination-btn', function () {
            const pageNum = parseInt($(this).data('page'), 10);
            if (!isNaN(pageNum)) {
                loadTableData(pageNum);
            }
        });

        $('#journalPaginationPrev').on('click', function () {
            if (currentPage > 1) {
                loadTableData(currentPage - 1);
            }
        });

        $('#journalPaginationNext').on('click', function () {
            const totalPages = Math.max(1, Math.ceil(filteredRecords / pageLength));
            if (currentPage < totalPages) {
                loadTableData(currentPage + 1);
            }
        });


        // --- RESET FILTER ---
        $('#btn-reset').on('click', function () {
            const today = new Date().toISOString().split('T')[0];
            $('#customSearch').val('');
            $('#start_date').val(today);
            $('#end_date').val(today);
            $('#region').val('').trigger('change');
            currentPage = 1;
            pageLength = 25;
            $('#journalPaginationLength').val(25);
            loadTableData(1);
        });

        // --- MODAL EXPORT ---
        $('#btnOpenExport').on('click', function (e) {
            e.preventDefault();
            if ($('#export_region').length && $('#region').length) {
                $('#export_region').val($('#region').val()).trigger('change');
            }
            openModal(document.getElementById('modalExportJournal'));
        });

        $('.btn-close-modal').on('click', function (e) {
            e.preventDefault();
            closeModal(document.getElementById('modalExportJournal'));
        });

        $(document).on('click', '.modal-wrapper.flex', function (e) {
            if (e.target === this) {
                closeModal(this);
            }
        });

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                const visibleModal = document.querySelector('.modal-wrapper.flex');
                if (visibleModal) {
                    closeModal(visibleModal);
                }
            }
        });

        // --- DATE PICKER ---
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
    initEvents();
    loadTableData(1);
};

// Jalankan inisialisasi saat DOM sudah siap
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupJournalPage);
} else {
    setupJournalPage();
}