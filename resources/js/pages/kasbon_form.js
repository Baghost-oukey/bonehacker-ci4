/**
 * Kasbon Module - Unified Logic (Tabs + Form)
 * Handles navigation and loan application in one place.
 */
const setupKasbonModule = () => {
    const config = window.kasbonDetailConfig;
    // Safety check dependencies
    if (!config || typeof window.$ === "undefined") return;

    const { sisaLimit, csrfName, storeUrl } = config;
    // const { sisaLimit, totalHutang, csrfName, storeUrl, cicilanUrl } = config;
    const swal = window.Swal || null;
    const $ = window.$;

    // --- 1. HELPERS ---
    const formatIDR = (num) => new Intl.NumberFormat('id-ID').format(num);
    const parseNumber = (str) => parseInt(str.toString().replace(/[^0-9]/g, '')) || 0;

    // --- 2. TABS NAVIGATION ---
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const target = this.getAttribute('data-target');

            // Toggle active styles (Teal Theme)
            tabBtns.forEach(b => {
                b.classList.remove('text-teal-600', 'border-teal-600', 'active');
                b.classList.add('text-slate-400', 'border-transparent');
            });
            this.classList.add('text-teal-600', 'border-teal-600', 'active');
            this.classList.remove('text-slate-400', 'border-transparent');

            // Switch Content with Animation
            tabContents.forEach(c => {
                c.classList.add('hidden');
                c.classList.remove('animate-in', 'fade-in', 'duration-300');
            });

            const activeContent = document.getElementById(target);
            if (activeContent) {
                activeContent.classList.remove('hidden');
                activeContent.classList.add('animate-in', 'fade-in', 'duration-300');
            }
        });
    });

    // --- 3. FORM CALCULATION ---
    const el = {
        input: document.getElementById('inputNominal'),
        estimasi: document.getElementById('estimasiSisaGaji'),
        btn: document.getElementById('btnSubmitKasbon'),
        form: document.getElementById('formPengajuanKasbon')
    };

    const updateDisplay = (val) => {
        let cleanVal = sisaLimit > 0 ? Math.min(val, sisaLimit) : val;
        if (el.input) {
            let selectionStart = el.input.selectionStart;
            let oldLength = el.input.value.length;
            const formatted = formatIDR(cleanVal);
            el.input.value = formatted;
            let newLength = formatted.length;
            selectionStart = selectionStart + (newLength - oldLength);
            el.input.setSelectionRange(selectionStart, selectionStart);
        }

        if (el.estimasi) {
            const sisa = sisaLimit - cleanVal;
            el.estimasi.innerText = `Rp ${formatIDR(sisa)}`;
        }

        const isValid = cleanVal > 0 && (sisaLimit > 0 ? cleanVal <= sisaLimit : true);

        if (el.btn) {
            el.btn.disabled = !isValid;
            el.btn.classList.toggle('opacity-50', !isValid);
            el.btn.classList.toggle('cursor-not-allowed', !isValid);
            // Ganti warna tombol jika valid
            el.btn.classList.toggle('bg-teal-600', isValid);
            el.btn.classList.toggle('bg-slate-400', !isValid);
        }
    };

    if (el.input) {
        el.input.addEventListener('input', (e) => {
            const rawVal = parseNumber(e.target.value);
            updateDisplay(rawVal);
        });
        updateDisplay(0); // Set initial state
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
                error: () => handleError('Gagal terhubung ke server. Silakan coba lagi.')
            });

            function handleError(msg) {
                if (swal) {
                    swal.fire({ icon: 'error', title: 'Oops...', text: msg });
                } else {
                    alert(msg);
                }
                el.btn.disabled = false;
                el.btn.innerHTML = originalBtnText;
            }
        });
    }
};

// Initialize on Load
document.addEventListener("DOMContentLoaded", setupKasbonModule);