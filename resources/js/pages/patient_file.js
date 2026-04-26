/**
 * Patient File Card Script
 * Handles file upload, preview, and batch delete
 */

const MODAL_VISIBLE_CLASS = "flex";
const MODAL_HIDDEN_CLASS = "hidden";

const openModal = (modal) => {
  if (!modal) return;
  modal.classList.remove(MODAL_HIDDEN_CLASS);
  modal.classList.add(MODAL_VISIBLE_CLASS);
  document.body.style.overflow = "hidden";
};

const closeModal = (modal) => {
  if (!modal) return;
  modal.classList.remove(MODAL_VISIBLE_CLASS);
  modal.classList.add(MODAL_HIDDEN_CLASS);
  document.body.style.overflow = "";
};

const FileUploadPage = {
  config: {
    fileUrls: [],
    fileBaseUrl: "",
    fileUploadUrl: "",
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
    this.config.fileBaseUrl = window.fileBaseUrl || "";
    this.config.fileUploadUrl = window.fileUploadUrl || "";
  },

  getFileUrl(fileUrl) {
    if (!fileUrl) return "";
    return fileUrl.startsWith("http")
      ? fileUrl
      : `${this.config.fileBaseUrl}/${fileUrl}`;
  },

  initPreviewButtons() {
    document.addEventListener("click", (e) => {
      const btn = e.target.closest(".previewBtn");
      if (!btn) return;
      this.previewFile(btn.getAttribute("data-id"));
    });
  },

  previewFile(id) {
    const modal = document.getElementById("fileUploadModal");
    const content = document.getElementById("fileUploadContent");
    if (!modal || !content) return;

    content.innerHTML = `<div class="text-center"><i class="fas fa-spinner fa-spin text-2xl text-slate-400"></i><p class="mt-2 text-sm text-slate-500">Memuat dokumen...</p></div>`;
    openModal(modal);

    let files = this.config.fileUrls;
    if (typeof files === "string") {
      try {
        files = JSON.parse(files);
      } catch (e) {
        files = [];
      }
    }

    if (!files || !files[id]) {
      content.innerHTML = `<div class="text-center py-12 text-slate-500">File tidak ditemukan</div>`;
      return;
    }

    const url = this.getFileUrl(files[id]);
    const ext = files[id].split(".").pop().toLowerCase();

    if (ext === "pdf") {
      content.innerHTML = `<embed src="${url}" type="application/pdf" class="w-full h-full min-h-[60vh] rounded-lg border">`;
    } else if (["jpg", "jpeg", "png", "gif"].includes(ext)) {
      content.innerHTML = `<img src="${url}" class="max-w-full max-h-[70vh] object-contain rounded-lg" alt="Preview">`;
    } else {
      content.innerHTML = `<div class="text-center py-12"><i class="fas fa-file text-4xl text-slate-300 mb-3"></i><a href="${url}" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Unduh Berkas</a></div>`;
    }
  },

  initBatchDelete() {
    document.addEventListener("change", (e) => {
      if (!e.target.classList.contains("delete-checkbox")) return;
      const btn = document.getElementById("batchDeleteBtn");
      if (btn) {
        const count = document.querySelectorAll(
          ".delete-checkbox:checked",
        ).length;
        btn.classList.toggle("hidden", count === 0);
      }
    });

    document
      .getElementById("batchDeleteBtn")
      ?.addEventListener("click", function (e) {
        e.preventDefault();
        if (confirm("Hapus dokumen terpilih?")) {
          this.closest("form").submit();
        }
      });
  },

  initUploadForm() {
    const form = document.getElementById("uploadForm");
    const input = document.getElementById("modalFileInput");
    if (!form || !input) return;

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      if (!input.files.length) {
        alert("Silakan pilih dokumen terlebih dahulu.");
        return;
      }
      const btn = this.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.innerHTML =
          '<i class="fas fa-spinner fa-spin mr-2"></i>Mengunggah...';
      }
      fetch(window.fileUploadUrl, { method: "POST", body: new FormData(this) })
        .then(() => setTimeout(() => location.reload(), 600))
        .catch(() => {
          alert("Gagal mengunggah");
          if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-upload mr-2"></i>Unggah';
          }
        });
    });
  },

  initModalHandlers() {
    document.addEventListener("click", (e) => {
      const openTrigger = e.target.closest("[data-modal-open]");
      if (openTrigger) {
        openModal(
          document.getElementById(openTrigger.getAttribute("data-modal-open")),
        );
        return;
      }
      const closeBtn = e.target.closest("[data-modal-close]");
      if (closeBtn) {
        closeModal(closeBtn.closest(".modal-wrapper"));
        return;
      }
      if (e.target.classList.contains("modal-wrapper")) closeModal(e.target);
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        const m = document.querySelector(".modal-wrapper.flex");
        if (m) closeModal(m);
      }
    });
  },
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => FileUploadPage.init());
} else {
  FileUploadPage.init();
}
