<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Antrian Bone Hacker <?= esc($regionName ?? 'Cilacap') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .container {
            max-width: 100%;
        }

        @media (min-width: 768px) {
            .container {
                max-width: 800px;
            }
        }
    </style>
</head>

<body class="bg-gray-100 p-4">
    <div class="container mx-auto bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-semibold text-blue-700 text-center mb-4">Daftar Antrian</h1>
        <h2 class="text-xl font-semibold text-green-600 text-center mb-2">Bone Hacker <?= esc($regionName ?? 'Cilacap') ?></h2>
        <p class="text-gray-700 text-center mb-4">Periksa nomor urut dan status pendaftaran pasien terbaru.</p>
        <p class="text-gray-700 text-center mb-4">Rata-rata durasi terapi untuk setiap pasien adalah 15-20 menit, tergantung pada jenis keluhan dan tingkat keparahan.</p>
        <p class="text-gray-700 text-center mb-4">Pendaftaran dilakukan langsung di lokasi terapi. Urutan terapi mengikuti urutan kedatangan.</p>

        <div class="flex flex-wrap justify-center space-x-4 mb-6">
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-2 rounded">
                <div class="font-bold text-center">Menunggu</div>
                <div class="text-center"><?= esc($waiting_queues ?? 0) ?></div>
            </div>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-2 rounded">
                <div class="font-bold text-center">Dalam Proses Terapi</div>
                <div class="text-center"><?= esc($processed_queues ?? 0) ?></div>
            </div>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded">
                <div class="font-bold text-center">Selesai</div>
                <div class="text-center"><?= esc($finished_queues ?? 0) ?></div>
            </div>
        </div>

        <div class="bg-blue-800 text-white text-center rounded-md py-2 mb-4">
            Daftar Pasien Tanggal : <?= esc($currentDate) ?>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg shadow-md">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left">NO</th>
                        <th class="px-4 py-2 text-left">NAMA</th>
                        <th class="px-4 py-2 text-left">STATUS</th>
                        <th class="px-4 py-2 text-left">ANTRIAN</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php
                    $queueNumber = 1;
                    if (!empty($patient_queues)):
                        foreach ($patient_queues as $key => $queue):
                    ?>
                            <tr>
                                <td class="px-4 py-2"><?= esc($queue->pq->id ?? $key + 1) ?></td>
                                <td class="px-4 py-2"><?= esc($queue->patient_name ?? 'Tanpa Nama') ?></td>
                                <td class="px-4 py-2">
                                    <?php
                                    if ($queue->finish_at) {
                                        echo 'Selesai';
                                    } elseif ($queue->process_at) {
                                        echo 'Sedang diterapi';
                                    } else {
                                        echo 'Menunggu';
                                    }
                                    ?>
                                </td>
                                <td class="px-4 py-2">
                                    <?= ($queue->finish_at == null) ? $queueNumber++ : '-' ?>
                                </td>
                            </tr>
                        <?php
                        endforeach;
                    else:
                        ?>
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-center text-gray-500">Belum ada antrian untuk hari ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap justify-center space-x-4 mt-6">
            <a href="http://wa.me/62882007080700" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">WhatsApp</a>
            <a href="https://forms.gle/ETfG2zTm1nVyge7L7" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">Kritik & Saran</a>
        </div>
        <div class="flex justify-center mt-4">
            <a href="https://maps.app.goo.gl/9oiJibFfTQ9CZYh28" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Lokasi Cilacap</a>
        </div>

        <p class="text-center mt-8 text-md">
            💬 Satu review darimu bisa bantu lebih banyak orang menemukan terapi yang tepat!
        </p>
        <p class="text-center text-md">
            Yuk, luangkan 1 menit untuk kasih review bintang 5 di Google Maps ⭐⭐⭐⭐⭐
        </p>
        <p class="text-center text-md">
            Terima kasih🙏💙
        </p>
    </div>
</body>

</html>