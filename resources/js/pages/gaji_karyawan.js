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

    // --- A. Tab Navigation ---
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

    // --- B. Auto Format Rupiah on Input ---
    $(document).on('keyup', '.input-rupiah', function () {
        this.value = formatRupiah(this.value, 'Rp ');
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
                const data = res.data.terapis;
                document.getElementById('oc_terapis_id').value = data.id;
                document.getElementById('oc_nama_terapis').innerText = data.nama;

                let nominalGajiPokok = data.nominal_gaji || 0;
                let totalPotongan = 0;
                let gajiBersih = parseInt(nominalGajiPokok) - totalPotongan;

                document.getElementById('oc_gaji_pokok').value = formatRupiah(nominalGajiPokok);
                document.getElementById('oc_potongan').value = formatRupiah(totalPotongan);
                document.getElementById('oc_bersih').value = formatRupiah(gajiBersih);

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