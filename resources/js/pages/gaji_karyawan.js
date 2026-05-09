/**
 * Gaji Karyawan Management Script
 * Handles Payroll Calculations, Settings, and Offcanvas Interactions
 */

// 1. KONSTANTA GLOBAL (Sesuai style Terapis Anda)
const MODAL_VISIBLE_CLASS = "flex";
const MODAL_HIDDEN_CLASS = "hidden";

// 2. GLOBAL HELPER: Format Angka ke Rupiah (Hanya satu deklarasi)
const formatRupiah = (angka, prefix = 'Rp ') => {
    if (!angka) return prefix + '0';
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
    const page = document.getElementById("gajiPage"); // Pastikan ID ini ada di wrapper utama View

    if (!config || typeof window.$ === "undefined") return;

    // --- Tab Navigation ---
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => {
                b.classList.remove('border-blue-600', 'text-blue-600', 'active');
                b.classList.add('border-transparent', 'text-slate-500');
            });
            tabContents.forEach(c => c.classList.add('hidden'));

            btn.classList.replace('border-transparent', 'border-blue-600');
            btn.classList.replace('text-slate-500', 'text-blue-600');
            btn.classList.add('active');

            const targetId = btn.getAttribute('data-target');
            document.getElementById(targetId)?.classList.remove('hidden');
        });
    });

    // --- Auto Format Rupiah on Input ---
    $(document).on('keyup', '.input-rupiah', function () {
        this.value = formatRupiah(this.value, 'Rp ');
    });

    // --- Event Delegation untuk Button Setting ---
    $(document).on('click', '.btn-setting', function(e) {
        e.preventDefault();
        const terapisId = this.dataset.terapisId;
        const tipeGaji = this.dataset.tipeGaji;
        const nominal = parseInt(this.dataset.nominal) || 0;
        window.bukaModalSetting(terapisId, tipeGaji, nominal);
    });

    // --- Event Delegation untuk Button Proses Gaji ---
    $(document).on('click', '.btn-proses-gaji', function(e) {
        e.preventDefault();
        const terapisId = this.dataset.terapisId;
        window.bukaOffcanvas(terapisId);
    });

    // --- Close Button Modal Setting ---
    $(document).on('click', '.btn-close-modal-setting', function(e) {
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
        showSwalError('Gaji bersih belum terhitung. Pastikan data kehadiran dan perhitungan sudah benar.');
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

// 4. FUNGSI GLOBAL (Exposed untuk onclick HTML)
// Menggunakan window. agar pasti terbaca oleh atribut onclick di Datatables

window.bukaModalSetting = (id, tipe, nominal) => {
    const modal = document.getElementById('modalSetting');
    const content = document.getElementById('modalSettingContent');

    if (!modal) return;

    document.getElementById('set_terapis_id').value = id;
    document.getElementById('set_tipe_gaji').value = (tipe === 'Belum Diset') ? 'bulanan' : tipe;
    document.getElementById('set_nominal_gaji').value = formatRupiah(nominal);

    modal.classList.replace(MODAL_HIDDEN_CLASS, MODAL_VISIBLE_CLASS);
    setTimeout(() => content.classList.replace('scale-95', 'scale-100'), 10);
};

window.tutupModalSetting = () => {
    const modal = document.getElementById('modalSetting');
    const content = document.getElementById('modalSettingContent');

    content.classList.replace('scale-100', 'scale-95');
    setTimeout(() => modal.classList.replace(MODAL_VISIBLE_CLASS, MODAL_HIDDEN_CLASS), 200);
};

window.bukaOffcanvas = (terapisId) => {
    const config = window.gajiConfig;
    const offcanvas = document.getElementById('offcanvasProses');
    const backdrop = document.getElementById('offcanvasBackdrop');
    const loading = document.getElementById('loadingState');
    const form = document.getElementById('formBayarGaji');

    backdrop.classList.remove('hidden');
    offcanvas.classList.remove('translate-x-full');
    loading.classList.remove('hidden');
    form.classList.add('hidden');

    fetch(`${config.detailUrl}/${terapisId}`)
        .then(response => response.json())
        .then(res => {
            if (res.status === 'success') {
                const data = res.data;
                document.getElementById('oc_terapis_id').value = data.id;
                document.getElementById('oc_nama_terapis').innerText = data.nama;

                const tipeGaji = data.tipe_gaji || 'bulanan';
                const nominalGaji = parseInt(data.nominal_gaji) || 0;
                const totalKasbon = parseInt(data.total_kasbon) || 0;
                const totalTunjangan = parseInt(data.total_tunjangan) || 0;
                const currentKehadiran = parseInt(data.current_kehadiran) || 0;
                const attendanceInput = document.getElementById('oc_kehadiran');
                const attendanceGroup = document.getElementById('oc_kehadiran_group');
                const attendanceLabel = document.getElementById('oc_kehadiran_label');
                const tipeInfo = document.getElementById('oc_tipe_info');
                const gajiPokokInput = document.getElementById('oc_gaji_pokok');
                const tunjanganInput = document.getElementById('oc_tunjangan');
                const potonganInput = document.getElementById('oc_potongan');
                const bersihInput = document.getElementById('oc_bersih');
                const tipeGajiField = document.getElementById('oc_tipe_gaji');

                const renderPayroll = () => {
                    const attendance = parseInt(attendanceInput.value) || 0;
                    const gajiPokokTotal = tipeGaji === 'harian'
                        ? nominalGaji * attendance
                        : nominalGaji;

                    const gajiBersih = gajiPokokTotal + totalTunjangan - totalKasbon;

                    gajiPokokInput.value = formatRupiah(gajiPokokTotal);
                    tunjanganInput.value = formatRupiah(totalTunjangan);
                    potonganInput.value = formatRupiah(totalKasbon);
                    bersihInput.value = formatRupiah(gajiBersih);
                };

                const updateFormState = () => {
                    tipeGajiField.value = tipeGaji;
                    form.dataset.tipeGaji = tipeGaji;

                    if (tipeGaji === 'harian') {
                        attendanceGroup.classList.remove('hidden');
                        attendanceInput.required = true;
                        attendanceInput.value = currentKehadiran;
                        attendanceLabel.innerText = 'Total Kehadiran (Hari)';
                        tipeInfo.innerText = 'Gaji harian dihitung dari kehadiran real karyawan setiap bulan.';
                    } else {
                        attendanceGroup.classList.add('hidden');
                        attendanceInput.required = false;
                        attendanceInput.value = 0;
                        attendanceLabel.innerText = 'Kehadiran tidak diperlukan';
                        tipeInfo.innerText = 'Gaji bulanan tetap sesuai nominal yang sudah diatur.';
                    }
                };

                attendanceInput.oninput = renderPayroll;
                updateFormState();
                renderPayroll();

                loading.classList.add('hidden');
                form.classList.remove('hidden');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            loading.innerHTML = '<p class="text-red-500 mt-4 text-center">Gagal memuat data.</p>';
        });
};

window.tutupOffcanvas = () => {
    document.getElementById('offcanvasProses').classList.add('translate-x-full');
    setTimeout(() => document.getElementById('offcanvasBackdrop').classList.add('hidden'), 300);
};

// 5. INITIALIZE
document.addEventListener('DOMContentLoaded', setupGajiPage);