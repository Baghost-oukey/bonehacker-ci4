function initMonitoringBranchComponent() {
	const componentRoot = document.getElementById("monitoringBranch");
	if (!componentRoot || typeof window.$ === "undefined") {
		return;
	}

	const switchRegionUrl = componentRoot.dataset.switchRegionUrl;
	const csrfToken = $("meta[name='csrf-token']").attr("content");
	const csrfHeader = $("meta[name='csrf-header']").attr("content");

	if (!switchRegionUrl || !csrfToken || !csrfHeader) {
		return;
	}

	componentRoot.querySelectorAll(".btn-switch-region").forEach(function (button) {
		button.addEventListener("click", function (event) {
			event.preventDefault();
			event.stopPropagation();

			if ($.fn.niceScroll) {
				$(".dropdown-list-content").getNiceScroll().remove();
				$(".dropdown-list-content").css({
					"overflow-y": "auto",
					"overflow-x": "hidden",
				});
			}

			const $this = $(button);
			const id = $this.attr("data-id");
			const name = $this.attr("data-name");

			$this.css("opacity", "0.5").prop("disabled", true);

			$.ajax({
				url: switchRegionUrl,
				type: "POST",
				data: {
					region_id: id,
					region_name: name,
					[csrfHeader]: csrfToken,
				},
				dataType: "json",
				success: function (response) {
					if (response.status === "success") {
						window.location.reload();
					} else {
						alert("Gagal: " + response.message);
						$this.css("opacity", "1").prop("disabled", false);
					}
				},
				error: function () {
					window.location.reload();
				},
			});
		});
	});
}

document.addEventListener("DOMContentLoaded", initMonitoringBranchComponent);
