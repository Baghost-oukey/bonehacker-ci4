/**
 * Jasa Pelayanan - Daftar Pasien (Mirip Rekam Medis)
 * Custom pagination, Search, dan navigasi ke detail pasien
 */

const setupJasaPelayananPage = () => {
    const config = window.jasaPelayananConfig;
    const page = document.getElementById("jasaPelayananPage");

    // Cegah script berjalan di halaman lain
    if (!config || !page) return;

    // ==========================================
    // STATE
    // ==========================================
    let currentPage = 1;
    let pageLength = parseInt(document.getElementById("paginationLength")?.value || 25);
    let searchQuery = "";
    let totalRecords = 0;
    let totalFiltered = 0;

    // ==========================================
    // DOM ELEMENTS
    // ==========================================
    const tbody = document.querySelector("#table-JasaPelayanan tbody");
    const searchInput = document.getElementById("searchInput");
    const paginationInfo = document.getElementById("paginationInfo");
    const paginationNumbers = document.getElementById("paginationNumbers");
    const paginationPrev = document.getElementById("paginationPrev");
    const paginationNext = document.getElementById("paginationNext");
    const paginationLength = document.getElementById("paginationLength");

    // ==========================================
    // DEBOUNCE
    // ==========================================
    const debounce = (fn, delay = 400) => {
        let timerId;
        return (...args) => {
            clearTimeout(timerId);
            timerId = setTimeout(() => fn(...args), delay);
        };
    };

    // ==========================================
    // FETCH DATA
    // ==========================================
    const fetchData = () => {
        const start = (currentPage - 1) * pageLength;

        // Loading state
        tbody.innerHTML = `
            <tr class="hover:bg-slate-50 transition">
                <td colspan="7" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                    <i class="fas fa-spinner fa-spin mr-2 text-slate-300"></i>
                    Memuat data pasien...
                </td>
            </tr>`;

        const formData = new FormData();
        formData.append(config.csrfName, config.csrfHash);
        formData.append("start", start);
        formData.append("length", pageLength);
        formData.append("search", searchQuery);
        formData.append("kategori", config.kategori);
        formData.append("draw", currentPage);

        fetch(config.fetchUrl, {
            method: "POST",
            body: formData,
        })
            .then((res) => res.json())
            .then((json) => {
                // Auto-renew CSRF token
                if (json.csrfHash) {
                    config.csrfHash = json.csrfHash;
                }

                totalRecords = json.recordsTotal || 0;
                totalFiltered = json.recordsFiltered || 0;

                renderTable(json.data || []);
                renderPagination();
            })
            .catch((err) => {
                console.error("Fetch error:", err);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-red-400 italic text-sm">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Gagal memuat data. Silakan refresh halaman.
                        </td>
                    </tr>`;
            });
    };

    // ==========================================
    // RENDER TABLE
    // ==========================================
    const renderTable = (data) => {
        if (!data || data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-400 italic text-sm">
                        <i class="fas fa-inbox mr-2 text-slate-300"></i>
                        Tidak ada data pasien ditemukan
                    </td>
                </tr>`;
            return;
        }

        let html = "";
        data.forEach((row) => {
            html += `
                <tr class="hover:bg-slate-50/50 transition-colors group">
                    <td class="px-6 py-3.5 text-left">
                        <span class="inline-flex items-center justify-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">
                            ${row.id}
                        </span>
                    </td>
                    <td class="px-6 py-3.5 text-left font-medium text-slate-800">${row.name}</td>
                    <td class="px-6 py-3.5 text-left">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-medium text-teal-700">
                            <i class="fas fa-map-marker-alt text-teal-400 text-[10px]"></i>
                            ${row.name_region}
                        </span>
                    </td>
                    <td class="px-6 py-3.5 text-left text-slate-500 max-w-[200px] truncate" title="${row.address}">${row.address}</td>
                    <td class="px-6 py-3.5 text-center">
                        <span class="text-xs font-medium text-slate-600">${row.date}</span>
                    </td>
                    <td class="px-6 py-3.5 text-center">
                        <span class="inline-flex items-center justify-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-bold text-indigo-600 min-w-[28px]">
                            ${row.visit_count}
                        </span>
                    </td>
                    <td class="px-6 py-3.5 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="${config.showBaseUrl}/${row.id}"
                                title="Detail Pasien"
                                class="group/btn flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm transition-all hover:border-teal-200 hover:bg-teal-50 hover:text-teal-600">
                                <i class="fas fa-eye text-xs transition-transform group-hover/btn:scale-110"></i>
                            </a>
                        </div>
                    </td>
                </tr>`;
        });

        tbody.innerHTML = html;
    };

    // ==========================================
    // RENDER PAGINATION
    // ==========================================
    const renderPagination = () => {
        const total = totalFiltered;
        const totalPages = Math.ceil(total / pageLength);
        const start = (currentPage - 1) * pageLength + 1;
        const end = Math.min(currentPage * pageLength, total);

        // Info text
        if (total === 0) {
            paginationInfo.textContent = "Menampilkan 0 sampai 0 dari 0 data";
        } else {
            paginationInfo.textContent = `Menampilkan ${start} sampai ${end} dari ${total} data`;
        }

        // Prev/Next buttons
        paginationPrev.disabled = currentPage <= 1;
        paginationNext.disabled = currentPage >= totalPages;

        // Page numbers
        paginationNumbers.innerHTML = "";
        const maxVisible = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);

        if (endPage - startPage + 1 < maxVisible) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }

        for (let i = startPage; i <= endPage; i++) {
            const btn = document.createElement("button");
            btn.textContent = i;
            btn.className =
                i === currentPage
                    ? "inline-flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-xs font-bold text-white shadow-sm"
                    : "inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100";
            btn.addEventListener("click", () => {
                currentPage = i;
                fetchData();
            });
            paginationNumbers.appendChild(btn);
        }
    };

    // ==========================================
    // EVENT LISTENERS
    // ==========================================
    if (searchInput) {
        searchInput.addEventListener(
            "input",
            debounce((e) => {
                searchQuery = e.target.value.trim();
                currentPage = 1;
                fetchData();
            }, 400)
        );
    }

    if (paginationPrev) {
        paginationPrev.addEventListener("click", () => {
            if (currentPage > 1) {
                currentPage--;
                fetchData();
            }
        });
    }

    if (paginationNext) {
        paginationNext.addEventListener("click", () => {
            const totalPages = Math.ceil(totalFiltered / pageLength);
            if (currentPage < totalPages) {
                currentPage++;
                fetchData();
            }
        });
    }

    if (paginationLength) {
        paginationLength.addEventListener("change", (e) => {
            pageLength = parseInt(e.target.value);
            currentPage = 1;
            fetchData();
        });
    }

    // ==========================================
    // INITIAL LOAD
    // ==========================================
    fetchData();
};

document.addEventListener("DOMContentLoaded", setupJasaPelayananPage);