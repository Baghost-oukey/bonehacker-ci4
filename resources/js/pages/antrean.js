// document.addEventListener("DOMContentLoaded", () => {
//   setupAntreanPage();
// });

// const setupAntreanPage = () => {
//   const config = window.antreanConfig;
//   const page = document.getElementById("antreanPage");

//   if (!config || !page) {
//     return;
//   }

//   let currentPage = 1;
//   let pageLength = 25;
//   let totalRecords = 0;
//   let filteredRecords = 0;
//   let searchValue = "";
//   let deleteId = null;
//   let startDate = null;
//   let endDate = null;

//   // Initialize elements
//   const searchInput = document.getElementById("searchInput");
//   const paginationLength = document.getElementById("paginationLength");
//   const paginationPrev = document.getElementById("paginationPrev");
//   const paginationNext = document.getElementById("paginationNext");
//   const paginationNumbers = document.getElementById("paginationNumbers");
//   const paginationInfo = document.getElementById("paginationInfo");
//   const startDateInput = document.getElementById("startDate");
//   const endDateInput = document.getElementById("endDate");
//   const btnPdf = document.getElementById("btnPdf");
//   const btnExcel = document.getElementById("btnExcel");

//   // Load initial data
//   loadTableData(1);

//   // Event listeners
//   if (searchInput) {
//     let searchTimeout;
//     searchInput.addEventListener("input", (e) => {
//       clearTimeout(searchTimeout);
//       searchValue = e.target.value;
//       searchTimeout = setTimeout(() => {
//         currentPage = 1;
//         loadTableData(1);
//       }, 400);
//     });
//   }

//   if (paginationLength) {
//     paginationLength.addEventListener("change", (e) => {
//       pageLength = parseInt(e.target.value);
//       currentPage = 1;
//       loadTableData(1);
//     });
//   }

//   if (paginationPrev) {
//     paginationPrev.addEventListener("click", () => {
//       if (currentPage > 1) {
//         currentPage -= 1;
//         loadTableData(currentPage);
//       }
//     });
//   }

//   if (paginationNext) {
//     paginationNext.addEventListener("click", () => {
//       const totalPages = Math.ceil(filteredRecords / pageLength);
//       if (currentPage < totalPages) {
//         currentPage += 1;
//         loadTableData(currentPage);
//       }
//     });
//   }

//   // Pagination number buttons (event delegation)
//   if (paginationNumbers) {
//     paginationNumbers.addEventListener("click", (e) => {
//       if (e.target.classList.contains("pagination-btn")) {
//         const pageNum = parseInt(e.target.dataset.page);
//         if (pageNum) {
//           currentPage = pageNum;
//           loadTableData(pageNum);
//         }
//       }
//     });
//   }

//   // Date range filtering
//   if (startDateInput && endDateInput) {
//     const handleDateChange = () => {
//       startDate = startDateInput.value;
//       endDate = endDateInput.value;
//       currentPage = 1;
//       loadTableData(1);
//     };

//     startDateInput.addEventListener("change", handleDateChange);
//     endDateInput.addEventListener("change", handleDateChange);
//   }

//   // Export buttons
//   if (btnPdf) {
//     btnPdf.addEventListener("click", () => {
//       exportData("pdf");
//     });
//   }

//   if (btnExcel) {
//     btnExcel.addEventListener("click", () => {
//       exportData("excel");
//     });
//   }

//   // Helper functions
//   const updateCsrf = (newToken) => {
//     config.csrfHash = newToken;
//     $("meta[name='csrf-token']").attr("content", newToken);
//   };

//   const updatePaginationUI = () => {
//     const totalPages = Math.max(1, Math.ceil(filteredRecords / pageLength));
//     const container = document.getElementById("paginationNumbers");

//     if (!container) {
//       return;
//     }

//     container.innerHTML = "";

//     const startPage = Math.max(1, currentPage - 2);
//     const endPage = Math.min(totalPages, currentPage + 2);

//     if (startPage > 1) {
//       const btn1 = document.createElement("button");
//       btn1.className = "pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400";
//       btn1.dataset.page = "1";
//       btn1.textContent = "1";
//       container.appendChild(btn1);

//       if (startPage > 2) {
//         const span = document.createElement("span");
//         span.className = "px-1 text-slate-300";
//         span.textContent = "...";
//         container.appendChild(span);
//       }
//     }

//     for (let pageNum = startPage; pageNum <= endPage; pageNum += 1) {
//       const btn = document.createElement("button");
//       const isActive = pageNum === currentPage;
//       btn.className = `pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg text-xs ${isActive ? "bg-teal-600 border-teal-600 text-white font-semibold shadow-md shadow-teal-600/30" : "border border-slate-300 bg-white text-slate-700 font-semibold transition hover:bg-slate-100 hover:border-slate-400"}`;
//       btn.dataset.page = pageNum;
//       btn.textContent = pageNum;
//       container.appendChild(btn);
//     }

//     if (endPage < totalPages) {
//       if (endPage < totalPages - 1) {
//         const span = document.createElement("span");
//         span.className = "px-1 text-slate-300";
//         span.textContent = "...";
//         container.appendChild(span);
//       }

//       const btnLast = document.createElement("button");
//       btnLast.className = "pagination-btn inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400";
//       btnLast.dataset.page = totalPages;
//       btnLast.textContent = totalPages;
//       container.appendChild(btnLast);
//     }

//     if (paginationPrev) {
//       paginationPrev.disabled = currentPage <= 1;
//     }

//     if (paginationNext) {
//       paginationNext.disabled = currentPage >= totalPages;
//     }
//   };

//   const updatePaginationInfo = () => {
//     if (!paginationInfo) {
//       return;
//     }

//     const totalPages = Math.max(1, Math.ceil(filteredRecords / pageLength));
//     const startRecord = (currentPage - 1) * pageLength + 1;
//     const endRecord = Math.min(currentPage * pageLength, filteredRecords);
//     const displayStart = filteredRecords === 0 ? 0 : startRecord;
//     const displayEnd = filteredRecords === 0 ? 0 : endRecord;

//     paginationInfo.textContent = `Menampilkan ${displayStart} sampai ${displayEnd} dari ${filteredRecords} data`;
//   };

//   const renderEmptyState = (message) => {
//     const tbody = document.querySelector("#table-queue tbody");
//     if (tbody) {
//       tbody.innerHTML = `<tr class="hover:bg-slate-50 transition"><td colspan="8" class="px-6 py-12 text-center text-slate-400 italic text-sm"><i class="fas fa-inbox mr-2 text-slate-300"></i>${message}</td></tr>`;
//     }
//   };

//   const loadTableData = (pageNumber = 1) => {
//     $.ajax({
//       url: config.fetchUrl,
//       type: "POST",
//       headers: {
//         [config.csrfName]: config.csrfHash,
//       },
//       data: {
//         draw: 1,
//         start: (pageNumber - 1) * pageLength,
//         length: pageLength,
//         search: { value: searchValue },
//         startDate: startDate,
//         endDate: endDate,
//       },
//       timeout: 15000,
//       success: (response) => {
//         try {
//           if (response.new_token) {
//             updateCsrf(response.new_token);
//           }

//           currentPage = pageNumber;
//           totalRecords = Number(response.recordsTotal || 0);
//           filteredRecords = Number(response.recordsFiltered || totalRecords);

//           const tbody = document.querySelector("#table-queue tbody");
//           if (!tbody) {
//             console.error("Table tbody not found");
//             renderEmptyState("Terjadi kesalahan pada halaman");
//             updatePaginationInfo();
//             updatePaginationUI();
//             return;
//           }

//           tbody.innerHTML = "";

//           if (!response.data || response.data.length === 0) {
//             renderEmptyState("Data antrean belum tersedia");
//             updatePaginationInfo();
//             updatePaginationUI();
//             return;
//           }

//           response.data.forEach((row) => {
//             const tr = document.createElement("tr");
//             tr.className = "hover:bg-slate-50 transition border-b border-slate-100";

//             tr.innerHTML = `
//               <td class="px-6 py-3.5 text-center">${row.queue_id || row.id || "-"}</td>
//               <td class="px-6 py-3.5">${row.date || row.tanggal || "-"}</td>
//               <td class="px-6 py-3.5">${row.name || row.nama_pasien || "-"}</td>
//               <td class="px-6 py-3.5 text-center">${row.age || row.patient_age || "-"}</td>
//               <td class="px-6 py-3.5">${row.address || row.alamat || "-"}</td>
//               <td class="px-6 py-3.5">${row.phone || row.no_wa || "-"}</td>
//               <td class="px-6 py-3.5 text-center">
//                 <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800">
//                   ${row.description || "Antrean"}
//                 </span>
//               </td>
//               <td class="px-6 py-3.5 text-right">${row.action || "-"}</td>
//             `;

//             tbody.appendChild(tr);
//           });

//           updatePaginationInfo();
//           updatePaginationUI();
//         } catch (err) {
//           console.error("Error processing response:", err);
//           renderEmptyState("Terjadi kesalahan saat memproses data");
//           filteredRecords = 0;
//           updatePaginationInfo();
//           updatePaginationUI();
//         }
//       },
//       error: (xhr, status, error) => {
//         console.error("AJAX Error:", { status, error, xhr });
//         renderEmptyState("Data antrean belum tersedia");
//         filteredRecords = 0;
//         totalRecords = 0;
//         updatePaginationInfo();
//         updatePaginationUI();
//       },
//     });
//   };

//   const exportData = (format) => {
//     const url = format === "pdf" ? config.pdfUrl : config.excelUrl;
//     const params = new URLSearchParams({
//       search: searchValue,
//       startDate: startDate || "",
//       endDate: endDate || "",
//       format: format,
//     });

//     window.open(`${url}?${params.toString()}`, "_blank");
//   };

//   // Modal handlers
//   const setupModalHandlers = () => {
//     document.addEventListener("click", (e) => {
//       // Modal open
//       if (e.target.hasAttribute("data-modal-open")) {
//         const modalId = e.target.getAttribute("data-modal-open");
//         const modal = document.getElementById(modalId);
//         if (modal) {
//           modal.classList.remove("hidden");
//           modal.classList.add("flex");
//         }
//       }

//       // Modal close
//       if (e.target.hasAttribute("data-modal-close")) {
//         const modal = e.target.closest(".modal-wrapper");
//         if (modal) {
//           modal.classList.add("hidden");
//           modal.classList.remove("flex");
//         }
//       }

//       // Delete action
//       if (e.target.hasAttribute("data-delete")) {
//         deleteId = e.target.getAttribute("data-delete");
//         const modal = document.getElementById("modalDelete");
//         if (modal) {
//           modal.classList.remove("hidden");
//           modal.classList.add("flex");
//         }
//       }
//     });

//     // Close modal on background click
//     document.querySelectorAll(".modal-wrapper").forEach((modal) => {
//       modal.addEventListener("click", (e) => {
//         if (e.target === modal) {
//           modal.classList.add("hidden");
//           modal.classList.remove("flex");
//         }
//       });
//     });

//     // Confirm delete
//     const confirmDeleteBtn = document.getElementById("confirmDelete");
//     if (confirmDeleteBtn) {
//       confirmDeleteBtn.addEventListener("click", () => {
//         if (deleteId) {
//           $.ajax({
//             url: `${config.deleteBaseUrl}/${deleteId}`,
//             type: "POST",
//             headers: {
//               [config.csrfName]: config.csrfHash,
//             },
//             success: (response) => {
//               if (response.new_token) {
//                 updateCsrf(response.new_token);
//               }

//               Swal.fire({
//                 icon: "success",
//                 title: "Berhasil",
//                 text: response.message || "Data berhasil dihapus",
//                 timer: 1500,
//               }).then(() => {
//                 const modal = document.getElementById("modalDelete");
//                 if (modal) {
//                   modal.classList.add("hidden");
//                   modal.classList.remove("flex");
//                 }
//                 loadTableData(currentPage);
//               });
//             },
//             error: (xhr) => {
//               const response = xhr.responseJSON || {};
//               Swal.fire({
//                 icon: "error",
//                 title: "Gagal",
//                 text: response.message || "Gagal menghapus data",
//               });
//             },
//           });
//         }
//       });
//     }
//   };

//   setupModalHandlers();

//   // Global destroy function for cleanup
//   window.destroy = () => {
//     currentPage = 1;
//     pageLength = 25;
//     totalRecords = 0;
//     filteredRecords = 0;
//     searchValue = "";
//     deleteId = null;
//   };
// };
