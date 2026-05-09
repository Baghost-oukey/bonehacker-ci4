$(document).ready(function() {
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