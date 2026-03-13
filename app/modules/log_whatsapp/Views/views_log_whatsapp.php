<?= $this->extend('layout/layout') ?>

<?= $this->section('content') ?>
<section class="section">
    <div class="section-header">
        <h1 class="text-center"><?= $title ?></h1>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="mb-4 text-center">
                <label for="startDate" class="mr-2">Start Date:</label>
                <input type="date" id="startDate" class="form-control d-inline-block" style="width: auto;">
                <label for="endDate" class="mx-3">End Date:</label>
                <input type="date" id="endDate" class="form-control d-inline-block" style="width: auto;">
            </div>

            <div class="table-responsive">
                <table id="whatsappLogs" class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>History ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Message</th>
                            <th>Status Pesan</th>
                            <th style="display:none;">Status Value</th> <th>Time Sent</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($logs as $log): ?>
                        <tr>
                            <td><?= $log->id ?></td>
                            <td><?= $log->history_id ?></td>
                            <td><?= $log->name ?></td>
                            <td>
                                <?php 
                                    // Logika format nomor telepon
                                    echo (isset($log->phone[1]) && $log->phone[0] === '6' && $log->phone[1] === '2') 
                                         ? '0' . substr($log->phone, 2) 
                                         : $log->phone; 
                                ?>
                            </td>
                            <td><?= $log->message ?></td>
                            <td>
                                <?php if($log->is_sent): ?>
                                    <span class="badge badge-success">Berhasil</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Gagal</span>
                                <?php endif; ?>
                            </td>
                            <td style="display:none;"><?= $log->is_sent ? 1 : 0 ?></td>
                            <td><?= $log->time_sent ?></td>
                            <td><?= $log->created_at ?></td>
                            <td><?= $log->updated_at ?></td>
                            <td>
                                <?php if(!$log->is_sent): ?>
                                    <form action="<?= base_url('whatsapp/log_whatsapp/resend') ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="log_id" value="<?= $log->id ?>">
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            <i class="fas fa-paper-plane"></i> Resend
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Inisialisasi DataTable
        var table = $('#whatsappLogs').DataTable({
            "searching": true,
            "responsive": true,
            "order": [[8, "desc"]], // Default sort berdasarkan Created At
            "columnDefs": [
                { "orderable": false, "targets": [5, 10] }, // Status & Action tidak bisa di-sort
                { "visible": false, "targets": [6] }        // Sembunyikan Status Value
            ]
        });

        // Fungsi Filter Rentang Tanggal
        function filterTable() {
            var startDate = $('#startDate').val();
            var endDate = $('#endDate').val();

            $.fn.dataTable.ext.search.pop(); // Reset filter sebelumnya

            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var createdAt = new Date(data[8]); // Kolom Created At
                var start = startDate ? new Date(startDate) : null;
                var end = endDate ? new Date(endDate) : null;

                if (start) start.setHours(0, 0, 0, 0);
                if (end) end.setHours(23, 59, 59, 999);

                if (
                    (!start && !end) || 
                    (!start && createdAt <= end) || 
                    (start <= createdAt && !end) || 
                    (start <= createdAt && createdAt <= end)
                ) {
                    return true;
                }
                return false;
            });

            table.draw();
        }

        // Event Listeners
        $('#startDate').on('change', function() {
            var val = $(this).val();
            $('#endDate').val(val); // Auto-fill end date agar memudahkan user
            filterTable();
        });

        $('#endDate').on('change', filterTable);
    });
</script>
<?= $this->endSection() ?>