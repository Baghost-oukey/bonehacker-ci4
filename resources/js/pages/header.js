function initHeaderPage() {
	const headerRoot = document.getElementById("appHeader");
	if (!headerRoot) {
		return;
	}

	// Bersihkan semua backdrop yang mungkin tersisa dari session sebelumnya
	const cleanupBackdrops = () => {
		// 1. SweetAlert
		if (window.Swal && Swal.isVisible()) Swal.close();
		$('.swal2-container').remove();
		$('.swal2-backdrop-show').remove();

		// 2. Select2
		$('.select2-container--open').removeClass('select2-container--open');
		$('.select2-dropdown').remove();

		// 3. Offcanvas backdrop (gaji)
		$('#offcanvasBackdrop').addClass('hidden');
		$('#offcanvasProses').addClass('translate-x-full');
		$('.offcanvas-backdrop').addClass('hidden');

		// 4. Semua modal dengan pola hidden/flex
		$('.modal-wrapper').removeClass('flex').addClass('hidden');

		// 5. Modal dengan pola opacity transition (users, whatsapp)
		$('.fixed.inset-0').not('#appHeader, #appSidebar, #sidebarBackdrop').each(function () {
			$(this).addClass('hidden').removeClass('flex');
			$(this).addClass('opacity-0').removeClass('opacity-100');
		});

		// 6. Modal kalender (tidak pakai modal-wrapper)
		$('#modalTambahLibur, #modalTambahRutin').addClass('hidden').removeClass('flex');

		// 7. body/html overflow yang mungkin terkunci (journal, dll)
		document.body.style.overflow = '';
		document.body.style.paddingRight = '';
		document.documentElement.style.overflow = '';
		document.body.classList.remove('overflow-hidden');
	};

	// Jalankan cleanup saat halaman dimuat
	cleanupBackdrops();

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

	// ---- CUSTOM REGION DROPDOWN ----
	const regionBtn     = document.getElementById('regionDropdownBtn');
	const regionMenu    = document.getElementById('regionDropdownMenu');
	const regionChevron = document.getElementById('regionDropdownChevron');
	const regionLabel   = document.getElementById('regionDropdownLabel');
	const regionSelect  = document.getElementById('globalRegionFilter');

	if (regionBtn && regionMenu) {
		// Toggle dropdown
		regionBtn.addEventListener('click', function (e) {
			e.stopPropagation();
			const isHidden = regionMenu.classList.contains('hidden');
			regionMenu.classList.toggle('hidden', !isHidden);
			regionChevron?.classList.toggle('rotate-180', isHidden);
		});

		// Close on outside click
		document.addEventListener('click', function (e) {
			if (!regionBtn.contains(e.target) && !regionMenu.contains(e.target)) {
				regionMenu.classList.add('hidden');
				regionChevron?.classList.remove('rotate-180');
				// Reset search
				if (regionSearchInput) {
					regionSearchInput.value = '';
					regionSearchInput.dispatchEvent(new Event('input'));
				}
			}
		});

		// Search filter
		const regionSearchInput = document.getElementById('regionSearchInput');
		const regionOptionsList = document.getElementById('regionOptionsList');
		const regionEmptyState  = document.getElementById('regionEmptyState');

		if (regionSearchInput) {
			regionSearchInput.addEventListener('input', function () {
				const keyword = this.value.toLowerCase().trim();
				let visibleCount = 0;

				regionOptionsList.querySelectorAll('.region-option').forEach(function (btn) {
					const label = btn.dataset.label?.toLowerCase() || '';
					const match = label.includes(keyword);
					btn.style.display = match ? '' : 'none';
					if (match) visibleCount++;
				});

				// Divider visibility
				const divider = regionOptionsList.querySelector('.border-t');
				if (divider) divider.style.display = keyword ? 'none' : '';

				// Empty state
				if (regionEmptyState) {
					regionEmptyState.classList.toggle('hidden', visibleCount > 0);
				}
			});

			// Focus search when dropdown opens
			regionBtn.addEventListener('click', function () {
				setTimeout(() => regionSearchInput.focus(), 50);
			});
		}

		// Handle option click
		regionMenu.querySelectorAll('.region-option').forEach(function (btn) {
			btn.addEventListener('click', function () {
				const selectedId   = this.dataset.value;
				const selectedName = this.dataset.label;

				// Update label
				if (regionLabel) regionLabel.textContent = selectedName;

				// Update hidden select
				if (regionSelect) {
					regionSelect.value = selectedId;
				}

				// Close dropdown
				regionMenu.classList.add('hidden');
				regionChevron?.classList.remove('rotate-180');

				// Switch region via AJAX
				const csrfName = document.querySelector('meta[name="csrf-token-name"]')?.getAttribute('content') || 'csrf_test_name';
				const csrfHash = document.querySelector('meta[name="csrf-token-hash"]')?.getAttribute('content')
					|| document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

				Swal.fire({
					title: 'Mengganti Wilayah...',
					allowOutsideClick: false,
					showConfirmButton: false,
					didOpen: () => Swal.showLoading()
				});

				const data = { region_id: selectedId, region_name: selectedName };
				data[csrfName] = csrfHash;

				$.ajax({
					url: '/auth/switch_region',
					type: 'POST',
					data: data,
					success: function (response) {
						if (response.status === 'success') {
							window.location.reload();
						} else {
							Swal.fire('Error', response.message || 'Gagal mengganti wilayah', 'error');
						}
					},
					error: function (xhr) {
						Swal.close();
						const msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem';
						Swal.fire('Error', msg, 'error');
					},
					complete: function () {
						setTimeout(() => { if (Swal.isVisible()) Swal.close(); }, 500);
					}
				});
			});
		});
	}
}

document.addEventListener("DOMContentLoaded", initHeaderPage);

// Global beforeunload cleanup — jalankan sebelum navigasi ke halaman lain
// Menangani semua pola modal di seluruh aplikasi
window.addEventListener('beforeunload', () => {
	// Semua modal
	document.querySelectorAll('.modal-wrapper').forEach(el => {
		el.classList.remove('flex');
		el.classList.add('hidden');
	});

	// Offcanvas gaji
	const offcanvasBackdrop = document.getElementById('offcanvasBackdrop');
	const offcanvasProses   = document.getElementById('offcanvasProses');
	if (offcanvasBackdrop) offcanvasBackdrop.classList.add('hidden');
	if (offcanvasProses)   offcanvasProses.classList.add('translate-x-full');

	// Modal opacity transition (whatsapp, users)
	document.querySelectorAll('.fixed.inset-0').forEach(el => {
		if (el.id === 'appSidebar' || el.id === 'sidebarBackdrop') return;
		el.classList.add('hidden', 'opacity-0');
		el.classList.remove('flex', 'opacity-100');
	});

	// Body overflow
	document.body.style.overflow = '';
	document.body.classList.remove('overflow-hidden');
});
