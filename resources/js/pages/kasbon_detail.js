/**
 * Kasbon Detail & Application Page Script
 * Handles Tabs, Interactive Slider, and AJAX form submission
 */

// === 1. GLOBAL HELPER FUNCTIONS ===
const formatRupiah = (angka) => {
    let number_string = angka.toString().replace(/[^,\d]/g, '');
    let split = number_string.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);
    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }
    return rupiah;
};
const setupKasbonModule = () => {
    const config = window.kasbonDetailConfig;

    // 1. Pengecekan Dependensi
    if (!config || typeof window.$ === "undefined") return;

    const { sisaLimit, csrfName, storeUrl } = config;
    const swal = window.Swal || null;
    const $ = window.$;

    // --- HELPERS ---
    const formatIDR = (num) => new Intl.NumberFormat('id-ID').format(num);
    const parseNumber = (str) => parseInt(str.toString().replace(/[^0-9]/g, '')) || 0;

    // --- 2. LOGIKA TABS ---
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const target = this.getAttribute('data-target');

            tabBtns.forEach(b => {
                b.classList.remove('text-teal-600', 'border-teal-600', 'active');
                b.classList.add('text-slate-400', 'border-transparent');
            });
            this.classList.add('text-teal-600', 'border-teal-600', 'active');

            tabContents.forEach(c => c.classList.add('hidden'));
            const activeTab = document.getElementById(target);
            if (activeTab) {
                activeTab.classList.remove('hidden');
                activeTab.classList.add('animate-in', 'fade-in', 'duration-300');
            }
        });
    });

    // --- 3. LOGIKA KALKULATOR ---
    const el = {
        input: document.getElementById('inputNominal'),
        estimasi: document.getElementById('estimasiSisaGaji'),
        btn: document.getElementById('btnSubmitKasbon'),
        form: document.getElementById('formPengajuanKasbon')
    };

    const updateDisplay = (val) => {
        // Mencegah input melebihi sisa plafon
        let cleanVal = Math.min(val, sisaLimit);

        if (el.input) el.input.value = formatIDR(cleanVal);

        if (el.estimasi) {
            const sisa = sisaLimit - cleanVal;
            el.estimasi.innerText = `Rp ${formatIDR(sisa)}`;
        }

        const isValid = cleanVal > 0 && cleanVal <= sisaLimit;
        if (el.btn) {
            el.btn.disabled = !isValid;
            el.btn.classList.toggle('opacity-50', !isValid);
            el.btn.classList.toggle('cursor-not-allowed', !isValid);
        }
    };

    if (el.input) {
        el.input.addEventListener('input', (e) => {
            updateDisplay(parseNumber(e.target.value));
        });
        updateDisplay(0); // Inisialisasi awal
    }

    // --- 4. AJAX SUBMISSION ---
    if (el.form) {
        $(el.form).on('submit', function (e) {
            e.preventDefault();

            const nominal = parseNumber(el.input.value);
            if (nominal <= 0) return;

            const originalBtnText = el.btn.innerHTML;
            el.btn.disabled = true;
            el.btn.innerHTML = `<i class="fas fa-circle-notch fa-spin mr-2"></i> Memproses...`;
            const formData = new FormData(this);
            formData.append(csrfName, window.kasbonDetailConfig.csrfHash);

            $.ajax({
                url: storeUrl,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    if (res.status === 'success') {
                        if (swal) {
                            swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => location.reload());
                        } else {
                            alert(res.message);
                            location.reload();
                        }
                    } else {
                        handleError(res.message);
                    }
                },
                error: () => handleError('Koneksi bermasalah atau sesi berakhir.')
            });

            function handleError(msg) {
                if (swal) swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                else alert(msg);

                el.btn.disabled = false;
                el.btn.innerHTML = originalBtnText;
            }
        });
    }
};

document.addEventListener("DOMContentLoaded", setupKasbonModule);