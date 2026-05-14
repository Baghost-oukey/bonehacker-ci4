/**
 * Users Management Page Script
 * Handles CRUD operations for user data with custom pagination and mobile cards
 */

const MODAL_VISIBLE_CLASS = "flex";
const MODAL_HIDDEN_CLASS = "hidden";

const getCsrfPayload = (config) => ({
    [config.csrfName]: config.csrfHash,
});

const debounce = (fn, delay = 400) => {
    let timerId;
    return (...args) => {
        clearTimeout(timerId);
        timerId = setTimeout(() => fn(...args), delay);
    };
};

const openModal = (modal) => {
    if (!modal) return;
    modal.classList.remove(MODAL_HIDDEN_CLASS);
    modal.classList.add(MODAL_VISIBLE_CLASS);
    setTimeout(() => {
        modal.querySelector('.transform')?.classList.remove('translate-y-full', 'scale-95');
        modal.classList.remove('opacity-0');
    }, 10);
};

const closeModal = (modal) => {
    if (!modal) return;
    modal.querySelector('.transform')?.classList.add('translate-y-full', 'md:scale-95');
    modal.classList.add('opacity-0');
    setTimeout(() => {
        modal.classList.remove(MODAL_VISIBLE_CLASS);
        modal.classList.add(MODAL_HIDDEN_CLASS);
    }, 300);
};

const setupUsersPage = () => {
    const config = window.karyawanConfig;
    const page = document.getElementById("usersPage");

    if (!config || !page || typeof window.$ === "undefined") return;

    const $ = window.$;
    const swalLib = window.Swal || window.swal;

    let currentPage = 1;
    let pageLength = 25;
    let totalRecords = 0;
    let filteredRecords = 0;
    let searchValue = "";
    let deleteUrl = null;
    let originalUsername = "";

    const initSelect2 = () => {
        $('#regions_add, #edit_regions').each(function() {
            const $this = $(this);
            $this.select2({
                width: '100%',
                placeholder: "-- Pilih Wilayah --",
                dropdownParent: $this.closest('.modal-wrapper')
            });
        });
    };

    const getLatestCsrfHash = () => $('meta[name="csrf-token-hash"]').attr('content');

    const getCsrfPayload = (cfg) => ({
        [cfg.csrfName]: getLatestCsrfHash()
    });

    const updateCsrf = (newToken) => {
        if (!newToken) return;
        config.csrfHash = newToken;
        $('meta[name="csrf-token-hash"]').attr('content', newToken);
        $('input[name="' + config.csrfName + '"]').val(newToken);
    };

    const updatePaginationUI = () => {
        const totalPages = Math.max(1, Math.ceil(filteredRecords / pageLength));
        const container = $("#paginationNumbers");
        container.empty();

        // Start/End for pagination window
        const startPage = Math.max(1, currentPage - 1);
        const endPage = Math.min(totalPages, currentPage + 1);

        for (let pageNum = startPage; pageNum <= endPage; pageNum += 1) {
            const activeClass = pageNum === currentPage
                ? "bg-teal-600 text-white font-black shadow-lg shadow-teal-600/20"
                : "bg-white text-slate-500 font-bold hover:bg-slate-50 border border-slate-100";
            container.append(`<button class="pagination-btn w-9 h-9 flex items-center justify-center rounded-xl text-[11px] transition-all ${activeClass}" data-page="${pageNum}">${pageNum}</button>`);
        }

        $("#paginationPrev").prop("disabled", currentPage <= 1);
        $("#paginationNext").prop("disabled", currentPage >= totalPages);
        
        const start = filteredRecords <= 0 ? 0 : (currentPage - 1) * pageLength + 1;
        const end = Math.min(currentPage * pageLength, filteredRecords);
        $("#paginationInfo").text(`Menampilkan ${start} - ${end} dari ${filteredRecords} User`);
    };

    const renderEmptyState = (message) => {
        const emptyHtml = `
            <div class="py-20 text-center opacity-30">
                <i class="fas fa-users-slash text-5xl text-slate-300 mb-4"></i>
                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">${message}</p>
            </div>
        `;
        $("#table-user tbody").html(`<tr><td colspan="6">${emptyHtml}</td></tr>`);
        $("#mobile-users-container").html(emptyHtml);
    };

    const loadTableData = (pageNumber = 1) => {
        $("#table-user tbody").css('opacity', '0.5');
        $("#mobile-users-container").css('opacity', '0.5');

        $.ajax({
            url: config.fetchUrl,
            type: "POST",
            dataType: "json",
            data: {
                ...getCsrfPayload(config),
                draw: 1,
                start: (pageNumber - 1) * pageLength,
                length: pageLength,
                search: { value: searchValue },
            },
            success: (response) => {
                if (response.csrfHash) updateCsrf(response.csrfHash);

                currentPage = pageNumber;
                totalRecords = Number(response.recordsTotal || 0);
                filteredRecords = Number(response.recordsFiltered || totalRecords);

                const tbody = $("#table-user tbody");
                const mobileContainer = $("#mobile-users-container");
                tbody.empty().css('opacity', '1');
                mobileContainer.empty().css('opacity', '1');

                if (!response.data || response.data.length === 0) {
                    renderEmptyState("Data user belum tersedia");
                    updatePaginationUI();
                    return;
                }

                response.data.forEach((row) => {
                    const isInactive = row.is_active == 0;
                    const inactiveTrClass = isInactive ? ' opacity-50 bg-slate-50/30' : '';
                    const inactiveTdClass = isInactive ? ' line-through text-slate-400' : '';

                    // Desktop row
                    const tr = $(`<tr class="hover:bg-slate-50/50 transition-colors border-b border-slate-50 group${inactiveTrClass}"></tr>`);
                    tr.append(`<td class="px-6 py-4 text-xs font-mono text-slate-400 italic">#${row.no || "-"}</td>`);
                    tr.append(`<td class="px-6 py-4 font-black text-slate-800 text-sm${inactiveTdClass}">${row.realname || "-"}</td>`);
                    tr.append(`<td class="px-6 py-4 text-xs font-bold text-slate-500${inactiveTdClass}">${row.username || "-"}</td>`);
                    tr.append(`<td class="px-6 py-4"><span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest bg-slate-100 text-slate-500 border border-slate-200">${row.role || "-"}</span></td>`);
                    tr.append(`<td class="px-6 py-4 text-xs font-bold text-slate-400${inactiveTdClass}">${row.region_name || "-"}</td>`);
                    tr.append(`<td class="px-6 py-4">${row.status || "-"}</td>`);
                    tr.append(`<td class="px-6 py-4 text-center">${row.action || "-"}</td>`);
                    tbody.append(tr);

                    // Mobile card
                    mobileContainer.append(`
                        <div class="p-5 space-y-4 hover:bg-slate-50/50 transition-all">
                            <div class="flex justify-between items-start">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center font-black text-sm shadow-sm border border-teal-100/50">
                                        ${(row.realname || "U")[0].toUpperCase()}
                                    </div>
                                    <div class="flex flex-col gap-0.5">
                                        <h3 class="text-sm font-black text-slate-800 leading-tight uppercase tracking-tight">${row.realname}</h3>
                                        <p class="text-[10px] font-bold text-slate-400">@${row.username}</p>
                                    </div>
                                </div>
                                <div class="flex gap-1.5">
                                    ${row.action}
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="px-2.5 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-slate-100 text-slate-500 border border-slate-200">
                                    <i class="fas fa-shield-alt mr-1 opacity-50"></i> ${row.role}
                                </span>
                                <span class="px-2.5 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest bg-teal-50 text-teal-600 border border-teal-100/50 max-w-[150px] truncate">
                                    <i class="fas fa-map-marker-alt mr-1 opacity-50"></i> ${row.region_name || 'Semua Wilayah'}
                                </span>
                            </div>
                        </div>
                    `);
                });

                updatePaginationUI();
            }
        });
    };

    const toggleExtraFields = (roleSelect) => {
        const role = $(roleSelect).val();
        const $extraTerapis = $("#extraTerapisFields");
        const $regionManagement = $("#regionFieldAdd");
        
        // Therapist fields
        if (role === 'terapis') {
            $extraTerapis.fadeIn().removeClass('hidden');
            $extraTerapis.find('input, select, textarea').prop('required', true);
            // Regions patient for therapist can be multiple, so show management region select
            $regionManagement.fadeIn().removeClass('hidden');
        } else {
            $extraTerapis.fadeOut().addClass('hidden');
            $extraTerapis.find('input, select, textarea').prop('required', false);
            
            // Management regions
            if (role === 'owner' || role === 'admin') {
                $regionManagement.fadeIn().removeClass('hidden');
                $("#regions_add").prop('required', true);
            } else {
                $regionManagement.fadeOut().addClass('hidden');
                $("#regions_add").prop('required', false);
            }
        }
        initSelect2();
    };

    const checkUsername = (username, feedbackElement, submitBtn, currentUsername = null) => {
        const inputField = $(feedbackElement).siblings('input');
        if (username.length < 3) {
            $(feedbackElement).addClass('hidden');
            inputField.removeClass('border-red-500 border-teal-500');
            return;
        }
        if (currentUsername && username.toLowerCase() === currentUsername.toLowerCase()) {
            $(feedbackElement).removeClass('hidden').text('USERNAME SAAT INI').css('color', '#94a3b8');
            inputField.removeClass('border-red-500').addClass('border-teal-500');
            return;
        }

        $(feedbackElement).removeClass('hidden').text('MEMERIKSA...').css('color', '#94a3b8');
        $.post(config.checkUsernameUrl, { username, ...getCsrfPayload(config) }, (res) => {
            if (res.exists) {
                $(feedbackElement).text('SUDAH DIGUNAKAN').css('color', '#ef4444');
                inputField.addClass('border-red-500').removeClass('border-teal-500');
                $(submitBtn).prop('disabled', true);
            } else {
                $(feedbackElement).text('TERSEDIA').css('color', '#10b981');
                inputField.removeClass('border-red-500').addClass('border-teal-500');
                $(submitBtn).prop('disabled', false);
            }
        }, 'json');
    };

    const searchHandler = debounce((v) => { searchValue = v; currentPage = 1; loadTableData(1); });

    // Events
    $("#searchInput").on("keyup", function () { searchHandler($(this).val()); });
    $("#paginationLength").on("change", function () { pageLength = parseInt($(this).val(), 10); currentPage = 1; loadTableData(1); });
    $(document).on("click", ".pagination-btn", function () { loadTableData(parseInt($(this).data("page"), 10)); });
    $("#paginationPrev").on("click", () => { if (currentPage > 1) loadTableData(currentPage - 1); });
    $("#paginationNext").on("click", () => { if (currentPage < Math.ceil(filteredRecords / pageLength)) loadTableData(currentPage + 1); });

    $('#role_add').on('change', function() { toggleExtraFields(this); });
    // Keep old logic for edit modal for now or refactor it too
    $('#edit_role').on('change', function() { 
        const role = $(this).val();
        const $regionEdit = $("#regionFieldEdit");
        if (role === 'owner' || role === 'admin' || role === 'terapis') {
            $regionEdit.fadeIn().removeClass('hidden');
        } else {
            $regionEdit.fadeOut().addClass('hidden');
        }
    });

    $('#username_add').on('keyup', debounce(function() { checkUsername($(this).val(), '#formAddUser .username-feedback', '#submitBtnAdd'); }, 500));
    $('#edit_username').on('keyup', debounce(function() { checkUsername($(this).val(), '#formEditUser .edit-username-feedback', '#submitBtnEdit', originalUsername); }, 500));

    // CRUD Ajax
    $('#formAddUser, #formEditUser').on('submit', function(e) {
        e.preventDefault();
        const form = this;
        const $form = $(form);
        const btn = $form.find('button[type="submit"]');
        const modal = $form.closest('.modal-wrapper')[0];

        if (!form.checkValidity()) { $form.addClass('was-validated'); return; }

        const url = $form.attr('action');
        if (!url || url === '#' || url.startsWith('javascript:')) {
            swalLib.fire('Error!', 'Tujuan pengiriman data tidak valid. Harap refresh halaman.', 'error');
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i>');
        const formData = new FormData(form);
        formData.append(config.csrfName, config.csrfHash);

        $.ajax({
            url: $form.attr('action'),
            type: "POST",
            data: formData, contentType: false, processData: false, dataType: "json",
            success: (res) => {
                if (res.status === 'success') {
                    closeModal(modal);
                    if (form.id === 'formAddUser') form.reset();
                    loadTableData(currentPage);
                    swalLib.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false });
                } else {
                    swalLib.fire('Gagal!', res.message, 'error');
                }
            },
            error: () => swalLib.fire('Error!', 'Terjadi kesalahan sistem.', 'error'),
            complete: () => btn.prop('disabled', false).text('Simpan')
        });
    });

    $(document).on('click', '.btn_edit', function() {
        const d = $(this).data();
        originalUsername = d.username;
        $('#formEditUser').attr('action', d.href);
        $('#edit_realname').val(d.realname);
        $('#edit_username').val(d.username);
        $('#edit_role').val(d.role).trigger('change');
        if (d.regions_patient) $('#edit_regions').val(String(d.regions_patient).split(',').map(Number)).trigger('change');
        openModal(document.getElementById("modalEdit"));
    });

    $(document).on('click', '.btn_create_account', function() {
        const d = $(this).data();
        $('#quick-karyawan-id').val(d.terapis_id);
        $('#quick-realname').text(d.realname);
        $('#quick-username').val(d.terapis_id);
        $('#quick-password').val('');
        openModal(document.getElementById("modalQuickCreateAccount"));
    });

    $('#formQuickCreateAccount').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const btn = $('#submitQuickAccount');
        const modal = document.getElementById("modalQuickCreateAccount");

        if (!this.checkValidity()) {
            $form.addClass('was-validated');
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i>');

        if (!config.generateUserUrl) {
            swalLib.fire('Error!', 'Konfigurasi URL tidak ditemukan. Harap refresh halaman.', 'error');
            btn.prop('disabled', false).text('Simpan & Buat Akun');
            return;
        }

        $.ajax({
            url: config.generateUserUrl,
            type: "POST",
            data: {
                ...getCsrfPayload(config),
                karyawan_id: $('#quick-karyawan-id').val(),
                username: $('#quick-username').val(),
                password: $('#quick-password').val()
            },
            dataType: "json",
            success: (res) => {
                updateCsrf(res.csrfHash);
                if (res.status === 'success') {
                    closeModal(modal);
                    setTimeout(() => {
                        loadTableData(currentPage);
                        swalLib.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false });
                    }, 200);
                } else {
                    swalLib.fire('Gagal!', res.message, 'error');
                }
            },
            error: () => swalLib.fire('Error!', 'Terjadi kesalahan sistem.', 'error'),
            complete: () => btn.prop('disabled', false).text('Simpan & Buat Akun')
        });
    });

    $(document).on('click', '.btn_add_patient', function(e) {
        e.preventDefault();
        const userId = $(this).data('userid');
        if (userId && config.viewPatientUrl) {
            window.location.href = config.viewPatientUrl + '/' + userId;
        }
    });

    $(document).on('click', '.btn_toggle_status', function(e) {
        e.preventDefault();
        const d = $(this).data();
        const status = d.status; // 1: aktif, 0: nonaktif
        const url = status == 1 ? config.activeUrl : config.nonActiveUrl;
        const msg = status == 1 ? "Aktifkan user ini?" : "Nonaktifkan user ini?";

        swalLib.fire({
            title: msg,
            text: status == 1 ? "User akan kembali dapat mengakses sistem." : "User tidak akan bisa login, namun data historis tetap tersimpan.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: status == 1 ? 'Ya, Aktifkan!' : 'Ya, Nonaktifkan!',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: status == 1 ? 'bg-emerald-600 text-white px-6 py-2 rounded-xl font-bold ml-2' : 'bg-red-600 text-white px-6 py-2 rounded-xl font-bold ml-2',
                cancelButton: 'bg-slate-100 text-slate-500 px-6 py-2 rounded-xl font-bold'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(url + '/' + d.id, { 
                    ...getCsrfPayload(config),
                    type: d.type
                }, (res) => {
                    if (res.csrfHash) updateCsrf(res.csrfHash);
                    swalLib.fire({ 
                        icon: res.status, 
                        title: res.status === 'success' ? 'Berhasil!' : 'Gagal!', 
                        text: res.message, 
                        timer: 1500, 
                        showConfirmButton: false 
                    });
                    loadTableData(currentPage);
                }, 'json');
            }
        });
    });

    $(document).on('click', '.btn_delete', function(e) {
        e.preventDefault();
        const href = $(this).data('href');
        swalLib.fire({
            title: 'Hapus User?', text: "Data ini akan dihapus permanen.", icon: 'warning',
            showCancelButton: true, confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal',
            customClass: { confirmButton: 'bg-red-600 text-white px-6 py-2 rounded-xl font-bold ml-2', cancelButton: 'bg-slate-100 text-slate-500 px-6 py-2 rounded-xl font-bold' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(href, getCsrfPayload(config), (res) => {
                    swalLib.fire({ icon: res.status, title: res.status === 'success' ? 'Berhasil!' : 'Gagal!', text: res.message, timer: 1500, showConfirmButton: false });
                    loadTableData(currentPage);
                }, 'json');
            }
        });
    });

    // Global Modal Handler
    $(document).on("click", "[data-modal-open]", function() { openModal(document.getElementById($(this).data("modal-open"))); initSelect2(); });
    $(document).on("click", "[data-modal-close], .modal-wrapper", function(e) { if (e.target === this || $(this).is('[data-modal-close]')) closeModal($(this).closest(".modal-wrapper")[0]); });

    initSelect2();
    loadTableData(1);
};

if (document.readyState === "loading") { document.addEventListener("DOMContentLoaded", setupUsersPage); } else { setupUsersPage(); }
