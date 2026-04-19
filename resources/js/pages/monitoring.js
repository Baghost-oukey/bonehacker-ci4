const setupMonitoringPage = () => {
    const page = document.getElementById("monitoringPage");

    if (!page) {
        return;
    }

    // ===== CLOCK UPDATE =====
    const updateClock = () => {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('realtimeClock').textContent = `${hours}:${minutes}:${seconds}`;
        
        applyAutoDarkMode(now);
    };

    setInterval(updateClock, 1000);
    updateClock();

    // ===== AUTO DARK MODE LOGIC =====
    const applyAutoDarkMode = (dateTime) => {
        const currentHour = dateTime.getHours();
        const isDarkModeManuallySet = localStorage.theme !== null && localStorage.theme !== "auto";
        
        if (!isDarkModeManuallySet) {
            if (currentHour >= 18 || currentHour < 6) {
                if (!document.documentElement.classList.contains("dark")) {
                    document.documentElement.classList.add("dark");
                }
            } else {
                if (document.documentElement.classList.contains("dark")) {
                    document.documentElement.classList.remove("dark");
                }
            }
        }
    };

    // ===== DARK MODE TOGGLE =====
    const toggleDarkMode = () => {
        const icon = document.getElementById("themeIcon");

        document.documentElement.classList.toggle("dark");

        if (document.documentElement.classList.contains("dark")) {
            icon.textContent = "🌙";
            localStorage.theme = "dark";
        } else {
            icon.textContent = "☀️";
            localStorage.theme = "light";
        }
    };

    document.getElementById("themeToggle")?.addEventListener("click", toggleDarkMode);

    if (localStorage.theme === "dark") {
        document.documentElement.classList.add("dark");
        document.getElementById("themeIcon").textContent = "🌙";
    } else if (localStorage.theme === "light") {
        document.documentElement.classList.remove("dark");
        document.getElementById("themeIcon").textContent = "☀️";
    } else {
        const now = new Date();
        applyAutoDarkMode(now);
        localStorage.theme = "auto";
    }
};

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupMonitoringPage);
} else {
    setupMonitoringPage();
}
