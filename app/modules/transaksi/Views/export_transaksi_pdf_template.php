<!DOCTYPE html>
<html>

<head>
    <title><?= $title ?></title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN TRANSAKSI</h2>
        <p>Dicetak pada: <?= date('d/m/Y H:i') ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Metode</th>
                <th>Usia</th>
                <th class="text-right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            <?php $total = 0;
            foreach ($transaksi as $i => $t) :
                $total += $t['nominal'];
            ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></td>
                    <td><?= strtoupper($t['metode_pembayaran']) ?></td>
                    <td><?= $t['rentang_usia'] ?></td>
                    <td class="text-right">Rp <?= number_format($t['nominal'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #eee; font-weight: bold;">
                <td colspan="4" class="text-right">TOTAL PENDAPATAN</td>
                <td class="text-right">Rp <?= number_format($total, 0, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>
</body>

</html>