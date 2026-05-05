const MODAL_VISIBLE_CLASS = "flex";
const MODAL_HIDDEN_CLASS = "hidden";

const getCsrfPayload = (config) => ({
    [config.csrfName]: config.csrfHash,
});

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

// --- INIT SCRIPT ---
const setupKasPage = () => {
    console.log("%c [JS Check] Kas Module Connected! 🚀", "color: teal; font-weight: bold; font-size: 12px;");

    const config = window.kasConfig;
    const page = document.getElementById("kasPage");
    if (!config || !page || typeof window.$ === "undefined") return;

    const $ = window.$;
    let tables = {};

    // --- COLUMN TABLE CONFIGURATION ---
    const commonColumns = [
        {
            data: "created_at",
            render: (data) => `<span class="text-slate-500">${data}</span>`
        },
        {
            data: "region_name",
            render: (data) => `<span class="font-bold text-slate-700">${data}</span>`
        },
        {
            data: "keterangan",
            render: $.fn.dataTable.render.text()
        },
        {
            data: "nominal",
            className: "text-right font-medium text-slate-900",
            render: (data) => new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(data)
        },
        {
            data: null,
            orderable: false,
            className: "text-center",
            render: function (data, type, row) {
                return `
                <div class="flex justify-center gap-2">
                    <button class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all btn-delete" data-id="${row.id_transaksi}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>`;
            }
        }
    ];

    // --- INIT DATATABLE FUNCTION (Enterprise Setup) ---
    const initDataTable = (id, url, columns) => {
        if (!$(id).length) return null;
        return $(id).DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            deferRender: true,
            ajax: {
                url: url,
                type: "POST",
                data: (d) => ({ ...d, ...getCsrfPayload(config) }),
                dataSrc: (json) => {
                    // Update CSRF Token untuk request selanjutnya
                    if (json.csrfHash) config.csrfHash = json.csrfHash;
                    console.log(`[Data ${id}] Berhasil ditarik:`, json);
                    return json.data || [];
                }
            },
            columns: columns,
            columnDefs: [
                { width: "15%", targets: 0 },
                { width: "15%", targets: 1 },
                { width: "40%", targets: 2 },
                { width: "20%", targets: 3 },
                { width: "10%", targets: 4 }
            ],
            order: [[0, "desc"]],
            language: {
                processing: `
                    <div class="absolute inset-0 z-20 flex items-center justify-center bg-white/70 backdrop-blur-[1px]">
                        <div class="flex items-center gap-3">
                            <div class="h-5 w-5 animate-spin rounded-full border-2 border-slate-200 border-t-teal-500"></div>
                            <span class="text-sm italic text-slate-400 font-medium">Memuat data...</span>
                        </div>
                    </div>
                `,
                paginate: {
                    previous: '<i class="fas fa-chevron-left"></i>',
                    next: '<i class="fas fa-chevron-right"></i>'
                },
                emptyTable: "Belum ada data transaksi untuk kategori ini."
            }
        });
    };

    // --- JALANKAN INISIALISASI ---
    tables.pemasukan = initDataTable("#table-pemasukan", config.urlPemasukan, commonColumns);
    tables.pengeluaran = initDataTable("#table-pengeluaran", config.urlPengeluaran, commonColumns);
    tables.rutinan = initDataTable("#table-pengeluaran-harian", config.urlPengeluaranHarian, commonColumns); // Pastikan ID tabel HTML-nya benar


    // --- KAS TAB SYSTEM (EVENT DRIVEN) ---
    const initTabSystem = (tables) => {
        const tabButtons = document.querySelectorAll('#kas-tabs .tab-btn');
        const tabPanes = document.querySelectorAll('.tab-pane');

        tabButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const targetTab = this.getAttribute('data-tab');
                tabButtons.forEach(b => {
                    b.classList.remove('active', 'text-teal-600', 'border-teal-600');
                    b.classList.add('text-slate-400', 'border-transparent');
                });
                this.classList.add('active', 'text-teal-600', 'border-teal-600');
                this.classList.remove('text-slate-400', 'border-transparent');
                tabPanes.forEach(pane => {
                    pane.classList.add('hidden');
                    pane.classList.remove('block');
                });
                const activePane = document.getElementById(`tab-content-${targetTab}`);
                if (activePane) {
                    activePane.classList.remove('hidden');
                    activePane.classList.add('block');
                }
                if (tables && tables[targetTab]) {
                    setTimeout(() => {
                        tables[targetTab].columns.adjust().draw();
                        tables[targetTab].ajax.reload(null, false);
                    }, 100);
                }
            });
        });
    };
    initTabSystem(tables);

    // --- EVENT HANDLERS ---
    document.querySelectorAll("[data-modal-close]").forEach(btn => {
        btn.addEventListener("click", () => {
            const modal = btn.closest(".modal-wrapper");
            closeModal(modal);
        });
    });

    // $('.table').on('click', '.btn-delete', function() {
    //     const id = $(this).data('id');
    // });
};

document.addEventListener("DOMContentLoaded", setupKasPage);