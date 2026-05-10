function initHeaderPage() {
	const headerRoot = document.getElementById("appHeader");
	if (!headerRoot) {
		return;
	}

	const sidebar = document.getElementById("appSidebar");
	const sidebarBackdrop = document.getElementById("sidebarBackdrop");

	function closeSidebarMobile() {
		if (!sidebar) return;
		sidebar.classList.add("-translate-x-full");
		sidebar.classList.remove("translate-x-0");
		sidebarBackdrop?.classList.add("hidden");
	}

	function openSidebarMobile() {
		if (!sidebar) return;
		sidebar.classList.remove("-translate-x-full");
		sidebar.classList.add("translate-x-0");
		sidebarBackdrop?.classList.remove("hidden");
	}

	function toggleSidebarMobile() {
		if (!sidebar || window.innerWidth >= 1024) return;
		const isHidden = sidebar.classList.contains("-translate-x-full");
		if (isHidden) {
			openSidebarMobile();
		} else {
			closeSidebarMobile();
		}
	}

	function closeAllMenus() {
		document.querySelectorAll("[data-menu-toggle]").forEach(function (button) {
			const target = document.getElementById(button.getAttribute("data-menu-toggle"));
			if (target) {
				target.classList.add("hidden");
			}
		});
	}

	document.querySelectorAll("[data-menu-toggle]").forEach(function (button) {
		button.addEventListener("click", function (event) {
			event.stopPropagation();
			const target = document.getElementById(button.getAttribute("data-menu-toggle"));
			const isHidden = target.classList.contains("hidden");
			closeAllMenus();
			if (isHidden) {
				target.classList.remove("hidden");
			}
		});
	});

	document
		.getElementById("mobileSidebarToggle")
		?.addEventListener("click", toggleSidebarMobile);
	sidebarBackdrop?.addEventListener("click", closeSidebarMobile);

	window.addEventListener("resize", function () {
		if (window.innerWidth >= 1024) {
			sidebarBackdrop?.classList.add("hidden");
		}
	});

	document.addEventListener("click", function (event) {
		if (
			!event.target.closest("[data-menu-toggle]") &&
			!event.target.closest("#regionMenu") &&
			!event.target.closest("#userMenu")
		) {
			closeAllMenus();
		}
	});

	document.addEventListener("keydown", function (event) {
		if (event.key === "Escape") {
			closeAllMenus();
		}
	});

	// Initialize Select2 for Global Region Filter
	if ($('#globalRegionFilter').length) {
		$('#globalRegionFilter').select2({
			theme: "classic",
			width: '100%'
		}).on('change', function() {
			const selectedId = $(this).val();
			const selectedName = $(this).find('option:selected').text().trim();
			const csrfName = $('meta[name="csrf-token-name"]').attr('content') || 'csrf_test_name';
			const csrfHash = $('meta[name="csrf-token-hash"]').attr('content') || $('meta[name="csrf-token"]').attr('content');

			Swal.fire({
				title: 'Mengganti Wilayah...',
				allowOutsideClick: false,
				showConfirmButton: false,
				didOpen: () => { Swal.showLoading(); }
			});

			let data = {
				region_id: selectedId,
				region_name: selectedName
			};
			data[csrfName] = csrfHash;

			$.ajax({
				url: '/auth/switch_region', // Adjust base_url if necessary, but /auth/switch_region usually works if app is at root
				type: 'POST',
				data: data,
				success: function(response) {
					if (response.status === 'success') {
						window.location.reload();
					} else {
						Swal.close(); // Tutup loading sebelum menampilkan error
						Swal.fire('Error', response.message || 'Gagal mengganti wilayah', 'error');
					}
				},
				error: function(xhr) {
					Swal.close(); // Tutup loading sebelum menampilkan error
					let msg = 'Terjadi kesalahan sistem';
					if (xhr.responseJSON && xhr.responseJSON.message) {
						msg = xhr.responseJSON.message;
					}
					Swal.fire('Error', msg, 'error');
					// Revert selection on error
					$('#globalRegionFilter').val($('#activeRegion').val() || 'all').trigger('change.select2');
				},
				complete: function() {
					// Pastikan loading tertutup dalam kondisi apapun
					setTimeout(function() {
						if (Swal.isVisible()) {
							Swal.close();
						}
					}, 500);
				}
			});
		});
	}
}

document.addEventListener("DOMContentLoaded", initHeaderPage);
