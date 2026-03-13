<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Publik Terapis - <?= esc($terapis->nama) ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(45deg, #ff0099, #6777EF, #8243ff, white);
            background-size: 400% 400%;
            animation: gradientAnimation 10s ease infinite;
            text-align: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 90%;
            width: 450px;
            margin-top: 100px;
            position: relative;
            text-align: center;
        }

        .container-background {
            background-color: #2c2c2c;
            opacity: 0.8; /* Disesuaikan agar lebih terbaca dibanding 30% */
            border-radius: 40px;
            border-top-right-radius: 10px;
            border-bottom-left-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
        }

        .container-content {
            position: relative;
            padding: 30px;
            padding-bottom: 50px;
            z-index: 2;
            color: aliceblue;
        }

        .profile {
            position: absolute;
            top: -100px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 3;
        }

        .profile img {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            object-fit: cover;
            border: 6px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .profile-info {
            padding-top: 90px;
        }

        .profile-info h2 {
            margin-bottom: 25px;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 1.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 12px 5px;
            font-size: 1.1rem;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 35%;
            text-align: left;
            color: #bdc3c7;
        }

        .separator {
            width: 5%;
        }

        .value {
            text-align: left;
            width: 60%;
        }

        /* Animation for gradient background */
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Responsive adjustment */
        @media (max-width: 480px) {
            .container { margin-top: 80px; }
            .profile img { width: 150px; height: 150px; }
            td { font-size: 1rem; }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="container-background"></div>

        <div class="container-content">
            <div class="profile">
                <?php 
                    $fotoPath = ($terapis->foto && file_exists('foto_terapis/' . $terapis->foto)) 
                                ? base_url('foto_terapis/' . $terapis->foto) 
                                : base_url('foto_terapis/no_profile.png');
                ?>
                <img src="<?= $fotoPath ?>" alt="Foto Terapis">
            </div>
            
            <div class="profile-info">
                <h2>Terapis Bonehacker</h2>
                
                <table>
                    <tr>
                        <td class="label">Nama</td>
                        <td class="separator">:</td>
                        <td class="value"><?= esc(!empty($terapis->nama) ? $terapis->nama : '-') ?></td>
                    </tr>
                    <tr>
                        <td class="label">Jabatan</td>
                        <td class="separator">:</td>
                        <td class="value"><?= esc(!empty($jabatan->nama_jabatan) ? $jabatan->nama_jabatan : '-') ?></td>
                    </tr>
                    <tr>
                        <td class="label">Wilayah</td>
                        <td class="separator">:</td>
                        <td class="value"><?= esc(!empty($wilayah->name) ? $wilayah->name : '-') ?></td>
                    </tr>
                    <tr>
                        <td class="label">Rank</td>
                        <td class="separator">:</td>
                        <td class="value"><strong><?= esc(!empty($terapis->rank) ? $terapis->rank : '-') ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>

</html>