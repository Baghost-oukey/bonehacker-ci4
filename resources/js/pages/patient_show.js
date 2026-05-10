const PatientShowPage = {
  init() {
    // Tab navigation removed - all content now displayed in single page
    // No initialization needed
  },
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => PatientShowPage.init());
} else {
  PatientShowPage.init();
}
