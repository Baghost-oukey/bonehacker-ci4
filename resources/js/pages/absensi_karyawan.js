const setupAbsensiPage = () => {
    const config = window.absensiConfig;
    const page = document.getElementById("absensiPage");
    
    if (!config || !page || typeof window.$ === "undefined") return;

    const $ = window.$;
    const swalLib = window.Swal || window.swal;
    const formAbsensi = document.getElementById("formAbsensi");
    const tanggalInput = document.getElementById("tanggal_absen");
    const tanggalHidden = document.getElementById("tanggal_absen_hidden");

    if (!formAbsensi) return;

    if (tanggalInput && tanggalHidden) {
        tanggalInput.addEventListener('change', () => {
            tanggalHidden.value = tanggalInput.value;
        });
    }

    $(formAbsensi).on('submit', function(e) {
        e.preventDefault();
        
        const btn = $('#btnSimpanAbsen');
        const originalText = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...').prop('disabled', true).addClass('opacity-70');

        // Ambil data form
        let formData = $(this).serializeArray();
        
        // Tambahkan CSRF Token bila belum ada
        if (!formData.some(field => field.name === config.csrfName)) {
            formData.push({ name: config.csrfName, value: config.csrfHash });
        }

        $.ajax({
            url: config.storeUrl,
            type: "POST",
            data: $.param(formData),
            dataType: "json",
            success: function(response) {
                if (response.csrfHash) config.csrfHash = response.csrfHash; 

                if (response.status === 'success') {
                    swalLib.fire({
                        icon: 'success',
                        title: 'Tersimpan!',
                        text: response.message,
                        confirmButtonColor: '#4f46e5',
                        customClass: { popup: 'rounded-3xl' }
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    swalLib.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message || "Terjadi kesalahan",
                        confirmButtonColor: '#4f46e5'
                    });
                    btn.html(originalText).prop('disabled', false).removeClass('opacity-70');
                }
            },
            error: function(xhr, status, error) {
                swalLib.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: 'Gagal menghubungi server.',
                    confirmButtonColor: '#4f46e5'
                });
                btn.html(originalText).prop('disabled', false).removeClass('opacity-70');
            }
        });
    });
};

document.addEventListener("DOMContentLoaded", setupAbsensiPage);