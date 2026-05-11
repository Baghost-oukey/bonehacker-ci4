<div class="inline-flex items-center gap-2 text-sm text-slate-600">

    <span id="clock-day" class="font-medium">
        Senin, 01 Januari 2026
    </span>

    <span class="text-slate-300">•</span>

    <span id="clock-time" class="font-semibold tabular-nums text-slate-800">
        00:00:00
    </span>

</div>

<script>
    // Update clock every second
    function updateClock() {
        const now = new Date();
        
        // Format day and date
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        
        const dayName = days[now.getDay()];
        const date = now.getDate();
        const month = months[now.getMonth()];
        const year = now.getFullYear();
        
        const dayElement = document.getElementById('clock-day');
        if (dayElement) {
            dayElement.textContent = `${dayName}, ${date} ${month} ${year}`;
        }
        
        // Format time
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        const timeElement = document.getElementById('clock-time');
        if (timeElement) {
            timeElement.textContent = `${hours}:${minutes}:${seconds}`;
        }
    }
    
    // Update immediately and then every second
    updateClock();
    setInterval(updateClock, 1000);
</script>
