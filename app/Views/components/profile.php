<?php

/** @var string $role */
$role = $role ?? session()->get('role') ?? '';
$realname = $realname ?? session()->get('realname') ?? 'User';
$avatarUrl = $avatarUrl ?? session()->get('avatar') ?? session()->get('avatar_url') ?? '';
$initial = strtoupper(substr(trim($realname), 0, 1));
if ($initial === '' || $initial === false) {
    $initial = 'U';
}

$avatarPalettes = [
    ['bg' => 'bg-rose-100', 'text' => 'text-rose-700', 'ring' => 'ring-rose-200'],
    ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'ring' => 'ring-orange-200'],
    ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'ring' => 'ring-amber-200'],
    ['bg' => 'bg-lime-100', 'text' => 'text-lime-700', 'ring' => 'ring-lime-200'],
    ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'ring' => 'ring-emerald-200'],
    ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-700', 'ring' => 'ring-cyan-200'],
    ['bg' => 'bg-sky-100', 'text' => 'text-sky-700', 'ring' => 'ring-sky-200'],
    ['bg' => 'bg-violet-100', 'text' => 'text-violet-700', 'ring' => 'ring-violet-200'],
    ['bg' => 'bg-fuchsia-100', 'text' => 'text-fuchsia-700', 'ring' => 'ring-fuchsia-200'],
];

$paletteIndex = ord($initial) % count($avatarPalettes);
$avatarPalette = $avatarPalettes[$paletteIndex];
?>

<div id="profileComponent" class="relative" data-edit-account-url="<?= site_url('users/edit_account') ?>"
    data-update-account-url="<?= site_url('users/update_account') ?>">

    <button
        type="button"
        data-menu-toggle="userMenu"
        class="group relative inline-flex h-10 items-center gap-2.5 rounded-full border border-slate-200 bg-white pl-1.5 pr-3 shadow-sm ring-1 ring-slate-100 transition hover:border-slate-300 hover:bg-slate-50 hover:shadow focus:outline-none focus:ring-2 focus:ring-slate-300"
        aria-label="Buka menu profil">

        <div class="flex h-7 w-7 shrink-0 items-center justify-center overflow-hidden rounded-full ring-1 ring-slate-200">
            <?php if (!empty($avatarUrl)): ?>
                <img
                    id="profileAvatarImage"
                    src="<?= esc($avatarUrl) ?>"
                    alt="Avatar"
                    class="h-full w-full object-cover"
                    onerror="this.classList.add('hidden');document.getElementById('profileAvatarFallback')?.classList.remove('hidden');" />
            <?php else: ?>
                <span
                    id="profileAvatarFallback"
                    class="flex h-full w-full items-center justify-center text-[10px] font-bold <?= $avatarPalette['bg'] ?> <?= $avatarPalette['text'] ?>">
                    <?= esc($initial) ?>
                </span>
            <?php endif; ?>

            <?php if (!empty($avatarUrl)): ?>
                <span
                    id="profileAvatarFallback"
                    class="hidden h-full w-full items-center justify-center text-[10px] font-bold <?= $avatarPalette['bg'] ?> <?= $avatarPalette['text'] ?>">
                    <?= esc($initial) ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="hidden md:flex flex-col items-start leading-none">
            <span class="text-[11px] font-bold text-slate-700"><?= esc($realname) ?></span>
            <span class="text-[9px] font-medium text-slate-400 uppercase tracking-tight"><?= esc($role) ?></span>
        </div>

        <i class="fas fa-chevron-down text-[10px] text-slate-300 transition-transform duration-200 group-hover:text-slate-400"></i>
    </button>

    <div id="userMenu"
        class="absolute right-0 z-40 mt-2 hidden w-64 rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl ring-1 ring-black/5">
        <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2.5">
            <div
                class="flex h-9 w-9 items-center justify-center rounded-full text-xs font-semibold ring-1 <?= $avatarPalette['bg'] ?> <?= $avatarPalette['text'] ?> <?= $avatarPalette['ring'] ?>">
                <?= esc($initial) ?>
            </div>
            <div class="flex flex-col leading-tight">
                <span class="text-sm font-medium text-slate-900 truncate"><?= esc($realname) ?></span>
                <span class="text-xs text-slate-500">Akun aktif</span>
            </div>
        </div>

        <div class="my-1 h-px bg-slate-100"></div>

        <button id="editAccountBtn"
            class="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
            <i class="far fa-user text-slate-500"></i>
            Akun saya
        </button>

        <a href="<?= site_url('auth/destroy') ?>"
            class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-700 transition hover:bg-rose-50">
            <i class="fas fa-sign-out-alt"></i>
            Keluar
        </a>
    </div>
</div>

<script>
function toggleEditAccountModal(show = true) {
    const modal = document.getElementById('accountManagementModal');
    const alert = document.getElementById('accountAlert');
    const root  = document.getElementById('profileComponent');
    
    if (!modal) {
        console.error('Modal accountManagementModal not found!');
        return;
    }
    
    if (show) {
        const userMenu = document.getElementById('userMenu');
        if (userMenu) userMenu.style.display = 'none';
        
        if (alert) {
            alert.style.setProperty('display', 'none', 'important');
            alert.textContent = '';
        }
        
        modal.style.setProperty('display', 'flex', 'important');
        
        const editUrl = root ? root.getAttribute('data-edit-account-url') : null;
        if (editUrl) {
            fetch(editUrl)
                .then(response => response.json())
                .then(res => {
                    document.getElementById('realname').value = res.realname || '';
                    document.getElementById('username').value = res.username || '';
                    document.getElementById('user_id').value = res.id || res.userId || '';
                })
                .catch(err => console.error('Error fetching account data:', err));
        }
    } else {
        modal.style.setProperty('display', 'none', 'important');
        const form = document.getElementById('editAccountForm');
        if (form) form.reset();
    }
}

// Handler untuk form submit (Vanilla JS)
(function() {
    function initAccountForm() {
        const form = document.getElementById('editAccountForm');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const root = document.getElementById('profileComponent');
            const updateUrl = root ? root.getAttribute('data-update-account-url') : null;
            const alert = document.getElementById('accountAlert');
            
            if (!updateUrl) return;
            
            const formData = new FormData(form);
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            
            btn.disabled = true;
            btn.textContent = 'Menyimpan...';
            
            fetch(updateUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(res => {
                if (res.status === 'success') {
                    if (alert) {
                        alert.className = "mb-4 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-700";
                        alert.textContent = res.message;
                        alert.style.setProperty('display', 'block', 'important');
                    }
                    const label = document.getElementById('currentUserName');
                    if (label) label.textContent = res.realname;
                    
                    setTimeout(() => {
                        toggleEditAccountModal(false);
                        location.reload();
                    }, 800);
                } else {
                    if (alert) {
                        alert.className = "mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700";
                        alert.textContent = res.message || 'Gagal memperbarui';
                        alert.style.setProperty('display', 'block', 'important');
                    }
                }
            })
            .catch(err => {
                if (alert) {
                    alert.className = "mb-4 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700";
                    alert.textContent = "Terjadi kesalahan sistem.";
                    alert.style.setProperty('display', 'block', 'important');
                }
                console.error('Update error:', err);
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = originalText;
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAccountForm);
    } else {
        initAccountForm();
    }
})();
</script>