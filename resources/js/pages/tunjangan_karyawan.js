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
    const firstInput = modal.querySelector('input[type="text"]');
    if (firstInput) setTimeout(() => firstInput.focus(), 100);
};

const closeModal = (modal) => {
    if (!modal) return;
    modal.classList.remove(MODAL_VISIBLE_CLASS);
    modal.classList.add(MODAL_HIDDEN_CLASS);
};


const setupMasterTunjanganPage = () => {
    const config = window.masterTunjanganConfig;
    const page = document.getElementById("masterTunjanganPage");
    if (!config || !page || typeof window.$ === "undefined") return;

    const $ = window.$;
    const swalLib = window.Swal || window.swal;
    const modalEl = document.getElementById('modalTunjangan');
    const formEl = document.getElementById('formTunjangan');
    const btnSimpan = document.getElementById('btnSimpan');
    const modalTitle = document.getElementById('modalTitle');
    const btnCloseModalArr = document.querySelectorAll('.btn-close-modal');

    const updateCSRF = (newHash) => {
        if (newHash) {
            config.csrfHash = newHash;
            $('input[name="' + config.csrfName + '"]').val(newHash); // Update hidden input di form
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': newHash } }); // Update Header
        }
    };
    
    updateCSRF(config.csrfHash);

    let tableTunjangan = $('#tableTunjangan').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: config.urlFetch,
            type: "POST"
        },
        columns: [
            { data: 'no', className: 'p-4 text-center' },
            { data: 'nama_tunjangan', className: 'p-4 font-medium text-slate-800' },
            { data: 'kategori', className: 'p-4' },
            { 
                data: 'id', 
                className: 'p-4 text-center',
                render: function(data, type, row) {
                    // Extract text dari tag HTML span untuk data atribut
                    const kategoriText = $(row.kategori).text().toLowerCase(); 
                    return `
                        <div class="flex justify-center gap-2">
                            <button class="btn-edit p-2 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition" 
                                    data-id="${data}" data-nama="${row.nama_tunjangan}" data-kategori="${kategoriText}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            <button class="btn-delete p-2 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition" 
                                    data-id="${data}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        language: {
            emptyTable: "Belum ada master tunjangan yang didaftarkan.",
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data"
        }
    });

    // --- Event Listeners : Tambah & Tutup Modal ---
    document.getElementById('btnTambahTunjangan').addEventListener('click', () => {
        formEl.reset();
        document.getElementById('id_tunjangan').value = '';
        modalTitle.textContent = 'Tambah Tunjangan';
        openModal(modalEl);
    });

    btnCloseModalArr.forEach(btn => {
        btn.addEventListener('click', () => closeModal(modalEl));
    });

    // --- Event Delegation : Edit & Delete ---
    // Menggunakan jQuery .on() karena tombol di-generate dinamis oleh DataTables
    $('#tableTunjangan tbody').on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const kategori = $(this).data('kategori');

        formEl.reset();
        document.getElementById('id_tunjangan').value = id;
        document.getElementById('nama_tunjangan').value = nama;
        document.getElementById('kategori').value = kategori;
        
        modalTitle.textContent = 'Edit Tunjangan';
        openModal(modalEl);
    });

    $('#tableTunjangan tbody').on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        
        swalLib.fire({
            title: 'Hapus Data?',
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${config.urlDelete}/${id}`,
                    type: "DELETE",
                    dataType: "JSON",
                    success: function(response) {
                        updateCSRF(response.csrfHash);
                        tableTunjangan.ajax.reload(null, false);
                        swalLib.fire('Terhapus!', response.message, 'success');
                    },
                    error: function(xhr) {
                        let res = xhr.responseJSON;
                        if(res && res.csrfHash) updateCSRF(res.csrfHash);
                        swalLib.fire('Gagal!', res?.message || 'Data tidak dapat dihapus.', 'error');
                    }
                });
            }
        });
    });

    // --- Form Submission (Store/Update) ---
    $(formEl).on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const originalBtnText = btnSimpan.innerHTML;

        // UI Feedback: Loading state
        btnSimpan.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menyimpan...';
        btnSimpan.disabled = true;

        $.ajax({
            url: config.urlStore,
            type: "POST",
            data: formData,
            dataType: "JSON",
            success: function(response) {
                closeModal(modalEl);
                updateCSRF(response.csrfHash);
                tableTunjangan.ajax.reload(null, false);
                swalLib.fire('Berhasil!', response.message, 'success');
                
                // Reset Button
                btnSimpan.innerHTML = originalBtnText;
                btnSimpan.disabled = false;
            },
            error: function(xhr) {
                // Reset Button
                btnSimpan.innerHTML = originalBtnText;
                btnSimpan.disabled = false;
                
                let res = xhr.responseJSON;
                if(res && res.csrfHash) updateCSRF(res.csrfHash);
                
                let errorMsg = 'Terjadi kesalahan sistem.';
                if (res && res.errors) {
                    errorMsg = Object.values(res.errors)[0]; // Ambil error validasi pertama dari Model
                } else if (res && res.message) {
                    errorMsg = res.message;
                }
                swalLib.fire('Gagal!', errorMsg, 'error');
            }
        });
    });
};

// --- EKSEKUSI SCRIPT KETIKA DOM SELESAI DILOAD ---
document.addEventListener("DOMContentLoaded", setupMasterTunjanganPage);