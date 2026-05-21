const MODAL_VISIBLE_CLASS = "flex";
const MODAL_HIDDEN_CLASS = "hidden";

const getCsrfPayload = (config) => ({
  [config.csrfName]: config.csrfHash,
});

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

// --- INIT SCRIPT ---
const setupKasPage = () => {
  // console.log("%c [JS Check] Kas Module Connected! 🚀", "color: teal; font-weight: bold; font-size: 12px;");

  const config = window.kasConfig;
  const page = document.getElementById("kasPage");
  if (!config || !page || typeof window.$ === "undefined") return;

  const $ = window.$;
  let tables = {};

  // --- COLUMN TABLE CONFIGURATION ---
  const commonColumns = [
    {
      data: "created_at",
      className: "whitespace-nowrap",
      render: (data) => `<span class="text-slate-500">${data}</span>`,
    },
    {
      data: "region_name",
      className: "hidden md:table-cell whitespace-nowrap",
      render: (data) => `<span class="font-bold text-slate-700">${data}</span>`,
    },
    {
      data: "keterangan",
      className: "min-w-[180px] md:min-w-[260px]",
      render: $.fn.dataTable.render.text(),
    },
    {
      data: "nama_pembuat",
      className: "hidden md:table-cell text-center whitespace-nowrap",
      render: (data) => {
        const namaUser = data ? data : "Sistem";
        return `<span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10">${namaUser}</span>`;
      },
    },
    {
      data: "nominal",
      className: "text-right font-medium whitespace-nowrap",
      render: (data, type, row) => {
        const formattedNominal = new Intl.NumberFormat("id-ID", {
          style: "currency",
          currency: "IDR",
          minimumFractionDigits: 0,
        }).format(Math.floor(Number(data)));

        const textColor =
          row.type === "expense" || row.kategori === "pengeluaran"
            ? "text-rose-600"
            : "text-emerald-600";

        return `<span class="${textColor}">${formattedNominal}</span>`;
      },
    },
    {
      data: null,
      orderable: false,
      className: "text-center whitespace-nowrap",
      render: function (data, type, row) {
        return `
                <div class="flex justify-center gap-2">
                    <button class="p-2 text-slate-400 hover:text-teal-500 hover:bg-rose-50 rounded-xl transition-all btn-delete" data-id="${row.id_transaksi}">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>`;
      },
    },
  ];

  // --- FILTER WILAYAH ---
  $("#filterRegionKas").on("change", function () {
    const regionId = $(this).val();
    $(this).prop("disabled", true);
    const csrfName = $("#csrfToken").attr("name");
    const csrfHash = $("#csrfToken").val();

    let ajaxData = {
      region_id: regionId,
    };
    ajaxData[csrfName] = csrfHash;

    // Buat efek loading
    $(this).prop("disabled", true);

    $.ajax({
      url: "/kas/set_filter_region",
      type: "POST",
      data: ajaxData,
      success: function (response) {
        if (response.status === "success") {
          window.location.reload();
        }
      },
      error: function () {
        alert("Gagal mengubah wilayah. Silakan coba lagi.");
        $("#filterRegionKas").prop("disabled", false);
      },
    });
  });

  // --- INIT DATATABLE FUNCTION (Enterprise Setup) ---
  const initDataTable = (id, url, columns) => {
    if (!$(id).length) return null;
    return $(id).DataTable({
      processing: true,
      serverSide: true,
      autoWidth: false,
      scrollX: true,
      scrollCollapse: true,
      deferRender: true,
      ajax: {
        url: url,
        type: "POST",
        data: (d) => ({ ...d, ...getCsrfPayload(config) }),
        dataSrc: (json) => {
          // Update CSRF Token untuk request selanjutnya
          if (json.csrfHash) config.csrfHash = json.csrfHash;
          // console.log(`[Data ${id}] Berhasil ditarik:`, json);
          return json.data || [];
        },
      },
      columns: columns,
      columnDefs: [
        { width: "16%", targets: 0 },
        { width: "44%", targets: 2 },
        { width: "14%", targets: 4 },
        { width: "10%", targets: 5 },
      ],
      order: [[0, "desc"]],
      language: {
        processing: `
                    <div class="absolute inset-0 z-20 flex items-center justify-center bg-white/70 backdrop-blur-[1px]">
                        <div class="flex items-center gap-3">
                            <div class="h-5 w-5 animate-spin rounded-full border-2 border-slate-200 border-t-teal-500"></div>
                            <span class="text-sm italic text-slate-400 font-medium">Memuat data...</span>
                        </div>
                    </div>
                `,
        paginate: {
          previous: '<i class="fas fa-chevron-left"></i>',
          next: '<i class="fas fa-chevron-right"></i>',
        },
        emptyTable: "Belum ada data transaksi untuk kategori ini.",
      },
    });
  };

  // --- JALANKAN INISIALISASI ---
  tables.pemasukan = initDataTable(
    "#table-pemasukan",
    config.urlPemasukan,
    commonColumns,
  );
  tables.pengeluaran = initDataTable(
    "#table-pengeluaran",
    config.urlPengeluaran,
    commonColumns,
  );
  tables.rutinan = initDataTable(
    "#table-pengeluaran-harian",
    config.urlPengeluaranHarian,
    commonColumns,
  ); // Pastikan ID tabel HTML-nya benar

  // --- KAS TAB SYSTEM (EVENT DRIVEN) ---
  const initTabSystem = (tables) => {
    const tabButtons = document.querySelectorAll("#kas-tabs .tab-btn");
    const tabPanes = document.querySelectorAll(".tab-pane");

    tabButtons.forEach((btn) => {
      btn.addEventListener("click", function () {
        const targetTab = this.getAttribute("data-tab");
        tabButtons.forEach((b) => {
          b.classList.remove("active", "text-teal-600", "border-teal-600");
          b.classList.add("text-slate-400", "border-transparent");
        });
        this.classList.add("active", "text-teal-600", "border-teal-600");
        this.classList.remove("text-slate-400", "border-transparent");
        tabPanes.forEach((pane) => {
          pane.classList.add("hidden");
          pane.classList.remove("block");
        });
        const activePane = document.getElementById(`tab-content-${targetTab}`);
        if (activePane) {
          activePane.classList.remove("hidden");
          activePane.classList.add("block");
        }
        if (tables && tables[targetTab]) {
          setTimeout(() => {
            tables[targetTab].columns.adjust().draw();
            tables[targetTab].ajax.reload(null, false);
          }, 100);
        }
      });
    });
  };
  initTabSystem(tables);

  // --- EVENT HANDLERS ---
  document.querySelectorAll("[data-modal-close]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const modal = btn.closest(".modal-wrapper");
      closeModal(modal);
    });
  });

  // $('.table').on('click', '.btn-delete', function() {
  //     const id = $(this).data('id');
  // });
};

document.addEventListener("DOMContentLoaded", setupKasPage);
