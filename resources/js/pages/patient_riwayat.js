// --- INIT SCRIPT ---
let complaintTagify, medhisTagify, resultTagify;
let deleteId = null;
let activeTerapis = window.activeTerapis || [];

const PatientHistoryPage = {
    init() {
        this.injectShadcnStyles();
        this.initDataTable();
        this.initTagify();
        this.initEventListeners();
        window.add = this.add.bind(this);
        window.show = this.show.bind(this);
        window.destroy = this.destroy.bind(this);
        window.toggleTerapiForm = this.toggleTerapiForm;
        window.showFrequency = this.showFrequency;
        window.toggleLainnyaTextbox = this.toggleLainnyaTextbox;

        this.checkGender();
        this.toggleTerapiForm();
        this.checkUrlParams();
    },

    injectShadcnStyles() {
        const style = document.createElement('style');
        style.innerHTML = `
            /* TAGIFY */
            .tagify {
                --tags-border-color: transparent !important;
                --tags-hover-border-color: transparent !important;
                --tags-focus-border-color: transparent !important;
                --tag-bg: #f1f5f9 !important; 
                --tag-hover: #e2e8f0 !important; 
                --tag-text-color: #0f172a !important; 
                --tag-pad: 0.25rem 0.5rem !important;
                border: none !important;
                padding: 0 !important;
                line-height: 1.5 !important;
            }
            .tagify__tag { margin: 2px !important; border-radius: 4px !important; }
            .tagify__input { margin: 2px !important; color: #0f172a !important; }

            /* SELECT2 */
            .select2-container--default .select2-selection--multiple,
            .select2-container .select2-selection--single {
                background-color: #ffffff !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 0.375rem !important;
                min-height: 2.5rem !important;
                display: flex !important;
                align-items: center !important;
            }
            .select2-container--focus .select2-selection--multiple,
            .select2-container--open .select2-selection--single {
                border-color: #0f172a !important;
                box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #0f172a !important;
                outline: none !important;
            }
            .select2-container--default .select2-selection--multiple .select2-selection__choice {
                background-color: #f1f5f9 !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 0.25rem !important;
                color: #0f172a !important;
                margin-top: 4px !important;
                margin-left: 4px !important;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #0f172a !important; padding-left: 0.75rem !important;
            }
            .select2-dropdown {
                border-color: #e2e8f0 !important; border-radius: 0.375rem !important; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1) !important;
            }
            .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background-color: #f1f5f9 !important; color: #0f172a !important;
            }
            /* DataTables Custom Search */
            .dataTables_filter input {
                border: 1px solid #e2e8f0; border-radius: 0.375rem; padding: 0.25rem 0.75rem; height: 2.25rem; font-size: 0.875rem; outline: none;
            }
            .dataTables_filter input:focus {
                border-color: #0f172a; box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #0f172a;
            }
        `;
        document.head.appendChild(style);
    },

    formatDate(dateTime) {
        const date = new Date(dateTime);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        return `${day}/${month}/${date.getFullYear()}`;
    },

    formatDateForInput(dateTime) {
        const date = new Date(dateTime);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
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
    },

    toggleTerapiForm() {
        const checkbox = document.getElementById('kejantanan');
        const terapiForm = document.getElementById('terapi-form');
        const pemeriksaan = document.getElementById('pemeriksaan');
        if (checkbox && terapiForm && pemeriksaan) {
            terapiForm.style.display = checkbox.checked ? "block" : "none";
            pemeriksaan.style.display = checkbox.checked ? "block" : "none";
        }
    },

    checkGender() {
        const gender = document.getElementById('gender')?.value;
        const terapiKejantanan = document.getElementById('terapi-kejantanan');
        if (terapiKejantanan) {
            terapiKejantanan.style.display = (gender === 'Man') ? 'flex' : 'none';
        }
    },

    showFrequency(elementId, show) {
        const element = document.getElementById(elementId);
        if (!element) return;
        if (show) {
            element.style.display = 'block';
        } else {
            element.style.display = 'none';
            element.querySelectorAll('input[type="radio"]').forEach(radio => radio.checked = false);
            const textbox = element.querySelector('input[type="text"]');
            if (textbox) {
                textbox.value = ''; textbox.style.display = 'none';
            }
        }
    },

    toggleLainnyaTextbox(textboxId, show, retainValue = false) {
        const textbox = document.getElementById(textboxId);
        if (textbox) {
            textbox.style.display = show ? 'block' : 'none';
            if (!show && !retainValue) textbox.value = '';
        }
    },

    // --- TABLE DATA PASIEN ---
    initDataTable() {
        if (typeof window.$ === 'undefined') return;
        $('#table-2').dataTable({
            processing: true, serverSide: true,
            "dom": '<"flex flex-col sm:flex-row items-center justify-between p-6 gap-4 bg-white border-b border-slate-100"<"flex items-center gap-4"l><"w-full sm:w-64"f>>t<"flex flex-col md:flex-row items-center justify-between p-5 bg-white border-t border-slate-100 gap-4"<"text-xs font-semibold text-slate-500"i><"flex items-center justify-end"p>>',
            "language": { "search": "", "searchPlaceholder": "Cari data riwayat...", "paginate": { "previous": '<i class="fas fa-chevron-left text-[10px]"></i>', "next": '<i class="fas fa-chevron-right text-[10px]"></i>' } },
            columns: [
                { data: 'no', class: 'px-6 py-4 align-middle font-medium', width: '5%', sortable: true },
                { data: 'complaint', class: 'px-6 py-4 align-middle', width: '25%', sortable: true },
                { data: 'medhis', class: 'px-6 py-4 align-middle', width: '25%', sortable: true },
                { data: 'date', class: 'px-6 py-4 align-middle text-slate-500', width: '15%', sortable: true },
                { data: 'type', class: 'px-6 py-4 align-middle', width: '15%', sortable: true },
                { data: 'action', class: 'px-6 py-4 align-middle text-center', width: '15%', sortable: false }
            ],
            order: [],
            ajax: {
                url: window.historyFetchUrl, type: 'POST',
                data: function (d) { d[window.csrfTokenName] = window.csrfHash; },
                dataSrc: function (json) { return json.data; }
            },
            rowCallback: function (row, data) {
                $(row).addClass('hover:bg-slate-50/80 transition-colors');
                if (data.is_delete === "1") {
                    $(row).find('td').addClass('text-red-500 line-through opacity-70');
                } else if (data.kejantanan === "ya") {
                    $(row).find('td').addClass('text-slate-900');
                }
            },
            initComplete: function () {
                $('.dataTables_filter label').contents().filter(function () { return this.nodeType === 3; }).remove();
            },
            drawCallback: function () {
                $('.dataTables_paginate').addClass('!flex !flex-row !items-center !justify-end gap-1');
                $('.dataTables_paginate > span').addClass('!flex !flex-row !items-center gap-1');
                $('.paginate_button').addClass('!inline-flex items-center justify-center min-w-[32px] h-8 rounded-md border border-slate-200 text-xs font-medium text-slate-600 cursor-pointer hover:bg-slate-100 transition-colors !m-0 !p-0');
                $('.paginate_button.current').addClass('!bg-slate-900 !text-white !border-slate-900 hover:!bg-slate-900/90').removeClass('bg-white text-slate-600');
                $('.paginate_button.disabled').addClass('!opacity-40 cursor-not-allowed shadow-none hover:bg-white hover:text-slate-600');
            }
        });
    },

    initTagifyWithServer(inputName, url) {
        const textarea = document.querySelector(`textarea[name="${inputName}"]`);
        if (!textarea) return null;
        const tagifyInstance = new Tagify(textarea, { whitelist: [] });
        let controller;
        tagifyInstance.on('input', (e) => {
            const value = e.detail.value;
            tagifyInstance.whitelist = null;
            if (controller) controller.abort();
            controller = new AbortController();
            tagifyInstance.loading(true);
            fetch(`${url}?query=${encodeURIComponent(value)}`, { signal: controller.signal })
                .then(res => res.json())
                .then((list) => {
                    tagifyInstance.whitelist = list;
                    tagifyInstance.loading(false).dropdown.show(value);
                }).catch(() => tagifyInstance.loading(false));
        });
        return tagifyInstance;
    },
    initTagify() {
        complaintTagify = this.initTagifyWithServer("complaint", window.complaintTagsUrl);
        medhisTagify = this.initTagifyWithServer("medhis", window.medisTagsUrl);
        resultTagify = this.initTagifyWithServer("results", window.resultTagsUrl);
    },
    add() {
        const modal = document.getElementById('exampleModal');
        const form = $('#save_data');

        if (!modal || form.length === 0) return;

        this.openModal(modal);
        modal.querySelector('.modal-title').textContent = 'Tambah Riwayat Pasien';

        form.attr('action', window.historyStoreUrl);
        form[0].reset();

        form.find('input[name="patient_id"]').val(window.patientId);
        form.find('input[name="queue_id"]').val(window.queueId);
        form.find(':input').prop('readonly', false);
        form.find(':checkbox').prop('disabled', false);

        [complaintTagify, medhisTagify, resultTagify].forEach(t => {
            if (t) { t.removeAllTags(); t.setReadonly(false); }
        });

        $('#terapi-kejantanan').show();
        $('#kejantanan').prop('checked', false);
        $('#history-info').hide();
        $('#notif-wa').css('display', 'flex'); 
        $('#date_modified_group').addClass('d-none');
        $('#region_history').prop('disabled', false);

        $('#date').val(this.formatDateForInput(new Date()));

        const terapisSelect = $('.terapis');
        terapisSelect.prop('disabled', false).empty();
        if (typeof activeTerapis !== 'undefined') {
            activeTerapis.forEach(t => terapisSelect.append(new Option(t.nama, t.id)));
        }
        terapisSelect.select2({ placeholder: "-- Pilih Terapis --", allowClear: true, dropdownParent: $('#exampleModal') }).val([]).trigger('change');
        $('#save-button').show();
    },

    show(id) {
        const self = this;
        const form = $('#save_data');
        if (form.length > 0) form[0].reset();

        $('input[type="checkbox"]').prop('checked', false);
        $('input[type="radio"]').prop('checked', false);

        $.ajax({
            url: `<?= site_url('history/show/') ?>/${id}`,
            type: "GET", dataType: "JSON",
            success: function (data) {
                const modal = document.getElementById('exampleModal');
                self.openModal(modal);
                $('#exampleModal form').attr('action', window.historyStoreUrl.replace('store', 'update'));

                $('#exampleModal .modal-title').text('Detil Riwayat Pasien');
                $('#notif-wa').hide();

                document.getElementById("terapi-kejantanan").style.display = "flex";
                document.getElementById('kejantanan').checked = (data.kejantanan === 'ya');
                self.toggleTerapiForm();

                if (data.ereksi) $(`input[name="ereksi"][value="${data.ereksi}"]`).prop('checked', true);
                if (data.porno) $(`input[name="nonton_porno"][value="${data.porno}"]`).prop('checked', true);

                if (complaintTagify) {
                    complaintTagify.removeAllTags();
                    if (data.complaint && data.complaint !== '-') complaintTagify.addTags(data.complaint.split(', '));
                }
                if (medhisTagify) {
                    medhisTagify.removeAllTags();
                    if (data.medhis && data.medhis !== '-') medhisTagify.addTags(data.medhis.split(', '));
                }
                if (resultTagify) {
                    resultTagify.removeAllTags();
                    if (data.results && data.results !== '-') resultTagify.addTags(data.results.split(', '));
                }

                $('#history-info').show();
                self.updateHistoryInfo(data);

                if (data.history_region) $('#region_history').val(data.history_region).trigger('change');
                else $('#region_history').val('');

                $('.terapis').empty();
                let selectedTerapisIds = data.selected_terapis.map(t => t.id.toString());

                data.active_terapis.forEach(function (t) {
                    let isSelected = selectedTerapisIds.includes(t.id.toString());
                    $('.terapis').append(new Option(t.nama, t.id, false, isSelected));
                });

                data.selected_terapis.forEach(function (t) {
                    if (!$('.terapis option[value="' + t.id + '"]').length) {
                        let option = new Option(t.nama + ' (Non-Aktif)', t.id, false, true);
                        option.disabled = true;
                        $('.terapis').append(option);
                    }
                });
                $('.terapis').trigger('change');
                $('input[name="processAt"]').val(data.process_at);
                $('input[name="finishAt"]').val(data.finish_at);
                $('input[name="timeConsume"]').val(data.time_consume);
                $('input[name="id"]').val(data.id);
                $('input[name="patient_id"]').val(data.patient_id);
                ['cervical', 'thoraxal', 'lumbar', 'sacrum', 'pelvis', 'power', 'pr', 'tensi'].forEach(k => {
                    $('input[name="' + k + '"]').val(data[k]);
                });
                ['other', 'measure', 'ket_vertebrata', 'ket_thorax', 'ket_kompresi', 'ket_plintiran', 'ket_viska', 'penyebab'].forEach(k => {
                    const dbKey = (k === 'ket_vertebrata') ? 'keterangan_verteba' : (k === 'ket_viska') ? 'keterangan_visualfoot' : k.replace('ket_', 'keterangan_');
                    $('textarea[name="' + k + '"]').val(data[dbKey] || data[k]);
                });
                $('input[name="date"]').val(self.formatDateForInput(data.date));
                ['vertebra', 'thorax', 'kompresi', 'plintiran', 'visual_kaki', 'pubis'].forEach(group => {
                    const dbKey = group === 'visual_kaki' ? 'visualfoot' : group === 'vertebra' ? 'verteba' : group;
                    const arr = data[dbKey] ? data[dbKey].split(',') : [];
                    arr.forEach(val => { $(`input[name="${group}[]"][value="${val}"]`).prop('checked', true); });
                });
                self.updateFormStatus(data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error('Error:', textStatus, errorThrown);
                alert('Terjadi kesalahan, silahkan coba lagi...');
            }
        });
    },

    updateHistoryInfo(data) {
        $('#created_by').text(data.history_created_by);
        if (data.history_updated_by && data.history_updated_by.trim() !== '-') {
            $('#updated_by').text(data.history_updated_by);
            $('#updated_info').show();
        } else {
            $('#updated_info').hide();
        }
    },

    updateFormStatus(data) {
        var currentDate = new Date();
        var recordDate = new Date(data.date_modified);
        var dayDifference = Math.ceil(Math.abs(currentDate - recordDate) / (1000 * 60 * 60 * 24));

        if (dayDifference > 1 && data.type !== 'draft') {
            $('#exampleModal form :input').prop('readonly', true);
            $('#exampleModal form :checkbox, #exampleModal form :radio').prop('disabled', true);
            if (complaintTagify) complaintTagify.setReadonly(true);
            if (medhisTagify) medhisTagify.setReadonly(true);
            if (resultTagify) resultTagify.setReadonly(true);
            $('.terapis, #region_history').prop('disabled', true);
            $('#save-button').hide();
        } else {
            $('#exampleModal form :input').prop('readonly', false);
            $('#exampleModal form :checkbox, #exampleModal form :radio').prop('disabled', false);
            if (complaintTagify) complaintTagify.setReadonly(false);
            if (medhisTagify) medhisTagify.setReadonly(false);
            if (resultTagify) resultTagify.setReadonly(false);
            $('.terapis, #region_history').prop('disabled', false);
            $('#save-button').show();
        }
    },

    // --- DELETE DATA ---
    destroy(id) {
        deleteId = id;
        this.openModal(document.getElementById('deleteModal'));
    },

    initEventListeners() {
        const self = this;
        if (window.$) {
            $('#region_history').select2({ placeholder: 'Pilih Wilayah', width: '100%', dropdownParent: $('#exampleModal') });
            $('.terapis').select2({ placeholder: '-- Pilih Terapis --', width: '100%', dropdownParent: $('#exampleModal') });
        }
        $('#confirmDeleteButton').on('click', function () {
            const btn = $(this);
            if (deleteId === null) return;
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...');
            $.ajax({
                url: `${window.historyDestroyUrl}/${deleteId}`,
                type: 'POST', dataType: 'json',
                data: { [window.csrfTokenName]: window.csrfHash },
                success: function (response) {
                    if (response.status) {
                        self.closeModal(document.getElementById('deleteModal'));
                        if ($.fn.DataTable.isDataTable('#table-2')) $('#table-2').DataTable().ajax.reload(null, false);
                        else location.reload();
                    } else alert('Gagal: ' + response.message);
                },
                error: () => alert('Terjadi kesalahan.'),
                complete: () => {
                    btn.prop('disabled', false).text('Ya, Hapus Data');
                    deleteId = null;
                }
            });
        });

        // --- SAVE DATA ---
        $(document).on('click', '#save-button', function (e) {
            e.preventDefault();
            const btn = $(this);
            let formData = new FormData();
            $('#exampleModal form').find('input[name], textarea[name], select[name]').each(function () {
                let input = $(this);
                let name = input.attr('name');
                let value = input.val();
                if (['complaint', 'medhis', 'results'].includes(name)) return;
                if (input.is(':checkbox')) {
                    if (input.is(':checked')) formData.append(name, value);
                } else if (input.is(':radio')) {
                    if (input.is(':checked')) formData.set(name, value);
                } else {
                    formData.set(name, value);
                }
            });

            if (complaintTagify) formData.set('complaint', JSON.stringify(complaintTagify.value || []));
            if (medhisTagify) formData.set('medhis', JSON.stringify(medhisTagify.value || []));
            if (resultTagify) formData.set('results', JSON.stringify(resultTagify.value || []));

            $.ajax({
                url: window.historyStoreUrl,
                type: "POST", data: formData, processData: false, contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token-hash"]').attr('content') },
                beforeSend: function () { btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...'); },
                success: function (response) {
                    if (response.status) {
                        self.closeModal(document.getElementById('exampleModal'));
                        Swal.fire({ title: 'Berhasil!', text: response.message, icon: 'success', confirmButtonColor: '#0f172a' })
                            .then(() => location.reload());
                    } else {
                        Swal.fire({ title: 'Gagal!', text: response.message, icon: 'error', confirmButtonColor: '#0f172a' });
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Simpan Data');
                    }
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Simpan Data');
                }
            });
        });

        // --- CLOSE MODAL TAMBAH ---
        document.addEventListener('click', (e) => {
            const closeBtn = e.target.closest('[data-modal-close]');
            if (closeBtn) self.closeModal(closeBtn.closest('.modal-wrapper'));
            if (e.target.classList.contains('modal-wrapper')) self.closeModal(e.target);
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const visibleModal = document.querySelector('.modal-wrapper.flex');
                if (visibleModal) self.closeModal(visibleModal);
            }
        });

        function limitOneGrade(areaPrefix) {
            $(`input[name="${areaPrefix}_kanan_grade"]`).on('change', function () {
                if ($(this).is(':checked')) $(`input[name="${areaPrefix}_kanan_grade"]`).not(this).prop('checked', false);
            });
            $(`input[name="${areaPrefix}_kiri_grade"]`).on('change', function () {
                if ($(this).is(':checked')) $(`input[name="${areaPrefix}_kiri_grade"]`).not(this).prop('checked', false);
            });
        }

        const areas = ['odp', 'vital', 'kelenjar', 'hormon', 'tk', 'fd', 'lp_atas', 'lp_bawah', 'lp_kanan', 'lp_kiri', 'cv4', 'cv6', 'l1', 'l3', 'piriformis', 'sendok'];
        areas.forEach(area => limitOneGrade(area));
    },

    checkUrlParams() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('openModalRiwayat') === 'true') {
            const hId = urlParams.get('history_id');
            if (hId && hId !== 'undefined' && hId !== '') {
                setTimeout(() => this.show(hId), 500);
            }
        }
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => PatientHistoryPage.init());
} else {
    PatientHistoryPage.init();
}