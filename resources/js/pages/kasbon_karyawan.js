/**
 * Kasbon Management Page Script
 * Handles Server-Side Fetching, AJAX Validation (Limit Gaji), and CRUD modals
 */

// === 1. GLOBAL HELPER FUNCTIONS ===
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

const formatRupiah = (angka, prefix = 'Rp ') => {
    if (!angka) return '';
    let number_string = angka.toString().replace(/[^,\d]/g, '');
    let split = number_string.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix + rupiah;
};

const setupKasbonIndexPage = () => {
    const config = window.kasbonIndexConfig;
    if (!config || typeof window.$ === "undefined") return;

    const tableElement = document.getElementById('table-karyawan-kasbon');
    if (tableElement) {
        $(tableElement).DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: config.fetchUrl,
                type: "POST",
                data: function (d) {
                    d[config.csrfName] = config.csrfHash;
                }
            },
            columns: [
                {
                    data: null,
                    className: 'py-4 px-4 border-b border-slate-100 text-center align-middle',
                    render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1
                },
                {
                    data: 'nama',
                    className: 'py-4 px-4 border-b border-slate-100 align-middle',
                    render: (data) => `<span class="items-center px-2.5 py-1 text-[12px] font-bold uppercase text-slate-600">${data}</span>`
                },
                {
                    data: 'jabatan',
                    className: 'py-4 px-4 border-b border-slate-100 text-center align-middle',
                    render: (data) => `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest bg-slate-100 text-slate-600 border border-slate-200">${data}</span>`
                },
                {
                    data: 'gaji_pokok',
                    className: 'py-4 px-4 border-b border-slate-100 text-center align-middle',
                    render: function (data, type, row) {
                        let colorClass = (row.kasbon_raw > 0) ? 'text-rose-500 font-bold' : 'text-slate-400 font-medium';
                        return `
                            <div class="flex flex-col gap-1 text-xs justify-center items-center">
                                <div><span class="text-slate-500 font-medium">Gaji Pokok:</span> <span class="text-slate-700 font-bold">${data}</span></div>
                                <div><span class="text-slate-500 font-medium">Kasbon Aktif:</span> <span class="${colorClass}">${row.kasbon_aktif}</span></div>
                            </div>
                        `;
                    }
                },
                {
                    data: 'id',
                    className: 'py-4 px-4 border-b border-slate-100 text-right align-middle',
                    render: function (data) {
                        let detailUrl = config.fetchUrl.replace('/fetch', '/detail/') + data;
                        return `<a href="${detailUrl}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition text-sm font-medium shadow-md shadow-slate-900/10"><i class="fas fa-wallet text-[10px]"></i> Kelola</a>`;
                    }
                }
            ],
            language: {
                search: "Cari Karyawan:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ karyawan",
                emptyTable: "Belum ada data karyawan aktif.",
                processing: "Memuat data..."
            },

            // ==========================================
            // INJECT TAILWIND CLASS VIA JQUERY (CLEAN WAY)
            // ==========================================
            drawCallback: function () {
                const api = this.api();
                const data = api.rows({ page: 'current' }).data().toArray();

                // RENDER MOBILE CARDS
                const mobileContainer = document.getElementById('mobile-card-container');
                if (mobileContainer) {
                    mobileContainer.innerHTML = '';
                    if (data.length === 0) {
                        mobileContainer.innerHTML = `
                            <div class="text-center py-10 text-slate-400 italic text-sm bg-white rounded-2xl border border-dashed border-slate-200">
                                Belum ada data karyawan aktif.
                            </div>
                        `;
                    } else {
                        data.forEach(row => {
                            let colorClass = (row.kasbon_raw > 0) ? 'text-rose-600 font-black' : 'text-slate-400 font-bold';
                            let detailUrl = config.fetchUrl.replace('/fetch', '/detail/') + row.id;

                            const card = `
                                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col gap-4 animate-in fade-in slide-in-from-bottom-4 duration-300">
                                    <div class="flex justify-between items-start">
                                        <div class="flex flex-col gap-1">
                                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight">${row.nama}</h3>
                                            <span class="inline-flex w-fit items-center px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase tracking-widest bg-slate-100 text-slate-600 border border-slate-200">${row.jabatan}</span>
                                        </div>
                                        <a href="${detailUrl}" class="h-10 w-10 flex items-center justify-center bg-slate-900 text-white rounded-xl shadow-lg shadow-slate-900/20 active:scale-95 transition-all">
                                            <i class="fas fa-wallet text-sm"></i>
                                        </a>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-50">
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Gaji Pokok</span>
                                            <span class="text-xs font-bold text-slate-700">${row.gaji_pokok}</span>
                                        </div>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Kasbon Aktif</span>
                                            <span class="text-xs ${colorClass} text-right">${row.kasbon_aktif}</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                            mobileContainer.insertAdjacentHTML('beforeend', card);
                        });
                    }
                }

                // Wrapper Utama (biar tidak ada error padding karena float)
                $('.dataTables_wrapper').addClass('flow-root');

                // 1. Search & Length (Bagian Atas)
                $('.dataTables_length').addClass('mb-4 text-sm text-slate-500 float-left');
                $('.dataTables_length select').addClass('border border-slate-200 rounded-lg px-2 py-1 mx-2 bg-slate-50 focus:ring-1 focus:ring-slate-900 outline-none cursor-pointer');

                $('.dataTables_filter').addClass('mb-4 text-sm text-slate-500 float-right');
                $('.dataTables_filter input').addClass('border border-slate-200 rounded-lg px-3 py-1 ml-2 bg-slate-50 focus:ring-1 focus:ring-slate-900 outline-none');

                // 2. Info Kiri Bawah (Showing 1 to 10...)
                $('.dataTables_info').addClass('mt-6 text-sm text-slate-500 float-left');

                $('.dataTables_paginate').addClass('mt-5 flex justify-end items-center gap-1 float-right');
                $('.dataTables_paginate span').addClass('flex gap-1');
                $('.dataTables_paginate .paginate_button').addClass('px-3 py-1.5 border border-slate-200 rounded-md bg-white text-slate-600 text-sm font-medium hover:bg-slate-50 cursor-pointer transition-colors inline-flex items-center justify-center');
                $('.dataTables_paginate .paginate_button.current')
                    .removeClass('bg-white text-slate-600 hover:bg-slate-50')
                    .addClass('bg-slate-900 text-white border-slate-900 hover:bg-slate-800');
                $('.dataTables_paginate .paginate_button.disabled')
                    .addClass('opacity-50 cursor-not-allowed')
                    .removeClass('hover:bg-slate-50 cursor-pointer');
            }
        });
    }
};

document.addEventListener("DOMContentLoaded", setupKasbonIndexPage);