<!-- CARD: RIWAYAT PERUBAHAN DATA (Collapsible) -->
<div class="rounded-lg border border-slate-200 bg-white shadow-sm">

    <!-- COLLAPSIBLE HEADER -->
    <div id="historyChangesHeader"
        class="cursor-pointer group flex items-center justify-between px-6 py-4 bg-slate-50 border-b border-slate-200 hover:bg-slate-100/60 transition-colors">
        
        <!-- Left: Icon + Title -->
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100">
                <i class="fas fa-history text-amber-600"></i>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Riwayat Perubahan Data</h2>
                <p class="text-sm text-slate-500">Timeline perubahan data pasien</p>
            </div>
        </div>

        <!-- Right: Refresh + Chevron -->
        <div class="flex items-center gap-2">
            <button type="button" id="refreshHistoryChangesBtn"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
            <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 group-hover:text-amber-600 group-hover:border-amber-100 transition-all shadow-sm">
                <i id="historyChangesChevron" class="fas fa-chevron-down transition-transform duration-300"></i>
            </div>
        </div>
    </div>

    <!-- COLLAPSIBLE CONTENT -->
    <div id="historyChangesContent"
        class="overflow-hidden transition-all duration-300 ease-in-out"
        style="max-height: 0px; opacity: 0;"
        data-state="collapsed">

        <div class="p-6">
            <!-- Loading State -->
            <div id="historyChangesLoading" class="flex items-center justify-center py-8">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-3xl text-slate-400 mb-2"></i>
                    <p class="text-sm text-slate-500">Memuat riwayat perubahan...</p>
                </div>
            </div>

            <!-- Empty State -->
            <div id="historyChangesEmpty" class="hidden text-center py-8">
                <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4">
                    <i class="fas fa-inbox text-2xl text-slate-400"></i>
                </div>
                <p class="text-slate-600 font-medium">Belum ada riwayat perubahan</p>
                <p class="text-sm text-slate-500 mt-1">Data pasien belum pernah diubah</p>
            </div>

            <!-- History Timeline -->
            <div id="historyChangesTimeline" class="hidden space-y-6">
                <!-- Timeline items will be inserted here by JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var patientId  = '<?= esc($patient_id ?? "") ?>';
    var historyUrl = '<?= site_url("patient/get-history") ?>/' + patientId;

    var headerEl   = document.getElementById('historyChangesHeader');
    var contentEl  = document.getElementById('historyChangesContent');
    var chevronEl  = document.getElementById('historyChangesChevron');
    var loadingEl  = document.getElementById('historyChangesLoading');
    var emptyEl    = document.getElementById('historyChangesEmpty');
    var timelineEl = document.getElementById('historyChangesTimeline');
    var refreshBtn = document.getElementById('historyChangesBtn');

    var dataLoaded = false; // Lazy load: only fetch once

    // ── Helpers ──────────────────────────────────────────
    function formatDate(dateString) {
        var date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            year: 'numeric', month: 'long', day: 'numeric',
            hour: '2-digit', minute: '2-digit', timeZone: 'Asia/Jakarta'
        }) + ' WIB';
    }

    function escapeHtml(text) {
        if (!text) return '-';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ── Render ────────────────────────────────────────────
    function renderHistory(data) {
        loadingEl.classList.add('hidden');
        if (!data || data.length === 0) {
            emptyEl.classList.remove('hidden');
            timelineEl.classList.add('hidden');
            return;
        }

        var html = '';
        data.forEach(function(session, index) {
            var isLast = (index === data.length - 1);
            html += '<div class="relative ' + (!isLast ? 'pb-6' : '') + '">';
            if (!isLast) html += '<div class="absolute left-5 top-10 -ml-px h-full w-0.5 bg-slate-200"></div>';
            html += '<div class="relative flex items-start gap-4">'
                +     '<div class="flex h-10 w-10 items-center justify-center rounded-full bg-teal-100 ring-8 ring-white">'
                +         '<i class="fas fa-edit text-teal-600"></i>'
                +     '</div>'
                +     '<div class="flex-1 rounded-lg border border-slate-200 bg-slate-50 p-4">'
                +         '<div class="flex items-start justify-between mb-3">'
                +             '<div>'
                +                 '<p class="font-semibold text-slate-900"><i class="fas fa-calendar-alt text-slate-400 mr-2"></i>' + formatDate(session.changed_at) + '</p>'
                +                 '<p class="text-sm text-slate-600 mt-1"><i class="fas fa-user text-slate-400 mr-2"></i>Oleh: <span class="font-medium">' + escapeHtml(session.changed_by_name || 'System') + '</span></p>'
                +             '</div>'
                +             '<div class="text-right text-xs text-slate-500">'
                +                 (session.ip_address ? '<p><i class="fas fa-network-wired mr-1"></i>' + escapeHtml(session.ip_address) + '</p>' : '')
                +             '</div>'
                +         '</div>'
                +         '<div class="space-y-2 border-t border-slate-200 pt-3">';

            session.changes.forEach(function(change) {
                html += '<div class="flex items-start gap-3 text-sm">'
                    +       '<span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 min-w-[120px]">' + escapeHtml(change.field_name) + '</span>'
                    +       '<div class="flex-1"><div class="flex items-center gap-2">'
                    +           '<span class="text-slate-500 line-through">' + escapeHtml(change.old_value) + '</span>'
                    +           '<i class="fas fa-arrow-right text-slate-400 text-xs"></i>'
                    +           '<span class="text-slate-900 font-medium">' + escapeHtml(change.new_value) + '</span>'
                    +       '</div></div></div>';
            });

            html += '</div></div></div></div>';
        });

        timelineEl.innerHTML = html;
        emptyEl.classList.add('hidden');
        timelineEl.classList.remove('hidden');

        // After data is rendered, recalculate maxHeight so content fits
        if (contentEl.getAttribute('data-state') === 'expanded') {
            contentEl.style.maxHeight = contentEl.scrollHeight + 'px';
            setTimeout(function() {
                contentEl.style.maxHeight = 'none';
                contentEl.style.overflow = 'visible';
            }, 350);
        }
    }

    // ── Fetch ─────────────────────────────────────────────
    function loadHistory() {
        if (!patientId) return;
        dataLoaded = false;
        loadingEl.classList.remove('hidden');
        emptyEl.classList.add('hidden');
        timelineEl.classList.add('hidden');

        fetch(historyUrl)
            .then(function(r) { return r.json(); })
            .then(function(result) {
                dataLoaded = true;
                if (result.success) {
                    renderHistory(result.data);
                } else {
                    loadingEl.classList.add('hidden');
                    emptyEl.classList.remove('hidden');
                }
            })
            .catch(function() {
                loadingEl.classList.add('hidden');
                emptyEl.classList.remove('hidden');
            });
    }

    // ── Collapse Toggle ───────────────────────────────────
    function toggle(forceExpand) {
        var state = contentEl.getAttribute('data-state') || 'collapsed';
        var shouldExpand = (forceExpand !== undefined) ? forceExpand : (state === 'collapsed');

        if (shouldExpand) {
            // Lazy load on first open
            if (!dataLoaded) loadHistory();

            contentEl.setAttribute('data-state', 'expanded');
            contentEl.style.overflow = 'hidden';
            contentEl.style.maxHeight = contentEl.scrollHeight + 'px';
            contentEl.style.opacity  = '1';
            chevronEl.style.transform = 'rotate(180deg)';
            setTimeout(function() {
                if (contentEl.getAttribute('data-state') === 'expanded') {
                    contentEl.style.maxHeight = 'none';
                    contentEl.style.overflow  = 'visible';
                }
            }, 350);
        } else {
            contentEl.setAttribute('data-state', 'collapsed');
            contentEl.style.overflow = 'hidden';
            contentEl.style.maxHeight = contentEl.scrollHeight + 'px';
            contentEl.offsetHeight; // force reflow
            contentEl.style.maxHeight = '0px';
            contentEl.style.opacity   = '0';
            chevronEl.style.transform = 'rotate(0deg)';
        }
    }

    // ── Event Listeners ───────────────────────────────────
    headerEl.addEventListener('click', function(e) {
        // Ignore click on Refresh button
        if (e.target.closest('#refreshHistoryChangesBtn')) return;
        toggle();
    });

    var refreshBtnEl = document.getElementById('refreshHistoryChangesBtn');
    if (refreshBtnEl) {
        refreshBtnEl.addEventListener('click', function(e) {
            e.stopPropagation();
            if (contentEl.getAttribute('data-state') === 'collapsed') {
                toggle(true);
            } else {
                loadHistory();
            }
        });
    }

})();
</script>
