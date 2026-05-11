const setupTambahPresensiPage = () => {
    const config = window.tambahPresensiConfig;
    const page = document.getElementById("tambahPresensiPage");
    
    if (!config || !page || typeof window.$ === "undefined") return;

    const $ = window.$;
    const swalLib = window.Swal || window.swal;
    const formTambahPresensi = document.getElementById("formTambahPresensi");
    const selectAllCheckbox = document.getElementById("selectAll");
    const terapisCheckboxes = document.querySelectorAll('input[name="terapis_ids[]"]');

    if (!formTambahPresensi) return;

    // Select All functionality
    if (selectAllCheckbox && terapisCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener('change', function() {
            terapisCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Update Select All checkbox when individual checkboxes change
        terapisCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(terapisCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(terapisCheckboxes).some(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            });
        });
    }

    // Form submit
    $(formTambahPresensi).on('submit', function(e) {
        e.preventDefault();
        
        const tanggal = $('#tanggal_presensi').val();
        const selectedTerapis = $('input[name="terapis_ids[]"]:checked').length;

        if (!tanggal) {
            swalLib.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Pilih tanggal presensi terlebih dahulu',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        if (selectedTerapis === 0) {
            swalLib.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Pilih minimal satu terapis',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        const btn = $('#btnSimpan');
        const originalText = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...').prop('disabled', true).addClass('opacity-70');

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
                    // Langsung redirect tanpa alert
                    window.location.href = config.redirectUrl;
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

document.addEventListener("DOMContentLoaded", setupTambahPresensiPage);
