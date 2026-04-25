// --- INIT SCRIPT ---
const PatientBiodataPage = {
    init() {
        this.injectSelect2ShadcnStyle();
        this.initSuspectiveToggle();
        this.initSelect2Instances();
        this.initVisibilityToggle();
        this.initEnterKeyPrevention();
    },


    injectSelect2ShadcnStyle() {
        const style = document.createElement('style');
        style.innerHTML = `
            .select2-container .select2-selection--single {
                height: 2.5rem !important; /* h-10 */
                border-color: #e2e8f0 !important; /* border-slate-200 */
                border-radius: 0.375rem !important; /* rounded-md */
                display: flex !important;
                align-items: center !important;
                font-size: 0.875rem !important; /* text-sm */
                color: #0f172a !important; /* text-slate-900 */
                background-color: #ffffff !important;
                box-shadow: none !important;
            }
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #0f172a !important;
                line-height: normal !important;
                padding-left: 0.75rem !important; /* px-3 */
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 2.5rem !important;
                right: 0.5rem !important;
            }
            .select2-container--open .select2-selection--single {
                border-color: #0f172a !important; /* ring-slate-900 */
                box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #0f172a !important; /* ring-offset-2 */
                outline: none !important;
            }
            .select2-dropdown {
                border-color: #e2e8f0 !important;
                border-radius: 0.375rem !important;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1) !important;
                font-size: 0.875rem !important;
                padding: 0.25rem !important;
            }
            .select2-container--default .select2-results__option {
                border-radius: 0.25rem !important;
                padding: 0.375rem 0.5rem !important;
                margin-bottom: 2px !important;
            }
            .select2-container--default .select2-results__option--highlighted[aria-selected] {
                background-color: #f1f5f9 !important; /* bg-slate-100 */
                color: #0f172a !important; /* text-slate-900 */
            }
            .select2-search--dropdown .select2-search__field {
                border: 1px solid #e2e8f0 !important;
                border-radius: 0.375rem !important;
                padding: 0.375rem 0.5rem !important;
            }
            .select2-search--dropdown .select2-search__field:focus {
                border-color: #0f172a !important;
                outline: none !important;
            }
        `;
        document.head.appendChild(style);
    },

// --- TOMBOL KETERANGAN RENTAN --- 
    initSuspectiveToggle() {
        const checkbox = document.getElementById("isSuspectiveCheckbox");
        if (!checkbox) return;

        checkbox.addEventListener("change", function() {
            const additionalInfo = document.getElementById("keterangan_rentan");
            if (additionalInfo) {
                additionalInfo.style.display = this.checked ? "block" : "none";
            }
        });
    },


    // --- SELECT REGION ---
    initSelect2Instances() {
        if (typeof window.$ === 'undefined') return;

        $('#region_id, #region_history').select2({
            placeholder: "PILIH",
            width: '100%'
        });
        const apiUrl = 'https://wilayah.smartsociety.id/public/desa';
        
        $('#desa_id').select2({
            placeholder: "Temukan Desa",
            allowClear: true,
            width: '100%',
            ajax: {
                url: apiUrl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    let options = [];
                    if (data.data && data.data.data) {
                        $.each(data.data.data, function(index, item) {
                            let optionText = item.desNama;
                            if (item.kecamatan && item.kecamatan.kabupaten) {
                                optionText += `<br><small style="color:#64748b">Kec. ${item.kecamatan.kecNama}, ${item.kecamatan.kabupaten.kabNama}</small>`;
                            }
                            options.push({
                                id: item.desIdDesa,
                                text: optionText,
                                data: {
                                    desa_nama: item.desNama,
                                    kecamatan_id: item.kecamatan ? item.kecamatan.kecIdKecamatan : '',
                                    kecamatan_nama: item.kecamatan ? item.kecamatan.kecNama : '',
                                    kabupaten_id: item.kecamatan && item.kecamatan.kabupaten ? item.kecamatan.kabupaten.kabIdKabupaten : '',
                                    kabupaten_nama: item.kecamatan && item.kecamatan.kabupaten ? item.kecamatan.kabupaten.kabNama : '',
                                    provinsi_id: item.kecamatan && item.kecamatan.kabupaten && item.kecamatan.kabupaten.provinsi ? item.kecamatan.kabupaten.provinsi.provIdProvinsi : '',
                                    provinsi_nama: item.kecamatan && item.kecamatan.kabupaten && item.kecamatan.kabupaten.provinsi ? item.kecamatan.kabupaten.provinsi.provNama : ''
                                }
                            });
                        });
                    }
                    return {
                        results: options,
                        pagination: { more: !!(data.data && data.data.next_page_url) }
                    };
                },
                cache: true
            },
            minimumInputLength: 1,
            escapeMarkup: function(markup) { return markup; },
            templateResult: function(item) { return item.text; },
            templateSelection: function(item) {
                return item.text ? item.text.split('<br>')[0] : item.text;
            }
        });

        $('#desa_id').on('select2:select', function(e) {
            const data = e.params.data.data;
            if (data) {
                $('#desa_nama').val(data.desa_nama);
                $('#kecamatan_id').val(data.kecamatan_id);
                $('#kecamatan_nama').val(data.kecamatan_nama);
                $('#kabupaten_id').val(data.kabupaten_id);
                $('#kabupaten_nama').val(data.kabupaten_nama);
                $('#provinsi_id').val(data.provinsi_id);
                $('#provinsi_nama').val(data.provinsi_nama);
            }
        });
    },

// --- SELECT COUNTRY ---
    initVisibilityToggle() {
        const updateVisibility = () => {
            const countryGroup = document.getElementById('country-group');
            if(!countryGroup) return; 

            const domesticChecked = document.querySelector('input[name="domestic"]')?.checked || false;
            const regionGroup = document.getElementById('region-group');
            const desaGroup = document.getElementById('desa-group');
            const desaaGroup = document.getElementById('desaa-group');
            const kecamatanGroup = document.getElementById('kecamatan-group');
            const kabupatenGroup = document.getElementById('kabupaten-group');
            const provinsiGroup = document.getElementById('provinsi-group');

            if (domesticChecked) {
                countryGroup.style.display = 'none';
                if(regionGroup) regionGroup.style.display = 'block';
                if(desaGroup) desaGroup.style.display = 'block';
                if(desaaGroup) desaaGroup.style.display = 'block';
                if(kecamatanGroup) kecamatanGroup.style.display = 'block';
                if(kabupatenGroup) kabupatenGroup.style.display = 'block';
                if(provinsiGroup) provinsiGroup.style.display = 'block';
            } else {
                countryGroup.style.display = 'block';
                if(regionGroup) regionGroup.style.display = 'none';
                if(desaGroup) desaGroup.style.display = 'none';
                if(desaaGroup) desaaGroup.style.display = 'none';
                if(kecamatanGroup) kecamatanGroup.style.display = 'none';
                if(kabupatenGroup) kabupatenGroup.style.display = 'none';
                if(provinsiGroup) provinsiGroup.style.display = 'none';
            }
        };

        const countryEl = document.getElementById('country_id');
        const domesticEl = document.querySelector('input[name="domestic"]');

        if (countryEl) countryEl.addEventListener('change', updateVisibility);
        if (domesticEl) domesticEl.addEventListener('change', updateVisibility);

        updateVisibility();
    },


    initEnterKeyPrevention() {
        if (typeof window.$ === 'undefined') return;
        $(document).on('keypress', '.select2-search__field', function(e) {
            if (e.which === 13) e.preventDefault();
        });
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => PatientBiodataPage.init());
} else {
    PatientBiodataPage.init();
}