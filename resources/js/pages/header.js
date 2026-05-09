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
}

document.addEventListener("DOMContentLoaded", initHeaderPage);
