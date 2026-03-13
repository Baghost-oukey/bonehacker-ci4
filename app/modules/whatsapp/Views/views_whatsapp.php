<?= $this->extend('layout/layout') ?>
<?= $this->section('content') ?>
<section class="section">
    <div class="section-header">
        <h1><?= $title ?></h1>
    </div>
    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <?php if (count($records) > 0): ?>
                    <div class="card p-3">
                        <div class="card-header">
                            <h4>Daftar Whatsapp Api</h4>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped" id="table-wa">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>URL API</th>
                                        <th>Instance ID</th>
                                        <th>Token</th>
                                        <th>Created At</th>
                                        <th>Updated At</th>
                                        <th>Message Text</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($records as $record): ?>
                                        <tr>
                                            <td><?= $record->id ?></td>
                                            <td><?= $record->url_api ?></td>
                                            <td><?= substr($record->instance_id, 0, 4) . '****' ?></td>
                                            <td><?= substr($record->token, 0, 5) . '****************' ?></td>
                                            <td><?= $record->created_at ?></td>
                                            <td><?= $record->updated_at ?></td>
                                            <td><?= strlen($record->message) > 30 ? substr($record->message, 0, 30) . '...' : $record->message ?></td>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal"
                                                    data-id="<?= $record->id ?>"
                                                    data-url="<?= $record->url_api ?>"
                                                    data-instance="<?= $record->instance_id ?>"
                                                    data-token="<?= $record->token ?>"
                                                    data-message="<?= htmlspecialchars($record->message) ?>">
                                                    <i class="fa fa-edit"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"
                                                    data-id="<?= $record->id ?>">
                                                    <i class="fa fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card p-3">
                        <div class="card-header">
                            <h4>Daftar Whatsapp Api</h4>
                            <div class="card-header-action">
                                <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addModal">
                                    <i class="fa fa-plus"></i> Add New Data
                                </button>
                            </div>
                        </div>
                        <div class="alert alert-info">No records found. Click "Add New Data" to get started.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Add New Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('whatsapp/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>URL API</label>
                        <input type="text" class="form-control" name="url_api" required>
                    </div>
                    <div class="form-group">
                        <label>Instance ID</label>
                        <input type="text" class="form-control" name="instance_id" required>
                    </div>
                    <div class="form-group">
                        <label>Token</label>
                        <input type="text" class="form-control" name="token" required>
                    </div>
                    <div class="form-group">
                        <label>Message Template</label>
                        <textarea class="form-control" name="message" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">Are you sure you want to delete this record?</div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST">
                    <?= csrf_field() ?>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="form-group">
                        <label>URL API</label>
                        <input type="text" class="form-control" id="editUrlApi" name="url_api" required>
                    </div>
                    <div class="form-group">
                        <label>Instance ID</label>
                        <input type="text" class="form-control" id="editInstanceId" name="instance_id" required>
                    </div>
                    <div class="form-group">
                        <label>Token</label>
                        <input type="text" class="form-control" id="editToken" name="token" required>
                    </div>
                    <div class="form-group">
                        <label>Message Template</label>
                        <textarea class="form-control" id="editMessageTemplate" name="message" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#table-wa').DataTable({
            "responsive": true,
            "autoWidth": false,
            "order": [[0, 'asc']]
        });
    });

    $('#deleteModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var recordId = button.data('id');
        $(this).find('#deleteForm').attr('action', '<?= base_url('whatsapp/delete') ?>/' + recordId);
    });

    $('#editModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var recordId = button.data('id');
        
        var modal = $(this);
        modal.find('#editUrlApi').val(button.data('url'));
        modal.find('#editInstanceId').val(button.data('instance'));
        modal.find('#editToken').val(button.data('token'));
        modal.find('#editMessageTemplate').val(button.data('message'));
        modal.find('#editForm').attr('action', '<?= base_url('whatsapp/edit') ?>/' + recordId);
    });
</script>
<?= $this->endSection() ?>