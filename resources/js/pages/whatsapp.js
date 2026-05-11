/**
 * WhatsApp API Management Page Script
 * Handles DataTables initialization and CRUD modal operations
 */

//  --- GLOBAL HELPER FUNCTIONS ---
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


window.openModal = (modal) => {
    if (!modal) return;
    modal.classList.remove("hidden");
    modal.classList.add("flex");
    setTimeout(() => {
        modal.classList.remove("opacity-0");
        modal.classList.add("opacity-100");
        const content = modal.querySelector('.transform');
        if (content) {
            content.classList.remove("scale-95");
            content.classList.add("scale-100");
        }
    }, 20);
};

window.closeModal = (modal) => {
    if (!modal) return;
    modal.classList.remove("opacity-100");
    modal.classList.add("opacity-0");
    const content = modal.querySelector('.transform');
    if (content) {
        content.classList.remove("scale-100");
        content.classList.add("scale-95");
    }
    setTimeout(() => {
        modal.classList.remove("flex");
        modal.classList.add("hidden");
    }, 300);
};

//  --- MAIN PAGE SETUP FUNCTION ---
const setupWhatsappPage = () => {
    const config = window.waConfig;
    if (!config || typeof window.$ === "undefined") return;
    const $ = window.$;
    let tableInstance = null;
    let currentRecordId = null;


    // --- INISIALISASI DATATABLES ---
    const initTable = () => {
        if ($.fn.DataTable.isDataTable('#table-wa')) {
            $('#table-wa').DataTable().destroy();
        }
        tableInstance = $('#table-wa').DataTable({
            responsive: false,
            autoWidth: false,
            order: [[0, 'asc']]
        });
    };

    // --- SETUP EVENT LISTENERS ---
    const initEvents = () => {

        // --- ADD BUTTON ---
        $('#btn-add-wa').on('click', function () {
            window.openModal(document.getElementById('addModal'));
        });

        // --- DELETE BUTTON ---
        $('#table-wa tbody').on('click', '.btn-delete', function () {
            const id = $(this).data('id');
            const actionUrl = `${config.baseUrl}/delete/${id}`;
            $('#deleteForm').attr('action', actionUrl);
            window.openModal(document.getElementById('deleteModal'));
        });

        // --- EDIT BUTTON ---
        $('#table-wa tbody').on('click', '.btn-edit', function () {
            const button = $(this);
            const id = button.data('id');
            $('#editUrlApi').val(button.data('url'));
            $('#editInstanceId').val(button.data('instance'));
            $('#editToken').val(button.data('token'));
            $('#editMessageTemplate').val(button.data('message'));
            const actionUrl = `${config.baseUrl}/update/${id}`;
            $('#editForm').attr('action', actionUrl);

            window.openModal(document.getElementById('editModal'));
        });
    };

    // --- EKSEKUSI FUNGSI ---
    initTable();
    initEvents();
};

// --- AUTO-RUN ---
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupWhatsappPage);
} else {
    setupWhatsappPage();
}