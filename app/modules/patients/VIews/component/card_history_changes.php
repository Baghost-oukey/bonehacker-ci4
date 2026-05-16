<!-- CARD: RIWAYAT PERUBAHAN DATA -->
<div class="rounded-lg border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100">
                    <i class="fas fa-history text-amber-600"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Riwayat Perubahan Data</h2>
                    <p class="text-sm text-slate-500">Timeline perubahan data pasien</p>
                </div>
            </div>
            <button type="button" id="refreshHistoryBtn" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <div class="p-6">
        <!-- Loading State -->
        <div id="historyLoading" class="flex items-center justify-center py-8">
            <div class="text-center">
                <i class="fas fa-spinner fa-spin text-3xl text-slate-400 mb-2"></i>
                <p class="text-sm text-slate-500">Memuat riwayat perubahan...</p>
            </div>
        </div>

        <!-- Empty State -->
        <div id="historyEmpty" class="hidden text-center py-8">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4">
                <i class="fas fa-inbox text-2xl text-slate-400"></i>
            </div>
            <p class="text-slate-600 font-medium">Belum ada riwayat perubahan</p>
            <p class="text-sm text-slate-500 mt-1">Data pasien belum pernah diubah</p>
        </div>

        <!-- History Timeline -->
        <div id="historyTimeline" class="hidden space-y-6">
            <!-- Timeline items will be inserted here by JavaScript -->
        </div>
    </div>
</div>

<script>
(function() {
    const patientId = '<?= esc($patient_id ?? "") ?>';
    const historyUrl = '<?= site_url("patient/get-history") ?>/' + patientId;

    const loadingEl = document.getElementById('historyLoading');
    const emptyEl = document.getElementById('historyEmpty');
    const timelineEl = document.getElementById('historyTimeline');
    const refreshBtn = document.getElementById('refreshHistoryBtn');

    function formatDate(dateString) {
        const date = new Date(dateString);
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            timeZone: 'Asia/Jakarta'
        };
        return date.toLocaleDateString('id-ID', options) + ' WIB';
    }

    function escapeHtml(text) {
        if (!text) return '-';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function renderHistory(data) {
        if (!data || data.length === 0) {
            loadingEl.classList.add('hidden');
            emptyEl.classList.remove('hidden');
            timelineEl.classList.add('hidden');
            return;
        }

        let html = '';
        data.forEach((session, index) => {
            const isLast = index === data.length - 1;
            
            html += `
                <div class="relative ${!isLast ? 'pb-6' : ''}">
                    ${!isLast ? '<div class="absolute left-5 top-10 -ml-px h-full w-0.5 bg-slate-200"></div>' : ''}
                    
                    <div class="relative flex items-start gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-teal-100 ring-8 ring-white">
                            <i class="fas fa-edit text-teal-600"></i>
                        </div>
                        
                        <div class="flex-1 rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <p class="font-semibold text-slate-900">
                                        <i class="fas fa-calendar-alt text-slate-400 mr-2"></i>
                                        ${formatDate(session.changed_at)}
                                    </p>
                                    <p class="text-sm text-slate-600 mt-1">
                                        <i class="fas fa-user text-slate-400 mr-2"></i>
                                        Oleh: <span class="font-medium">${escapeHtml(session.changed_by_name || 'System')}</span>
                                    </p>
                                </div>
                                <div class="text-right text-xs text-slate-500">
                                    ${session.ip_address ? `<p><i class="fas fa-network-wired mr-1"></i>${escapeHtml(session.ip_address)}</p>` : ''}
                                </div>
                            </div>
                            
                            <div class="space-y-2 border-t border-slate-200 pt-3">
            `;

            session.changes.forEach(change => {
                html += `
                    <div class="flex items-start gap-3 text-sm">
                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 min-w-[120px]">
                            ${escapeHtml(change.field_name)}
                        </span>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-slate-500 line-through">${escapeHtml(change.old_value)}</span>
                                <i class="fas fa-arrow-right text-slate-400 text-xs"></i>
                                <span class="text-slate-900 font-medium">${escapeHtml(change.new_value)}</span>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += `
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        timelineEl.innerHTML = html;
        loadingEl.classList.add('hidden');
        emptyEl.classList.add('hidden');
        timelineEl.classList.remove('hidden');
    }

    function loadHistory() {
        loadingEl.classList.remove('hidden');
        emptyEl.classList.add('hidden');
        timelineEl.classList.add('hidden');

        fetch(historyUrl)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    renderHistory(result.data);
                } else {
                    console.error('Failed to load history:', result.message);
                    loadingEl.classList.add('hidden');
                    emptyEl.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error loading history:', error);
                loadingEl.classList.add('hidden');
                emptyEl.classList.remove('hidden');
            });
    }

    // Load history on page load
    if (patientId) {
        loadHistory();
    }

    // Refresh button
    if (refreshBtn) {
        refreshBtn.addEventListener('click', loadHistory);
    }
})();
</script>
