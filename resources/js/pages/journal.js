// === HELPER FUNCTIONS (Wajib ada di paling atas agar terbaca sistem) ===
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

// Fungsi ini yang tadi hilang/not defined
const openModal = (modal) => {
    if (!modal) return;
    modal.classList.remove(MODAL_HIDDEN_CLASS);
    modal.classList.add(MODAL_VISIBLE_CLASS);
    document.body.classList.add('overflow-hidden');
};

// Fungsi untuk menutup modal
const closeModal = (modal) => {
    if (!modal) return;
    modal.classList.remove(MODAL_VISIBLE_CLASS);
    modal.classList.add(MODAL_HIDDEN_CLASS);
};


// === FUNGSI UTAMA HALAMAN JURNAL ===
const setupJournalPage = () => {
    const config = window.journalConfig;
    const tableEl = document.getElementById("table-journal");

    if (!config || !tableEl || typeof window.$ === "undefined") return;

    const $ = window.$;
    let journalTableInstance = null;

    // --- TABEL UTAMA JURNAL ---
    const loadJournalTable = () => {
        if ($.fn.DataTable.isDataTable('#table-journal')) {
            journalTableInstance.ajax.reload(null, false);
            return;
        }

        journalTableInstance = $('#table-journal').DataTable({
            serverSide: true,
            autoWidth: false,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            order: [],
            ajax: {
                url: config.fetchUrl,
                type: "POST",
                data: function (d) {
                    d[config.csrfName] = config.csrfHash;
                    d.region = $('#region').val() || '';
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                },
                dataSrc: function (json) {
                    if (json.new_token) {
                        config.csrfHash = json.new_token;
                        if (typeof updateCsrf === 'function') updateCsrf(json.new_token);
                    }
                    return json.data;
                }
            },
            dom: '<"overflow-x-auto w-full"t><"flex flex-col md:flex-row items-center justify-between p-6 bg-white border-t border-slate-100 gap-4"<"flex items-center gap-4"li>p><"clear">',
            columns: [
                { data: "no", className: "px-6 py-5 text-center text-xs text-slate-500 font-mono w-16 whitespace-nowrap", sortable: false, searchable: false },
                { data: "tanggal", className: "px-6 py-5 text-xs text-slate-600 font-bold w-32 whitespace-nowrap", searchable: true },
                { data: "nama", className: "px-6 py-5 font-black text-slate-800 text-sm uppercase tracking-tight whitespace-nowrap", searchable: true },
                { data: "status", className: "px-6 py-5 text-center text-xs font-bold w-24 whitespace-nowrap", searchable: false },
                { data: "alamat", className: "px-6 py-5 text-xs text-slate-500 uppercase leading-relaxed min-w-[200px]", searchable: false },
                { data: "result_names", className: "px-6 py-5 text-xs text-slate-600 font-medium whitespace-nowrap", searchable: false },
                { data: "measures", className: "px-6 py-5 text-xs text-slate-600 font-medium whitespace-nowrap", searchable: true },
                { data: "action", className: "px-6 py-5 text-center w-20 whitespace-nowrap", sortable: false, searchable: false }
            ],
            language: {
                search: "_INPUT_",
                lengthMenu: "Tampilkan _MENU_",
                emptyTable: "Belum ada data jurnal ditemukan.",
                info: "Data <span class='font-bold text-slate-800'>_START_</span> - <span class='font-bold text-slate-800'>_END_</span> dari <span class='font-bold text-teal-600'>_TOTAL_</span>",
            },

            // initComplete: function () {
            //     $('.dataTables_filter').addClass('w-full md:w-auto');
            //     $('.dataTables_filter label').addClass('w-full md:w-80 relative flex items-center mb-0');
            //     $('.dataTables_filter input')
            //         .addClass('w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 shadow-sm transition-all outline-none')
            //         .attr('placeholder', 'Cari pasien, jurnal...');
            //     $('.dataTables_filter label').prepend('<i class="fas fa-search absolute left-4 text-slate-400"></i>');
            // },

            drawCallback: function () {
                $('.pagination').addClass('flex flex-row items-center mb-0');
                $('.page-item').addClass('mx-0.5');
                $('.page-link').addClass('px-3 py-1 rounded-lg border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors');
                $('.active .page-link').addClass('bg-teal-600 text-white border-teal-600 hover:bg-teal-700').removeClass('text-slate-600 text-slate-500');
                $('.dataTables_length select').addClass('border border-slate-200 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 bg-white focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 mx-2 cursor-pointer');
            }
        });
    };

    // --- EVENT LISTENERS ---
    const initEvents = () => {
        if ($.fn.select2) {
            // $('region').select2({width: '100%'})
            $('.select2').select2({ width: '100%' });

            $('#export_region').select2({
                width: '100%',
                dropdownParent: $("#modalExportJournal")
            });
        }

        // Dropdown Cabang
        $('#btn-dropdown-region').on('click', function (e) {
            e.preventDefault();
            $('#region').select2('open');
        });

        // Search Pasien 
        $('#customSearch').on('keyup', function () {
            if (journalTableInstance) {
                // Langsung gunakan this.value, DataTables yang akan mengatur delay-nya
                journalTableInstance.search(this.value).draw();
            }
        });

        $('#region').on('select2:select', function (e) {
            const data = e.params.data;
            $('#selected-region-text').text(data.text);
            $('.fa-chevron-down').addClass('rotate-180');
            setTimeout(() => $('.fa-chevron-down').removeClass('rotate-180'), 300);
        });

        if ($('#region').val()) {
            const initialText = $('#region').find(':selected').text();
            $('#selected-region-text').text(initialText);
        }

        // Auto Reload Tabel
        $('#region, #start_date, #end_date').on('change', function () {
            if (journalTableInstance) journalTableInstance.ajax.reload();
        });

        // Reset Filter
        $('#btn-reset').on('click', function () {
            $('#start_date').val('');
            $('#end_date').val('');
            $('#region').val('').trigger('change');
        });

        // MODAL EXPORT: Buka Modal
        $('#btnOpenExport').on('click', function (e) {
            e.preventDefault();
            // Sync filter wilayah saat ini ke form export
            $('#export_region').val($('#region').val()).trigger('change');

            // Buka Modal pakai fungsi Tailwind kita
            openModal(document.getElementById('modalExportJournal'));
        });

        // MODAL EXPORT: Tutup Modal
        $('.btn-close-modal').on('click', function (e) {
            e.preventDefault();
            closeModal(document.getElementById('modalExportJournal'));
            $('body').removeClass('overflow-hidden');
        });

        // Logika Periode Tanggal (Modal Export)
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
    loadJournalTable();
};

// Jalankan Inisialisasi saat DOM sudah siap
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupJournalPage);
} else {
    setupJournalPage();
}