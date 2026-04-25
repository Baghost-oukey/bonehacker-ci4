// --- INIT SCRIPT ---
const fileUploadPage = {
    config: {
        fileUrls: [],
        fileBaseUrl: '',
        fileUploadUrl: '',
        csrfTokenName: '',
        csrfHash: ''
    },

    init() {
        this.loadConfig();
        this.initPreviewButtons();
        this.initBatchDelete();
        this.initUploadForm();
        this.initModalHandlers();
    },

    loadConfig() {
        this.config.fileUrls = window.fileUrlsData || [];
        this.config.fileBaseUrl = window.fileBaseUrl || '';
        this.config.fileUploadUrl = window.fileUploadUrl || '';
        this.config.csrfTokenName = window.csrfTokenName || 'csrf_token';
        this.config.csrfHash = window.csrfHash || '';
    },

    getFileUrl(fileUrl) {
        if (!fileUrl) return '';
        return fileUrl.startsWith('http') ? fileUrl : `${this.config.fileBaseUrl}/${fileUrl}`;
    },

    openModal(modal) {
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    },

    closeModal(modal) {
        if (!modal) return;
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        if (modal.id === 'fileUploadModal') {
            document.getElementById('fileUploadContent').innerHTML = '';
        }
    },

    showLoading(container) {
        if (!container) return;
        container.innerHTML = `
            <div class="text-center py-12 flex flex-col items-center">
                <i class="fas fa-spinner fa-spin text-3xl text-slate-400"></i>
                <p class="mt-3 text-sm font-medium text-slate-500">Membuka dokumen...</p>
            </div>`;
    },

    // --- MODAL UPLOAD ---
    previewFile(id) {
        const modalUpload = document.getElementById('mauupload');
        const modalPreview = document.getElementById('fileUploadModal');
        const contentContainer = document.getElementById('fileUploadContent');
        if (!modalPreview || !contentContainer) return;
        if (modalUpload) this.closeModal(modalUpload);
        this.showLoading(contentContainer);
        this.openModal(modalPreview);
        const fileUrls = this.config.fileUrls;
        const parsedUrls = typeof fileUrls === 'string' ? JSON.parse(fileUrls) : fileUrls;

        if (!parsedUrls || !parsedUrls[id]) {
            contentContainer.innerHTML = `
                <div class="text-center py-12 flex flex-col items-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-500 mb-3">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-900">File Tidak Ditemukan</h3>
                    <p class="text-sm text-slate-500">File mungkin telah dihapus atau dipindahkan.</p>
                </div>`;
            return;
        }

        const fileUrl = parsedUrls[id];
        const fullUrl = this.getFileUrl(fileUrl);
        const fileExtension = fileUrl.split('.').pop().toLowerCase();

        let fileContent = '';

        if (fileExtension === 'pdf') {
            fileContent = `
                <div class="flex flex-col h-full w-full">
                    <div class="flex justify-end mb-3">
                        <a href="${fullUrl}" target="_blank" class="inline-flex h-9 items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-sm font-medium text-slate-900 transition-colors hover:bg-slate-100 hover:text-slate-900 shadow-sm">
                            <i class="fas fa-external-link-alt mr-2"></i> Buka di Tab Baru
                        </a>
                    </div>
                    <embed src="${fullUrl}" type="application/pdf" class="w-full flex-1 rounded-lg border border-slate-200 shadow-sm min-h-[60vh]" />
                </div>`;
        } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
            fileContent = `
                <div class="flex flex-col items-center justify-center w-full">
                    <div class="flex justify-end w-full mb-3 max-w-4xl">
                        <a href="${fullUrl}" download class="inline-flex h-9 items-center justify-center rounded-md bg-slate-900 px-4 text-sm font-medium text-white shadow transition-colors hover:bg-slate-900/90">
                            <i class="fas fa-download mr-2"></i> Unduh Gambar
                        </a>
                    </div>
                    <img src="${fullUrl}" class="max-w-4xl max-h-[70vh] object-contain rounded-lg border border-slate-200 shadow-sm bg-white p-2" alt="Preview" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'text-center py-12 flex flex-col items-center\\'><div class=\\'flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-3\\'><i class=\\'fas fa-image text-xl\\'></i></div><p class=\\'text-sm font-medium text-slate-900\\'>Gambar Rusak</p></div>';" />
                </div>`;
        } else {
            fileContent = `
                <div class="text-center py-12 flex flex-col items-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4 border border-slate-200">
                        <i class="fas fa-file-code text-2xl text-slate-400"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-900 mb-1">Pratinjau Tidak Tersedia</h3>
                    <p class="text-sm text-slate-500 mb-4">Format file ini tidak dapat ditampilkan langsung di peramban.</p>
                    <a href="${fullUrl}" target="_blank" class="inline-flex h-9 items-center justify-center rounded-md bg-slate-900 px-4 text-sm font-medium text-white shadow transition-colors hover:bg-slate-900/90">
                        <i class="fas fa-download mr-2"></i> Unduh Berkas
                    </a>
                </div>`;
        }

        contentContainer.innerHTML = fileContent;
    },

    // --- PREVIEW BUTTON ---
    initPreviewButtons() {
        document.addEventListener('click', (e) => {
            const previewBtn = e.target.closest('.previewBtn');
            if (!previewBtn) return;

            const id = previewBtn.getAttribute('data-id');
            if (id !== null && id !== undefined) {
                this.previewFile(id);
            }
        });
    },

    // --- DELETE BUTTON ---
    initBatchDelete() {
        document.addEventListener('change', (e) => {
            if (!e.target.classList.contains('delete-checkbox')) return;
            const checkedCount = document.querySelectorAll('.delete-checkbox:checked').length;
            const batchDeleteBtn = document.getElementById('batchDeleteBtn');

            if (batchDeleteBtn) {
                if (checkedCount > 0) batchDeleteBtn.classList.remove('hidden');
                else batchDeleteBtn.classList.add('hidden');
            }
        });

        document.getElementById('batchDeleteBtn')?.addEventListener('click', function (e) {
            e.preventDefault();
            const form = this.closest('form');
            if (!form) return;
            const swalLib = window.Swal || window.swal;

            if (swalLib?.fire) {
                swalLib.fire({
                    title: 'Hapus Dokumen?',
                    text: 'Dokumen yang dihapus tidak dapat dikembalikan dari sistem.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626', // red-600
                    cancelButtonColor: '#f1f5f9', // slate-100
                    confirmButtonText: 'Ya, Hapus Data',
                    cancelButtonText: '<span style="color:#0f172a">Batal</span>',
                    reverseButtons: true,
                    customClass: {
                        popup: 'rounded-xl border border-slate-200 shadow-xl font-sans',
                        title: 'text-lg font-semibold text-slate-900 tracking-tight',
                        htmlContainer: 'text-sm text-slate-500'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        swalLib.fire({
                            title: 'Memproses...',
                            text: 'Sedang menghapus dokumen',
                            allowOutsideClick: false,
                            didOpen: () => { swalLib.showLoading(); }
                        });
                        form.submit();
                    }
                });
            } else {
                if (confirm('Hapus dokumen terpilih? Tindakan ini tidak bisa dibatalkan.')) {
                    form.submit();
                }
            }
        });
    },

    // --- UPLOAD FILE ---
    initUploadForm() {
        const self = this;
        const uploadForm = document.getElementById('uploadForm');
        const fileInput = document.getElementById('modalFileInput');
        const uploadAlert = document.getElementById('uploadAlert');
        if (!uploadForm || !fileInput || !uploadAlert) return;
        fileInput.addEventListener('change', () => {
            uploadAlert.classList.add('hidden');
        });

        uploadForm.addEventListener('submit', function (e) {
            e.preventDefault();
            uploadAlert.classList.add('hidden');
            const files = fileInput.files;
            if (files.length === 0) {
                self.showAlert(uploadAlert, 'error', 'Silakan pilih dokumen terlebih dahulu.');
                return;
            }
            const maxSize = 2048 * 1024; // 2MB
            const validFormats = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            for (let i = 0; i < files.length; i++) {
                if (!validFormats.includes(files[i].type)) {
                    self.showAlert(uploadAlert, 'error', `Format "${files[i].name}" tidak didukung (Hanya PDF/JPG/PNG).`);
                    return;
                }
                if (files[i].size > maxSize) {
                    self.showAlert(uploadAlert, 'error', `Ukuran "${files[i].name}" melebihi batas maksimal 2MB.`);
                    return;
                }
            }
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mengunggah...';
            }

            fetch(self.config.fileUploadUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token-hash"]')?.getAttribute('content') || '' }
            })
                .then(response => response.json().catch(() => ({})))
                .then(() => {
                    self.showAlert(uploadAlert, 'success', 'Dokumen berhasil diunggah.');
                    setTimeout(() => location.reload(), 600);
                })
                .catch(() => {
                    self.showAlert(uploadAlert, 'error', 'Terjadi kesalahan sistem saat mengunggah dokumen.');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-cloud-upload-alt mr-2"></i> Unggah Sekarang';
                    }
                });
        });
    },

    showAlert(element, type, message) {
        if (!element) return;
        element.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200', 'bg-red-50', 'text-red-700', 'border-red-200');

        if (type === 'success') {
            element.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
        } else {
            element.classList.add('bg-red-50', 'text-red-700', 'border', 'border-red-200');
        }

        element.textContent = message;
    },

    initModalHandlers() {
        document.addEventListener('click', (e) => {
            const triggerOpen = e.target.closest('[data-modal-open]');
            if (triggerOpen) {
                const modal = document.getElementById(triggerOpen.getAttribute('data-modal-open'));
                if (modal) this.openModal(modal);
            }

            const triggerClose = e.target.closest('[data-modal-close]');
            if (triggerClose) {
                const modal = triggerClose.closest('.modal-wrapper');
                if (modal) this.closeModal(modal);
            }

            if (e.target.classList.contains('modal-wrapper')) {
                this.closeModal(e.target);
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const visibleModal = document.querySelector('.modal-wrapper.flex');
                if (visibleModal) this.closeModal(visibleModal);
            }
        });
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => fileUploadPage.init());
} else {
    fileUploadPage.init();
}