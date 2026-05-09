/**
 * WhatsApp Logs Management Page Script
 * Handles DataTables initialization and Custom Date Range Filtering
 */

// --- 1. GLOBAL HELPER FUNCTIONS ===
// (Sama persis seperti di modul Terapis & Complaint)
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
const setupWhatsappLogsPage = () => {
    // Ambil konfigurasi (Sesuai dengan pattern kamu)
    const config = window.waLogConfig;
    
    // Validasi dependensi
    if (!config || typeof window.$ === "undefined") return;

    const $ = window.$;
    const swalLib = window.Swal || window.swal;

    // --- STATE VARIABLES ---
    let table = null;

    // --- INISIALISASI DATATABLES ---
    const initTable = () => {
        table = $('#whatsappLogs').DataTable({
            responsive: false, // Dimatikan agar scrollX Tailwind jalan
            scrollX: true,
            autoWidth: false,
            order: [[8, "desc"]], // Default sort berdasarkan Created At
            columnDefs: [
                { orderable: false, targets: [5, 10] }, // Status & Action non-sortable
                { visible: false, targets: [6] }        // Sembunyikan Status Value
            ],
            // Styling DOM untuk Tailwind
            dom: '<"w-full"t><"flex flex-col md:flex-row items-center justify-between p-6 bg-white border-t border-slate-100 gap-4"<"flex items-center gap-4"li>p><"clear">',
            language: {
                search: "_INPUT_",
                lengthMenu: "Tampilkan _MENU_",
                emptyTable: "Belum ada riwayat pesan WhatsApp.",
            },
            initComplete: function () {
                // Modifikasi kotak pencarian bawaan DataTables
                const searchInput = $('.dataTables_filter input');
                searchInput
                    .attr('placeholder', 'Cari log pesan...')
                    .addClass('w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all outline-none block shadow-sm');
                
                searchInput.appendTo($('#search-container'));
                $('.dataTables_filter').hide();
            },
            drawCallback: function () {
                // Styling Pagination ala Tailwind
                $('.pagination').addClass('flex flex-row items-center mb-0');
                $('.page-item').addClass('mx-0.5');
                $('.page-link').addClass('px-3 py-1 rounded-lg border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors');
                $('.active .page-link').addClass('bg-teal-600 text-white border-teal-600 hover:bg-teal-700').removeClass('text-slate-600');
                $('.dataTables_length select').addClass('border border-slate-200 rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 bg-white focus:outline-none focus:border-teal-500 mx-2 cursor-pointer');
            }
        });
    };

    // --- FUNGSI FILTER RENTANG TANGGAL ---
    const initFilters = () => {
        // Daftarkan Custom Filter ke DataTables
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'whatsappLogs') return true;

            const createdAt = new Date(data[8]); // Kolom Created At index 8
            const startDateVal = $('#startDate').val();
            const endDateVal = $('#endDate').val();

            const start = startDateVal ? new Date(startDateVal) : null;
            const end = endDateVal ? new Date(endDateVal) : null;

            if (start) start.setHours(0, 0, 0, 0);
            if (end) end.setHours(23, 59, 59, 999);

            if (
                (!start && !end) || 
                (!start && createdAt <= end) || 
                (start <= createdAt && !end) || 
                (start <= createdAt && createdAt <= end)
            ) {
                return true;
            }
            return false;
        });

        // Event Listeners untuk Input Tanggal
        $('#startDate').on('change', function() {
            const val = $(this).val();
            $('#endDate').val(val); // Auto-fill end date
            if (table) table.draw();
        });

        $('#endDate').on('change', function() {
            if (table) table.draw();
        });
    };

    // --- EKSEKUSI FUNGSI ---
    initTable();
    initFilters();
};

// === 3. AUTO-RUN ===
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupWhatsappLogsPage);
} else {
    setupWhatsappLogsPage();
}