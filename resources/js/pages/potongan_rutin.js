/**
 * Bonehacker HRIS - Potongan Rutin Logic
 * Standar Arsitektur Kasbon & Server-Side Processing
 */

const MODAL_VISIBLE_CLASS = "flex";
const MODAL_HIDDEN_CLASS = "hidden";

const openModal = (modal) => {
    if (!modal) return;
    modal.classList.remove(MODAL_HIDDEN_CLASS, 'opacity-0');
    modal.classList.add(MODAL_VISIBLE_CLASS);
};

const closeModal = (modal) => {
    if (!modal) return;
    modal.classList.remove(MODAL_VISIBLE_CLASS);
    modal.classList.add(MODAL_HIDDEN_CLASS);
};

const formatRupiah = (angka, prefix = 'Rp ') => {
    if (!angka) return 'Rp 0';
    let number_string = angka.toString().replace(/[^,\d]/g, '');
    let split = number_string.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }
    return prefix + rupiah;
};

const setupPotonganRutinPage = () => {
    const config = window.potonganRutinConfig;
    if (!config || typeof window.$ === "undefined") return;

    const $ = window.$;
    const Swal = window.Swal;
    const tableElement = document.getElementById('table-potongan-terapis');
    const modalMassal = document.getElementById('modalMassal');
    const inputNominalMassal = document.getElementById('inputNominalMassal');

    if (inputNominalMassal) {
        inputNominalMassal.addEventListener('input', function(e) {
            let value = this.value.replace(/[^0-9]/g, '');
            this.value = value ? formatRupiah(value) : '';
        });
    }

    if (tableElement) {
        const table = $(tableElement).DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: config.urlFetch,
                type: "POST",
                data: function (d) {
                    d[config.csrfName] = config.csrfHash;
                },
                dataSrc: function(json) {
                    if(json.csrfHash) config.csrfHash = json.csrfHash;
                    return json.data;
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
                    render: (data) => `<span class="px-2.5 py-1 text-[12px] font-bold uppercase text-slate-700">${data}</span>`
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
                        const potonganLabel = row.potongan_raw > 0
                            ? `<span class="text-rose-600 font-black">${row.potongan_info}</span>`
                            : `<span class="text-slate-400 italic text-[10px]">Belum ada setting</span>`;
                        return `
                            <div class="flex flex-col gap-1 text-[11px] justify-center items-center leading-tight">
                                <div><span class="text-slate-400 font-bold uppercase tracking-tighter">Gaji Pokok:</span> <span class="text-slate-700 font-bold">${data}</span></div>
                                <div><span class="text-slate-400 font-bold uppercase tracking-tighter">Potongan:</span> ${potonganLabel}</div>
                            </div>
                        `;
                    }
                },
                {
                    data: 'id',
                    className: 'py-4 px-4 border-b border-slate-100 text-right align-middle',
                    render: function (data) {
                        return `<a href="${config.urlDetail}/${data}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition text-[11px] font-medium"><i class="fas fa-wallet text-[10px]"></i> Kelola</a>`;
                    }
                }
            ],
            language: {
                search: "Cari Terapis:",
                lengthMenu: "Tampilkan _MENU_ data",
                emptyTable: "Belum ada data terapis aktif.",
                processing: "<span class='text-teal-600 font-bold'>Memuat Data...</span>"
            },
            drawCallback: function (settings) {
                const api = this.api();
                const data = api.rows({ page: 'current' }).data().toArray();
                
                // RENDER MOBILE CARDS
                const mobileContainer = document.getElementById('mobile-card-container');
                if (mobileContainer) {
                    mobileContainer.innerHTML = '';
                    if (data.length === 0) {
                        mobileContainer.innerHTML = `
                            <div class="text-center py-10 text-slate-400 italic text-sm bg-white rounded-2xl border border-dashed border-slate-200">
                                Belum ada data terapis aktif.
                            </div>
                        `;
                    } else {
                        data.forEach(row => {
                            const potonganLabel = row.potongan_raw > 0
                                ? `<span class="text-rose-600 font-black text-xs">${row.potongan_info}</span>`
                                : `<span class="text-slate-400 italic text-[10px]">Belum ada setting</span>`;

                            const card = `
                                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col gap-4 animate-in fade-in slide-in-from-bottom-4 duration-300">
                                    <div class="flex justify-between items-start">
                                        <div class="flex flex-col gap-1">
                                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight">${row.nama}</h3>
                                            <span class="inline-flex w-fit items-center px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase tracking-widest bg-slate-100 text-slate-600 border border-slate-200">${row.jabatan}</span>
                                        </div>
                                        <a href="${config.urlDetail}/${row.id}" class="h-10 w-10 flex items-center justify-center bg-teal-600 text-white rounded-xl active:scale-95 transition-all">
                                            <i class="fas fa-wallet text-sm"></i>
                                        </a>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-50">
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Gaji Pokok</span>
                                            <span class="text-xs font-bold text-slate-700">${row.gaji_pokok}</span>
                                        </div>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Potongan</span>
                                            <span class="text-xs font-bold text-right">${potonganLabel}</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                            mobileContainer.insertAdjacentHTML('beforeend', card);
                        });
                    }
                }

                $('.dataTables_wrapper').addClass('flow-root');
                $('.dataTables_length').addClass('mb-4 text-sm text-slate-500 float-left');
                $('.dataTables_length select').addClass('border border-slate-200 rounded-lg px-2 py-1 mx-2 bg-slate-50 outline-none');
                $('.dataTables_filter').addClass('mb-4 text-sm text-slate-500 float-right');
                $('.dataTables_filter input').addClass('border border-slate-200 rounded-lg px-3 py-1 ml-2 bg-slate-50 outline-none focus:ring-1 focus:ring-indigo-500');
                $('.dataTables_info').addClass('mt-6 text-sm text-slate-500 float-left');
                $('.dataTables_paginate').addClass('mt-5 flex justify-end items-center gap-1 float-right');
                $('.dataTables_paginate .paginate_button').addClass('px-3 py-1.5 border border-slate-200 rounded-md bg-white text-slate-600 text-sm font-medium hover:bg-slate-50 cursor-pointer transition-colors inline-flex items-center justify-center');
                $('.dataTables_paginate .paginate_button.current').removeClass('bg-white text-slate-600').addClass('bg-teal-600 text-white border-teal-600');
            }
        });

        // Modal Events
        $('#btnInputMassal').on('click', () => openModal(modalMassal));
        $('.close-modal').on('click', () => closeModal(modalMassal));

        // Submit Massal — setting ke semua terapis
        $('#formMassal').on('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSimpanMassal');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...`;

            $.ajax({
                url: config.urlSaveMassal,
                type: "POST",
                data: $(this).serialize() + '&' + config.csrfName + '=' + config.csrfHash,
                dataType: "json",
                success: function(res) {
                    if (res.csrfHash) config.csrfHash = res.csrfHash;
                    if (res.status === 'success') {
                        closeModal(modalMassal);
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 2000, showConfirmButton: false });
                        table.ajax.reload(null, false);
                        document.getElementById('formMassal').reset();
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                error: () => Swal.fire('Error!', 'Koneksi bermasalah.', 'error'),
                complete: () => { btn.disabled = false; btn.innerHTML = originalText; }
            });
        });
    }
};

const setupPotonganRutinDetailPage = () => {
    const config = window.potonganDetailConfig;
    if (!config || typeof window.$ === "undefined") return;

    const $ = window.$;
    const Swal = window.Swal;

    // Format rupiah
    $('#inputNominalSetting').on('input', function () {
        let v = this.value.replace(/[^0-9]/g, '');
        this.value = v ? 'Rp ' + parseInt(v).toLocaleString('id-ID') : '';
    });

    // Submit setting
    $('#formSaveSetting').on('submit', function (e) {
        e.preventDefault();
        const btn = $('#btnSaveSetting');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...');

        $.ajax({
            url: config.saveSettingUrl,
            type: 'POST',
            data: $(this).serialize() + '&' + config.csrfName + '=' + config.csrfHash,
            dataType: 'json',
            success: function (res) {
                if (res.csrfHash) config.csrfHash = res.csrfHash;
                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1200, showConfirmButton: false });
                    setTimeout(() => location.reload(), 1200);
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                }
            },
            error: () => Swal.fire('Error!', 'Koneksi bermasalah.', 'error'),
            complete: () => btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Simpan Setting')
        });
    });

    // Hapus setting
    window.hapusSetting = function (id) {
        Swal.fire({
            title: 'Hapus potongan rutin ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: config.deleteUrl + '/' + id,
                type: 'POST',
                data: { [config.csrfName]: config.csrfHash },
                dataType: 'json',
                success: function (res) {
                    if (res.csrfHash) config.csrfHash = res.csrfHash;
                    Swal.fire({ icon: 'success', title: 'Dihapus', timer: 1000, showConfirmButton: false });
                    setTimeout(() => location.reload(), 1000);
                },
                error: () => Swal.fire('Error!', 'Koneksi bermasalah.', 'error')
            });
        });
    };
};

document.addEventListener("DOMContentLoaded", () => {
    setupPotonganRutinPage();
    setupPotonganRutinDetailPage();
});
