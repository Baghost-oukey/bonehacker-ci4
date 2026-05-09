/**
 * Users Management Page Script
 * Handles CRUD operations for user data with custom pagination
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
};

const closeModal = (modal) => {
    if (!modal) return;
    modal.classList.remove(MODAL_VISIBLE_CLASS);
    modal.classList.add(MODAL_HIDDEN_CLASS);
};

const setupUsersPage = () => {
    const config = window.usersConfig;
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

    // Initialize Select2
    const initSelect2 = () => {
        $('#regions_add, #edit_regions').select2({
            width: '100%',
            placeholder: "-- Pilih Wilayah --",
            dropdownParent: $('#regions_add').closest('.modal-wrapper')
        });
    };

    const updateCsrf = (newToken) => {
        if (!newToken) return;
        config.csrfHash = newToken;
        $("meta[name='csrf-token']").attr("content", newToken);
        $(`input[name='${config.csrfName}']`).val(newToken);
    };

    const updatePaginationInfo = () => {
        if (filteredRecords <= 0) {
            $("#paginationInfo").text("Menampilkan 0 sampai 0 dari 0 data");
            return;
        }
        const start = (currentPage - 1) * pageLength + 1;
        const end = Math.min(currentPage * pageLength, filteredRecords);
        $("#paginationInfo").text(`Menampilkan ${start} sampai ${end} dari ${filteredRecords} data`);
    };

    const updatePaginationUI = () => {
        const totalPages = Math.max(1, Math.ceil(filteredRecords / pageLength));
        const container = $("#paginationNumbers");
        container.empty();

        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            container.append(`<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400" data-page="1">1</button>`);
            if (startPage > 2) {
                container.append('<span class="px-1 text-slate-300">...</span>');
            }
        }

        for (let pageNum = startPage; pageNum <= endPage; pageNum += 1) {
            const activeClass = pageNum === currentPage
                ? "bg-teal-600 border-teal-600 text-white font-semibold shadow-md shadow-teal-600/30"
                : "border border-slate-300 bg-white text-slate-700 font-semibold transition hover:bg-slate-100 hover:border-slate-400";
            container.append(`<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg ${activeClass} text-xs" data-page="${pageNum}">${pageNum}</button>`);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                container.append('<span class="px-1 text-slate-300">...</span>');
            }
            container.append(`<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400" data-page="${totalPages}">${totalPages}</button>`);
        }

        $("#paginationPrev").prop("disabled", currentPage <= 1);
        $("#paginationNext").prop("disabled", currentPage >= totalPages);
    };

    const renderEmptyState = (message) => {
        $("#table-user tbody").html(`<tr class="hover:bg-slate-50 transition"><td colspan="6" class="px-6 py-12 text-center text-slate-400 italic text-sm"><i class="fas fa-inbox mr-2 text-slate-300"></i>${message}</td></tr>`);
    };

    const loadTableData = (pageNumber = 1) => {
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
                if (response.csrfHash) {
                    updateCsrf(response.csrfHash);
                }

                currentPage = pageNumber;
                totalRecords = Number(response.recordsTotal || 0);
                filteredRecords = Number(response.recordsFiltered || totalRecords);

                const tbody = $("#table-user tbody");
                tbody.empty();

                if (!response.data || response.data.length === 0) {
                    renderEmptyState("Data user belum tersedia");
                    updatePaginationInfo();
                    updatePaginationUI();
                    return;
                }

                response.data.forEach((row) => {
                    const tr = $(`<tr class="hover:bg-slate-50 transition border-b border-slate-100"></tr>`);
                    tr.append(`<td class="px-6 py-3.5">${row.no || "-"}</td>`);
                    tr.append(`<td class="px-6 py-3.5 font-medium text-slate-800">${row.realname || "-"}</td>`);
                    tr.append(`<td class="px-6 py-3.5 text-slate-600">${row.username || "-"}</td>`);
                    tr.append(`<td class="px-6 py-3.5">${row.role || "-"}</td>`);
                    tr.append(`<td class="px-6 py-3.5 text-slate-500">${row.region_name || "-"}</td>`);
                    tr.append(`<td class="px-6 py-3.5 text-center">${row.action || "-"}</td>`);
                    tbody.append(tr);
                });

                updatePaginationInfo();
                updatePaginationUI();
            },
            error: () => {
                renderEmptyState("Gagal memuat data user");
                filteredRecords = 0;
                updatePaginationInfo();
                updatePaginationUI();
            },
        });
    };

    // Toggle region field based on role
    const toggleRegionField = (roleSelect, targetField) => {
        const role = $(roleSelect).val();
        const selectElement = $(targetField).find('select');
        
        if (role === 'owner' || role === 'admin' || role === 'user') {
            $(targetField).fadeIn().removeClass('hidden');
            selectElement.attr('required', true);
            
            // Determine if multiple regions are allowed
            const isMultiple = (role === 'owner');
            
            // Re-initialize select2 with new multiple setting
            if (selectElement.hasClass("select2-hidden-accessible")) {
                selectElement.select2('destroy');
            }
            
            selectElement.prop('multiple', isMultiple);
            
            selectElement.select2({
                width: '100%',
                placeholder: "-- Pilih Wilayah --",
                dropdownParent: selectElement.closest('.modal-wrapper')
            });
            
            // Clear multiple selections if switching to single select role
            if (!isMultiple && Array.isArray(selectElement.val()) && selectElement.val().length > 1) {
                selectElement.val(null).trigger('change');
            }
        } else {
            $(targetField).fadeOut().addClass('hidden');
            selectElement.attr('required', false).val(null).trigger('change');
        }
    };

    // Check username availability
    const checkUsername = (username, feedbackElement, submitBtn, currentUsername = null) => {
        if (username.length < 3) {
            $(feedbackElement).addClass('hidden');
            $(submitBtn).prop('disabled', false);
            return;
        }

        // Skip check if editing and username hasn't changed
        if (currentUsername && username === currentUsername) {
            $(feedbackElement).removeClass('hidden').text('Username valid').css('color', 'green');
            $(submitBtn).prop('disabled', false);
            return;
        }

        $.ajax({
            url: config.checkUsernameUrl,
            type: "POST",
            data: {
                username: username,
                [config.csrfName]: config.csrfHash
            },
            dataType: "json",
            success: function(res) {
                if (res.exists === true || res.exists === "true") {
                    $(feedbackElement).removeClass('hidden').text('Username sudah digunakan').css('color', '#ef4444');
                    $(submitBtn).prop('disabled', true);
                } else {
                    $(feedbackElement).removeClass('hidden').text('Username tersedia').css('color', '#10b981');
                    $(submitBtn).prop('disabled', false);
                }
            },
            error: function() {
                $(feedbackElement).removeClass('hidden').text('Gagal memeriksa username').css('color', '#ef4444');
                $(submitBtn).prop('disabled', true);
            }
        });
    };

    // Search handler with debounce
    const searchHandler = debounce((value) => {
        searchValue = value;
        currentPage = 1;
        loadTableData(1);
    }, 400);

    // Event Listeners
    $("#searchInput").on("keyup", function () {
        searchHandler($(this).val());
    });

    $("#paginationLength").on("change", function () {
        pageLength = parseInt($(this).val(), 10);
        currentPage = 1;
        loadTableData(1);
    });

    $(document).on("click", ".pagination-btn", function () {
        const pageNum = parseInt($(this).data("page"), 10);
        if (!Number.isNaN(pageNum)) loadTableData(pageNum);
    });

    $("#paginationPrev").on("click", () => {
        if (currentPage > 1) loadTableData(currentPage - 1);
    });

    $("#paginationNext").on("click", () => {
        const totalPages = Math.max(1, Math.ceil(filteredRecords / pageLength));
        if (currentPage < totalPages) loadTableData(currentPage + 1);
    });

    // Role change handlers
    $('#role_add').on('change', function() {
        toggleRegionField(this, '#regionFieldAdd');
    });

    $('#edit_role').on('change', function() {
        toggleRegionField(this, '#regionFieldEdit');
    });

    // Username validation (Add)
    const debouncedCheckAdd = debounce((username) => {
        checkUsername(username, '#formAddUser .username-feedback', '#submitBtnAdd');
    }, 500);

    $('#username_add').on('keyup', function() {
        const username = $(this).val();
        debouncedCheckAdd(username);
    });

    // Username validation (Edit)
    const debouncedCheckEdit = debounce((username) => {
        checkUsername(username, '#formEditUser .edit-username-feedback', '#submitBtnEdit', originalUsername);
    }, 500);

    $('#edit_username').on('keyup', function() {
        const username = $(this).val();
        debouncedCheckEdit(username);
    });

    // Submit form add
    $('#formAddUser').on('submit', function(e) {
        e.preventDefault();

        const form = this;
        const $form = $(form);
        const btn = $('#submitBtnAdd');

        if (!form.checkValidity()) {
            $form.addClass('was-validated');
            $form.find('.invalid-feedback').removeClass('hidden');
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        const formData = new FormData(form);
        formData.append(config.csrfName, config.csrfHash);

        $.ajax({
            url: $form.attr('action'),
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(res) {
                if (res.status === 'success') {
                    closeModal(document.getElementById("modalAdd"));
                    form.reset();
                    $form.removeClass('was-validated');
                    $('#regionFieldAdd').addClass('hidden');
                    loadTableData(currentPage);

                    if (swalLib?.fire) {
                        swalLib.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        alert(res.message);
                    }
                } else {
                    if (swalLib?.fire) {
                        swalLib.fire('Gagal!', res.message, 'error');
                    } else {
                        alert(res.message);
                    }
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                if (swalLib?.fire) {
                    swalLib.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                } else {
                    alert('Terjadi kesalahan sistem.');
                }
            },
            complete: function() {
                btn.prop('disabled', false).text('Simpan');
            }
        });
    });

    // Edit button handler
    $(document).on('click', '.btn_edit', function() {
        const d = $(this).data();
        const modal = $('#modalEdit');

        originalUsername = d.username;

        modal.find('form').attr('action', d.href);
        $('#edit_realname').val(d.realname);
        $('#edit_username').val(d.username);
        $('#edit_role').val(d.role).trigger('change');

        if (d.regions_patient) {
            const regions = String(d.regions_patient).split(',').map(Number);
            $('#edit_regions').val(regions).trigger('change');
        }

        // Reset feedback
        $('#formEditUser .edit-username-feedback').addClass('hidden');
        $('#submitBtnEdit').prop('disabled', false);

        openModal(document.getElementById("modalEdit"));
    });

    // Submit form edit
    $('#formEditUser').on('submit', function(e) {
        e.preventDefault();

        const form = this;
        const $form = $(form);
        const btn = $('#submitBtnEdit');

        if (!form.checkValidity()) {
            $form.addClass('was-validated');
            $form.find('.invalid-feedback').removeClass('hidden');
            return;
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        const formData = new FormData(form);
        formData.append(config.csrfName, config.csrfHash);
        formData.append('_method', 'PUT');

        $.ajax({
            url: $form.attr('action'),
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(res) {
                if (res.status === 'success') {
                    closeModal(document.getElementById("modalEdit"));
                    loadTableData(currentPage);

                    if (swalLib?.fire) {
                        swalLib.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        alert(res.message);
                    }
                } else {
                    if (swalLib?.fire) {
                        swalLib.fire('Gagal!', res.message, 'error');
                    } else {
                        alert(res.message);
                    }
                    btn.prop('disabled', false).text('Simpan Perubahan');
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                if (swalLib?.fire) {
                    swalLib.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                } else {
                    alert('Terjadi kesalahan sistem.');
                }
                btn.prop('disabled', false).text('Simpan Perubahan');
            }
        });
    });

    // Delete button handler
    $(document).on('click', '.btn_delete', function(e) {
        e.preventDefault();
        deleteUrl = $(this).data('href');
        if (!deleteUrl) {
            console.error("URL tidak ditemukan pada atribut data-href!");
            return;
        }
        openModal(document.getElementById("modalDelete"));
    });

    // Confirm delete
    $('#confirmDelete').on('click', function() {
        if (!deleteUrl) return;

        const btn = $(this);
        btn.prop('disabled', true).text('Menghapus...');

        const formData = new FormData();
        formData.append(config.csrfName, config.csrfHash);
        formData.append('_method', 'DELETE');

        $.ajax({
            url: deleteUrl,
            type: "POST",
            data: formData,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function(res) {
                if (res.status === 'success') {
                    closeModal(document.getElementById("modalDelete"));
                    deleteUrl = null;
                    loadTableData(currentPage);

                    if (swalLib?.fire) {
                        swalLib.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        alert(res.message);
                    }
                } else {
                    if (swalLib?.fire) {
                        swalLib.fire('Gagal!', res.message, 'error');
                    } else {
                        alert(res.message);
                    }
                }
            },
            error: function() {
                if (swalLib?.fire) {
                    swalLib.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                } else {
                    alert('Terjadi kesalahan sistem.');
                }
            },
            complete: function() {
                btn.prop('disabled', false).text('Ya, Hapus');
            }
        });
    });

    // Modal handlers
    document.addEventListener("click", (event) => {
        const openTrigger = event.target.closest("[data-modal-open]");
        if (openTrigger) {
            const targetId = openTrigger.getAttribute("data-modal-open");
            const modal = document.getElementById(targetId);
            openModal(modal);

            // Re-initialize Select2 when modal opens
            setTimeout(() => {
                if (targetId === 'modalAdd' || targetId === 'modalEdit') {
                    initSelect2();
                }
            }, 100);
            return;
        }

        const closeTrigger = event.target.closest("[data-modal-close]");
        if (closeTrigger) {
            closeModal(closeTrigger.closest(".modal-wrapper"));
            return;
        }

        if (event.target.classList && event.target.classList.contains("modal-wrapper")) {
            closeModal(event.target);
        }
    });

    // Reset form when modal add is closed
    $("#modalAdd").on("click", "[data-modal-close]", function() {
        $('#formAddUser')[0].reset();
        $('#formAddUser').removeClass('was-validated');
        $('#formAddUser .invalid-feedback').addClass('hidden');
        $('#formAddUser .username-feedback').addClass('hidden');
        $('#regionFieldAdd').addClass('hidden');
        $('#submitBtnAdd').prop('disabled', false);
    });

    // Reset form when modal edit is closed
    $("#modalEdit").on("click", "[data-modal-close]", function() {
        $('#formEditUser').removeClass('was-validated');
        $('#formEditUser .invalid-feedback').addClass('hidden');
        $('#formEditUser .edit-username-feedback').addClass('hidden');
    });

    // Initialize
    initSelect2();
    loadTableData(1);
};

// Initialize page
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupUsersPage);
} else {
    setupUsersPage();
}
