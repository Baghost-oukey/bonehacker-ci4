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
        } catch (e) {}
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

    // --- Event Delegation untuk Button Setting ---
    $(document).on('click', '.btn-setting', function(e) {
        try {
            e.preventDefault();
            const terapisId = this.dataset.terapisId;
            const tipeGaji = this.dataset.tipeGaji;
            const nominal = parseInt(this.dataset.nominal) || 0;
            const potong = this.dataset.potong || '0';

            window.bukaModalSetting(terapisId, tipeGaji, nominal, potong);
        } catch (err) {
            alert("Error: " + err.message);
        }
    });

    // --- Event Delegation untuk Button Proses Gaji ---
    $(document).on('click', '.btn-proses-gaji', function(e) {
        try {
            e.preventDefault();
            const terapisId = this.dataset.terapisId;
            window.bukaOffcanvas(terapisId);
        } catch (err) {}
    });

    // --- Close Button Modal Setting ---
    $(document).on('click', '.btn-close-modal-setting, #modalSetting [data-dismiss="modal"], #modalSetting .close', function(e) {
        e.preventDefault();
        window.tutupModalSetting();
    });

    // --- Close Button Offcanvas ---
    $(document).on('click', '.btn-close-offcanvas', function(e) {
        e.preventDefault();
        window.tutupOffcanvas();
    });

    // --- Backdrop Click ---
    $(document).on('click', '.offcanvas-backdrop', function(e) {
        if(e.target === this) {
            window.tutupOffcanvas();
        }
    });

    // --- Toggle Potong Absen on Select Tipe Gaji (Modal) ---
    $(document).on('change', '#set_tipe_gaji', function() {
        const divPotong = document.getElementById('div_potong_absen');
        if (divPotong) {
            if (this.value === 'bulanan') {
                $(divPotong).show();
            } else {
                $(divPotong).hide();
            }
        }
    });

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

// 4. FUNGSI GLOBAL (JQUERY OPTIMIZED)
window.bukaModalSetting = (id, tipe, nominal, potong = '0') => {
    try {
        const modal = $('#modalSetting');
        const content = $('#modalSettingContent');

        if (modal.length === 0) return;

        $('#set_terapis_id').val(id);
        
        const tipeClean = (tipe || 'bulanan').toLowerCase();
        $('#set_tipe_gaji').val((tipeClean === 'belum diset') ? 'bulanan' : tipeClean);
        
        $('#set_nominal_gaji').val(formatRupiah(nominal));

        const potongAbsenCheckbox = $('#set_potong_absen');
        if (potongAbsenCheckbox.length > 0) {
            potongAbsenCheckbox.prop('checked', (potong == '1'));
        }

        const divPotong = $('#div_potong_absen');
        if (divPotong.length > 0) {
            if ($('#set_tipe_gaji').val() === 'bulanan') {
                divPotong.show();
            } else {
                divPotong.hide();
            }
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
        alert("Buka Modal Error: " + error.message);
    }
};

window.tutupModalSetting = () => {
    try {
        const modal = $('#modalSetting');
        const content = $('#modalSettingContent');

        if (modal.length === 0) return;

        modal.removeClass('opacity-100').addClass('opacity-0');
        if (content.length > 0) {
            content.removeClass('scale-100').addClass('scale-95');
        }

        setTimeout(() => {
            modal.removeClass('flex').addClass('hidden');
            $('body').removeClass('modal-open').css('overflow', '');
        }, 300);
    } catch (err) {}
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
                    const totalKasbon = parseInt(data.total_kasbon) || 0;
                    const totalTunjangan = parseInt(data.total_tunjangan) || 0;
                    const currentKehadiran = parseInt(data.current_kehadiran) || 0;
                    
                    const attendanceInput = $('#oc_kehadiran');
                    const attendanceGroup = $('#oc_kehadiran_group');
                    const attendanceLabel = $('#oc_kehadiran_label');
                    const tipeInfo = $('#oc_tipe_info');
                    
                    const gajiPokokInput = $('#oc_gaji_pokok');
                    const tunjanganInput = $('#oc_tunjangan');
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
                                    const hariKerja = parseInt(kalkulasi.hari_kerja) || 0;
                                    if (hariKerja > 0) {
                                        const absen = hariKerja - attendance;
                                        if (absen > 0) {
                                            potonganAbsen = Math.round((nominalGaji / hariKerja) * absen);
                                        }
                                    }
                                }
                            }

                            const gajiBersih = Math.max(0, (gajiPokokTotal - potonganAbsen) + totalTunjangan - totalKasbon);

                            gajiPokokInput.val(formatRupiah(gajiPokokTotal));
                            tunjanganInput.val(formatRupiah(totalTunjangan));
                            potonganInput.val(formatRupiah(totalKasbon));
                            bersihInput.val(formatRupiah(gajiBersih));

                            if (tipeGaji === 'bulanan' && potonganAbsen > 0) {
                                if (potonganAbsenGroup.length > 0) potonganAbsenGroup.removeClass('hidden');
                                if (potonganAbsenInput.length > 0) potonganAbsenInput.val(`- ${formatRupiah(potonganAbsen)}`);
                            } else {
                                if (potonganAbsenGroup.length > 0) potonganAbsenGroup.addClass('hidden');
                            }
                        } catch (calcErr) {}
                    };

                    const updateFormState = () => {
                        tipeGajiField.val(tipeGaji);
                        form.attr('data-tipe-gaji', tipeGaji);

                        if (tipeGaji === 'harian') {
                            attendanceGroup.removeClass('hidden');
                            attendanceInput.prop('required', true);
                            attendanceInput.val(currentKehadiran);
                            attendanceLabel.text('Total Kehadiran (Hari)');
                            tipeInfo.text('Gaji harian dihitung dari kehadiran real karyawan setiap bulan.');
                        } else {
                            if (data.potong_absen == 1) {
                                attendanceGroup.removeClass('hidden');
                                attendanceInput.prop('required', true);
                                attendanceInput.val(currentKehadiran);
                                attendanceLabel.text('Kehadiran Terdeteksi (Hari)');
                                tipeInfo.text('Gaji bulanan dipotong otomatis jika ada hari absen kerja.');
                            } else {
                                attendanceGroup.addClass('hidden');
                                attendanceInput.prop('required', false);
                                attendanceInput.val(0);
                                attendanceLabel.text('Kehadiran tidak diperlukan');
                                tipeInfo.text('Gaji bulanan tetap sesuai nominal yang sudah diatur.');
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
    } catch (err) {}
};

window.tutupOffcanvas = () => {
    try {
        const offcanvas = $('#offcanvasProses');
        const backdrop = $('#offcanvasBackdrop');
        if (offcanvas.length > 0) offcanvas.addClass('translate-x-full');
        setTimeout(() => {
            if (backdrop.length > 0) backdrop.addClass('hidden');
        }, 300);
    } catch (err) {}
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
        if (backdrop.length > 0) backdrop.addClass('hidden');
        if (offcanvas.length > 0) offcanvas.addClass('translate-x-full');
        if (modal.length > 0) {
            modal.removeClass(MODAL_VISIBLE_CLASS);
            modal.addClass(MODAL_HIDDEN_CLASS);
        }
        document.body.style.overflow = '';
    } catch (e) {}
});