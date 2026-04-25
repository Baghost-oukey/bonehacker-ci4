/**
 * WhatsApp API Management Page Script
 * Handles DataTables initialization and CRUD modal operations
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
const setupWhatsappPage = () => {
    // Ambil konfigurasi dari View
    const config = window.waConfig;
    
    // Sabuk pengaman: Pastikan jQuery dan Config tersedia
    if (!config || typeof window.$ === "undefined") return;

    const $ = window.$;

    // --- STATE VARIABLES ---
    // (Gunakan let, persis seperti di Terapis)
    let tableInstance = null;
    let currentRecordId = null;

    // --- INISIALISASI DATATABLES ---
    const initTable = () => {
        tableInstance = $('#table-wa').DataTable({
            responsive: true,
            autoWidth: false,
            order: [[0, 'asc']]
        });
    };

    // --- SETUP EVENT LISTENERS ---
    const initEvents = () => {
        
        // Event: Saat Modal Hapus Terbuka (Bootstrap style)
        $('#deleteModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            currentRecordId = button.data('id');

            // Dinamis ubah action URL pada form hapus
            // Support fallback jika masih pakai baseUrl biasa atau sudah pakai deleteBaseUrl
            const actionUrl = config.deleteBaseUrl 
                ? `${config.deleteBaseUrl}/${currentRecordId}` 
                : `${config.baseUrl}/delete/${currentRecordId}`;
                
            $('#deleteForm').attr('action', actionUrl);
        });

        // Event: Saat Modal Edit Terbuka (Bootstrap style)
        $('#editModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            currentRecordId = button.data('id');

            // Ekstrak data dari tombol ke dalam input form
            $('#editUrlApi').val(button.data('url'));
            $('#editInstanceId').val(button.data('instance'));
            $('#editToken').val(button.data('token'));
            $('#editMessageTemplate').val(button.data('message'));

            // Dinamis ubah action URL pada form edit
            const actionUrl = config.editBaseUrl 
                ? `${config.editBaseUrl}/${currentRecordId}` 
                : `${config.baseUrl}/edit/${currentRecordId}`;

            $('#editForm').attr('action', actionUrl);
        });
    };

    // --- EKSEKUSI FUNGSI ---
    initTable();
    initEvents();
};

// === 3. AUTO-RUN ===
// Jalankan script saat halaman HTML selesai dimuat sepenuhnya
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupWhatsappPage);
} else {
    setupWhatsappPage();
}