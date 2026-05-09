/**
 * Greeting Management Page Script
 * Handles Form population for editing and resetting
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
const setupGreetingPage = () => {
    const config = window.greetingConfig;
    if (!config || typeof window.$ === "undefined") return;

    const $ = window.$;

    // --- FUNGSI RESET FORM ---
    const resetGreetingForm = () => {
        $('#greetings_input').val("");
        $('#greeting_index').val("");
        $('#form-title').text("Menambah Salam Baru");
        
        // Sembunyikan tombol batal
        $('#btn-cancel').addClass('hidden').removeClass('inline-block');
    };

    // --- SETUP EVENT LISTENERS ---
    const initEvents = () => {
        
        // Event Klik Tombol Edit di List Group
        $(document).on('click', '.btn-edit', function(e) {
            e.preventDefault();
            const btn = $(this);
            
            const index = btn.data('index');
            const text = btn.data('text');

            // Isi Form
            $('#greetings_input').val(text);
            $('#greeting_index').val(index);
            $('#form-title').text("Mengubah Data Salam");
            
            // Munculkan tombol batal
            $('#btn-cancel').removeClass('hidden').addClass('inline-block');

            // Scroll otomatis ke text area & beri fokus
            $('#greetings_input').focus();
            $('html, body').animate({
                scrollTop: $("#form-title").offset().top - 100
            }, 300);
        });

        // Event Klik Tombol Batal
        $('#btn-cancel').on('click', function(e) {
            e.preventDefault();
            resetGreetingForm();
        });
    };

    // ==========================================
    // 3. EKSEKUSI FUNGSI (RUN)
    // ==========================================
    initEvents();
};

// === 4. AUTO-RUN SAAT HALAMAN DIMUAT ===
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupGreetingPage);
} else {
    setupGreetingPage();
}