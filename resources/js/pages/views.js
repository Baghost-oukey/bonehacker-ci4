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
    if (!modal) {
        return;
    }

    modal.classList.remove(MODAL_HIDDEN_CLASS);
    modal.classList.add(MODAL_VISIBLE_CLASS);
};

const closeModal = (modal) => {
    if (!modal) {
        return;
    }

    modal.classList.remove(MODAL_VISIBLE_CLASS);
    modal.classList.add(MODAL_HIDDEN_CLASS);
};

const setupAntreanPage = () => {
    const config = window.antreanConfig;
    const page = document.getElementById("antreanPage");

    if (!config || !page || typeof window.$ === "undefined") {
        return;
    }

    const $ = window.$;
    let searchValue = "";
    let deleteId = null;

    $.ajaxSetup({ data: getCsrfPayload(config) });

    let currentPage = 1;
    let pageLength = 25;
    let totalRecords = 0;
    let filteredRecords = 0;

    const loadTableData = (page = 1) => {
        $.ajax({
            url: config.fetchUrl,
            type: "POST",
            data: {
                ...getCsrfPayload(config),
                draw: 1,
                start: (page - 1) * pageLength,
                length: pageLength,
                region: $("#region_id").val() || "",
                start_date: $("#startDate").val(),
                end_date: $("#endDate").val(),
                search: { value: searchValue },
            },
            success: (response) => {
                currentPage = page;
                totalRecords = response.recordsTotal;
                filteredRecords = response.recordsFiltered;

                const tbody = $("#table-1 tbody");
                tbody.empty();

                if (response.data && response.data.length > 0) {
                    response.data.forEach((row) => {
                        const tr = $(`<tr class="hover:bg-slate-50 transition border-b border-slate-100">`)
                            .append(`<td class="px-6 py-3.5 text-center">${row.patient_id}</td>`)
                            .append(`<td class="px-6 py-3.5">${row.date}</td>`)
                            .append(`<td class="px-6 py-3.5">${row.name}</td>`)
                            .append(`<td class="px-6 py-3.5">${row.age}</td>`)
                            .append(`<td class="px-6 py-3.5">${row.address}</td>`)
                            .append(`<td class="px-6 py-3.5">${row.phone}</td>`)
                            .append(`<td class="px-6 py-3.5 text-center">${row.description}</td>`)
                            .append(`<td class="px-6 py-3.5 text-right">${row.action}</td>`);
                        tbody.append(tr);
                    });
                } else {
                    tbody.html(
                        '<tr class="hover:bg-slate-50 transition"><td colspan="8" class="px-6 py-12 text-center text-slate-400 italic text-sm"><i class="fas fa-inbox mr-2 text-slate-300"></i>Antrean saat ini masih kosong</td></tr>'
                    );
                }

                updatePaginationUI();
                updatePaginationInfo();
            },
        });
    };

    const updatePaginationUI = () => {
        const totalPages = Math.ceil(filteredRecords / pageLength);
        const container = $("#paginationNumbers");
        container.empty();

        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            container.append(
                `<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400" data-page="1">1</button>`
            );
            if (startPage > 2) {
                container.append(`<span class="px-1 text-slate-300">...</span>`);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === currentPage;
            container.append(
                `<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg ${
                    isActive
                        ? "bg-teal-600 border-teal-600 text-white font-semibold shadow-md shadow-teal-600/30"
                        : "border border-slate-300 bg-white text-slate-700 font-semibold transition hover:bg-slate-100 hover:border-slate-400"
                } text-xs" data-page="${i}">${i}</button>`
            );
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                container.append(`<span class="px-1 text-slate-300">...</span>`);
            }
            container.append(
                `<button class="pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400" data-page="${totalPages}">${totalPages}</button>`
            );
        }

        $("#paginationPrev").prop("disabled", currentPage === 1);
        $("#paginationNext").prop("disabled", currentPage === totalPages);
    };

    const updatePaginationInfo = () => {
        const start = (currentPage - 1) * pageLength + 1;
        const end = Math.min(currentPage * pageLength, filteredRecords);
        const info = `Menampilkan ${start} sampai ${end} dari ${filteredRecords} data`;
        $("#paginationInfo").text(info);
    };

    loadTableData(1);

    // Event: Pagination length change
    $("#paginationLength").on("change", function () {
        pageLength = parseInt($(this).val());
        currentPage = 1;
        loadTableData(1);
    });

    // Event: Pagination button clicks
    $(document).on("click", ".pagination-btn", function () {
        const page = parseInt($(this).data("page"));
        loadTableData(page);
    });

    // Event: Previous/Next buttons
    $("#paginationPrev").on("click", function () {
        if (currentPage > 1) {
            loadTableData(currentPage - 1);
        }
    });

    $("#paginationNext").on("click", function () {
        const totalPages = Math.ceil(filteredRecords / pageLength);
        if (currentPage < totalPages) {
            loadTableData(currentPage + 1);
        }
    });

    // Event: Search with debounce
    const onSearch = debounce((value) => {
        searchValue = value;
        currentPage = 1;
        loadTableData(1);
    }, 400);

    $("#searchInput").on("keyup", function () {
        onSearch($(this).val());
    });

    // Event: Filter changes
    $("#startDate, #endDate, #region_id").on("change", function () {
        currentPage = 1;
        loadTableData(1);
    });

    $("#btnPdf").on("click", function () {
        const region = $("#region_id").val() || "";
        window.open(`${config.pdfUrl}?region_id=${region}`, "_blank");
    });

    $("#btnExcel").on("click", function () {
        const region = $("#region_id").val() || "";
        window.location.href = `${config.excelUrl}?region_id=${region}`;
    });

    window.destroy = function (id) {
        deleteId = id;
        openModal(document.getElementById("modalDelete"));
    };

    $("#confirmDelete").on("click", function () {
        if (!deleteId) {
            return;
        }

        $.post(`${config.deleteBaseUrl}/${deleteId}`, getCsrfPayload(config))
            .done(() => window.location.reload())
            .fail(() => window.alert("Gagal menghapus data"));
    });

    document.addEventListener("click", (event) => {
        const openTrigger = event.target.closest("[data-modal-open]");
        if (openTrigger) {
            const targetId = openTrigger.getAttribute("data-modal-open");
            openModal(document.getElementById(targetId));
            return;
        }

        const closeTrigger = event.target.closest("[data-modal-close]");
        if (closeTrigger) {
            closeModal(closeTrigger.closest(".modal-wrapper"));
            return;
        }

        const clickedOverlay = event.target.classList.contains("modal-wrapper");
        if (clickedOverlay) {
            closeModal(event.target);
        }
    });

    const toggleSuspectiveNote = () => {
        const isSuspective = document.getElementById("isSuspectiveCheckbox");
        const noteField = document.getElementById("keterangan_rentan");

        if (!isSuspective || !noteField) {
            return;
        }

        if (isSuspective.checked) {
            noteField.classList.remove("hidden");
        } else {
            noteField.classList.add("hidden");
        }
    };

    const toggleCountryField = () => {
        const domesticType = document.querySelector('input[name="domestic"]:checked');
        const countryGroup = document.getElementById("country-group");

        if (!domesticType || !countryGroup) {
            return;
        }

        if (domesticType.value === "luar_negeri") {
            countryGroup.classList.remove("hidden");
        } else {
            countryGroup.classList.add("hidden");
        }
    };

    $("#isSuspectiveCheckbox").on("change", toggleSuspectiveNote);
    $('input[name="domestic"]').on("change", toggleCountryField);

    toggleSuspectiveNote();
    toggleCountryField();
};

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupAntreanPage);
} else {
    setupAntreanPage();
}
