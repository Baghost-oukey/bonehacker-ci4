function initProfileComponent() {
	const profileRoot = document.getElementById("profileComponent");
	if (!profileRoot || typeof window.$ === "undefined") {
		return;
	}

	const editAccountUrl = profileRoot.dataset.editAccountUrl;
	const updateAccountUrl = profileRoot.dataset.updateAccountUrl;

	const accountModal = document.getElementById("accountModal");
	const accountAlert = document.getElementById("accountAlert");
	const editAccountBtn = document.getElementById("editAccountBtn");
	const closeAccountModal = document.getElementById("closeAccountModal");
	const cancelAccountModal = document.getElementById("cancelAccountModal");
	const currentUserNameEl = document.getElementById("currentUserName");
	const avatarFallbackEl = document.getElementById("profileAvatarFallback");
	const profileTriggerEl = profileRoot.querySelector("[data-menu-toggle='userMenu']");

	const avatarPaletteClasses = [
		["bg-rose-100", "text-rose-700", "ring-rose-200"],
		["bg-orange-100", "text-orange-700", "ring-orange-200"],
		["bg-amber-100", "text-amber-700", "ring-amber-200"],
		["bg-lime-100", "text-lime-700", "ring-lime-200"],
		["bg-emerald-100", "text-emerald-700", "ring-emerald-200"],
		["bg-cyan-100", "text-cyan-700", "ring-cyan-200"],
		["bg-sky-100", "text-sky-700", "ring-sky-200"],
		["bg-violet-100", "text-violet-700", "ring-violet-200"],
		["bg-fuchsia-100", "text-fuchsia-700", "ring-fuchsia-200"],
	];

	function applyAvatarPalette(initial) {
		if (!avatarFallbackEl) return;
		const code = initial.charCodeAt(0) || 85;
		const palette = avatarPaletteClasses[code % avatarPaletteClasses.length];

		avatarPaletteClasses.flat().forEach(function (cls) {
			avatarFallbackEl.classList.remove(cls);
		});

		palette.forEach(function (cls) {
			avatarFallbackEl.classList.add(cls);
		});
	}

	function updateProfileLabel(realname) {
		if (!realname) return;
		const trimmed = String(realname).trim();
		const initial = trimmed ? trimmed.charAt(0).toUpperCase() : "U";

		if (currentUserNameEl) {
			currentUserNameEl.textContent = trimmed || "User";
		}

		if (avatarFallbackEl) {
			avatarFallbackEl.textContent = initial;
			applyAvatarPalette(initial);
		}

		if (profileTriggerEl) {
			profileTriggerEl.setAttribute("title", trimmed || "User");
		}
	}

		if (avatarFallbackEl) {
			const initial = (avatarFallbackEl.textContent || "U").trim().charAt(0).toUpperCase() || "U";
			applyAvatarPalette(initial);
		}

	function openAccountModal() {
		if (!accountModal || !accountAlert) return;
		accountAlert.className = "mb-4 hidden rounded-xl px-4 py-3 text-sm font-medium";
		accountAlert.textContent = "";
		accountModal.classList.remove("hidden");
		accountModal.classList.add("flex");
	}

	function closeAccountModalFn() {
		if (!accountModal) return;
		accountModal.classList.add("hidden");
		accountModal.classList.remove("flex");
		const formEl = document.getElementById("editAccountForm");
		if (formEl) {
			formEl.reset();
		}
	}

	editAccountBtn?.addEventListener("click", function () {
		if (!editAccountUrl) return;
		openAccountModal();
		$.ajax({
			url: editAccountUrl,
			type: "GET",
			dataType: "json",
			success: function (response) {
				updateProfileLabel(response.realname);
				$("#realname").val(response.realname);
				$("#username").val(response.username);
				$("#user_id").val(response.userId);
			},
		});
	});

	closeAccountModal?.addEventListener("click", closeAccountModalFn);
	cancelAccountModal?.addEventListener("click", closeAccountModalFn);

	accountModal?.addEventListener("click", function (event) {
		if (event.target === accountModal) {
			closeAccountModalFn();
		}
	});

	$("#editAccountForm").on("submit", function (event) {
		event.preventDefault();
		if (!accountAlert || !updateAccountUrl) return;

		accountAlert.className = "mb-4 hidden rounded-xl px-4 py-3 text-sm font-medium";
		accountAlert.textContent = "";

		const formData = $(this).serialize();
		const csrfToken = $("meta[name='csrf-token']").attr("content");
		const csrfHeader = $("meta[name='csrf-header']").attr("content");
		const headers = {};

		if (csrfHeader && csrfToken) {
			headers[csrfHeader] = csrfToken;
		}

		$.ajax({
			url: updateAccountUrl,
			type: "POST",
			headers: headers,
			data: formData,
			dataType: "json",
			success: function (response) {
				if (response.status === "success") {
					accountAlert.className =
						"mb-4 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700";
					accountAlert.textContent = response.message;
					updateProfileLabel(response.realname);
					setTimeout(function () {
						closeAccountModalFn();
						location.reload();
					}, 800);
				} else {
					accountAlert.className =
						"mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700";
					accountAlert.textContent = response.message;
					accountAlert.classList.remove("hidden");
				}
			},
			error: function () {
				accountAlert.className =
					"mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700";
				accountAlert.textContent = "Gagal memperbarui akun.";
				accountAlert.classList.remove("hidden");
			},
		});
	});
}

document.addEventListener("DOMContentLoaded", initProfileComponent);
