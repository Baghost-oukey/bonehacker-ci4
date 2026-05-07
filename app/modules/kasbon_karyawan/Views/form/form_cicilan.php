<div class="w-full py-4">
    <form id="formPenyicilanKasbon" class="space-y-10">
        <input type="hidden" name="terapis_id" value="<?= $karyawan['id'] ?>">

        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 border-b border-slate-100 pb-8">
            <div class="max-w-xl">
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Input Cicilan Manual</h2>
                <p class="text-slate-500 text-sm font-medium mt-1">Gunakan form ini jika karyawan melakukan pembayaran di luar potongan gaji.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            <div class="space-y-4">
                <div class="flex justify-between items-end">
                    <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Nominal Pembayaran</label>
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Total Hutang: Rp <?= number_format($karyawan['total_kasbon_aktif'], 0, ',', '.') ?></span>
                </div>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none">
                        <span class="text-xl font-bold text-slate-400 group-focus-within:text-indigo-600 transition-colors">Rp</span>
                    </div>
                    <input type="text" id="inputNominalCicilan" name="nominal_cicilan" 
                        class="w-full pl-10 pr-0 py-3 bg-transparent border-b-2 border-slate-200 rounded-none text-3xl font-bold text-slate-800 focus:border-indigo-500 focus:ring-0 transition-all placeholder:text-slate-200" 
                        placeholder="0" required>
                </div>
                
                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-50 rounded-lg border border-indigo-100">
                    <div class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></div>
                    <p class="text-[11px] font-bold text-indigo-600 uppercase tracking-tight">
                        Sisa Hutang Akhir: <span id="estimasiSisaHutang" class="font-black ml-1">Rp <?= number_format($karyawan['total_kasbon_aktif'], 0, ',', '.') ?></span>
                    </p>
                </div>
            </div>

            <div class="space-y-4">
                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Catatan / Metode Bayar</label>
                <textarea name="keterangan_cicilan" 
                    class="w-full min-h-[120px] rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 p-5 text-sm font-semibold text-slate-700 transition-all resize-none" 
                    placeholder="Contoh: Bayar tunai ke kasir, Potongan THR, dll..." required></textarea>
            </div>
        </div>

        <div class="pt-6">
            <button type="submit" id="btnSubmitCicilan" 
                class="group relative inline-flex items-center justify-center px-8 py-4 font-bold text-white transition-all duration-300 bg-indigo-600 rounded-xl hover:bg-indigo-700 active:scale-95 shadow-lg shadow-indigo-900/20">
                <span class="mr-3">Catat Pembayaran</span>
                <i class="fas fa-check-circle text-[10px] transition-transform group-hover:scale-110"></i>
            </button>
        </div>
    </form>
</div>