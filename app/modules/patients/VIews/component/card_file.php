<div class="rounded-xl border border-slate-200 bg-white shadow-sm mt-8 font-sans">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-b border-slate-200 px-6 py-5 gap-4">
        <div>
            <h4 class="text-lg font-semibold tracking-tight text-slate-900">Dokumen & Berkas Pasien</h4>
            <p class="text-sm text-slate-500">Kelola file lampiran medis dan dokumen identitas terkait.</p>
        </div>
        <button type="button" class="inline-flex h-9 items-center justify-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow transition-colors hover:bg-slate-900/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" data-modal-open="mauupload">
            <i class="fas fa-upload mr-2"></i> Unggah File Baru
        </button>
    </div>

    <div class="p-0">
        <div id="filePreview"></div>
        <form action="<?= site_url('patient/update_files') ?>" method="POST" id="deleteFilesForm">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc($patient_id) ?>">

            <?php if (!empty($file_urls)) : ?>
                <?php $files = is_array($file_urls) ? $file_urls : (json_decode($file_urls, true) ?? []); ?>
                <?php if (!empty($files)) : ?>
                    <div class="overflow-x-auto w-full">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-slate-50/80 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                <tr>
                                    <th class="px-6 py-4">Nama File</th>
                                    <th class="px-6 py-4 text-center">Tipe</th>
                                    <th class="px-6 py-4 text-center">Aksi</th>
                                    <th class="px-6 py-4 text-center">Pilih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                                <?php foreach ($files as $index => $file_url) :
                                    $file_extension = pathinfo($file_url, PATHINFO_EXTENSION);
                                    $file_name = basename($file_url);
                                ?>
                                    <tr class="align-middle hover:bg-slate-50/80 transition-colors">
                                        <td class="px-6 py-3.5">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-slate-100 text-slate-500">
                                                    <i class="fas fa-file-alt"></i>
                                                </div>
                                                <span class="font-medium text-slate-900 truncate max-w-50 sm:max-w-xs"><?= esc($file_name) ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3.5 text-center">
                                            <span class="inline-flex items-center justify-center rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold text-slate-600 border border-slate-200">
                                                <?= strtoupper($file_extension) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-3.5 text-center">
                                            <button type="button" class="inline-flex h-8 items-center justify-center rounded-md border border-slate-200 bg-white px-3 py-1 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-100 hover:text-slate-900 previewBtn" data-id="<?= $index ?>">
                                                <i class="fas fa-eye mr-1.5"></i> Lihat
                                            </button>
                                        </td>
                                        <td class="px-6 py-3.5 text-center">
                                            <label class="inline-flex cursor-pointer items-center justify-center gap-2 text-sm text-slate-500 hover:text-red-600 transition" for="delete_file_<?= $index ?>">
                                                <input class="delete-checkbox h-4 w-4 rounded border-slate-300 text-red-600 focus:ring-red-600/20 cursor-pointer" type="checkbox" name="delete_files[]" value="<?= $index ?>" id="delete_file_<?= $index ?>">
                                            </label>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-slate-200 bg-slate-50/50 p-4 flex justify-end">
                        <button type="submit" id="batchDeleteBtn" class="hidden inline-flex h-9 items-center justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2">
                            <i class="fas fa-trash-alt mr-2"></i> Hapus File Terpilih
                        </button>
                    </div>

                <?php else : ?>
                    <div class="flex flex-col items-center justify-center py-16 text-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 border border-slate-100 mb-4">
                            <i class="fas fa-folder-open text-slate-300 text-2xl"></i>
                        </div>
                        <p class="text-sm font-medium text-slate-900">Tidak Ada File</p>
                        <p class="text-sm text-slate-500 mt-1">Belum ada file yang diunggah untuk pasien ini.</p>
                    </div>
                <?php endif ?>
            <?php else : ?>
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-50 border border-slate-100 mb-4">
                        <i class="fas fa-folder-open text-slate-300 text-2xl"></i>
                    </div>
                    <p class="text-sm font-medium text-slate-900">Belum Ada File</p>
                    <p class="text-sm text-slate-500 mt-1">Silakan klik unggah file baru untuk menambahkan dokumen.</p>
                </div>
            <?php endif ?>
        </form>
    </div>
</div>

<!-- FORM TAMBAH FILE -->
<div id="mauupload" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/60 backdrop-blur-sm p-4 font-sans transition-all duration-300">
    <div class="w-full max-w-md overflow-hidden rounded-xl bg-white shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Unggah File Baru</h3>
            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition-colors" data-modal-close>
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-6">
            <div id="uploadAlert" class="mb-4 hidden rounded-md p-3 text-[13px] font-medium"></div>

            <form id="uploadForm" enctype="multipart/form-data" class="space-y-6">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= esc($patient_id) ?>">

                <div class="space-y-2">
                    <label class="text-sm font-medium leading-none text-slate-900">Pilih Berkas</label>
                    <p class="text-[13px] text-slate-500">Mendukung format PDF, JPG, PNG (Maks. 2MB)</p>

                    <input type="file" name="userfiles[]" multiple id="modalFileInput"
                        class="flex w-full cursor-pointer rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-500 file:mr-4 file:cursor-pointer file:border-0 file:bg-slate-100 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-slate-900 file:rounded hover:file:bg-slate-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2 transition-colors">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="inline-flex h-9 items-center justify-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-900 transition-colors hover:bg-slate-100" data-modal-close>
                        Batal
                    </button>
                    <button type="submit" class="inline-flex h-9 items-center justify-center rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow transition-colors hover:bg-slate-900/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                        <i class="fas fa-cloud-upload-alt mr-2"></i> Unggah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="fileUploadModal" class="modal-wrapper hidden fixed inset-0 z-50 items-center justify-center bg-black/80 backdrop-blur-sm p-4 font-sans transition-all duration-300">
    <div class="w-full max-w-5xl overflow-hidden rounded-xl bg-white shadow-2xl border border-slate-200 flex flex-col animate-in fade-in zoom-in-95 duration-200" style="height: 90vh;">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 bg-white shrink-0">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900" id="fileUploadModalLabel">Pratinjau Dokumen</h3>
            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100 hover:text-slate-900 transition-colors" data-modal-close>
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50">
            <div id="fileUploadContent" class="h-full flex items-center justify-center">
                <div class="text-center text-slate-400">
                    <i class="fas fa-spinner fa-spin text-2xl"></i>
                    <p class="mt-2 text-sm font-medium">Memuat dokumen...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.fileUrlsData = <?= !empty($file_urls) ? (is_array($file_urls) ? json_encode($file_urls) : $file_urls) : '[]' ?>;
    window.fileBaseUrl = '<?= base_url("patient_file") ?>';
    window.fileUploadUrl = '<?= base_url("patient/update_files") ?>';
    window.csrfTokenName = '<?= csrf_token() ?>';
    window.csrfHash = '<?= csrf_hash() ?>';
</script>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/pages/file-upload.js') ?>"></script>
<?= $this->endSection() ?>