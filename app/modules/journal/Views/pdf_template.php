<!DOCTYPE html>
<html>

<head>
    <title>Data Journal</title>
    <style>
        body {
            font-size: 10pt;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            word-wrap: break-word;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        h1 {
            font-size: 12pt;
            text-align: center;
            margin-bottom: 10px;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <h1>Data Journal</h1>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Status</th>
                <th>Alamat</th>
                <th>Hasil Pemeriksaan</th>
                <th>Tindakan</th>
                <th>No. WA</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($journals as $item): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $item->tgl ?></td>
                <td><?= $item->nama ?></td>
                <td><?= $status[$item->id]?></td>
                <td><?= $item->alamat ?></td>
                <td><?= $item->result_names ?></td>
                <td><?= $item->measures ?></td>
                <td><?= $item->nowa ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>
