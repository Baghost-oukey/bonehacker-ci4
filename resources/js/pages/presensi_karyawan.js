const setupPresensiPage = () => {
    const config = window.presensiConfig;
    const page = document.getElementById("presensiPage");
    
    if (!config || !page || typeof window.$ === "undefined") return;

    const $ = window.$;
    const swalLib = window.Swal || window.swal;
    const formPresensi = document.getElementById("formPresensi");
    const tanggalInput = document.getElementById("tanggal_presensi");
    const tanggalHidden = document.getElementById("tanggal_presensi_hidden");

    if (!formPresensi) return;

    // Sinkronisasi tanggal input dengan hidden field
    if (tanggalInput && tanggalHidden) {
        tanggalInput.addEventListener('change', () => {
            const newDate = tanggalInput.value;
            tanggalHidden.value = newDate;
            
            // Reload halaman dengan tanggal baru untuk backdate
            const baseUrl = window.location.origin + window.location.pathname.split('/').slice(0, -1).join('/');
            window.location.href = baseUrl + '/presensi/' + newDate;
        });
    }

    $(formPresensi).on('submit', function(e) {
        e.preventDefault();
        
        const btn = $('#btnSimpanPresensi');
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
                        // Redirect ke halaman kehadiran
                        window.location.href = config.redirectUrl || '/kehadiran';
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

document.addEventListener("DOMContentLoaded", setupPresensiPage);
