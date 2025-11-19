<?php
session_start();
include 'config.php';

// Cek status surat
$result = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cek_nim'])) {
    $nim = $_POST['cek_nim'];
    $sql = "SELECT * FROM surat WHERE nim_mahasiswa = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Surat Mahasiswa</title>
    <link rel="icon" type="image/png" href="logofpsd.png">
    <style>
        /* CSS tetap sama seperti sebelumnya */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color:rgb(211, 211, 211);
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg,rgb(255, 255, 255),rgb(255, 255, 255));
            border-radius: 15px;
            color:rgb(0, 0, 0);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .logo {
            width: 120px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
        }
        
        h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .banner-slide {
            background-color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-align: center;
            border-left: 5px solid #ffa500;
        }
        
        .banner-slide h2 {
            color:rgb(170, 0, 0);
            margin-bottom: 10px;
        }
        
        .form-section {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .form-section h2 {
            color:rgb(204, 0, 0);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .input-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input[type="text"]:focus {
            border-color: #ffa500;
            outline: none;
            box-shadow: 0 0 0 2px rgba(255, 165, 0, 0.2);
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(135deg,rgb(243, 117, 0),rgb(138, 1, 1));
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        .btn-full {
            width: 100%;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6b6bff, #6b6bff);
        }
        
        .status-result {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .status-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }
        
        .status-item:last-child {
            border-bottom: none;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-proses {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-diambil {
            background-color: #d4edda;
            color: #155724;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #777;
        }
        
        @media (max-width: 600px) {
            .container {
                padding: 10px;
            }
            
            .logo {
                width: 100px;
                font-size: 40px;
            }
            
            h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <img class="logo" src="logofpsd.png" alt="">
            <h5>SISTEM PENGAJUAN SURAT MAHASISWA</h5>
            <p>Fakultas Pendidikan Seni dan Design</p>
        </header>
        
        <section class="banner-slide">
            <h2>Informasi Penting</h2>
            <p>Pengajuan surat akan diproses dalam waktu 1-3 hari kerja. Harap periksa status surat secara berkala.</p>
        </section>
        
        <section class="form-section">
            <h2>Cek Status Surat</h2>
            <form method="POST" action="">
                <div class="input-group">
                    <label for="cek_nim">Masukkan NIM</label>
                    <input type="text" id="cek_nim" name="cek_nim" placeholder="Contoh: 123456789" required>
                </div>
                <button type="submit" class="btn btn-full">Cek Status</button>
            </form>
            
            <?php if ($result): ?>
                <div class="status-result">
                    <h3>Status Surat untuk NIM: <?php echo htmlspecialchars($_POST['cek_nim']); ?></h3>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <div class="status-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($row['jenis_surat']); ?></strong><br>
                                    <small>Nomor: <?php echo htmlspecialchars($row['nomor_surat'] ?: 'Belum ada'); ?></small>
                                </div>
                                <div>
                                    <span class="status-badge <?php echo $row['status'] == 'dalam proses' ? 'status-proses' : 'status-diambil'; ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-data">
                            <p>Belum ada pengajuan surat untuk NIM ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
        
        <section class="form-section">
            <h2>Ajukan Surat Baru</h2>
            <a href="pengajuan.php" class="btn btn-full">Ajukan Surat</a>
        </section>
    </div>
</body>
</html>