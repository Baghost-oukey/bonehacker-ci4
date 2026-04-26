<div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
    <div
        class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-slate-800">Riwayat Kunjungan Pasien</h3>
            <p class="text-sm text-slate-500">Daftar rekam medis dan histori terapi</p>
        </div>
        <button id="btn-add-history" type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 transition">
            <i class="fas fa-plus"></i> Tambah Riwayat
        </button>
    </div>

    <!-- TABLE -->
    <div class="overflow-x-auto">
        <table id="table-2" class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                <tr>
                    <th class="px-6 py-3.5 text-center font-semibold w-12">No</th>
                    <th class="px-6 py-3.5 text-left font-semibold">Keluhan</th>
                    <th class="px-6 py-3.5 text-left font-semibold">Rekam Medis</th>
                    <th class="px-6 py-3.5 text-left font-semibold">Tanggal</th>
                    <th class="px-6 py-3.5 text-center font-semibold w-20">Type</th>
                    <th class="px-6 py-3.5 text-center font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700"></tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-medium text-slate-600">Tampilkan</label>
                    <select id="paginationLength"
                        class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 transition focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500/15">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-xs font-medium text-slate-600">data per halaman</span>
                </div>
                <div class="text-xs font-medium text-slate-600 sm:ml-auto">
                    <span id="paginationInfo">Menampilkan 0 sampai 0 dari 0 data</span>
                </div>
            </div>

            <div class="flex items-center justify-center gap-1.5 sm:justify-end">
                <button id="paginationPrev"
                    class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400 disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-left text-xs mr-1"></i>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </button>
                <div id="paginationNumbers" class="flex items-center gap-1"></div>
                <button id="paginationNext"
                    class="inline-flex h-8 items-center justify-center rounded-lg border border-slate-300 bg-white px-3 py-1 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 hover:border-slate-400 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="hidden sm:inline">Berikutnya</span>
                    <i class="fas fa-chevron-right text-xs ml-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Riwayat -->
<div id="exampleModal" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800 modal-title">Tambah Riwayat Pasien</h5>
            <button type="button" data-modal-close
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>

        <form id="save_data" action="<?= site_url('history/store') ?>" method="post" class="space-y-4 p-5">
            <input type="hidden" name="id" id="history_id">
            <input type="hidden" name="patient_id" id="patient_id" value="<?= $patient_id ?>">
            <input type="hidden" name="queue_id" id="queue_id" value="<?= $queue_id ?>">

            <div class="max-h-[65vh] space-y-4 overflow-y-auto pr-1">
                <div class="rounded-lg border border-slate-200 bg-slate-50/50 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-slate-500">Tanggal Dibuat</label>
                            <input type="date" name="date" id="date" value="<?= esc($current_date) ?>" required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-slate-500">Nama</label>
                            <p class="text-sm font-semibold text-slate-800"><?= esc($patient->name ?? '-') ?></p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-slate-500">Usia</label>
                            <p class="text-sm font-semibold text-slate-800"><?= esc($patient->age ?? '-') ?> Tahun</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Keluhan</label>
                        <textarea name="complaint" id="complaintTags" rows="2"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"></textarea>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium text-slate-700">Riwayat Medis</label>
                        <textarea name="medhis" id="medhisTags" rows="2"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                    <button type="button" data-modal-close
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                    <button type="submit" id="save-button"
                        class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Simpan
                        Data</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus Riwayat -->
<div id="deleteModal" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 space-y-4">
        <h3 class="text-lg font-semibold text-slate-800">Konfirmasi Hapus</h3>
        <p class="text-sm text-slate-500">Apakah Anda yakin ingin menghapus riwayat kunjungan ini? Data tidak dapat
            dikembalikan.</p>
        <div class="flex justify-end gap-2">
            <button type="button" data-modal-close
                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
            <button id="confirmDeleteButton"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Ya,
                Hapus</button>
        </div>
    </div>
</div>