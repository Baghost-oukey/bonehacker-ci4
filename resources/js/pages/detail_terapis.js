/**
 * Detail Terapis Page Script
 * Handles photo preview, validation, and modal interactions
 */

const MODAL_VISIBLE_CLASS = "flex";
const MODAL_HIDDEN_CLASS = "hidden";

const openModal = (modal) => {
    if (!modal) return;
    modal.classList.remove(MODAL_HIDDEN_CLASS);
    modal.classList.add(MODAL_VISIBLE_CLASS);
};

const closeModal = (modal) => {
    if (!modal) return;
    modal.classList.remove(MODAL_VISIBLE_CLASS);
    modal.classList.add(MODAL_HIDDEN_CLASS);
};

const setupDetailTerapisPage = () => {
    const config = window.detailTerapisConfig;
    const page = document.getElementById("detailTerapisPage");

    if (!config || !page || typeof window.$ === "undefined") return;

    const $ = window.$;
    const swalLib = window.Swal || window.swal;

    // Photo container overlay
    const photoContainer = document.querySelector('.photo-container');
    const overlay = document.querySelector('.overlay');

    if (photoContainer && overlay) {
        photoContainer.addEventListener('mouseenter', () => {
            overlay.style.opacity = '1';
            overlay.style.visibility = 'visible';
        });

        photoContainer.addEventListener('mouseleave', () => {
            overlay.style.opacity = '0';
            overlay.style.visibility = 'hidden';
        });
    }

    // Global functions for onclick handlers
    window.triggerEdit = function() {
        document.getElementById('foto').click();
    };

    window.previewQr = function() {
        openModal(document.getElementById('modalQrPreview'));
    };

    window.previewImageModal = function() {
        const previewImg = document.getElementById('photo-preview');
        const fullPreview = document.getElementById('full-photo-preview');
        if (previewImg && fullPreview) {
            fullPreview.src = previewImg.src;
            openModal(document.getElementById('modalPhotoPreview'));
        }
    };

    window.previewImage = function(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const preview = document.getElementById('photo-preview');
            if (preview) {
                preview.src = reader.result;
            }
        };
        if (event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    };

    window.confirmDeletePhoto = function() {
        openModal(document.getElementById('modalDeletePhoto'));
    };

    window.showErrorModal = function(msg) {
        document.getElementById('fileErrorMessage').innerText = msg;
        openModal(document.getElementById('modalFileError'));
    };

    // ID Validation
    const terapisIdInput = document.getElementById('terapis_id');
    const submitBtn = document.getElementById('btnSimpan');
    const feedbackElement = document.querySelector('.id-feedback');

    const checkIdAvailability = (id) => {
        if (id.trim() === '') {
            terapisIdInput.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500/15');
            terapisIdInput.classList.remove('border-teal-500', 'focus:border-teal-500', 'focus:ring-teal-500/15');
            feedbackElement.classList.remove('hidden');
            feedbackElement.textContent = 'ID tidak boleh kosong';
            feedbackElement.style.color = '#ef4444';
            submitBtn.disabled = true;
            return;
        }

        $.ajax({
            url: config.checkIdUrl,
            type: 'POST',
            data: {
                terapis_id: id,
                currentId: config.currentId,
                [config.csrfName]: config.csrfHash
            },
            dataType: 'json',
            success: function(response) {
                if (response.exists) {
                    terapisIdInput.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500/15');
                    terapisIdInput.classList.remove('border-teal-500', 'focus:border-teal-500', 'focus:ring-teal-500/15');
                    feedbackElement.classList.remove('hidden');
                    feedbackElement.textContent = 'ID sudah ada, gunakan ID lain';
                    feedbackElement.style.color = '#ef4444';
                    submitBtn.disabled = true;
                } else {
                    terapisIdInput.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500/15');
                    terapisIdInput.classList.add('border-teal-500', 'focus:border-teal-500', 'focus:ring-teal-500/15');
                    feedbackElement.classList.add('hidden');
                    submitBtn.disabled = false;
                }
            },
            error: function() {
                terapisIdInput.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500/15');
                feedbackElement.classList.remove('hidden');
                feedbackElement.textContent = 'Gagal memeriksa ID';
                feedbackElement.style.color = '#ef4444';
                submitBtn.disabled = true;
            }
        });
    };

    // Debounce ID check
    let debounceTimer;
    if (terapisIdInput) {
        terapisIdInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const id = this.value;
            debounceTimer = setTimeout(() => checkIdAvailability(id), 500);
        });
    }

    // Form submission validation
    const form = document.getElementById('detailterapis');
    if (form) {
        form.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('foto');
            const file = fileInput.files[0];

            if (file) {
                const validFormats = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!validFormats.includes(file.type)) {
                    e.preventDefault();
                    showErrorModal('Format file harus JPG, JPEG, atau PNG.');
                    return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    e.preventDefault();
                    showErrorModal('Ukuran file maksimal 2MB.');
                    return;
                }
            }

            // Check required fields
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('border-red-500');
                    isValid = false;
                } else {
                    field.classList.remove('border-red-500');
                }
            });

            if (!isValid) {
                e.preventDefault();
                if (swalLib?.fire) {
                    swalLib.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Mohon lengkapi semua field yang wajib diisi',
                    });
                }
            }
        });
    }

    // Modal handlers
    document.addEventListener("click", (event) => {
        const closeTrigger = event.target.closest("[data-modal-close]");
        if (closeTrigger) {
            closeModal(closeTrigger.closest(".modal-wrapper"));
            return;
        }

        if (event.target.classList && event.target.classList.contains("modal-wrapper")) {
            closeModal(event.target);
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            const visibleModals = document.querySelectorAll('.modal-wrapper.flex');
            visibleModals.forEach(modal => closeModal(modal));
        }
    });
};

// Initialize page
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupDetailTerapisPage);
} else {
    setupDetailTerapisPage();
}
