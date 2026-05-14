<?= $this->extend('App\Views\layout\layout') ?>

<?= $this->section('content') ?>

<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Akun Saya</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola informasi akun dan ubah password Anda</p>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Alert -->
        <?php if (session()->has('message')): ?>
            <?php $msg = session('message'); ?>
            <div class="p-6 border-b border-slate-100">
                <div class="rounded-xl px-4 py-3 text-sm font-medium <?= $msg[0] === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' ?>">
                    <?= $msg[1] ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form action="<?= site_url('users/update_account') ?>" method="POST" class="p-6">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" value="<?= $user->id ?>">

            <!-- Nama Lengkap -->
            <div class="mb-6">
                <label for="realname" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="realname" 
                    name="realname" 
                    value="<?= esc($user->realname) ?>"
                    required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 outline-none transition focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-500/10"
                    placeholder="Masukkan nama lengkap">
            </div>

            <!-- Username -->
            <div class="mb-6">
                <label for="username" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">
                    Username <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    value="<?= esc($user->username) ?>"
                    required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 outline-none transition focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-500/10"
                    placeholder="Masukkan username">
            </div>

            <!-- Password -->
            <div class="mb-6">
                <label for="password" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">
                    Password Baru <span class="text-xs normal-case italic font-normal opacity-50">(Kosongkan jika tidak ingin mengubah)</span>
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-700 outline-none transition focus:border-teal-500 focus:bg-white focus:ring-4 focus:ring-teal-500/10"
                    placeholder="••••••••">
                <p class="mt-2 text-xs text-slate-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Kosongkan jika tidak ingin mengubah password
                </p>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <a 
                    href="<?= site_url('beranda') ?>"
                    class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-3 text-center text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
                <button 
                    type="submit"
                    class="flex-1 rounded-xl bg-teal-500 px-4 py-3 text-sm font-bold text-white transition hover:bg-teal-600">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <!-- Info Card -->
    <div class="mt-6 bg-blue-50 rounded-xl border border-blue-100 p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <i class="fas fa-shield-alt text-blue-500 text-xl"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-blue-900 mb-1">Keamanan Akun</h3>
                <p class="text-xs text-blue-700">
                    Pastikan password Anda kuat dan tidak mudah ditebak. Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol.
                </p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
