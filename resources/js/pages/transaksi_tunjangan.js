/**
 * Bonehacker HRIS - Transaksi Tunjangan Logic
 * Standar Arsitektur Kasbon & Server-Side Processing
 */

const MODAL_VISIBLE_CLASS = "flex";
const MODAL_HIDDEN_CLASS = "hidden";

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

const setupTransaksiTunjanganPage = () => {
    const config = window.transaksiTunjanganConfig;
    if (!config || typeof window.$ === "undefined") return;

    const $ = window.$;
    const Swal = window.Swal;
    // SINKRONISASI ID TABEL DISINI
    const tableElement = document.getElementById('table-tunjangan-terapis');
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
                        return `
                            <div class="flex flex-col gap-1 text-[11px] justify-center items-center leading-tight">
                                <div><span class="text-slate-400 font-bold uppercase tracking-tighter">Gaji Pokok:</span> <span class="text-slate-700 font-bold">${data}</span></div>
                                <div><span class="text-slate-400 font-bold uppercase tracking-tighter">Tunjangan Aktif:</span> <span class="text-indigo-600 font-black">${row.tunjangan_aktif}</span></div>
                            </div>
                        `;
                    }
                },
                {
                    data: 'id',
                    className: 'py-4 px-4 border-b border-slate-100 text-right align-middle',
                    render: function (data) {
                        return `<a href="${config.urlDetail}/${data}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition text-[11px] font-black uppercase tracking-widest shadow-md shadow-slate-900/10"><i class="fas fa-wallet text-[10px]"></i> Kelola</a>`;
                    }
                }
            ],
            language: {
                search: "Cari Terapis:",
                lengthMenu: "Tampilkan _MENU_ data",
                emptyTable: "Belum ada data terapis aktif.",
                processing: "<span class='text-indigo-600 font-bold'>Memuat Data...</span>"
            },
            drawCallback: function () {
                $('.dataTables_wrapper').addClass('flow-root');
                $('.dataTables_length').addClass('mb-4 text-sm text-slate-500 float-left');
                $('.dataTables_length select').addClass('border border-slate-200 rounded-lg px-2 py-1 mx-2 bg-slate-50 outline-none');
                $('.dataTables_filter').addClass('mb-4 text-sm text-slate-500 float-right');
                $('.dataTables_filter input').addClass('border border-slate-200 rounded-lg px-3 py-1 ml-2 bg-slate-50 outline-none focus:ring-1 focus:ring-indigo-500');
                $('.dataTables_info').addClass('mt-6 text-sm text-slate-500 float-left');
                $('.dataTables_paginate').addClass('mt-5 flex justify-end items-center gap-1 float-right');
                $('.dataTables_paginate .paginate_button').addClass('px-3 py-1.5 border border-slate-200 rounded-md bg-white text-slate-600 text-sm font-medium hover:bg-slate-50 cursor-pointer transition-colors inline-flex items-center justify-center');
                $('.dataTables_paginate .paginate_button.current').removeClass('bg-white text-slate-600').addClass('bg-indigo-600 text-white border-indigo-600');
            }
        });

        // Modal Events
        $('#btnInputMassal').on('click', () => openModal(modalMassal));
        $('.close-modal').on('click', () => closeModal(modalMassal));

        // Submit Massal
        $('#formMassal').on('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnSimpanMassal');
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = `<i class="fas fa-circle-notch fa-spin mr-2"></i> Memproses...`;

            $.ajax({
                url: config.urlStore,
                type: "POST",
                data: $(this).serialize(),
                dataType: "JSON",
                success: function(res) {
                    if (res.status === 'success') {
                        closeModal(modalMassal);
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false });
                        table.ajax.reload(null, false);
                        document.getElementById('formMassal').reset();
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                    if(res.csrfHash) config.csrfHash = res.csrfHash;
                },
                error: () => Swal.fire('Error!', 'Koneksi bermasalah.', 'error'),
                complete: () => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });
        });
    }
};

document.addEventListener("DOMContentLoaded", setupTransaksiTunjanganPage);