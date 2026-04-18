<?php
$realname = session()->get('realname') ?? ($realname ?? 'User');
$avatarUrl = session()->get('avatar_url') ?? '';
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
        class="group relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white shadow-sm ring-1 ring-slate-100 transition hover:border-slate-300 hover:bg-slate-50 hover:shadow focus:outline-none focus:ring-2 focus:ring-slate-300"
        aria-label="Buka menu profil"
        title="<?= esc($realname) ?>">
        <?php if (!empty($avatarUrl)): ?>
            <img
                id="profileAvatarImage"
                src="<?= esc($avatarUrl) ?>"
                alt="Avatar"
                class="h-9 w-9 rounded-full object-cover ring-1 ring-slate-200"
                onerror="this.classList.add('hidden');document.getElementById('profileAvatarFallback')?.classList.remove('hidden');" />
        <?php else: ?>
            <span
                id="profileAvatarFallback"
                class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-semibold ring-1 <?= $avatarPalette['bg'] ?> <?= $avatarPalette['text'] ?> <?= $avatarPalette['ring'] ?>">
                <?= esc($initial) ?>
            </span>
        <?php endif; ?>

        <?php if (!empty($avatarUrl)): ?>
            <span
                id="profileAvatarFallback"
                class="hidden h-9 w-9 items-center justify-center rounded-full text-sm font-semibold ring-1 <?= $avatarPalette['bg'] ?> <?= $avatarPalette['text'] ?> <?= $avatarPalette['ring'] ?>">
                <?= esc($initial) ?>
            </span>
        <?php endif; ?>

        <span id="currentUserName" class="sr-only"><?= esc($realname) ?></span>
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

<div id="accountModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white shadow-lg ring-1 ring-black/5">

        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
            <h5 class="text-sm font-semibold text-slate-900">Edit akun</h5>
            <button id="closeAccountModal"
                class="flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-900">
                &times;
            </button>
        </div>

        <form id="editAccountForm" class="px-4 py-4 space-y-4">
            <?= csrf_field() ?>

            <div id="accountAlert" class="hidden rounded-md px-3 py-2 text-sm font-medium"></div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Nama lengkap</label>
                <input id="realname" type="text" name="realname" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm 
                    focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Username</label>
                <input id="username" type="text" name="username" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm 
                    focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Password baru</label>
                <input id="password" type="password" name="password" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm 
                    focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>

            <input id="user_id" type="hidden" name="user_id">

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" id="cancelAccountModal"
                    class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                    Batal
                </button>

                <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>