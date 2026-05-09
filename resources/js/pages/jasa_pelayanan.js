/**
 * Pelayanan Management Page Script (Reguler & Kejantanan)
 * Custom pagination, DataTables Server-Side, and Global Helpers
 */

// ==========================================
// 1. GLOBAL HELPERS
// ==========================================
const MODAL_VISIBLE_CLASS = "flex";
const MODAL_HIDDEN_CLASS = "hidden";

// Mengambil CSRF Token secara dinamis
const getCsrfPayload = (config) => {
    let payload = {};
    payload[config.csrfName] = config.csrfHash;
    return payload;
};

// Fungsi Debounce (Untuk pencarian manual jika diperlukan)
const debounce = (fn, delay = 400) => {
    let timerId;
    return (...args) => {
        clearTimeout(timerId);
        timerId = setTimeout(() => fn(...args), delay);
    };
};

// Modal Controls
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


// ==========================================
// 2. MAIN SETUP FUNCTION
// ==========================================
const setupPelayananPage = () => {
    const config = window.pelayananConfig;
    const page = document.getElementById("pelayananPage");
    
    // Cegah script berjalan di halaman lain
    if (!config || !page || typeof window.$ === "undefined") return;

    const $ = window.$;
    const swalLib = window.Swal || window.swal;

    // Inject Custom Style DataTables agar menyatu dengan Tailwind
    const injectStyle = () => {
        const style = document.createElement('style');
        style.innerHTML = `
            .dataTables_filter input { border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 0.35rem 0.75rem; height: 2.25rem; font-size: 0.875rem; outline: none; transition: all 0.2s; }
            .dataTables_filter input:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #c7d2fe; }
            .dataTables_length select { border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 0.25rem 2rem 0.25rem 0.75rem; height: 2.25rem; font-size: 0.875rem; outline: none; }
            .dataTables_length select:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #c7d2fe; }
        `;
        document.head.appendChild(style);
    };
    injectStyle();

    // Inisialisasi DataTables Server-Side
    const tablePelayanan = $('#tablePelayanan').DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "autoWidth": false,
        "language": {
            "search": "Cari Pasien/Terapis:",
            "lengthMenu": "Tampilkan _MENU_ data",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "infoEmpty": "Tidak ada data tersedia",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        },
        "ajax": {
            "url": config.fetchUrl,
            "type": "POST",
            "data": function (d) {
                // Injeksi Token CSRF dan Kategori Jasa (Reguler/Kejantanan)
                d[config.csrfName] = config.csrfHash;
                d.kategori = config.kategori;
            },
            "dataSrc": function (json) {
                // Auto-renew token CSRF dari backend
                if (json.csrfHash) {
                    config.csrfHash = json.csrfHash;
                }
                return json.data;
            }
        },
        "columns": [
            { "data": "no", "orderable": false, "searchable": false, "className": "p-4 pl-0" },
            { "data": "tanggal_layanan", "className": "p-4 font-bold text-slate-800" },
            { "data": "nama_pasien", "className": "p-4 uppercase" },
            { "data": "nama_terapis", "className": "p-4 uppercase" },
            { "data": "action", "orderable": false, "searchable": false, "className": "p-4 text-center pr-0" }
        ],
        "createdRow": function (row) {
            $(row).addClass('border-b border-slate-50 hover:bg-slate-50/50 transition-colors');
        }
    });

    // ==========================================
    // 3. ACTION HANDLERS (Terpasang ke Window)
    // ==========================================
    
    window.destroy = (id) => {
        swalLib.fire({
            title: 'Hapus data layanan ini?',
            text: "Data yang dihapus (soft delete) tidak akan tampil lagi di tabel.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-3xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                
                // Gunakan Helper CSRF!
                let postData = getCsrfPayload(config);

                $.ajax({
                    url: config.deleteUrl + id,
                    type: "POST",
                    data: postData,
                    dataType: "json",
                    success: function(response) {
                        if (response.new_token) {
                            config.csrfHash = response.new_token;
                        }

                        if (response.status) {
                            swalLib.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: response.message,
                                confirmButtonColor: '#4f46e5',
                                customClass: { popup: 'rounded-3xl' }
                            });
                            // Refresh tabel tanpa reload halaman
                            tablePelayanan.ajax.reload(null, false);
                        } else {
                            swalLib.fire('Gagal', 'Terjadi kesalahan saat menghapus data.', 'error');
                        }
                    },
                    error: function() {
                        swalLib.fire('Error', 'Gagal terhubung ke server.', 'error');
                    }
                });
            }
        });
    };
};

document.addEventListener("DOMContentLoaded", setupPelayananPage);