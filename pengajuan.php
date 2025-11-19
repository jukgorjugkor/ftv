<?php
session_start();
include 'config.php';

$nim = "";
$mahasiswa = null;
$showForm = false;
$message = "";

// Cek apakah NIM sudah diinput
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nim'])) {
    $nim = $_POST['nim'];
    
    // Cek apakah NIM sudah ada di database
    $sql = "SELECT * FROM surat WHERE nim_mahasiswa = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $mahasiswa = $result->fetch_assoc();
        $showForm = false;
    } else {
        $showForm = true;
    }
}

// Proses pengajuan surat
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['jenis_surat'])) {
    $jenis_surat = $_POST['jenis_surat'];
    $nim = $_POST['nim_mahasiswa'];
    $email = $_POST['email_mahasiswa']; // Tambahan field email
    
    // Generate nomor surat
    $prefix = "";
    if ($jenis_surat == "Surat Keterangan") $prefix = "SK";
    elseif ($jenis_surat == "Surat Pernyataan Masih Kuliah") $prefix = "SPMK";
    else $prefix = "SKR";
    
    $nomor_surat = $prefix . "/" . date('Y') . "/" . sprintf("%04d", rand(1, 9999));
    
    // Data tambahan berdasarkan jenis surat
    $ipk = null;
    $alasan_keperluan = null;
    
    if ($jenis_surat == "Surat Keterangan/Rekomendasi") {
        $ipk = $_POST['ipk'];
    } elseif ($jenis_surat == "Surat Keterangan") {
        $alasan_keperluan = $_POST['alasan_keperluan'];
    }
    
    // Insert data ke database
    $sql = "INSERT INTO surat (nama_mahasiswa, nim_mahasiswa, email_mahasiswa, jenjang, semester, program_studi, alamat_lengkap_mahasiswa, tahun_akademik, ipk, alasan_keperluan, tanggal_bulan_tahun, status, nomor_surat, jenis_surat) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'dalam proses', ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssisssdsss", 
        $_POST['nama_mahasiswa'], 
        $nim, 
        $email,
        $_POST['jenjang'], 
        $_POST['semester'], 
        $_POST['program_studi'], 
        $_POST['alamat_lengkap_mahasiswa'], 
        $_POST['tahun_akademik'], 
        $ipk, 
        $alasan_keperluan, 
        $nomor_surat, 
        $jenis_surat
    );
    
    if ($stmt->execute()) {
        $message = "Pengajuan surat berhasil! Nomor surat: " . $nomor_surat;
    } else {
        $message = "Terjadi kesalahan: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Surat</title>
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
            background-color: #f9f9f9;
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
        
        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            color: #ff6b6b;
            text-decoration: none;
            font-weight: 600;
        }
        
        .form-section {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .form-section h2 {
            color:rgb(114, 0, 0);
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
        
        input[type="text"], input[type="number"], input[type="email"], select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input[type="text"]:focus, input[type="number"]:focus, input[type="email"]:focus, select:focus, textarea:focus {
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
        
        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b, #ff6b6b);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffa500, #ffa500);
        }
        
        .surat-options {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .surat-option {
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .surat-option:hover {
            border-color: #ffa500;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .surat-option h3 {
            color: #ff6b6b;
            margin-bottom: 10px;
        }
        
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: white;
            border-radius: 15px;
            padding: 25px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-header h3 {
            color: #ff6b6b;
            margin: 0;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #777;
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
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
            
            .modal-content {
                width: 95%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <img src="logofpsd.png" class="logo" alt="">
            <h1>PENGAJUAN SURAT MAHASISWA</h1>
            <p>Fakultas Pendidikan Seni dan Desain</p>
        </header>
        
        <a href="index.php" class="back-btn">← Cek Status Surat</a>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'berhasil') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <section class="form-section">
            <h2>Masukkan NIM</h2>
            <form method="POST" action="">
                <div class="input-group">
                    <label for="nim">NIM Mahasiswa</label>
                    <input type="text" id="nim" name="nim" placeholder="Contoh: 123456789" value="<?php echo htmlspecialchars($nim); ?>" required>
                </div>
                <button type="submit" class="btn btn-full">Lanjutkan</button>
            </form>
        </section>
        
        <?php if ($showForm): ?>
            <!-- Form untuk data mahasiswa baru -->
            <section class="form-section">
                <h2>Data Mahasiswa</h2>
                <p>Silakan lengkapi data diri Anda</p>
                <form id="formMahasiswaBaru" method="POST" action="">
                    <input type="hidden" name="nim_mahasiswa" value="<?php echo htmlspecialchars($nim); ?>">
                    
                    <div class="input-group">
                        <label for="nama_mahasiswa">Nama Lengkap</label>
                        <input type="text" id="nama_mahasiswa" name="nama_mahasiswa" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="email_mahasiswa">Email</label>
                        <input type="email" id="email_mahasiswa" name="email_mahasiswa" placeholder="contoh@email.com" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="jenjang">Jenjang</label>
                        <select id="jenjang" name="jenjang" required>
                            <option value="">Pilih Jenjang</option>
                            <option value="D3">D3</option>
                            <option value="S1">S1</option>
                            <option value="S2">S2</option>
                            <option value="S3">S3</option>
                        </select>
                    </div>
                    
                    <div class="input-group">
                        <label for="semester">Semester</label>
                        <input type="number" id="semester" name="semester" min="1" max="14" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="program_studi">Program Studi</label>
                        <input type="text" id="program_studi" name="program_studi" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="alamat_lengkap_mahasiswa">Alamat Lengkap</label>
                        <textarea id="alamat_lengkap_mahasiswa" name="alamat_lengkap_mahasiswa" rows="3" required></textarea>
                    </div>
                    
                    <div class="input-group">
                        <label for="tahun_akademik">Tahun Akademik</label>
                        <input type="text" id="tahun_akademik" name="tahun_akademik" placeholder="Contoh: 2023/2024" required>
                    </div>
                    
                    <div class="input-group">
                        <label>Pilih Jenis Surat</label>
                        <div class="surat-options">
                            <div class="surat-option" onclick="openModal('modalKeterangan')">
                                <h3>Surat Keterangan</h3>
                                <p>Untuk keperluan umum</p>
                                <button type="button" class="btn btn-full">Ajukan</button>
                            </div>
                            
                            <div class="surat-option" onclick="openModal('modalPernyataan')">
                                <h3>Surat Pernyataan Masih Kuliah</h3>
                                <p>Untuk keperluan beasiswa, dll</p>
                                <button type="button" class="btn btn-full">Ajukan</button>
                            </div>
                            
                            <div class="surat-option" onclick="openModal('modalRekomendasi')">
                                <h3>Surat Keterangan/Rekomendasi</h3>
                                <p>Untuk keperluan akademik</p>
                                <button type="button" class="btn btn-full">Ajukan</button>
                            </div>
                        </div>
                    </div>
                </form>
            </section>
        <?php elseif ($mahasiswa): ?>
            <!-- Pilihan surat untuk mahasiswa yang sudah terdaftar -->
            <section class="form-section">
                <h2>Data Mahasiswa</h2>
                <div class="input-group">
                    <label>Nama</label>
                    <input type="text" value="<?php echo htmlspecialchars($mahasiswa['nama_mahasiswa']); ?>" readonly>
                </div>
                
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($mahasiswa['email_mahasiswa']); ?>" readonly>
                </div>
                
                <div class="input-group">
                    <label>Program Studi</label>
                    <input type="text" value="<?php echo htmlspecialchars($mahasiswa['program_studi']); ?>" readonly>
                </div>
                
                <div class="input-group">
                    <label>Pilih Jenis Surat</label>
                    <div class="surat-options">
                        <div class="surat-option" onclick="openModal('modalKeterangan')">
                            <h3>Surat Keterangan</h3>
                            <p>Untuk keperluan umum</p>
                            <button type="button" class="btn btn-full">Ajukan</button>
                        </div>
                        
                        <div class="surat-option" onclick="openModal('modalPernyataan')">
                            <h3>Surat Pernyataan Masih Kuliah</h3>
                            <p>Untuk keperluan beasiswa, dll</p>
                            <button type="button" class="btn btn-full">Ajukan</button>
                        </div>
                        
                        <div class="surat-option" onclick="openModal('modalRekomendasi')">
                            <h3>Surat Keterangan/Rekomendasi</h3>
                            <p>Untuk keperluan akademik</p>
                            <button type="button" class="btn btn-full">Ajukan</button>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <!-- Modal untuk Surat Keterangan -->
    <div id="modalKeterangan" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Surat Keterangan</h3>
                <button class="close-modal" onclick="closeModal('modalKeterangan')">&times;</button>
            </div>
            <form method="POST" action="">
                <?php if ($showForm): ?>
                    <input type="hidden" name="nama_mahasiswa" value="">
                    <input type="hidden" name="email_mahasiswa" value="">
                    <input type="hidden" name="jenjang" value="">
                    <input type="hidden" name="semester" value="">
                    <input type="hidden" name="program_studi" value="">
                    <input type="hidden" name="alamat_lengkap_mahasiswa" value="">
                    <input type="hidden" name="tahun_akademik" value="">
                <?php else: ?>
                    <input type="hidden" name="nama_mahasiswa" value="<?php echo htmlspecialchars($mahasiswa['nama_mahasiswa']); ?>">
                    <input type="hidden" name="email_mahasiswa" value="<?php echo htmlspecialchars($mahasiswa['email_mahasiswa']); ?>">
                    <input type="hidden" name="jenjang" value="<?php echo htmlspecialchars($mahasiswa['jenjang']); ?>">
                    <input type="hidden" name="semester" value="<?php echo htmlspecialchars($mahasiswa['semester']); ?>">
                    <input type="hidden" name="program_studi" value="<?php echo htmlspecialchars($mahasiswa['program_studi']); ?>">
                    <input type="hidden" name="alamat_lengkap_mahasiswa" value="<?php echo htmlspecialchars($mahasiswa['alamat_lengkap_mahasiswa']); ?>">
                    <input type="hidden" name="tahun_akademik" value="<?php echo htmlspecialchars($mahasiswa['tahun_akademik']); ?>">
                <?php endif; ?>
                <input type="hidden" name="nim_mahasiswa" value="<?php echo htmlspecialchars($nim); ?>">
                <input type="hidden" name="jenis_surat" value="Surat Keterangan">
                
                <div class="input-group">
                    <label for="alasan_keperluan">Alasan Keperluan</label>
                    <textarea id="alasan_keperluan" name="alasan_keperluan" rows="3" placeholder="Jelaskan alasan pengajuan surat keterangan..." required></textarea>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalKeterangan')">Batal</button>
                    <button type="submit" class="btn">Ajukan Surat</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal untuk Surat Pernyataan Masih Kuliah -->
    <div id="modalPernyataan" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Surat Pernyataan Masih Kuliah</h3>
                <button class="close-modal" onclick="closeModal('modalPernyataan')">&times;</button>
            </div>
            <form method="POST" action="">
                <?php if ($showForm): ?>
                    <input type="hidden" name="nama_mahasiswa" value="">
                    <input type="hidden" name="email_mahasiswa" value="">
                    <input type="hidden" name="jenjang" value="">
                    <input type="hidden" name="semester" value="">
                    <input type="hidden" name="program_studi" value="">
                    <input type="hidden" name="alamat_lengkap_mahasiswa" value="">
                    <input type="hidden" name="tahun_akademik" value="">
                <?php else: ?>
                    <input type="hidden" name="nama_mahasiswa" value="<?php echo htmlspecialchars($mahasiswa['nama_mahasiswa']); ?>">
                    <input type="hidden" name="email_mahasiswa" value="<?php echo htmlspecialchars($mahasiswa['email_mahasiswa']); ?>">
                    <input type="hidden" name="jenjang" value="<?php echo htmlspecialchars($mahasiswa['jenjang']); ?>">
                    <input type="hidden" name="semester" value="<?php echo htmlspecialchars($mahasiswa['semester']); ?>">
                    <input type="hidden" name="program_studi" value="<?php echo htmlspecialchars($mahasiswa['program_studi']); ?>">
                    <input type="hidden" name="alamat_lengkap_mahasiswa" value="<?php echo htmlspecialchars($mahasiswa['alamat_lengkap_mahasiswa']); ?>">
                    <input type="hidden" name="tahun_akademik" value="<?php echo htmlspecialchars($mahasiswa['tahun_akademik']); ?>">
                <?php endif; ?>
                <input type="hidden" name="nim_mahasiswa" value="<?php echo htmlspecialchars($nim); ?>">
                <input type="hidden" name="jenis_surat" value="Surat Pernyataan Masih Kuliah">
                
                <p>Surat ini untuk keperluan beasiswa, bantuan pendidikan, atau keperluan administratif lainnya.</p>
                
                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalPernyataan')">Batal</button>
                    <button type="submit" class="btn">Ajukan Surat</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal untuk Surat Keterangan/Rekomendasi -->
    <div id="modalRekomendasi" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Surat Keterangan/Rekomendasi</h3>
                <button class="close-modal" onclick="closeModal('modalRekomendasi')">&times;</button>
            </div>
            <form method="POST" action="">
                <?php if ($showForm): ?>
                    <input type="hidden" name="nama_mahasiswa" value="">
                    <input type="hidden" name="email_mahasiswa" value="">
                    <input type="hidden" name="jenjang" value="">
                    <input type="hidden" name="semester" value="">
                    <input type="hidden" name="program_studi" value="">
                    <input type="hidden" name="alamat_lengkap_mahasiswa" value="">
                    <input type="hidden" name="tahun_akademik" value="">
                <?php else: ?>
                    <input type="hidden" name="nama_mahasiswa" value="<?php echo htmlspecialchars($mahasiswa['nama_mahasiswa']); ?>">
                    <input type="hidden" name="email_mahasiswa" value="<?php echo htmlspecialchars($mahasiswa['email_mahasiswa']); ?>">
                    <input type="hidden" name="jenjang" value="<?php echo htmlspecialchars($mahasiswa['jenjang']); ?>">
                    <input type="hidden" name="semester" value="<?php echo htmlspecialchars($mahasiswa['semester']); ?>">
                    <input type="hidden" name="program_studi" value="<?php echo htmlspecialchars($mahasiswa['program_studi']); ?>">
                    <input type="hidden" name="alamat_lengkap_mahasiswa" value="<?php echo htmlspecialchars($mahasiswa['alamat_lengkap_mahasiswa']); ?>">
                    <input type="hidden" name="tahun_akademik" value="<?php echo htmlspecialchars($mahasiswa['tahun_akademik']); ?>">
                <?php endif; ?>
                <input type="hidden" name="nim_mahasiswa" value="<?php echo htmlspecialchars($nim); ?>">
                <input type="hidden" name="jenis_surat" value="Surat Keterangan/Rekomendasi">
                
                <div class="input-group">
                    <label for="ipk">IPK</label>
                    <input type="number" id="ipk" name="ipk" step="0.01" min="0" max="4" placeholder="Contoh: 3.75" required>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalRekomendasi')">Batal</button>
                    <button type="submit" class="btn">Ajukan Surat</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            // Untuk mahasiswa baru, pastikan form sudah diisi sebelum membuka modal
            if (<?php echo $showForm ? 'true' : 'false'; ?>) {
                const form = document.getElementById('formMahasiswaBaru');
                if (!form.checkValidity()) {
                    alert('Harap lengkapi semua data mahasiswa terlebih dahulu!');
                    form.reportValidity();
                    return;
                }
                
                // Isi data form ke modal
                const modal = document.getElementById(modalId);
                const inputs = modal.querySelectorAll('input[type="hidden"]');
                inputs.forEach(input => {
                    if (input.name && input.name !== 'jenis_surat' && input.name !== 'nim_mahasiswa') {
                        const formInput = document.querySelector(`[name="${input.name}"]`);
                        if (formInput) {
                            input.value = formInput.value;
                        }
                    }
                });
            }
            
            document.getElementById(modalId).style.display = 'flex';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Tutup modal ketika klik di luar konten modal
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>