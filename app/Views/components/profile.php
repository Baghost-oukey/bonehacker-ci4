<?php

/** @var string $role */
$role = $role ?? session()->get('role') ?? '';
$realname = $realname ?? session()->get('realname') ?? 'User';

// Always get fresh avatar from database, not from session
$avatarUrl = '';
$db = \Config\Database::connect();
$terapisId = session()->get('terapis_id_int');

if ($terapisId) {
    $terapis = $db->table('terapis')
        ->select('foto')
        ->where('id', $terapisId)
        ->get()
        ->getRow();
    
    if ($terapis && !empty($terapis->foto)) {
        $avatarUrl = base_url('foto_karyawan/' . $terapis->foto);
    }
}

$initial = strtoupper(substr(trim($realname), 0, 1));
if ($initial === '' || $initial === false) {
    $initial = 'U';
}

// Get jabatan and rank for terapis
$jabatanName = '';
$rankName = '';

if ($terapisId) {
    // Get raw terapis data
    $terapisRaw = $db->table('terapis')
        ->select('*')
        ->where('id', $terapisId)
        ->get()
        ->getRow();
    
    // Check if rank is an ID or a text value
    $rankValue = $terapisRaw->rank ?? null;
    
    $terapis = $db->table('terapis')
        ->select('terapis.jabatan_id, terapis.rank, jabatan.nama_jabatan as jabatan_nama')
        ->join('jabatan', 'jabatan.id = terapis.jabatan_id', 'left')
        ->where('terapis.id', $terapisId)
        ->get()
        ->getRow();
    
    if ($terapis) {
        $jabatanName = $terapis->jabatan_nama ?? '';
        
        // Check if rank is numeric (ID) or text
        if ($rankValue && is_numeric($rankValue)) {
            // Rank is an ID, get from rank_terapis table
            $rankData = $db->table('rank_terapis')
                ->select('name')
                ->where('id', $rankValue)
                ->get()
                ->getRow();
            $rankName = $rankData->name ?? '';
        } else {
            // Rank is stored as text directly
            $rankName = $rankValue ?? '';
        }
    }
}

// Determine display text for user status
$userStatus = '';
if (!empty($jabatanName) && !empty($rankName)) {
    $userStatus = $jabatanName . ' - ' . $rankName;
} elseif (!empty($jabatanName)) {
    $userStatus = $jabatanName;
} elseif (!empty($rankName)) {
    $userStatus = $rankName;
} else {
    $userStatus = ucfirst($role);
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
                <span class="text-xs text-slate-500"><?= esc($userStatus) ?></span>
            </div>
        </div>

        <div class="my-1 h-px bg-slate-100"></div>

        <a href="<?= site_url('users/account') ?>"
            class="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
            <i class="far fa-user text-slate-500"></i>
            Akun saya
        </a>

        <a href="<?= site_url('auth/destroy') ?>"
            class="flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-700 transition hover:bg-rose-50">
            <i class="fas fa-sign-out-alt"></i>
            Keluar
        </a>
    </div>
</div>