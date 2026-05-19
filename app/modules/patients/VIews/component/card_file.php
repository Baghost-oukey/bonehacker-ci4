<div class="rounded-2xl bg-white shadow-sm border border-slate-200/50 overflow-hidden">
    <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h3 class="text-lg font-semibold text-slate-800">Dokumen & Berkas Pasien</h3>
            <p class="text-sm text-slate-500">Kelola file lampiran medis dan dokumen identitas</p>
        </div>
        <button type="button" data-modal-open="modalUpload"
            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700 transition">
            <i class="fas fa-upload"></i> Unggah File
        </button>
    </div>

    <div class="p-0">
        <form action="<?= site_url('patient/update_files') ?>" method="POST" id="deleteFilesForm">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc($patient_id) ?>">

            <?php 
            $files = !empty($file_urls) ? (is_array($file_urls) ? $file_urls : (json_decode($file_urls, true) ?? [])) : [];
            if (!empty($files)): ?>
                <!-- DESKTOP LAYOUT (TABLE) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-6 py-3.5 text-left font-semibold">Nama File</th>
                                <th class="px-6 py-3.5 text-center font-semibold">Tipe</th>
                                <th class="px-6 py-3.5 text-center font-semibold">Aksi</th>
                                <th class="px-6 py-3.5 text-center font-semibold">Pilih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php foreach ($files as $index => $file_url):
                                $file_extension = pathinfo($file_url, PATHINFO_EXTENSION);
                                $file_name = basename($file_url);
                            ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                                <i class="fas fa-file-alt text-xs"></i>
                                            </div>
                                            <span class="text-sm font-medium text-slate-800 truncate block max-w-[150px] sm:max-w-xs" title="<?= esc($file_name) ?>"><?= esc($file_name) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 text-center">
                                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                                            <?= strtoupper($file_extension) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 text-center">
                                        <button type="button" class="previewBtn inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition" data-id="<?= $index ?>" data-url="<?= esc($file_url) ?>">
                                            <i class="fas fa-eye"></i> Lihat
                                        </button>
                                    </td>
                                    <td class="px-6 py-3.5 text-center">
                                        <input class="delete-checkbox h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-500 cursor-pointer" 
                                            type="checkbox" name="delete_files[]" value="<?= $index ?>" id="delete_file_<?= $index ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- MOBILE & TABLET LAYOUT (CARDS) -->
                <div class="block md:hidden p-4 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php foreach ($files as $index => $file_url):
                            $file_extension = strtolower(pathinfo($file_url, PATHINFO_EXTENSION));
                            $file_name = basename($file_url);
                            
                            // Beautiful colors and icons based on file type
                            $icon_bg = 'bg-teal-50 text-teal-600 border border-teal-100/50';
                            $icon_class = 'fa-file-alt';
                            if ($file_extension === 'pdf') {
                                $icon_bg = 'bg-red-50 text-red-600 border border-red-100/50';
                                $icon_class = 'fa-file-pdf';
                            } elseif (in_array($file_extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                                $icon_bg = 'bg-blue-50 text-blue-600 border border-blue-100/50';
                                $icon_class = 'fa-file-image';
                            }
                        ?>
                            <div class="relative flex flex-col gap-3 rounded-2xl border border-slate-100 bg-slate-50/50 p-4 transition hover:bg-slate-100 hover:border-slate-200">
                                <!-- Top Info Header -->
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider <?= $icon_bg ?>">
                                        <?= strtoupper($file_extension) ?>
                                    </span>
                                    
                                    <!-- Deletion check label -->
                                    <label class="flex items-center gap-2 cursor-pointer select-none">
                                        <input class="delete-checkbox h-4.5 w-4.5 rounded border-slate-300 text-red-600 focus:ring-red-500 cursor-pointer transition" 
                                            type="checkbox" name="delete_files[]" value="<?= $index ?>" id="delete_file_mob_<?= $index ?>">
                                        <span class="text-xs font-semibold text-slate-500">Pilih</span>
                                    </label>
                                </div>

                                <!-- File Details -->
                                <div class="flex items-start gap-3 min-w-0 mt-1">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl <?= $icon_bg ?>">
                                        <i class="fas <?= $icon_class ?> text-lg"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-semibold text-slate-800 break-all" title="<?= esc($file_name) ?>">
                                            <?= esc($file_name) ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Action Buttons row -->
                                <div class="mt-2 border-t border-slate-200/60 pt-3 flex items-center justify-end">
                                    <button type="button" class="previewBtn w-full justify-center inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200/80 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:border-slate-300 transition active:scale-[0.98] shadow-sm" 
                                        data-id="<?= $index ?>" data-url="<?= esc($file_url) ?>">
                                        <i class="fas fa-eye text-teal-600"></i> Lihat Berkas
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-3 flex justify-end">
                    <button type="submit" id="batchDeleteBtn" class="hidden rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition">
                        <i class="fas fa-trash-alt mr-1"></i> Hapus File Terpilih
                    </button>
                </div>
            <?php else: ?>
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <i class="fas fa-folder-open text-4xl text-slate-300 mb-3"></i>
                    <p class="text-sm font-medium text-slate-800">Belum Ada File</p>
                    <p class="text-sm text-slate-500 mt-1">Klik unggah file baru untuk menambahkan dokumen</p>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Modal Upload File -->
<div id="modalUpload" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800">Unggah File Baru</h5>
            <button type="button" data-modal-close class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>
        <form id="uploadForm" action="<?= site_url('patient/upload_file') ?>" enctype="multipart/form-data" class="space-y-4 p-5">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc($patient_id) ?>">
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700">Pilih Berkas</label>
                <p class="text-xs text-slate-500">Mendukung format PDF, JPG, JPEG, PNG, WEBP (Maks. 10MB)</p>
                <input type="file" name="userfiles[]" multiple id="modalFileInput" accept=".pdf,.jpg,.jpeg,.png,.webp"
                    class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-teal-50 file:text-teal-600 hover:file:bg-teal-100 cursor-pointer">
            </div>
            <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
                <button type="button" data-modal-close class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">Unggah</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Preview File -->
<div id="fileUploadModal" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-xl" style="height: 90vh;">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h5 class="text-lg font-semibold text-slate-800" id="fileUploadModalLabel">Pratinjau Dokumen</h5>
            <button type="button" data-modal-close class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">&times;</button>
        </div>
        <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50" style="height: calc(90vh - 60px);">
            <div id="fileUploadContent" class="h-full flex items-center justify-center text-slate-400">
                <i class="fas fa-spinner fa-spin text-2xl"></i>
                <span class="ml-2 text-sm">Memuat dokumen...</span>
            </div>
        </div>
    </div>
</div>

