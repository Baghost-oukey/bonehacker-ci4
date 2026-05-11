<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>

<div id="usersPage" class="w-full px-2 py-4 md:px-4 md:py-6 space-y-6 overflow-x-hidden mx-auto">
    <!-- HEADER -->
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between px-1">
        <div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tight text-slate-800 uppercase"><?= $title ?></h1>
            <p class="text-xs md:text-sm font-medium text-slate-500 mt-1">Kelola data pasien berdasarkan region admin.</p>
        </div>
        <a href="<?= base_url('users') ?>" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-slate-100 text-slate-600 text-xs font-black tracking-widest uppercase hover:bg-slate-200 transition-all">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div id="user-info" data-user-id="<?= $user_id ?>"></div>

    <!-- TABLE SEMUA PASIEN -->
    <div class="bg-white rounded-3xl border border-slate-100 overflow-hidden shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-black text-slate-800 uppercase tracking-tight">Data <?= $user_role === 'superadmin' ? 'Semua Pasien' : 'Pasien ' . $region_name ?></h2>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">DAFTAR PASIEN WILAYAH</p>
            </div>
        </div>
        <div class="p-6 overflow-x-auto">
            <table id="table-patients" class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">No</th>
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama</th>
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Jenis Kelamin</th>
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Usia</th>
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Alamat</th>
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Wilayah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-slate-700 text-sm"></tbody>
            </table>
        </div>
    </div>

    <!-- TABLE PASIEN LUAR -->
    <?php if ($user_role !== 'superadmin'): ?>
    <div class="bg-white rounded-3xl border border-slate-100 overflow-hidden shadow-sm">
        <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-black text-slate-800 uppercase tracking-tight">Data Pasien Luar</h2>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">PASIEN DARI LUAR WILAYAH YANG BERKUNJUNG</p>
            </div>
            <button type="button" data-modal-open="modalAddOutside" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-teal-600 text-white text-xs font-black tracking-widest uppercase shadow-lg shadow-teal-500/30 hover:bg-teal-700 transition-all">
                <i class="fas fa-plus"></i> Tambah Pasien Luar
            </button>
        </div>
        <div class="p-6 overflow-x-auto">
            <table id="table-patients-luar" class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">No</th>
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama</th>
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Jenis Kelamin</th>
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Usia</th>
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Alamat</th>
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest">Wilayah</th>
                        <th class="px-4 py-3 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-slate-700 text-sm"></tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- MODAL ADD PASIEN LUAR (VERSI BARU) -->
<div id="modalAddOutside" class="modal-wrapper hidden fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm flex flex-col items-center justify-end md:justify-center p-0 md:p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-t-3xl md:rounded-3xl shadow-2xl w-full max-w-4xl overflow-hidden transform translate-y-full md:translate-y-0 md:scale-95 transition-all duration-300 flex flex-col max-h-[90vh]">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5 bg-white shrink-0">
            <h5 class="text-lg font-black text-slate-800 uppercase tracking-tight">Cari & Tambah Pasien Luar</h5>
            <button type="button" data-modal-close class="text-slate-400 hover:text-red-500 transition-colors outline-none">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-6 space-y-6 overflow-hidden flex flex-col">
            <!-- Search Bar -->
            <div class="flex flex-col md:flex-row gap-4 items-center shrink-0">
                <div class="relative flex-1 w-full">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="patientSearchInput" placeholder="Ketik Nama atau Nomor WhatsApp..." class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 text-sm font-bold text-slate-700 focus:border-teal-500 focus:ring-4 focus:ring-teal-500/10 outline-none bg-slate-50 focus:bg-white transition-all shadow-inner">
                </div>
                <button type="button" class="w-full md:w-auto px-6 py-3 rounded-xl bg-teal-600 text-white text-xs font-black tracking-widest uppercase hover:bg-teal-700 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-user-plus"></i> Pasien Baru
                </button>
            </div>
            
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                <i class="fas fa-info-circle text-teal-500"></i> Pencarian otomatis mencakup seluruh wilayah/cabang.
            </p>

            <!-- Patient List Table -->
            <div class="flex-1 overflow-auto rounded-2xl border border-slate-100 shadow-inner bg-slate-50/30">
                <table class="w-full text-left border-collapse" id="modalPatientTable">
                    <thead class="sticky top-0 bg-white shadow-sm z-10">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">ID</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">NAMA</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">ALAMAT</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">STATUS</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="modalPatientList" class="divide-y divide-slate-100 bg-white">
                        <!-- Data loaded via JS -->
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <i class="fas fa-circle-notch fa-spin text-3xl text-teal-500 mb-4"></i>
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Memuat data pasien...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Hidden Form for submission -->
        <form id="addOutsidePatientForm" action="<?= base_url('users/add_outside_patient') ?>" method="POST" class="hidden">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" value="<?= $user_id ?>">
            <input type="hidden" name="patient_id" id="selected_patient_id">
        </form>

        <div class="flex items-center justify-end px-6 py-4 bg-slate-50 border-t border-slate-100 shrink-0">
            <button type="button" data-modal-close class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-500 text-xs font-black uppercase tracking-widest hover:bg-slate-100 transition-all">Tutup</button>
        </div>
    </div>
</div>

<style>
    /* DataTables Tailwind styling */
    .dataTables_wrapper .dataTables_paginate {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: center;
        gap: 0.25rem;
        margin-top: 1rem;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.375rem 0.75rem;
        margin-left: 0 !important;
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        background: white;
        color: #64748b !important;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f8fafc;
        color: #0f172a !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #0d9488 !important;
        color: white !important;
        border-color: #0d9488;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .dataTables_wrapper .dataTables_info {
        font-size: 0.875rem;
        color: #64748b;
        margin-top: 1rem;
        float: left;
    }
    .dataTables_wrapper::after {
        content: "";
        clear: both;
        display: table;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        const userId = $('#user-info').data('user-id');
        let currentToken = "<?= csrf_hash() ?>";

        // Modal Functions
        const openModal = (modal) => {
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modal.querySelector('.transform')?.classList.remove('translate-y-full', 'scale-95');
                modal.classList.remove('opacity-0');
            }, 10);
            
            if (modal.id === 'modalAddOutside') {
                loadPatientSearch("");
            }
        };

        const closeModal = (modal) => {
            if (!modal) return;
            modal.querySelector('.transform')?.classList.add('translate-y-full', 'md:scale-95');
            modal.classList.add('opacity-0');
            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }, 300);
        };

        $(document).on("click", "[data-modal-open]", function() { 
            openModal(document.getElementById($(this).data("modal-open"))); 
        });
        
        $(document).on("click", "[data-modal-close], .modal-wrapper", function(e) { 
            if (e.target === this || $(this).is('[data-modal-close]') || $(this).closest('[data-modal-close]').length) {
                closeModal($(this).closest(".modal-wrapper")[0]); 
            }
        });

        // 1. Table Semua Pasien
        $('#table-patients').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= base_url('users/fetch_patients') ?>",
                type: "POST",
                data: function(d) {
                    d.user_id = userId;
                    d["<?= csrf_token() ?>"] = currentToken;
                },
                dataSrc: function(json) {
                    if (json.csrfHash) currentToken = json.csrfHash;
                    return json.data;
                }
            },
            columns: [
                { data: "no", width: "5%", sortable: false, searchable: false, className: "px-4 py-3 text-xs font-mono text-slate-400 italic" },
                { data: "nama", searchable: true, className: "px-4 py-3 font-black text-slate-800 text-sm" },
                { data: "gender", searchable: false, className: "px-4 py-3 text-xs font-bold text-slate-500" },
                { data: "age", searchable: false, className: "px-4 py-3 text-xs font-bold text-slate-500" },
                { data: "address", searchable: false, className: "px-4 py-3 text-xs font-bold text-slate-500" },
                { data: "wilayah", searchable: false, className: "px-4 py-3 text-xs font-bold text-slate-400" }
            ],
            language: {
                search: "Cari Pasien:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pasien",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 pasien",
                infoFiltered: "(difilter dari _MAX_ total pasien)",
                zeroRecords: "Tidak ada data pasien yang ditemukan",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        // 2. Table Pasien Luar
        <?php if ($user_role !== 'superadmin'): ?>
        const tableLuar = $('#table-patients-luar').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "<?= base_url('users/fetch_patients_luar') ?>",
                type: "POST",
                data: function(d) {
                    d.user_id = userId;
                    d["<?= csrf_token() ?>"] = currentToken;
                },
                dataSrc: function(json) {
                    if (json.csrfHash) currentToken = json.csrfHash;
                    return json.data;
                }
            },
            columns: [
                { data: "no", width: "5%", sortable: false, searchable: false, className: "px-4 py-3 text-xs font-mono text-slate-400 italic" },
                { data: "nama", searchable: true, className: "px-4 py-3 font-black text-slate-800 text-sm" },
                { data: "gender", searchable: false, className: "px-4 py-3 text-xs font-bold text-slate-500" },
                { data: "age", searchable: false, className: "px-4 py-3 text-xs font-bold text-slate-500" },
                { data: "address", searchable: false, className: "px-4 py-3 text-xs font-bold text-slate-500" },
                { data: "wilayah", searchable: false, className: "px-4 py-3 text-xs font-bold text-slate-400" },
                { data: "aksi", class: "text-center px-4 py-3", sortable: false, searchable: false }
            ],
            language: {
                search: "Cari Pasien Luar:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pasien",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 pasien",
                infoFiltered: "(difilter dari _MAX_ total pasien)",
                zeroRecords: "Tidak ada data pasien luar yang ditemukan"
            }
        });

        // Search Patient Logic for Modal
        const debounce = (fn, delay = 500) => {
            let timeout;
            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => fn(...args), delay);
            };
        };

        const loadPatientSearch = (term) => {
            const list = $('#modalPatientList');
            list.html(`
                <tr>
                    <td colspan="5" class="px-6 py-20 text-center">
                        <i class="fas fa-circle-notch fa-spin text-3xl text-teal-500 mb-4"></i>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Mencari pasien...</p>
                    </td>
                </tr>
            `);

            $.ajax({
                url: '<?= base_url('users/get_outside_patients_select') ?>',
                type: 'POST',
                data: {
                    searchTerm: term,
                    user_id: userId,
                    "<?= csrf_token() ?>": currentToken
                },
                dataType: 'json',
                success: function(data) {
                    list.empty();
                    if (data.length === 0) {
                        list.append(`
                            <tr>
                                <td colspan="5" class="px-6 py-20 text-center opacity-40">
                                    <i class="fas fa-user-slash text-5xl text-slate-300 mb-4"></i>
                                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Tidak menemukan pasien luar</p>
                                </td>
                            </tr>
                        `);
                        return;
                    }

                    data.forEach(item => {
                        const row = `
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-xs font-mono text-slate-400 italic">#${item.id}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-slate-800 uppercase tracking-tight">${item.nama}</span>
                                        <span class="text-[10px] font-bold text-slate-400">@ ${item.wilayah}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs font-bold text-slate-500 max-w-[200px] truncate">${item.address || "-"}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest bg-orange-50 text-orange-600 border border-orange-100/50">Lama</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button type="button" class="btn-pilih-patient px-5 py-2 rounded-xl bg-teal-600 text-white text-[10px] font-black tracking-widest uppercase shadow-md shadow-teal-500/20 hover:bg-teal-700 transition-all flex items-center gap-2 mx-auto" 
                                        data-id="${item.id}" data-nama="${item.nama}">
                                        PILIH <i class="fas fa-chevron-right text-[8px]"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                        list.append(row);
                    });
                }
            });
        };

        $('#patientSearchInput').on('keyup', debounce(function() {
            loadPatientSearch($(this).val());
        }));

        $(document).on('click', '.btn-pilih-patient', function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            const btn = $(this);

            Swal.fire({
                title: 'Konfirmasi',
                text: `Tambahkan "${nama}" sebagai pasien luar?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Tambahkan',
                cancelButtonText: 'Batal',
                customClass: { confirmButton: 'bg-teal-600 text-white px-6 py-2 rounded-xl font-bold ml-2', cancelButton: 'bg-slate-100 text-slate-500 px-6 py-2 rounded-xl font-bold' },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i>');
                    
                    $.post('<?= base_url('users/add_outside_patient') ?>', {
                        user_id: userId,
                        patient_id: id,
                        "<?= csrf_token() ?>": currentToken
                    }, function(res) {
                        if (res.csrfHash) currentToken = res.csrfHash;

                        if (res.status === 'success') {
                            closeModal(document.getElementById('modalAddOutside'));
                            tableLuar.ajax.reload();
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.message, timer: 1500, showConfirmButton: false });
                        } else {
                            btn.prop('disabled', false).html('PILIH <i class="fas fa-chevron-right text-[8px]"></i>');
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    }, 'json');
                }
            });
        });

        $(document).on('click', '.btn-delete-patient', function() {
            const pid = $(this).data('patient-id');
            const uid = $(this).data('user-id');

            Swal.fire({
                title: 'Hapus Pasien?',
                text: "Pasien akan dihapus dari daftar pantauan Anda.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                customClass: { confirmButton: 'bg-red-600 text-white px-6 py-2 rounded-xl font-bold ml-2', cancelButton: 'bg-slate-100 text-slate-500 px-6 py-2 rounded-xl font-bold' },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('<?= base_url('users/delete_outside_patient') ?>', {
                        patient_id: pid,
                        user_id: uid,
                        "<?= csrf_token() ?>": currentToken
                    }, function(res) {
                        if (res.csrfHash) currentToken = res.csrfHash;
                        if (res.success) {
                            tableLuar.ajax.reload();
                            Swal.fire({icon: 'success', title: 'Berhasil', text: 'Daftar pasien diperbarui', timer: 1500, showConfirmButton: false});
                        }
                    }, 'json');
                }
            });
        });

        $(document).on('click', '.btn-send-wa', function() {
            const pid = $(this).data('patient-id');
            const btn = $(this);

            Swal.fire({
                title: 'Kirim Notifikasi?',
                text: "Kirim pesan pengingat via WhatsApp ke pasien.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Kirim Sekarang',
                cancelButtonText: 'Batal',
                customClass: { confirmButton: 'bg-teal-600 text-white px-6 py-2 rounded-xl font-bold ml-2', cancelButton: 'bg-slate-100 text-slate-500 px-6 py-2 rounded-xl font-bold' },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
                    $.post("<?= base_url('whatsapp/send_notif_patients') ?>/" + pid, {
                        "<?= csrf_token() ?>": currentToken
                    }, function(res) {
                        btn.html('<i class="fab fa-whatsapp"></i>').prop('disabled', false);
                        if (res.csrfHash) currentToken = res.csrfHash;
                        if (res.status === 'success') {
                            Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Pesan dalam antrean server.', timer: 1500, showConfirmButton: false});
                        } else {
                            Swal.fire('Gagal', 'Gagal menghubungi server WA.', 'error');
                        }
                    }, 'json').fail(() => {
                        btn.html('<i class="fab fa-whatsapp"></i>').prop('disabled', false);
                        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                    });
                }
            });
        });
        <?php endif; ?>
    });
</script>
<?= $this->endSection() ?>
