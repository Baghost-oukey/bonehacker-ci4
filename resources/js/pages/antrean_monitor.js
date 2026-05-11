class AntreanMonitor {
    constructor() {
        this.pollInterval = 3000; // 3 seconds
        this.scrollInterval = 50; // 50 ms
        this.scrollSpeed = 1;
        this.pauseDuration = 2000; // 2 seconds

        this.init();
    }

    init() {
        // Only run on the monitor page
        if (!document.getElementById('stats-container') || !document.getElementById('queue-columns')) {
            return;
        }

        this.startRealtimePolling();
        this.startAutoScroll();
        this.startClock();
    }

    startRealtimePolling() {
        setInterval(() => this.fetchQueueData(), this.pollInterval);
    }

    async fetchQueueData() {
        try {
            const response = await fetch(window.location.href);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            const newStats = doc.getElementById('stats-container');
            const newColumns = doc.getElementById('queue-columns');
            
            if (newStats && newColumns) {
                document.getElementById('stats-container').innerHTML = newStats.innerHTML;
                document.getElementById('queue-columns').innerHTML = newColumns.innerHTML;
            }
        } catch (error) {
            console.error('Failed to fetch queue data:', error);
        }
    }

    startAutoScroll() {
        setInterval(() => {
            const scrollContainers = document.querySelectorAll('.auto-scroll-list');
            
            scrollContainers.forEach(container => {
                if (container.isPaused) return;

                const maxScroll = container.scrollHeight - container.clientHeight;
                if (maxScroll <= 0) return;
                
                container.scrollTop += this.scrollSpeed;

                if (container.scrollTop >= maxScroll - 1) { // Account for floating point issues
                    container.isPaused = true;
                    setTimeout(() => { 
                        if (container) { 
                            container.scrollTop = 0; 
                            container.isPaused = false; 
                        }
                    }, this.pauseDuration);
                }
            });
        }, this.scrollInterval);
    }

    startClock() {
        const timeEl = document.getElementById('liveTime');
        const dateEl = document.getElementById('liveDate');
        
        if (!timeEl || !dateEl) return;

        const updateTime = () => {
            const now = new Date();
            
            const timeString = now.toLocaleTimeString('id-ID', { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            }).replace(/\./g, ':');
            timeEl.textContent = timeString;
            
            const dateString = now.toLocaleDateString('id-ID', { 
                weekday: 'long', 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric' 
            });
            dateEl.textContent = dateString;
        };

        setInterval(updateTime, 1000);
        updateTime();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new AntreanMonitor();
});
