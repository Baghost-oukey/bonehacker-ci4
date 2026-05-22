$(document).ready(function() {
    // Helper Format Rupiah
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

    const createManualRowHTML = () => {
        return `
            <tr class="manual-item-row hover:bg-slate-50 transition-colors">
                <td class="p-2">
                    <select name="manual_kelompok[]" class="manual-kelompok border-slate-200 rounded-lg text-[11px] bg-white p-1.5 focus:ring-indigo-500 w-full">
                        <option value="take_home">Gaji Pokok & Jaspel</option>
                        <option value="benefit">Tunjangan (Cash)</option>
                        <option value="benefit_non_cash">Tunjangan Non-Tunai</option>
                        <option value="potongan">Potongan</option>
                    </select>
                </td>
                <td class="p-2">
                    <input type="text" name="manual_deskripsi[]" placeholder="Deskripsi" class="manual-deskripsi border-slate-200 rounded-lg text-[11px] p-1.5 focus:ring-indigo-500 w-full" required>
                </td>
                <td class="p-2">
                    <input type="text" name="manual_nominal[]" placeholder="Rp" class="manual-nominal input-rupiah border-slate-200 rounded-lg text-[11px] p-1.5 focus:ring-indigo-500 text-right font-bold text-slate-700 w-full" required>
                </td>
                <td class="p-2 text-center">
                    <button type="button" class="btn-hapus-manual-item text-rose-500 hover:text-rose-700 hover:bg-rose-50 p-1.5 rounded-lg transition">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
    };

    const recalculateReviewTotals = () => {
        const form = $('#formProsesGaji');
        if (form.length === 0) return;

        const baseTakeHome = parseFloat(form.attr('data-base-take-home')) || 0;
        const baseBenefit = parseFloat(form.attr('data-base-benefit')) || 0;
        const basePotongan = parseFloat(form.attr('data-base-potongan')) || 0;
        const baseBenefitNonCash = parseFloat(form.attr('data-base-benefit-non-cash')) || 0;

        let addTakeHome = 0;
        let addBenefit = 0;
        let addPotongan = 0;
        let addBenefitNonCash = 0;

        // Clear lists first
        $('#review_manual_take_home_list').empty();
        $('#review_manual_benefit_list').empty();
        $('#review_manual_benefit_non_cash_list').empty();
        $('#review_manual_potongan_list').empty();

        // Loop through inputs
        $('#review_manual_items_container .manual-item-row').each(function() {
            const kelompok = $(this).find('.manual-kelompok').val();
            const deskripsi = $(this).find('.manual-deskripsi').val() || '';
            const nominalStr = $(this).find('.manual-nominal').val() || '0';
            const nominal = parseFloat(nominalStr.replace(/[^0-9]/g, '')) || 0;

            if (deskripsi && nominal > 0) {
                const displayNominal = formatRupiah(nominal);
                if (kelompok === 'take_home') {
                    addTakeHome += nominal;
                    $('#review_manual_take_home_list').append(`
                        <div class="flex justify-between px-5 py-3 text-sm text-indigo-600 font-medium animate-fade-in">
                            <span>${deskripsi} (Manual)</span>
                            <span>${displayNominal}</span>
                        </div>
                    `);
                } else if (kelompok === 'benefit') {
                    addBenefit += nominal;
                    $('#review_manual_benefit_list').append(`
                        <div class="flex justify-between px-5 py-3 text-sm text-indigo-600 font-medium animate-fade-in">
                            <span>${deskripsi} (Manual)</span>
                            <span>${displayNominal}</span>
                        </div>
                    `);
                } else if (kelompok === 'benefit_non_cash') {
                    addBenefitNonCash += nominal;
                    $('#review_manual_benefit_non_cash_list').append(`
                        <div class="flex justify-between px-5 py-3 text-sm text-indigo-600 font-medium animate-fade-in">
                            <span>${deskripsi} (Manual)</span>
                            <span>${displayNominal}</span>
                        </div>
                    `);
                } else if (kelompok === 'potongan') {
                    addPotongan += nominal;
                    $('#review_manual_potongan_list').append(`
                        <div class="flex justify-between px-5 py-3 text-sm text-rose-500 font-medium animate-fade-in">
                            <span>${deskripsi} (Manual)</span>
                            <span>- ${displayNominal}</span>
                        </div>
                    `);
                }
            }
        });

        const finalTakeHome = baseTakeHome + addTakeHome;
        const finalBenefit = baseBenefit + addBenefit;
        const finalPotongan = basePotongan + addPotongan;
        const finalBenefitNonCash = baseBenefitNonCash + addBenefitNonCash;

        const finalBersih = (finalTakeHome + finalBenefit) - finalPotongan;

        // Update texts
        $('#review_total_take_home').text(formatRupiah(finalTakeHome));
        $('#review_total_benefit').text(formatRupiah(finalBenefit));
        $('#review_total_benefit_non_cash').text(formatRupiah(finalBenefitNonCash));
        $('#review_total_potongan').text('- ' + formatRupiah(finalPotongan));

        // Update bottom summary
        $('#review_take_home_benefit').text(formatRupiah(finalTakeHome + finalBenefit));
        $('#review_summary_potongan').text('- ' + formatRupiah(finalPotongan));
        $('#review_gaji_bersih').text(formatRupiah(Math.max(0, finalBersih)));

        // Handle empty state displays
        if (finalBenefit > 0) {
            $('#review_empty_benefit').addClass('hidden');
        } else {
            $('#review_empty_benefit').removeClass('hidden');
        }

        if (finalBenefitNonCash > 0) {
            $('#review_empty_benefit_non_cash').addClass('hidden');
        } else {
            $('#review_empty_benefit_non_cash').removeClass('hidden');
        }

        if (finalPotongan > 0) {
            $('#review_empty_potongan').addClass('hidden');
        } else {
            $('#review_empty_potongan').removeClass('hidden');
        }
    };

    // --- Event Handlers ---
    $(document).on('keyup', '.input-rupiah', function () {
        this.value = formatRupiah(this.value, 'Rp ');
    });

    $('#btnTambahItemManualReview').on('click', function(e) {
        e.preventDefault();
        $('#review_manual_items_container').append(createManualRowHTML());
        recalculateReviewTotals();
    });

    $(document).on('click', '#review_manual_items_container .btn-hapus-manual-item', function(e) {
        e.preventDefault();
        $(this).closest('.manual-item-row').remove();
        recalculateReviewTotals();
    });

    $(document).on('input change', '#review_manual_items_container input, #review_manual_items_container select', function() {
        recalculateReviewTotals();
    });

    $('#formProsesGaji').on('submit', function(e) {
        e.preventDefault();
        
        let form = $(this);
        let actionUrl = form.attr('action'); // Ambil URL dari atribut action form
        let btn = $('#btnKonfirmasi');
        let originalText = btn.html();
        
        // Animasi Loading Tombol
        btn.html('Memproses...').prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

        $.ajax({
            url: actionUrl,
            type: "POST",
            data: form.serialize(),
            dataType: "json",
            success: function(response) {
                // Update CSRF token agar aman jika ada request berikutnya
                if(response.csrfHash) {
                    $('input[name="csrf_test_name"]').val(response.csrfHash);
                }

                if(response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        confirmButtonColor: '#4f46e5',
                        customClass: { popup: 'rounded-3xl' }
                    }).then(() => {
                        // Trik redirect ke halaman utama (hapus '/proses_simpan' dari URL)
                        let redirectUrl = actionUrl.replace('/proses_simpan', '');
                        window.location.href = redirectUrl;
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message,
                        confirmButtonColor: '#4f46e5',
                        customClass: { popup: 'rounded-3xl' }
                    });
                    // Kembalikan tombol seperti semula
                    btn.html(originalText).prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: 'Terjadi kesalahan pada server. ' + error,
                    confirmButtonColor: '#4f46e5'
                });
                // Kembalikan tombol seperti semula
                btn.html(originalText).prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            }
        });
    });

});