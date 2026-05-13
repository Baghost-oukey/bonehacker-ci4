<div class="w-full py-4">
    <form id="formPengajuanKasbon" class="space-y-10">
        <input type="hidden" name="terapis_id" value="<?= $karyawan['id'] ?>">

        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 border-b border-slate-100 pb-8">
            <div class="max-w-xl">
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Input Kasbon</h2>
                <p class="text-slate-500 text-sm font-medium mt-1">Sistem akan mencatat Kasbon Dari Karyawan.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

            <div class="space-y-4">
                <div class="flex justify-between items-end">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Nominal Pencairan</label>
                </div>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none">
                        <span class="text-xl font-bold text-slate-400 group-focus-within:text-teal-600 transition-colors">Rp</span>
                    </div>
                    <input type="text" id="inputNominal" name="nominal"
                        class="w-full pl-10 pr-0 py-3 bg-transparent border-b-2 border-slate-200 rounded-none text-3xl font-bold text-slate-800 focus:border-teal-500 focus:ring-0 transition-all placeholder:text-slate-200"
                        placeholder="0" required>
                </div>

                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 rounded-lg border border-slate-200">
                    <div class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></div>
                    <p class="text-[11px] font-bold text-slate-600 uppercase tracking-tight">
                        Total Hutang Aktif: <span class="text-amber-600 font-black ml-1">Rp <?= number_format($karyawan['total_kasbon_aktif'], 0, ',', '.') ?></span>
                    </p>
                </div>
            </div>

            <div class="space-y-4">
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Alasan / Keperluan</label>
                <textarea name="keterangan"
                    class="w-full min-h-[120px] rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-teal-500 focus:ring-4 focus:ring-teal-500/5 p-5 text-sm font-semibold text-slate-700 transition-all resize-none"
                    placeholder="Tuliskan alasan singkat pencairan kasbon..." required></textarea>
            </div>
        </div>

        <div class="pt-6">
            <button type="submit" id="btnSubmitKasbon"
                class="group relative inline-flex items-center justify-center px-8 py-4 font-bold text-white transition-all duration-300 bg-slate-900 rounded-xl hover:bg-teal-600 active:scale-95 shadow-lg shadow-slate-900/10">
                <span class="mr-3">Konfirmasi Pencairan</span>
                <i class="fas fa-chevron-right text-[10px] transition-transform group-hover:translate-x-1"></i>
            </button>
        </div>
    </form>
</div>