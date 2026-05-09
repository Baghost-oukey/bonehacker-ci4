/**
 * Cicilan Module - Logic untuk Pembayaran Kasbon Manual
 * Disusun identik dengan Kasbon Module untuk konsistensi UX
 */
const setupCicilanModule = () => {
    const config = window.kasbonDetailConfig;
    
    // Safety check dependencies
    if (!config || typeof window.$ === "undefined") return;

    // Ambil data dari config (Pastikan namanya sinkron dengan yang di View)
    // Di sini kita pakai variabel totalHutangAktif yang Mas buat di baris sebelumnya
    const hutangLimit = parseInt(config.totalHutang) || 0; 
    const csrfName = config.csrfName;
    const cicilanUrl = config.cicilanUrl;

    const swal = window.Swal || null;
    const $ = window.$;

    // --- 1. HELPERS ---
    const formatIDR = (num) => new Intl.NumberFormat('id-ID').format(num);
    const parseNumber = (str) => parseInt(str.toString().replace(/[^0-9]/g, '')) || 0;

    // --- 2. DOM ELEMENTS ---
    const el = {
        input: document.getElementById('inputNominalCicilan'),
        estimasi: document.getElementById('estimasiSisaHutang'),
        btn: document.getElementById('btnSubmitCicilan'),
        form: document.getElementById('formPenyicilanKasbon')
    };

    // --- 3. DISPLAY & CALCULATION LOGIC ---
    const updateDisplay = (val) => {
        // Gunakan hutangLimit yang diambil dari config di atas
        let cleanVal = Math.min(val, hutangLimit);

        // Update nilai di field input dengan format ribuan & jaga posisi kursor
        if (el.input) {
            let selectionStart = el.input.selectionStart;
            let oldLength = el.input.value.length;

            const formatted = formatIDR(cleanVal);
            el.input.value = formatted;

            let newLength = formatted.length;
            selectionStart = selectionStart + (newLength - oldLength);
            el.input.setSelectionRange(selectionStart, selectionStart);
        }

        // Update tampilan estimasi sisa hutang akhir setelah dibayar
        if (el.estimasi) {
            const sisaHutangAkhir = hutangLimit - cleanVal;
            el.estimasi.innerText = `Rp ${formatIDR(sisaHutangAkhir)}`;
        }

        // Validasi tombol submit (Harus > 0 dan <= hutangLimit)
        const isValid = cleanVal > 0 && cleanVal <= hutangLimit;
        
        if (el.btn) {
            el.btn.disabled = !isValid;
            el.btn.classList.toggle('opacity-50', !isValid);
            el.btn.classList.toggle('cursor-not-allowed', !isValid);
            
            // Indigo style untuk membedakan dengan Kasbon (Teal)
            el.btn.classList.toggle('bg-indigo-600', isValid);
            el.btn.classList.toggle('bg-slate-400', !isValid);
        }
    };

    // --- 4. EVENT LISTENERS ---
    if (el.input) {
        el.input.addEventListener('input', (e) => {
            const rawVal = parseNumber(e.target.value);
            updateDisplay(rawVal);
        });

        el.input.addEventListener('focus', (e) => {
            if (parseNumber(e.target.value) === 0) e.target.value = '';
        });

        el.input.addEventListener('blur', (e) => {
            if (e.target.value === '' || parseNumber(e.target.value) === 0) updateDisplay(0);
        });

        updateDisplay(0); // Initial state
    }

    // --- 5. AJAX SUBMISSION ---
    if (el.form) {
        $(el.form).on('submit', function (e) {
            e.preventDefault();
            const nominal = parseNumber(el.input.value);
            if (nominal <= 0) return;

            const originalBtnText = el.btn.innerHTML;
            el.btn.disabled = true;
            el.btn.innerHTML = `<i class="fas fa-circle-notch fa-spin mr-2"></i> Mencatat Pembayaran...`;

            const formData = new FormData(this);
            formData.append(csrfName, window.kasbonDetailConfig.csrfHash);

            $.ajax({
                url: cicilanUrl,
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
                                title: 'Pembayaran Berhasil!',
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
                error: () => handleError('Gagal terhubung ke server. Periksa koneksi Anda.')
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

// Start
document.addEventListener("DOMContentLoaded", setupCicilanModule);