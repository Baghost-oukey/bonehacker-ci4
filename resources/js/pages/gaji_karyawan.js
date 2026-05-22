/**
 * Gaji Karyawan Management Script
 * Handles Payroll Calculations, Settings, and Offcanvas Interactions
 */

// --- Fix NiceScroll MutationObserver Bug (Infinite reflow crash) ---
if (window.jQuery) {
    const $ = window.jQuery;
    if ($.nicescroll && $.nicescroll.options) {
        $.nicescroll.options.disablemutationobserver = true;
    }
    if ($.fn.getNiceScroll) {
        try {
            $('*').getNiceScroll().remove();
        } catch (e) { }
    }
}

// 1. KONSTANTA GLOBAL
const MODAL_VISIBLE_CLASS = "flex";
const MODAL_HIDDEN_CLASS = "hidden";

// 2. GLOBAL HELPER: Format Angka ke Rupiah
const formatRupiah = (angka, prefix = 'Rp ') => {
    if (angka === undefined || angka === null || angka === '') return prefix + '0';
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

// 3. LOGIKA UTAMA (Encapsulated)
const createManualRowHTML = (isModal = false) => {
    const hapusClass = isModal ? 'btn-hapus-manual-item-sm' : 'btn-hapus-manual-item';
    return `
        <tr class="manual-item-row hover:bg-slate-50 transition-colors">
            <td class="p-2 align-middle">
                <select name="manual_kelompok[]" class="manual-kelompok border border-slate-200 rounded-lg text-[11px] bg-white p-1.5 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 w-full transition">
                    <option value="take_home">Gaji Pokok & Jaspel</option>
                    <option value="benefit">Tunjangan (Cash)</option>
                    <option value="benefit_non_cash">Tunjangan Non-Tunai</option>
                    <option value="potongan">Potongan</option>
                </select>
            </td>
            <td class="p-2 align-middle">
                <input type="text" name="manual_deskripsi[]" placeholder="Deskripsi" class="manual-deskripsi border border-slate-200 rounded-lg text-[11px] bg-slate-50/30 p-1.5 focus:bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 w-full transition" required>
            </td>
            <td class="p-2 align-middle">
                <input type="text" name="manual_nominal[]" placeholder="Rp" class="manual-nominal input-rupiah border border-slate-200 rounded-lg text-[11px] bg-slate-50/30 p-1.5 focus:bg-white focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-right font-bold text-slate-700 w-full transition" required>
            </td>
            <td class="p-2 align-middle text-center">
                <button type="button" class="${hapusClass} text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-1.5 rounded-lg transition-all">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr>
    `;
};

const loadTerapisList = () => {
    const select = $('#sm_terapis_id');
    if (!select.length) return;

    select.html('<option value="">-- Memuat terapis... --</option>');

    fetch(window.gajiConfig.getTerapisListUrl)
        .then(response => response.json())
        .then(res => {
            if (res.status === 'success') {
                select.empty();
                select.append('<option value="">-- Pilih Terapis --</option>');
                res.data.forEach(t => {
                    select.append(`<option value="${t.id}">${t.nama} (${t.wilayah})</option>`);
                });
            } else {
                select.html('<option value="">Gagal memuat terapis</option>');
            }
        })
        .catch(err => {
            select.html('<option value="">Gagal memuat terapis</option>');
        });
};

const calculateManualSlipTotals = () => {
    let totalPokok = 0;
    let totalTunjangan = 0;
    let totalPotongan = 0;

    $('#sm_manual_items_container .manual-item-row').each(function () {
        const kelompok = $(this).find('.manual-kelompok').val();
        const nominalStr = $(this).find('.manual-nominal').val() || '0';
        const nominal = parseFloat(nominalStr.replace(/[^0-9]/g, '')) || 0;

        if (kelompok === 'take_home') {
            totalPokok += nominal;
        } else if (kelompok === 'benefit') {
            totalTunjangan += nominal;
        } else if (kelompok === 'potongan') {
            totalPotongan += nominal;
        }
    });

    const totalBersih = (totalPokok + totalTunjangan) - totalPotongan;

    $('#sm_total_pokok').text(formatRupiah(totalPokok));
    $('#sm_total_tunjangan').text(formatRupiah(totalTunjangan));
    $('#sm_total_potongan').text('- ' + formatRupiah(totalPotongan));
    $('#sm_total_bersih').text(formatRupiah(totalBersih));
};

const setupGajiPage = () => {
    const config = window.gajiConfig;
    const page = document.getElementById("gajiPage");

    if (!page || !config || typeof window.$ === "undefined") {
        return;
    }

    // --- Tab Navigation ---
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');

            tabBtns.forEach(b => {
                b.classList.remove('border-blue-600', 'text-blue-600', 'active');
                b.classList.add('border-transparent', 'text-slate-500');
            });
            tabContents.forEach(c => c.classList.add('hidden'));

            btn.classList.replace('border-transparent', 'border-blue-600');
            btn.classList.replace('text-slate-500', 'text-blue-600');
            btn.classList.add('active');

            document.getElementById(targetId)?.classList.remove('hidden');
        });
    });

    // --- Auto Format Rupiah on Input ---
    $(document).on('keyup', '.input-rupiah', function () {
        this.value = formatRupiah(this.value, 'Rp ');
    });


    // --- Event Delegation untuk Button Proses Gaji ---
    $(document).on('click', '.btn-proses-gaji', function (e) {
        try {
            e.preventDefault();
            const terapisId = this.dataset.terapisId;
            window.bukaOffcanvas(terapisId);
        } catch (err) { }
    });

    // --- Event Delegation untuk Button Show Detail Riwayat Modal ---
    $(document).on('click', '.btn-show-detail-modal', function (e) {
        try {
            e.preventDefault();
            const targetId = this.dataset.target;
            window.bukaModalDetail(targetId);
        } catch (err) { }
    });

    // --- Close Button Modal Detail ---
    $(document).on('click', '.btn-close-modal-detail', function (e) {
        e.preventDefault();
        window.tutupModalDetail();
    });

    // --- Modal Detail Backdrop Click ---
    $(document).on('click', '#modalDetailRiwayatBackdrop, #modalDetailRiwayat', function (e) {
        if (e.target === this) {
            window.tutupModalDetail();
        }
    });


    // --- Close Button Offcanvas ---
    $(document).on('click', '.btn-close-offcanvas', function (e) {
        e.preventDefault();
        window.tutupOffcanvas();
    });

    // --- Backdrop Click ---
    $(document).on('click', '.offcanvas-backdrop', function (e) {
        if (e.target === this) {
            window.tutupOffcanvas();
        }
    });

    // --- LOGIKA MANUAL COMPONENTS PADA OFFCANVAS ---
    $(document).on('click', '#btnTambahItemManualOC', function (e) {
        e.preventDefault();
        $('#oc_manual_items_container').append(createManualRowHTML(false));
        if (window.oc_renderPayroll) {
            window.oc_renderPayroll();
        }
    });

    $(document).on('click', '#oc_manual_items_container .btn-hapus-manual-item', function (e) {
        e.preventDefault();
        $(this).closest('.manual-item-row').remove();
        if (window.oc_renderPayroll) {
            window.oc_renderPayroll();
        }
    });

    $(document).on('input change', '#oc_manual_items_container select, #oc_manual_items_container input', function () {
        if (window.oc_renderPayroll) {
            window.oc_renderPayroll();
        }
    });


    // --- LOGIKA SLIP GAJI MANUAL (THR, DLL.) ---
    $(document).on('click', '#btnSlipManual', function (e) {
        e.preventDefault();
        window.bukaModalSlipManual();
    });

    $(document).on('click', '.btn-close-modal-manual', function (e) {
        e.preventDefault();
        window.tutupModalSlipManual();
    });

    $(document).on('click', '#modalSlipManualBackdrop, #modalSlipManual', function (e) {
        if (e.target === this) {
            window.tutupModalSlipManual();
        }
    });

    $(document).on('click', '#btnTambahItemManualSM', function (e) {
        e.preventDefault();
        $('#sm_manual_items_container').append(createManualRowHTML(true));
        calculateManualSlipTotals();
    });

    $(document).on('click', '#sm_manual_items_container .btn-hapus-manual-item-sm', function (e) {
        e.preventDefault();
        $(this).closest('.manual-item-row').remove();
        calculateManualSlipTotals();
    });

    $(document).on('input change', '#sm_manual_items_container select, #sm_manual_items_container input', function () {
        calculateManualSlipTotals();
    });

    const formSlipManual = document.getElementById('formSlipManual');
    if (formSlipManual) {
        formSlipManual.addEventListener('submit', function (e) {
            e.preventDefault();
            const terapisId = $('#sm_terapis_id').val();
            const rowsCount = $('#sm_manual_items_container .manual-item-row').length;
            const totalBersihText = $('#sm_total_bersih').text();

            if (!terapisId) {
                showSwalError('Pilih terapis terlebih dahulu.');
                return;
            }

            if (rowsCount === 0) {
                showSwalError('Masukkan minimal satu komponen gaji manual.');
                return;
            }

            confirmSwal(`Yakin ingin memproses slip gaji manual ini?\n\nGaji Bersih: ${totalBersihText}`)
                .then((result) => {
                    if (result.isConfirmed) {
                        formSlipManual.submit();
                    }
                });
        });
    }

    const formBayarGaji = document.getElementById('formBayarGaji');
    if (formBayarGaji) {
        formBayarGaji.addEventListener('submit', handleProsesBayarSubmit);
    }
};

const showSwalError = (message) => {
    if (window.Swal?.fire) {
        Swal.fire({ icon: 'error', title: 'Validasi Gagal', text: message });
        return;
    }
    alert(message);
};

const confirmSwal = (message) => {
    if (window.Swal?.fire) {
        return Swal.fire({
            icon: 'question',
            title: 'Konfirmasi Proses Gaji',
            text: message,
            showCancelButton: true,
            confirmButtonText: 'Ya, proses sekarang',
            cancelButtonText: 'Batal'
        });
    }
    return Promise.resolve({ isConfirmed: window.confirm(message) });
};

const handleProsesBayarSubmit = (event) => {
    event.preventDefault();
    const form = event.target;
    const terapisId = form.querySelector('[name="terapis_id"]').value;
    const attendanceValue = form.querySelector('[name="total_kehadiran"]').value;
    const gajiBersih = form.querySelector('[name="gaji_bersih"]').value;
    const tipeGaji = form.dataset.tipeGaji || 'bulanan';
    const totalKehadiran = parseInt(attendanceValue, 10) || 0;

    if (!terapisId) {
        showSwalError('Terapis belum terpilih. Silakan ulangi proses gaji.');
        return;
    }

    if (totalKehadiran < 0) {
        showSwalError('Total kehadiran tidak boleh kurang dari 0.');
        return;
    }

    if (tipeGaji === 'harian' && totalKehadiran <= 0) {
        showSwalError('Masukkan total kehadiran minimal 1 untuk gaji harian.');
        return;
    }

    if (!gajiBersih || gajiBersih.trim() === '' || gajiBersih.trim() === 'Rp 0') {
        showSwalError('Gaji bersih belum terhitung. Pastikan data kehadiran and perhitungan sudah benar.');
        return;
    }

    confirmSwal(`Yakin ingin memproses gaji ini?\n\nGaji Bersih: ${gajiBersih}`)
        .then((result) => {
            if (result.isConfirmed) {
                form.removeEventListener('submit', handleProsesBayarSubmit);
                form.submit();
            }
        });
};



window.bukaModalDetail = (targetId) => {
    try {
        const modal = $('#modalDetailRiwayat');
        const content = $('#modalDetailRiwayatContent');
        const sourceData = $('#' + targetId);

        if (modal.length === 0 || sourceData.length === 0) return;

        // Get elements inside modal
        const avatarContainer = $('#modal-detail-avatar-container');
        const namaEl = $('#modal-detail-nama');
        const metaEl = $('#modal-detail-meta');
        const gajiEl = $('#modal-detail-gaji-bersih');
        const tglEl = $('#modal-detail-tanggal-bayar');
        const periodeEl = $('#modal-detail-periode-text');
        const gridContainer = $('#modal-detail-grid-container');

        // Extract metadata from source header
        const headerData = sourceData.find('.modal-header-data');
        if (headerData.length > 0) {
            const nama = headerData.attr('data-nama') || '-';
            const foto = headerData.attr('data-foto') || '';
            const initial = headerData.attr('data-initial') || '-';
            const jabatan = headerData.attr('data-jabatan') || '-';
            const wilayah = headerData.attr('data-wilayah') || '-';
            const periode = headerData.attr('data-periode') || '-';
            const tglBayar = headerData.attr('data-tanggal-bayar') || '-';
            const gajiBersih = headerData.attr('data-gaji-bersih') || 'Rp 0';

            // Populate text
            namaEl.text(nama);
            metaEl.text(`${jabatan} \u2022 ${wilayah}`);
            gajiEl.text(gajiBersih);
            tglEl.text(`Tanggal Bayar: ${tglBayar}`);
            periodeEl.text(`Periode: ${periode}`);

            // Populate avatar
            avatarContainer.empty();
            if (foto) {
                avatarContainer.append(`<img src="${foto}" class="w-12 h-12 rounded-xl object-cover border border-slate-200" alt="${nama}">`);
            } else {
                avatarContainer.append(`<div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 font-bold text-sm">${initial}</div>`);
            }
        }

        // Populate breakdown grid (clone the grid div, remove hidden class if any, empty container first)
        gridContainer.empty();
        const originalGrid = sourceData.find('.grid').first();
        if (originalGrid.length > 0) {
            const clonedGrid = originalGrid.clone();
            clonedGrid.removeClass('hidden');
            gridContainer.append(clonedGrid);
        }

        modal.appendTo('body');
        modal.removeClass('hidden').addClass('flex');

        setTimeout(() => {
            modal.removeClass('opacity-0').addClass('opacity-100');
            if (content.length > 0) {
                content.removeClass('scale-95').addClass('scale-100');
            }
        }, 20);

        $('body').addClass('modal-open').css('overflow', 'hidden');
    } catch (error) {
        console.error("Buka Modal Detail Error: ", error);
    }
};

window.tutupModalDetail = () => {
    try {
        const modal = $('#modalDetailRiwayat');
        const content = $('#modalDetailRiwayatContent');

        if (modal.length === 0) return;

        modal.removeClass('opacity-100').addClass('opacity-0');
        if (content.length > 0) {
            content.removeClass('scale-100').addClass('scale-95');
        }

        setTimeout(() => {
            modal.removeClass('flex').addClass('hidden');
            $('body').removeClass('modal-open').css('overflow', '');
        }, 300);
    } catch (err) { }
};

window.bukaModalSlipManual = () => {
    try {
        const modal = $('#modalSlipManual');
        const content = $('#modalSlipManualContent');
        if (modal.length === 0) return;

        // Reset form
        $('#formSlipManual')[0].reset();
        $('#sm_manual_items_container').empty();
        $('#sm_total_pokok').text('Rp 0');
        $('#sm_total_tunjangan').text('Rp 0');
        $('#sm_total_potongan').text('- Rp 0');
        $('#sm_total_bersih').text('Rp 0');

        loadTerapisList();

        modal.appendTo('body');
        modal.removeClass('hidden').addClass('flex');

        setTimeout(() => {
            modal.removeClass('opacity-0').addClass('opacity-100');
            if (content.length > 0) {
                content.removeClass('scale-95').addClass('scale-100');
            }
        }, 20);

        $('body').addClass('modal-open').css('overflow', 'hidden');
    } catch (error) {
        console.error("Buka Modal Slip Manual Error: ", error);
    }
};

window.tutupModalSlipManual = () => {
    try {
        const modal = $('#modalSlipManual');
        const content = $('#modalSlipManualContent');

        if (modal.length === 0) return;

        modal.removeClass('opacity-100').addClass('opacity-0');
        if (content.length > 0) {
            content.removeClass('scale-100').addClass('scale-95');
        }

        setTimeout(() => {
            modal.removeClass('flex').addClass('hidden');
            $('body').removeClass('modal-open').css('overflow', '');
        }, 300);
    } catch (err) { }
};

window.bukaOffcanvas = (terapisId) => {
    try {
        const config = window.gajiConfig;
        const offcanvas = $('#offcanvasProses');
        const backdrop = $('#offcanvasBackdrop');
        const loading = $('#loadingState');
        const form = $('#formBayarGaji');

        if (offcanvas.length === 0 || backdrop.length === 0) return;

        backdrop.appendTo('body');
        offcanvas.appendTo('body');

        backdrop.removeClass('hidden');
        offcanvas.removeClass('translate-x-full');
        loading.removeClass('hidden');
        form.addClass('hidden');

        // Reset manual items
        $('#oc_manual_items_container').empty();

        const fetchUrl = `${config.detailUrl}/${terapisId}`;

        fetch(fetchUrl)
            .then(response => response.json())
            .then(res => {
                if (res.status === 'success') {
                    const data = res.data;
                    const kalkulasi = res.kalkulasi;

                    $('#oc_terapis_id').val(data.id || terapisId);
                    $('#oc_nama_terapis').text(data.nama || '-');

                    const tipeGaji = (data.tipe_gaji || 'bulanan').toLowerCase();
                    const nominalGaji = parseInt(data.nominal_gaji) || 0;
                    // total_C holds total deductions (routine + kasbon)
                    const totalPotongan = (kalkulasi && typeof kalkulasi.total_C !== 'undefined') ? parseInt(kalkulasi.total_C) : (parseInt(data.total_kasbon) || 0);
                    const totalTunjangan = parseInt(data.total_tunjangan) || 0;
                    const totalBenefitNonCash = (kalkulasi && typeof kalkulasi.total_benefit_non_cash !== 'undefined') ? parseInt(kalkulasi.total_benefit_non_cash) : 0;
                    const currentKehadiran = parseInt(data.current_kehadiran) || 0;
                    const currentAbsen = (kalkulasi && typeof kalkulasi.absen !== 'undefined') ? parseInt(kalkulasi.absen) : 0;

                    const attendanceInput = $('#oc_kehadiran');
                    const attendanceGroup = $('#oc_kehadiran_group');
                    const attendanceLabel = $('#oc_kehadiran_label');
                    const tipeInfo = $('#oc_tipe_info');

                    const gajiPokokInput = $('#oc_gaji_pokok');
                    const tunjanganInput = $('#oc_tunjangan');
                    const benefitNonCashInput = $('#oc_benefit_non_cash');
                    const benefitNonCashGroup = $('#oc_benefit_non_cash_group');
                    const potonganInput = $('#oc_potongan');
                    const bersihInput = $('#oc_bersih');
                    const tipeGajiField = $('#oc_tipe_gaji');

                    const potonganAbsenGroup = $('#oc_potongan_absen_group');
                    const potonganAbsenInput = $('#oc_potongan_absen');

                    const renderPayroll = () => {
                        try {
                            const attendance = parseInt(attendanceInput.val()) || 0;
                            let gajiPokokTotal = nominalGaji;
                            let potonganAbsen = 0;

                            if (tipeGaji === 'harian') {
                                gajiPokokTotal = nominalGaji * attendance;
                            } else {
                                if (data.potong_absen == 1 && kalkulasi) {
                                    const absen = currentAbsen;
                                    if (absen > 0) {
                                        const nominalPotong = parseFloat(data.nominal_potong_absen) || 0;
                                        potonganAbsen = Math.round(nominalPotong * absen);
                                    }
                                }
                            }

                            // Sum manual components from offcanvas
                            let addPokok = 0;
                            let addTunjangan = 0;
                            let addBenefitNonCash = 0;
                            let addPotongan = 0;

                            $('#oc_manual_items_container .manual-item-row').each(function () {
                                const kelompok = $(this).find('.manual-kelompok').val();
                                const nominalStr = $(this).find('.manual-nominal').val() || '0';
                                const nominal = parseFloat(nominalStr.replace(/[^0-9]/g, '')) || 0;

                                if (kelompok === 'take_home') {
                                    addPokok += nominal;
                                } else if (kelompok === 'benefit') {
                                    addTunjangan += nominal;
                                } else if (kelompok === 'benefit_non_cash') {
                                    addBenefitNonCash += nominal;
                                } else if (kelompok === 'potongan') {
                                    addPotongan += nominal;
                                }
                            });

                            const finalGajiPokok = gajiPokokTotal + addPokok;
                            const finalTunjangan = totalTunjangan + addTunjangan;
                            const finalBenefitNonCash = totalBenefitNonCash + addBenefitNonCash;
                            const finalPotongan = totalPotongan + addPotongan;

                            const gajiBersih = Math.max(0, (finalGajiPokok - potonganAbsen) + finalTunjangan - finalPotongan);

                            const formattedGajiPokok = formatRupiah(finalGajiPokok);
                            const formattedTunjangan = formatRupiah(finalTunjangan);
                            const formattedBenefitNonCash = formatRupiah(finalBenefitNonCash);
                            const formattedPotongan = formatRupiah(finalPotongan);
                            const formattedGajiBersih = formatRupiah(gajiBersih);

                            gajiPokokInput.val(formattedGajiPokok);
                            $('#oc_gaji_pokok_text').text(formattedGajiPokok);

                            tunjanganInput.val(formattedTunjangan);
                            $('#oc_tunjangan_text').text(formattedTunjangan);

                            if (benefitNonCashInput.length > 0) {
                                benefitNonCashInput.val(formattedBenefitNonCash);
                                $('#oc_benefit_non_cash_text').text(formattedBenefitNonCash);
                            }
                            if (benefitNonCashGroup.length > 0) {
                                if (finalBenefitNonCash > 0) {
                                    benefitNonCashGroup.removeClass('hidden');
                                } else {
                                    benefitNonCashGroup.addClass('hidden');
                                }
                            }

                            potonganInput.val(formattedPotongan);
                            $('#oc_potongan_text').text(formattedPotongan);

                            bersihInput.val(formattedGajiBersih);
                            $('#oc_bersih_text').text(formattedGajiBersih);

                            if (tipeGaji === 'bulanan' && potonganAbsen > 0) {
                                if (potonganAbsenGroup.length > 0) potonganAbsenGroup.removeClass('hidden');
                                const formattedPotonganAbsen = `- ${formatRupiah(potonganAbsen)}`;
                                if (potonganAbsenInput.length > 0) {
                                    potonganAbsenInput.val(formattedPotonganAbsen);
                                }
                                $('#oc_potongan_absen_text').text(formattedPotonganAbsen);
                            } else {
                                if (potonganAbsenGroup.length > 0) potonganAbsenGroup.addClass('hidden');
                            }
                        } catch (calcErr) {
                            console.error(calcErr);
                        }
                    };

                    window.oc_renderPayroll = renderPayroll;

                    const updateFormState = () => {
                        tipeGajiField.val(tipeGaji);
                        form.attr('data-tipe-gaji', tipeGaji);

                        // Ensure hidden input is never HTML5 required to avoid non-focusable validation issues
                        attendanceInput.prop('required', false);

                        if (tipeGaji === 'harian') {
                            attendanceGroup.removeClass('hidden');
                            attendanceInput.val(currentKehadiran);
                            $('#oc_kehadiran_text').text(currentKehadiran);
                            attendanceLabel.text('Hadir');
                            tipeInfo.text('Gaji harian dihitung dari kehadiran real karyawan setiap bulan.');
                            $('#oc_absen_display_group').removeClass('hidden');
                            $('#oc_absen').val(currentAbsen);
                            $('#oc_absen_text').text(currentAbsen);
                        } else {
                            if (data.potong_absen == 1) {
                                attendanceGroup.removeClass('hidden');
                                attendanceInput.val(currentKehadiran);
                                $('#oc_kehadiran_text').text(currentKehadiran);
                                attendanceLabel.text('Hadir');
                                tipeInfo.text('Gaji bulanan dipotong otomatis jika ada hari absen kerja.');

                                $('#oc_absen_display_group').removeClass('hidden');
                                $('#oc_absen').val(currentAbsen);
                                $('#oc_absen_text').text(currentAbsen);
                            } else {
                                attendanceGroup.removeClass('hidden');
                                attendanceInput.val(currentKehadiran);
                                $('#oc_kehadiran_text').text(currentKehadiran);
                                attendanceLabel.text('Hadir');
                                tipeInfo.text('Gaji bulanan tetap sesuai nominal yang sudah diatur.');
                                $('#oc_absen_display_group').removeClass('hidden');
                                $('#oc_absen').val(currentAbsen);
                                $('#oc_absen_text').text(currentAbsen);
                            }
                        }
                    };

                    if (attendanceInput.length > 0) {
                        attendanceInput.off('input').on('input', renderPayroll);
                    }

                    updateFormState();
                    renderPayroll();

                    loading.addClass('hidden');
                    form.removeClass('hidden');
                } else {
                    alert('Gagal: ' + (res.message || 'Data tidak ditemukan'));
                    window.tutupOffcanvas();
                }
            })
            .catch(err => {
                loading.html('<p class="text-red-500 mt-4 text-center">Gagal memuat data.</p>');
            });
    } catch (err) { }
};

window.tutupOffcanvas = () => {
    try {
        const offcanvas = $('#offcanvasProses');
        const backdrop = $('#offcanvasBackdrop');
        if (offcanvas.length > 0) offcanvas.addClass('translate-x-full');
        setTimeout(() => {
            if (backdrop.length > 0) backdrop.addClass('hidden');
        }, 300);
    } catch (err) { }
};

// 5. INITIALIZE (ASYNCHRONOUS READY STATE CHECKER)
if (document.readyState === "complete" || document.readyState === "interactive") {
    setupGajiPage();
} else {
    document.addEventListener('DOMContentLoaded', setupGajiPage);
}

// Cleanup saat navigasi pergi dari halaman ini
window.addEventListener('beforeunload', () => {
    try {
        const backdrop = $('#offcanvasBackdrop');
        const offcanvas = $('#offcanvasProses');
        const modal = $('#modalSetting');
        const modalDetail = $('#modalDetailRiwayat');
        const modalManual = $('#modalSlipManual');
        if (backdrop.length > 0) backdrop.addClass('hidden');
        if (offcanvas.length > 0) offcanvas.addClass('translate-x-full');
        if (modal.length > 0) {
            modal.removeClass(MODAL_VISIBLE_CLASS);
            modal.addClass(MODAL_HIDDEN_CLASS);
        }
        if (modalDetail.length > 0) {
            modalDetail.removeClass(MODAL_VISIBLE_CLASS);
            modalDetail.addClass(MODAL_HIDDEN_CLASS);
        }
        if (modalManual.length > 0) {
            modalManual.removeClass(MODAL_VISIBLE_CLASS);
            modalManual.addClass(MODAL_HIDDEN_CLASS);
        }
        document.body.style.overflow = '';
    } catch (e) { }
});