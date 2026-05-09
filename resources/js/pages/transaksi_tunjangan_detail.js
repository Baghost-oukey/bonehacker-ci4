// Helper untuk mendapatkan payload CSRF
const getCsrfPayload = (config) => ({
    [config.csrfName]: config.csrfHash,
});

const setupTunjanganDetailPage = () => {
    const config = window.tunjanganDetailConfig;

    if (!config || typeof window.$ === "undefined") return;

    const $ = window.$;
    const swalLib = window.Swal || window.swal;
    const formInput = document.getElementById("formInputTunjangan");
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => {
                b.classList.remove('text-indigo-600', 'border-indigo-600', 'active');
                b.classList.add('text-slate-400', 'border-transparent');
            });
            tabContents.forEach(c => c.classList.add('hidden'));

            btn.classList.add('text-indigo-600', 'border-indigo-600', 'active');
            btn.classList.remove('text-slate-400', 'border-transparent');

            const targetId = btn.getAttribute('data-target');
            const activeTab = document.getElementById(targetId);
            if (activeTab) {
                activeTab.classList.remove('hidden');
                activeTab.classList.add('animate-in', 'fade-in', 'duration-300');
            }
        });
    });

    if (!formInput) return;

    $(formInput).on('submit', function(e) {
        e.preventDefault();

        const btnSubmit = $('#btnSubmitTunjangan');
        const originalText = btnSubmit.html();

        btnSubmit.html('Memproses...').prop('disabled', true).addClass('opacity-70 cursor-not-allowed');

        const formData = new FormData(this);
        formData.append(config.csrfName, config.csrfHash);

        $.ajax({
            url: config.storeUrl,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(response) {
                if (response.csrfHash) {
                    config.csrfHash = response.csrfHash;
                }

                if (response.status === 'success') {
                    if (swalLib) {
                        swalLib.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            confirmButtonColor: '#4f46e5',
                            customClass: { popup: 'rounded-3xl' }
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        alert(response.message);
                        window.location.reload();
                    }
                } else {
                    if (swalLib) {
                        swalLib.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message,
                            confirmButtonColor: '#4f46e5',
                            customClass: { popup: 'rounded-3xl' }
                        });
                    } else {
                        alert(response.message);
                    }
                    btnSubmit.html(originalText).prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
                }
            },
            error: function(xhr, status, error) {
                if (swalLib) {
                    swalLib.fire({
                        icon: 'error',
                        title: 'Kesalahan Sistem',
                        text: 'Terjadi kesalahan pada server. ' + error,
                        confirmButtonColor: '#4f46e5'
                    });
                } else {
                    alert('Terjadi kesalahan pada server. ' + error);
                }
                btnSubmit.html(originalText).prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
            }
        });
    });
};

// Eksekusi fungsi setup saat DOM selesai dimuat
document.addEventListener("DOMContentLoaded", setupTunjanganDetailPage);