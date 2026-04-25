/**
 * System Logs Page Script
 * Handles auto-submit for date filtering
 */

const LogViewerPage = {
    init() {
        this.initDateAutoSubmit();
    },

    /**
     * Listen for date changes and automatically submit the form
     */
    initDateAutoSubmit() {
        const logDateInput = document.getElementById('log_date');
        const logSearchForm = document.getElementById('logSearchForm');

        if (logDateInput && logSearchForm) {
            logDateInput.addEventListener('change', () => {
                // Show a wait cursor to let user know it's fetching
                document.body.style.cursor = 'wait';
                logDateInput.style.opacity = '0.7'; // Slight visual feedback
                
                logSearchForm.submit();
            });
        }
    }
};

// Start application when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => LogViewerPage.init());
} else {
    LogViewerPage.init();
}