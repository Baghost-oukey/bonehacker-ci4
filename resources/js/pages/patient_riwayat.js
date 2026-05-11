/**
 * Patient History Card Script
 * Custom pagination, modals, tagify, and CRUD operations
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

let complaintTagify = null;
let medhisTagify = null;
let resultTagify = null;
let deleteId = null;

// --- INIT SCRIPT ---
const PatientHistoryPage = {
  currentPage: 1,
  pageLength: 25,
  totalRecords: 0,
  filteredRecords: 0,
  searchValue: "",
  config: {},
  currentCsrfHash: "",

  init() {
    this.config = window.patientConfig || {};
    this.currentCsrfHash = this.config.csrfHash || "";
    const page = document.getElementById("patientHistoryContainer");
    if (!page || typeof window.$ === "undefined") return;
    
    // DEBUG: Log config untuk memastikan patientRegionId ada
    console.log('=== PATIENT CONFIG ===');
    console.log('Patient ID:', this.config.patientId);
    console.log('Patient Region ID:', this.config.patientRegionId);
    console.log('Config:', this.config);
    
    this.initTagify();
    this.initEventListeners();
    this.loadTableData(1);
    this.checkUrlParams();
    
    // Init biodata read-only mode
    this.initBiodataMode();
  },

  // Initialize biodata read-only mode
  initBiodataMode() {
    const biodataContent = document.getElementById('biodata-content');
    if (!biodataContent) return;
    
    // Set semua input ke readonly saat pertama kali load
    this.setBiodataReadOnly(true);
    
    // Event listener untuk tombol Edit
    $('#btn-edit-biodata').on('click', () => {
      this.setBiodataReadOnly(false);
      $('#btn-edit-biodata').hide();
      $('#btn-cancel-edit, #btn-save-biodata').show();
      $('#biodata-subtitle').text('Ubah dan perbarui informasi dasar pasien');
    });
    
    // Event listener untuk tombol Batal
    $('#btn-cancel-edit').on('click', () => {
      // Reset form ke nilai awal
      document.getElementById('patientForm').reset();
      // Reload halaman untuk kembalikan data asli
      location.reload();
    });
  },

  // Set biodata form ke mode read-only atau editable
  setBiodataReadOnly(isReadOnly) {
    const form = document.getElementById('patientForm');
    if (!form) return;
    
    if (isReadOnly) {
      // Set semua input, select, textarea ke readonly/disabled
      $(form).find('input:not([type="hidden"]), select, textarea').each(function() {
        const $el = $(this);
        const tagName = this.tagName.toLowerCase();
        
        if (tagName === 'select' || this.type === 'checkbox' || this.type === 'radio') {
          $el.prop('disabled', true);
          $el.addClass('cursor-not-allowed opacity-60');
        } else {
          $el.prop('readonly', true);
          $el.addClass('bg-slate-50 cursor-not-allowed');
        }
      });
      
      // Hide tombol save & cancel, show tombol edit
      $('#btn-save-biodata, #btn-cancel-edit').hide();
      $('#btn-edit-biodata').show();
      $('#biodata-subtitle').text('Informasi dasar pasien (Mode Lihat)');
    } else {
      // Remove readonly/disabled dari semua input
      $(form).find('input:not([type="hidden"]), select, textarea').each(function() {
        const $el = $(this);
        const tagName = this.tagName.toLowerCase();
        
        if (tagName === 'select' || this.type === 'checkbox' || this.type === 'radio') {
          $el.prop('disabled', false);
          $el.removeClass('cursor-not-allowed opacity-60');
        } else {
          $el.prop('readonly', false);
          $el.removeClass('bg-slate-50 cursor-not-allowed');
        }
      });
      
      // Hide tombol edit, show tombol save & cancel
      $('#btn-edit-biodata').hide();
      $('#btn-save-biodata, #btn-cancel-edit').show();
      $('#biodata-subtitle').text('Ubah dan perbarui informasi dasar pasien');
    }
  },


  // --- UPDATE CRSF TOKEN ---
  updateCsrf(newToken) {
    if (!newToken) return;
    this.currentCsrfHash = newToken;
    $(`input[name="${this.config.csrfTokenName}"]`).val(newToken);
  },

  formatDateForInput(dateTime) {
    if (!dateTime) return "";
    const d = new Date(dateTime);
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, "0")}-${String(d.getDate()).padStart(2, "0")}`;
  },

  formatDateTimeForInput(dateTime) {
    if (!dateTime) return "";
    const d = new Date(dateTime);
    if (isNaN(d.getTime())) return "";
    const pad = (n) => String(n).padStart(2, "0");
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
  },


  // --- PAGINATION ---
  updatePaginationInfo() {
    if (this.filteredRecords <= 0) {
      $("#paginationInfo").text("Menampilkan 0 sampai 0 dari 0 data");
      return;
    }
    const start = (this.currentPage - 1) * this.pageLength + 1;
    const end = Math.min(this.currentPage * this.pageLength, this.filteredRecords);
    $("#paginationInfo").text(`Menampilkan ${start} sampai ${end} dari ${this.filteredRecords} data`);
  },

  updatePaginationUI() {
    const totalPages = Math.max(1, Math.ceil(this.filteredRecords / this.pageLength));
    const container = $("#paginationNumbers");
    container.empty();

    const startPage = Math.max(1, this.currentPage - 2);
    const endPage = Math.min(totalPages, this.currentPage + 2);

    if (startPage > 1) {
      container.append(`<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100" data-page="1">1</button>`);
      if (startPage > 2) container.append('<span class="px-1 text-slate-300">...</span>');
    }

    for (let p = startPage; p <= endPage; p++) {
      const active = p === this.currentPage
        ? "bg-teal-600 border-teal-600 text-white font-semibold shadow-md shadow-teal-600/30"
        : "border border-slate-300 bg-white text-slate-700 font-semibold transition hover:bg-slate-100";
      container.append(`<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg ${active} text-xs" data-page="${p}">${p}</button>`);
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) container.append('<span class="px-1 text-slate-300">...</span>');
      container.append(`<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100" data-page="${totalPages}">${totalPages}</button>`);
    }

    $("#paginationPrev").prop("disabled", this.currentPage <= 1);
    $("#paginationNext").prop("disabled", this.currentPage >= totalPages);
  },


  // --- TABLE LOAD ---
  renderTableState(message, isLoading = false) {
    const icon = isLoading ? '<i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>' : '<i class="fas fa-inbox mr-2 text-slate-300"></i>';
    $("#table-2 tbody").html(`<tr class="hover:bg-slate-50 transition"><td colspan="6" class="px-6 py-12 text-center text-slate-400 italic text-sm">${icon}${message}</td></tr>`);
  },
  loadTableData(pageNumber = 1) {
    const self = this;
    this.renderTableState("Memuat data riwayat...", true);
    $.ajax({
      url: self.config.urls.historyFetch,
      type: "POST",
      dataType: "json",
      data: {
        [self.config.csrfTokenName]: self.currentCsrfHash,
        draw: 1,
        start: (pageNumber - 1) * self.pageLength,
        length: self.pageLength,
        search: { value: self.searchValue },
      },
      success: function (response) {
        const freshToken = response.new_token || response.csrf_hash;
        if (freshToken) self.updateCsrf(freshToken);
        self.currentPage = pageNumber;
        self.totalRecords = Number(response.recordsTotal || 0);
        self.filteredRecords = Number(response.recordsFiltered || self.totalRecords);
        const tbody = $("#table-2 tbody");
        tbody.empty();
        if (!response.data || response.data.length === 0) {
          self.renderTableState("Belum ada data riwayat");
        } else {
          response.data.forEach(function (row) {
            const isDeleted = row.is_delete === "1" ? "text-red-500 line-through" : "text-slate-700";
            const tr = $(`<tr class="hover:bg-slate-50 transition border-b border-slate-100 ${isDeleted}"></tr>`);
            tr.append(`<td class="px-6 py-3.5 text-center text-xs">${row.no || "-"}</td>`);
            tr.append(`<td class="px-6 py-3.5 text-xs">${row.complaint || "-"}</td>`);
            tr.append(`<td class="px-6 py-3.5 text-xs">${row.medhis || "-"}</td>`);
            tr.append(`<td class="px-6 py-3.5 text-xs text-slate-600">${row.date || "-"}</td>`);
            tr.append(`<td class="px-6 py-3.5 text-center text-xs font-medium text-slate-600">${row.duration || "-"}</td>`);
            tr.append(`<td class="px-6 py-3.5 text-center text-xs">
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium ${row.type === 'draft' ? 'bg-amber-50 text-amber-700' : 'bg-teal-50 text-teal-700'}">${row.type || "-"}</span>
                        </td>`);
            tr.append(`<td class="px-6 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" class="btn-edit-history text-teal-600 hover:bg-teal-50 p-1.5 rounded" data-id="${row.id}" title="Edit"><i class="fas fa-edit"></i></button>
                                <button type="button" class="btn-copy-history text-blue-600 hover:bg-blue-50 p-1.5 rounded" data-id="${row.id}" title="Duplikat"><i class="fas fa-copy"></i></button>
                                <button type="button" class="btn-delete-history text-red-600 hover:bg-red-50 p-1.5 rounded" data-id="${row.id}" title="Hapus"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>`);
            tbody.append(tr);
          });
        }
        self.updatePaginationInfo();
        self.updatePaginationUI();
      },
      error: () => self.renderTableState("Gagal memuat data riwayat"),
    });
  },


  // --- TAGIFY ---
  initTagify() {
    if (typeof Tagify === 'undefined') return;
    complaintTagify = this.initTagifyWithServer("complaint", this.config.urls.complaintTags);
    medhisTagify = this.initTagifyWithServer("medhis", this.config.urls.medisTags);
    resultTagify = this.initTagifyWithServer("results", this.config.urls.resultTags);
  },

  initTagifyWithServer(inputName, url) {
    const textarea = document.querySelector(`textarea[name="${inputName}"]`);
    if (!textarea) {
      // console.error(` [Tagify ERROR] Error! Textarea dengan name="${inputName}" Tidak ditemukan.`);
      return null;
    }
    if (textarea.value.includes('[{"value":')) textarea.value = "";

    const tagify = new Tagify(textarea, {
      whitelist: [],
      originalInputValueFormat: valuesArr => valuesArr.map(item => item.value).join(', '),
      dropdown: {
        maxItems: 20,
        enabled: 0,
        closeOnSelect: false,
        appendTarget: document.body,
      }
    });

    let controller;

    tagify.on("input", (e) => {
      const value = e.detail.value;
      const fetchUrl = `${url}?query=${encodeURIComponent(value)}`;
      tagify.settings.whitelist.length = 0;
      tagify.loading(true).dropdown.hide();

      if (controller) controller.abort();
      controller = new AbortController();
      fetch(fetchUrl, { signal: controller.signal })
        .then((res) => {
          if (!res.ok) throw new Error(`HTTP Error: ${res.status}`);
          return res.json();
        })
        .then((list) => {
          const dataArray = Array.isArray(list) ? list : (list.data || []);
          tagify.settings.whitelist = dataArray;
          tagify.loading(false).dropdown.show(value);
        })
        .catch((err) => {
          if (err.name !== 'AbortError') {
            console.error(`[X] FETCH GAGAL PADA [${inputName.toUpperCase()}]:`, err);
          }
          tagify.loading(false);
        });
    });
    return tagify;
  },


  // --- CRUD & FORM ---
  setCheckboxes(name, dataString) {
    const arr = dataString ? dataString.split(',').map(s => s.trim()).filter(Boolean) : [];
    arr.forEach(val => $(`input[name="${name}[]"][value="${val}"]`).prop('checked', true));
  },

  setMatrix(name, dataString) {
    const arr = dataString ? dataString.split(',').map(item => item.trim()).filter(Boolean) : [];
    const hasSakit = arr.includes('sakit');
    const grade = arr.find(item => item.startsWith('grade')) || null;

    if (hasSakit || grade) {
      $(`input[name="${name}"]`).prop('checked', true);
    }
    if (grade) {
      $(`input[name="${name}_grade"][value="${grade}"]`).prop('checked', true);
    }
  },

  populateForm(data, isDuplicate = false) {
    const form = document.getElementById("save_data");
    form.reset();

    $('input[type="checkbox"], input[type="radio"]').prop('checked', false);
    $('#terapi-form, #pemeriksaan, #nonton-porn-frequency, #onani-frequency, #onani-lainnya-textbox, #nonton-lainnya-textbox, #hubungan-lainnya-textbox').hide();

    const type = data.type || "posted";
    $("#history-type").val(type);

    if (type === "draft") {
      $("#save-draft-button").text("Perbarui Draft").show();
      $("#save-button").text("Finalisasi & Simpan");
    } else {
      $("#save-draft-button").hide();
      $("#save-button").text("Simpan Data");
    }

    if (!isDuplicate) form.querySelector('input[name="id"]').value = data.id || "";
    form.querySelector('input[name="patient_id"]').value = data.patient_id || this.config.patientId;
    form.querySelector('input[name="queue_id"]').value = data.queue_id || this.config.queueId;
    form.querySelector('input[name="date"]').value = isDuplicate ? this.formatDateForInput(new Date()) : this.formatDateForInput(data.date);

    if (complaintTagify) { complaintTagify.removeAllTags(); if (data.complaint && data.complaint !== "-") complaintTagify.addTags(data.complaint.split(", ")); }
    if (medhisTagify) { medhisTagify.removeAllTags(); if (data.medhis && data.medhis !== "-") medhisTagify.addTags(data.medhis.split(", ")); }
    if (resultTagify) { resultTagify.removeAllTags(); if (data.results && data.results !== "-") resultTagify.addTags(data.results.split(", ")); }
    const textFields = ['timeConsume', 'cervical', 'thoraxal', 'lumbar', 'sacrum', 'pelvis', 'power', 'pr'];
    textFields.forEach(f => {
      let dbField = f;
      if (f === 'timeConsume') dbField = 'time_consume';
      if (data[dbField]) $(`input[name="${f}"]`).val(data[dbField]);
    });

    if (data.processAt) $('input[name="processAt"]').val(this.formatDateTimeForInput(data.processAt));
    if (data.finishAt) $('input[name="finishAt"]').val(this.formatDateTimeForInput(data.finishAt));
    
    // Hitung durasi otomatis dan pastikan field readonly
    this.calculateDuration();
    $('#timeConsume').prop('readonly', true);
    const textareas = ['other', 'measure', 'ket_vertebrata', 'ket_thorax', 'ket_kompresi', 'ket_plintiran', 'ket_viska', 'penyebab', 'results'];
    textareas.forEach(f => {
      let dbField = f;
      if (f === 'ket_vertebrata') dbField = 'keterangan_verteba';
      if (f === 'ket_thorax') dbField = 'keterangan_thorax';
      if (f === 'ket_kompresi') dbField = 'keterangan_kompresi';
      if (f === 'ket_plintiran') dbField = 'keterangan_plintiran';
      if (f === 'ket_viska') dbField = 'keterangan_visualfoot';
      if (data[dbField]) $(`textarea[name="${f}"]`).val(data[dbField]);
    });
    $('input[name="tensi"]').val(data.tensi);
    this.setCheckboxes('vertebra', data.verteba);
    this.setCheckboxes('thorax', data.thorax);
    this.setCheckboxes('kompresi', data.kompresi);
    this.setCheckboxes('plintiran', data.plintiran);
    this.setCheckboxes('visual_kaki', data.visualfoot);
    this.setCheckboxes('pubis', data.pubis);
    const matrixMap = {
      'odp_kiri': data.otot_dada_perut_kiri, 'odp_kanan': data.otot_dada_perut_kanan,
      'vital_kiri': data.vital_kiri, 'vital_kanan': data.vital_kanan,
      'kelenjar_kiri': data.kelenjar_kiri, 'kelenjar_kanan': data.kelenjar_kanan,
      'hormon_kiri': data.hormon_kiri, 'hormon_kanan': data.hormon_kanan,
      'tk_kiri': data.tulang_kering_kiri, 'tk_kanan': data.tulang_kering_kanan,
      'fd_kiri': data.femur_dalam_kiri, 'fd_kanan': data.femur_dalam_kanan,
      'lp_atas': data.lingkar_perut_atas, 'lp_bawah': data.lingkar_perut_bawah,
      'lp_kiri': data.lingkar_perut_kiri, 'lp_kanan': data.lingkar_perut_kanan,
      'cv4_kiri': data.cv4_kiri, 'cv4_kanan': data.cv4_kanan,
      'cv6_kiri': data.cv6_kiri, 'cv6_kanan': data.cv6_kanan,
      'l1_kiri': data.l1_kiri, 'l1_kanan': data.l1_kanan,
      'l3_kiri': data.l3_kiri, 'l3_kanan': data.l3_kanan,
      'piriformis_kiri': data.piriformis_kiri, 'piriformis_kanan': data.piriformis_kanan,
      'sendok_kiri': data.sendok_kiri, 'sendok_kanan': data.sendok_kanan
    };
    for (const [key, val] of Object.entries(matrixMap)) this.setMatrix(key, val);

    if (data.kejantanan === 'ya') {
      $('#kejantanan').prop('checked', true);
      window.toggleTerapiForm();
    }
    if (data.ereksi) $(`input[name="ereksi"][value="${data.ereksi}"]`).prop('checked', true);
    if (data.obat_kuat) $(`input[name="obat_kuat"][value="${data.obat_kuat}"]`).prop('checked', true);
    if (data.ranjang) $(`input[name="ranjang"][value="${data.ranjang}"]`).prop('checked', true);
    if (data.porno) {
      $(`input[name="nonton_porno"][value="${data.porno}"]`).prop('checked', true);
      if (data.porno === 'ya') {
        window.showFrequency('nonton-porn-frequency', true);
        $(`input[name="frekuensi_nonton_porno"][value="${data.frekuensi_porno}"]`).prop('checked', true);
        if (data.frekuensi_porno === 'lainnya') {
          window.toggleLainnyaTextbox('nonton-lainnya-textbox', true);
          $('#nonton-lainnya-textbox').val(data.frekuensi_porno_lain);
        }
      }
    }
    if (data.onani) {
      $(`input[name="sering_onani"][value="${data.onani}"]`).prop('checked', true);
      if (data.onani === 'ya') {
        window.showFrequency('onani-frequency', true);
        $(`input[name="frekuensi_onani"][value="${data.frekuensi_onani}"]`).prop('checked', true);
        if (data.frekuensi_onani === 'lainnya') {
          window.toggleLainnyaTextbox('onani-lainnya-textbox', true);
          $('#onani-lainnya-textbox').val(data.frekuensi_onani_lain);
        }
      }
    }
    if (data.frekuensi_ranjang) {
      $(`input[name="frekuensi_ranjang"][value="${data.frekuensi_ranjang}"]`).prop('checked', true);
      if (data.frekuensi_ranjang === 'lainnya') {
        window.toggleLainnyaTextbox('hubungan-lainnya-textbox', true);
        $('#hubungan-lainnya-textbox').val(data.frekuensi_ranjang_lain);
      }
    }
    if (data.selected_terapis && data.active_terapis) {
      $('.terapis').empty();
      let selectedIds = data.selected_terapis.map(t => t.id.toString());
      data.active_terapis.forEach(t => {
        $('.terapis').append(new Option(t.nama, t.id, false, selectedIds.includes(t.id.toString())));
      });
      data.selected_terapis.forEach(t => {
        if (!$('.terapis option[value="' + t.id + '"]').length) {
          $('.terapis').append($('<option>', { value: t.id, text: t.nama + ' (Non-Aktif)', disabled: true, selected: true }));
        }
      });
      $('.terapis').trigger('change');
    }
    if (data.history_region) $('#region_history').val(data.history_region).trigger('change');
    if (!isDuplicate) {
      const timeDiff = Math.abs(new Date() - new Date(data.date_modified));
      const dayDiff = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
      if (dayDiff > 1 && data.type !== 'draft') {
        $('#exampleModal form :input').prop('readonly', true);
        $('#exampleModal form :checkbox, #exampleModal form :radio, #region_history, .terapis').prop('disabled', true);
        [complaintTagify, medhisTagify, resultTagify].forEach(t => { if (t) t.setReadonly(true); });
        $('#save-button').hide();
      } else {
        $('#exampleModal form :input').prop('readonly', false);
        $('#exampleModal form :checkbox, #exampleModal form :radio, #region_history, .terapis').prop('disabled', false);
        [complaintTagify, medhisTagify, resultTagify].forEach(t => { if (t) t.setReadonly(false); });
        $('#save-button').prop('disabled', false).show();
      }
    }
    if (!isDuplicate) {
      $('#history-info').show();
      $('#created_by').text(data.history_created_by || '-');
      if (data.history_updated_by && data.history_updated_by.trim() !== '-') {
        $('#updated_by').text(data.history_updated_by);
        $('#updated_info').removeClass('hidden');
      } else {
        $('#updated_info').addClass('hidden');
      }
    } else {
      $('#history-info').hide();
    }
  },


  // --- ADD RIWAYAT ---
  add() {
    console.log('=== ADD RIWAYAT CALLED ===');
    const modal = document.getElementById("exampleModal");
    const form = document.getElementById("save_data");
    if (!modal || !form) {
      console.error('Modal or form not found!');
      return;
    }

    openModal(modal);
    modal.querySelector(".modal-title").textContent = "Tambah Riwayat Pasien";
    form.setAttribute("action", this.config.urls.historyStore);
    
    // Reset form KECUALI select region
    form.querySelector('input[name="id"]').value = '';
    form.querySelector('input[name="patient_id"]').value = this.config.patientId;
    form.querySelector('input[name="queue_id"]').value = this.config.queueId;
    form.querySelector('input[name="date"]').value = this.formatDateForInput(new Date());

    const now = new Date();
    const processAtValue = this.formatDateTimeForInput(now);
    const finishAtValue = this.formatDateTimeForInput(now);
    
    console.log('Setting processAt to:', processAtValue);
    console.log('Setting finishAt to:', finishAtValue);
    
    form.querySelector('input[name="processAt"]').value = processAtValue;
    form.querySelector('input[name="finishAt"]').value = finishAtValue;
    
    // Clear all inputs except region
    $(form).find('input[type="text"]:not(#timeConsume), textarea').val('');
    $(form).find('input[type="checkbox"], input[type="radio"]').prop('checked', false);
    
    // Set wilayah periksa dari biodata pasien
    console.log('=== SETTING REGION ===');
    console.log('Patient Region ID from config:', this.config.patientRegionId);
    console.log('Region dropdown exists:', $('#region_history').length > 0);
    console.log('Region dropdown options:', $('#region_history option').length);
    
    if (this.config.patientRegionId && this.config.patientRegionId !== '' && this.config.patientRegionId !== 'null') {
      console.log('Attempting to set region dropdown to:', this.config.patientRegionId);
      
      // Set value langsung
      const regionSelect = document.getElementById('region_history');
      if (regionSelect) {
        regionSelect.value = this.config.patientRegionId;
        console.log('Region dropdown value after direct set:', regionSelect.value);
      }
      
      // Set via jQuery
      $('#region_history').val(this.config.patientRegionId);
      console.log('Region dropdown value after jQuery set:', $('#region_history').val());
      
      // Trigger change untuk select2 dan load terapis
      setTimeout(() => {
        $('#region_history').trigger('change');
        console.log('Region dropdown value after trigger change:', $('#region_history').val());
        console.log('Selected option text:', $('#region_history option:selected').text());
      }, 100);
    } else {
      console.warn('Patient region ID is empty, undefined, or null:', this.config.patientRegionId);
    }
    
    // Hitung durasi otomatis LANGSUNG setelah set waktu
    console.log('=== CALCULATING INITIAL DURATION ===');
    setTimeout(() => {
      this.calculateDuration();
    }, 150);

    $(form).find(":input").prop("readonly", false);
    $(form).find(":checkbox, :radio").prop("disabled", false);
    
    // Pastikan timeConsume tetap readonly
    $('#timeConsume').prop('readonly', true);

    [complaintTagify, medhisTagify, resultTagify].forEach((t) => { if (t) { t.removeAllTags(); t.setReadonly(false); } });

    $("#history-info, #terapi-form, #pemeriksaan").hide();
    $('#terapi-kejantanan').show();
    $("#history-type").val("posted");
    $("#save-draft-button").text("Simpan sebagai Draft").show();
    $("#save-button").prop('disabled', false).html('Simpan Data').show();
    $("#region_history").prop('disabled', false);
    $(".terapis").prop("disabled", false).val([]).trigger("change");
  },


  // --- SHOW DETAIL RIWAYAT ---
  show(id, isDuplicate = false) {
    const self = this;
    const fetchUrl = this.config.urls.historyFetch.replace(/\/\d+$/, "/" + id).replace("fetch", "show");

    $.ajax({
      url: fetchUrl,
      type: "GET",
      dataType: "json",
      success: function (data) {
        const modal = document.getElementById("exampleModal");
        openModal(modal);

        modal.querySelector(".modal-title").textContent = isDuplicate ? "Duplikat Riwayat Pasien" : "Detail Riwayat Pasien";
        const formAction = isDuplicate ? self.config.urls.historyStore.replace('store', 'copy') : self.config.urls.historyStore.replace('store', 'update');
        document.getElementById("save_data").setAttribute("action", formAction);

        self.populateForm(data, isDuplicate);
      },
      error: () => alert("Gagal memuat data detail riwayat")
    });
  },

  // --- HAPUS RIWAYAT ---
  destroy(id) {
    this.deleteId = id;
    openModal(document.getElementById("deleteModal"));
  },

  checkUrlParams() {
    const params = new URLSearchParams(window.location.search);
    if (params.get("openModalRiwayat") === "true") {
      const hId = params.get("history_id");
      if (hId && hId !== "undefined" && hId !== "") setTimeout(() => this.show(hId), 500);
    }
  },

  // Load terapis berdasarkan region yang dipilih
  loadTerapisByRegion(regionId) {
    console.log('=== LOADING TERAPIS BY REGION ===');
    console.log('Region ID:', regionId);
    console.log('URL:', this.config.urls.terapisByRegion);
    
    const self = this;
    
    // Show loading state
    $('.terapis').empty().append('<option value="">Memuat terapis...</option>').trigger('change');
    
    $.ajax({
      url: self.config.urls.terapisByRegion,
      type: 'GET',
      data: { region_id: regionId },
      dataType: 'json',
      success: function(response) {
        console.log('Terapis response:', response);
        
        $('.terapis').empty();
        
        if (response.status && response.data && response.data.length > 0) {
          $('.terapis').append('<option value="">Pilih Terapis</option>');
          
          response.data.forEach(function(terapis) {
            $('.terapis').append(
              $('<option>', {
                value: terapis.id,
                text: terapis.nama
              })
            );
          });
          
          console.log('Loaded', response.data.length, 'terapis');
        } else {
          $('.terapis').append('<option value="">Tidak ada terapis di wilayah ini</option>');
          console.warn('No terapis found for region:', regionId);
        }
        
        $('.terapis').trigger('change');
      },
      error: function(xhr, status, error) {
        console.error('Error loading terapis:', error);
        console.error('Response:', xhr.responseText);
        $('.terapis').empty().append('<option value="">Error memuat terapis</option>').trigger('change');
      }
    });
  },

  initEventListeners() {
    const self = this;
    document.getElementById("btn-add-history")?.addEventListener("click", () => self.add());
    $(document).on("click", ".btn-edit-history", function () { self.show($(this).data('id')); });
    $(document).on("click", ".btn-copy-history", function () { self.show($(this).data('id'), true); });
    $(document).on("click", ".btn-delete-history", function (e) {
      e.preventDefault();
      const id = $(this).data('id');
      const modalHapus = document.getElementById("deleteModal");
      if (!modalHapus) {
        return; 
      }
      self.destroy(id);
    });
    $("#region_history, .terapis").select2({ dropdownParent: $("#exampleModal"), width: "100%" });

    // Event listener untuk perubahan wilayah periksa - update dropdown terapis
    $(document).on('change', '#region_history', function() {
      const regionId = $(this).val();
      console.log('=== REGION CHANGED ===');
      console.log('Selected region ID:', regionId);
      
      if (regionId) {
        self.loadTerapisByRegion(regionId);
      } else {
        // Jika wilayah kosong, kosongkan juga terapis
        $('.terapis').empty().append('<option value="">Pilih Terapis</option>').trigger('change');
      }
    });

    // --- SIMPAN BUTTON ---
    $(document).on("click", "#save-button", function (e) {
      e.preventDefault();
      $("#history-type").val("posted");
      self.submitForm($(this));
    });

    $(document).on("click", "#save-draft-button", function (e) {
      e.preventDefault();
      $("#history-type").val("draft");
      self.submitForm($(this));
    });

    // --- DELETE KONFIRMASI ---
    $(document).on("click", "#confirmDeleteButton", function () {
      if (!self.deleteId) return;
      const btn = $(this);
      btn.prop("disabled", true).text("Memproses...");
      $.ajax({
        url: `${self.config.urls.historyDestroy}/${self.deleteId}`,
        type: "POST",
        dataType: "json",
        data: { [self.config.csrfTokenName]: self.currentCsrfHash },
        success: (res) => {
          const freshToken = res.new_token || res.csrf_hash;
          if (freshToken) self.updateCsrf(freshToken);

          if (res.status) {
            closeModal(document.getElementById("deleteModal"));
            self.loadTableData(self.currentPage);
            if (window.Swal?.fire) window.Swal.fire({ icon: "success", title: "Terhapus!", timer: 2000, showConfirmButton: false });
          } else alert("Gagal: " + res.message);
        },
        complete: () => {
          btn.prop("disabled", false).text("Ya, Hapus");
          self.deleteId = null;
        },
      });
    });

    // --- THERAPY DURATION (Auto-calculate, field is readonly) ---
    // Gunakan event delegation untuk memastikan event tetap bekerja
    $(document).on('change', '#processAt, #finishAt', function() {
      console.log('=== TIME INPUT CHANGED ===');
      console.log('Changed element:', this.name, 'New value:', this.value);
      self.calculateDuration();
      // Pastikan field timeConsume tetap readonly setelah perhitungan
      $('#timeConsume').prop('readonly', true);
    });
    
    // Tambahan: trigger saat user mengetik (untuk real-time update)
    $(document).on('input', '#processAt, #finishAt', function() {
      console.log('=== TIME INPUT (typing) ===');
      self.calculateDuration();
      $('#timeConsume').prop('readonly', true);
    });

    $(document).on('change', '#pemeriksaan input[type="checkbox"]', function () {
      const name = $(this).attr('name');
      if (!name) return;

      // Jika yang diklik adalah pilihan GRADE
      if (name.includes('_grade')) {
        if ($(this).is(':checked')) {
          $(`input[name="${name}"]`).not(this).prop('checked', false);
          const baseName = name.replace(/_grade$/, '');
          $(`input[name="${baseName}"]`).prop('checked', true);
        }
      }
      // Jika yang diklik adalah SAKIT/TIDAK (Kolom pertama)
      else {
        if (!$(this).is(':checked')) {
          // UX: Jika "Sakit" di-uncheck, hapus juga centang grade-nya agar bersih
          $(`input[name="${name}_grade"]`).prop('checked', false);
        }
      }
    });

    document.addEventListener("click", (e) => {
      const closeBtn = e.target.closest("[data-modal-close]");
      if (closeBtn) closeModal(closeBtn.closest(".modal-wrapper"));
      if (e.target.classList.contains("modal-wrapper")) closeModal(e.target);
    });
  },

  calculateDuration() {
    console.log('=== CALCULATE DURATION CALLED ===');
    const modal = $('#exampleModal');
    if (!modal.length) {
      console.log('Modal not found');
      return;
    }

    const startVal = modal.find('#processAt').val();
    const endVal = modal.find('#finishAt').val();
    const timeConsumeField = modal.find('#timeConsume');

    console.log('Process At value:', startVal);
    console.log('Finish At value:', endVal);

    if (startVal && endVal) {
      try {
        // Parse datetime-local format (YYYY-MM-DDTHH:mm)
        const startTime = new Date(startVal);
        const endTime = new Date(endVal);

        console.log('Start Time parsed:', startTime);
        console.log('End Time parsed:', endTime);

        if (!isNaN(startTime.getTime()) && !isNaN(endTime.getTime())) {
          // Calculate difference in minutes
          const diffMs = endTime.getTime() - startTime.getTime();
          const diffMins = Math.floor(diffMs / (1000 * 60));
          
          console.log('Difference in milliseconds:', diffMs);
          console.log('Calculated duration in minutes:', diffMins);
          
          // Update the timeConsume field (readonly, auto-calculated)
          const finalValue = diffMins > 0 ? diffMins : 0;
          timeConsumeField.val(finalValue);
          console.log('Set timeConsume field to:', finalValue);
          
          // Pastikan field tetap readonly
          timeConsumeField.prop('readonly', true);
        } else {
          console.error('Invalid date/time values');
        }
      } catch (e) {
        console.error('Error calculating duration:', e);
        timeConsumeField.val('');
      }
    } else {
      // Jika salah satu waktu kosong, kosongkan juga total waktu
      console.log('One or both times are empty, clearing timeConsume');
      timeConsumeField.val('');
    }
  },

  submitForm(btn) {
    const self = this;
    const form = $("#save_data");
    const formData = new FormData(form[0]);
    const originalBtnHtml = btn.html();

    if (complaintTagify) {
      const val = complaintTagify.value;
      formData.set("complaint", Array.isArray(val) ? val.map(t => t.value).join(', ') : "");
    }
    if (medhisTagify) {
      const val = medhisTagify.value;
      formData.set("medhis", Array.isArray(val) ? val.map(t => t.value).join(', ') : "");
    }
    if (resultTagify) {
      const val = resultTagify.value;
      formData.set("results", Array.isArray(val) ? val.map(t => t.value).join(', ') : "");
    }

    formData.set(self.config.csrfTokenName, self.currentCsrfHash);
    btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...');

    $.ajax({
      url: form.attr('action'),
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      success: (res) => {
        const freshToken = res.new_token || res.csrf_hash;
        if (freshToken) self.updateCsrf(freshToken);
        if (res.status) {
          closeModal(document.getElementById("exampleModal"));
          self.loadTableData(self.currentPage);
          if (window.Swal?.fire) window.Swal.fire({ icon: "success", title: "Berhasil!", text: res.message, timer: 2000, showConfirmButton: false });
        } else {
          if (window.Swal?.fire) window.Swal.fire({ icon: "error", title: "Gagal!", text: res.message });
        }
        btn.prop("disabled", false).html(originalBtnHtml);
      },
      error: (xhr) => {
        btn.prop("disabled", false).html(originalBtnHtml);
        if (xhr.status === 500) {
          if (window.Swal) {
            Swal.fire({
              icon: "error",
              title: "Error 500 (Server Crash)",
              text: "Terdapat kesalahan di server. Cek log error.",
            });
          }
        } else {
          if (window.Swal) Swal.fire('Gagal Menyimpan', 'Terjadi masalah jaringan atau server.', 'error');
        }
      }
    });
  },
};

// --- HELPERS ---
window.toggleTerapiForm = function () {
  const isChecked = document.getElementById("kejantanan").checked;
  document.getElementById("terapi-form").style.display = isChecked ? "block" : "none";
  document.getElementById("pemeriksaan").style.display = isChecked ? "block" : "none";
};
window.showFrequency = function (elementId, show) {
  const element = document.getElementById(elementId);
  if (show) {
    element.style.display = 'block';
  } else {
    element.style.display = 'none';
    element.querySelectorAll('input[type="radio"]').forEach(r => r.checked = false);
    const textbox = element.querySelector('input[type="text"]');
    if (textbox) { textbox.value = ''; textbox.style.display = 'none'; }
  }
};
window.toggleLainnyaTextbox = function (textboxId, show, retainValue = false) {
  const textbox = document.getElementById(textboxId);
  if (textbox) {
    textbox.style.display = show ? 'block' : 'none';
    if (!show && !retainValue) textbox.value = '';
  }
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => PatientHistoryPage.init());
} else {
  PatientHistoryPage.init();
}

// Expose fungsi global untuk dipanggil dari halaman lain (seperti antrean)
window.loadHistoryData = function(historyId) {
  if (PatientHistoryPage && typeof PatientHistoryPage.editHistory === 'function') {
    PatientHistoryPage.editHistory(historyId);
  }
};

window.resetHistoryForm = function() {
  const form = document.getElementById('formRiwayat');
  if (form) {
    form.reset();
    // Reset tagify
    if (complaintTagify) complaintTagify.removeAllTags();
    if (medhisTagify) medhisTagify.removeAllTags();
    if (resultTagify) resultTagify.removeAllTags();
    
    // Set mode tambah
    document.getElementById('history_id').value = '';
    document.getElementById('modalRiwayatLabel').textContent = 'Tambah Rekam Medis';
  }
};
