<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h4>Riwayat Kunjungan Pasien</h4>
                <div class="card-header-action">
                    <button type="button" class="btn btn-primary" onclick="add()">
                        <i class="fas fa-plus"></i> Tambah Riwayat
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-2" class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Keluhan</th>
                                <th>Rekam Medis</th>
                                <th>Tanggal</th>
                                <th>Type</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    $("#table-2").dataTable({
        "processing": true,
        "serverSide": true,
        columns: [{
                "data": "no",
                "class": "",
                "width": "7%",
                'sortable': true
            },
            {
                "data": "complaint",
                "class": "",
                "width": "25%",
                'sortable': true
            },
            {
                "data": "medhis",
                "class": "",
                "width": "25%",
                'sortable': true
            },
            {
                "data": "date",
                "class": "",
                "width": "25%",
                'sortable': true
            },
            {
                "data": "type",
                "class": "",
                "width": "25%",
                'sortable': true
            },
            {
                "data": "action",
                "class": "text-center",
                "width": "10%",
                'sortable': false
            },
        ],
        "order": [],
        "ajax": {
            "url": "{{ site_url('history/fetch/' . $patient->id) }}",
            "type": "POST",
            "data": function(d) {
                d["{{ get_instance()->security->get_csrf_token_name()}}"] = "{{ get_instance()->security->get_csrf_hash() }}";
            },
            "dataSrc": function(json) {
                return json.data;
            }
        },
        "rowCallback": function(row, data) {
            if (data.is_delete === "1") {
                $(row).css('color', 'red').css('text-decoration', 'line-through');
            }
            if (data.kejantanan === "ya") {
                $(row).css('color', 'blue');
            }
        }
    });
</script>
<?= $this->endSection() ?>