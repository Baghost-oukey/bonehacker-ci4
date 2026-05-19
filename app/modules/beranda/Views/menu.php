<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<section class="w-full space-y-6 p-4">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">Menu Utama</h2>
        <p class="text-sm text-slate-500">Akses cepat fitur BoneHacker</p>
    </div>

    <?= $this->include('App\Views\components\mobile_grid_menu') ?>
</section>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    /**
     * Jurus Anti-Menu-Desktop: 
     * Kalau layar sudah lebar (Desktop), ngapain masih di /menu? 
     * Langsung tendang ke Beranda.
     */
    function handleDesktopRedirect() {
        if (window.innerWidth >= 1024) { // Breakpoint lg
            window.location.href = "<?= base_url('beranda') ?>";
        }
    }

    // Cek pas halaman dibuka
    handleDesktopRedirect();

    // Cek juga pas layar di-resize (misal dari landscape ke portrait atau sebaliknya)
    window.addEventListener('resize', handleDesktopRedirect);
</script>
<?= $this->endSection() ?>
