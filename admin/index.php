<?php
session_start();
include '../config.php';
include 'email_functions.php'; // Include fungsi email

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Hapus data surat
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM surat WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Data surat berhasil dihapus!";
        $message_type = "success";
    } else {
        $message = "Gagal menghapus data: " . $stmt->error;
        $message_type = "error";
    }
}

// Update status surat dengan email notifikasi
if (isset($_GET['update_status'])) {
    $id = $_GET['update_status'];
    $status = $_GET['status'];
    
    // Ambil data surat untuk mendapatkan email
    $sql_surat = "SELECT * FROM surat WHERE id = ?";
    $stmt_surat = $conn->prepare($sql_surat);
    $stmt_surat->bind_param("i", $id);
    $stmt_surat->execute();
    $result_surat = $stmt_surat->get_result();
    $surat_data = $result_surat->fetch_assoc();
    
    $sql = "UPDATE surat SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $id);
    
    if ($stmt->execute()) {
        // Kirim email jika status berubah menjadi "dapat diambil"
        if ($status == 'dapat diambil' && !empty($surat_data['email_mahasiswa'])) {
            $email_sent = sendSuratApprovalEmail(
                $surat_data['email_mahasiswa'],
                $surat_data['nama_mahasiswa'],
                $surat_data['jenis_surat'],
                $surat_data['nomor_surat']
            );
            
            if ($email_sent) {
                $message = "Status surat berhasil diupdate dan email notifikasi telah dikirim!";
            } else {
                $message = "Status surat berhasil diupdate namun email notifikasi gagal dikirim!";
            }
        } else {
            $message = "Status surat berhasil diupdate!";
        }
        $message_type = "success";
    } else {
        $message = "Gagal mengupdate status: " . $stmt->error;
        $message_type = "error";
    }
}

// Filter tanggal
$filter_tanggal = isset($_GET['filter_tanggal']) ? $_GET['filter_tanggal'] : '';
$where_clause = "";

if (!empty($filter_tanggal)) {
    $where_clause = "WHERE DATE(created_at) = '$filter_tanggal'";
}

// Ambil semua data surat dengan filter
$sql = "SELECT * FROM surat $where_clause ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Sistem Surat Mahasiswa</title>
        <link rel="icon" type="image/png" href="logofpsd.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .header-left h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .header-left p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header-right span {
            font-weight: 500;
        }
        
        .header-right a {
            color: white;
            text-decoration: none;
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .header-right a:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        
        /* Filter Section */
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .filter-section h3 {
            color: #dc3545;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .filter-form {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-form label {
            font-weight: 600;
            color: #555;
        }
        
        .filter-form input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .filter-form button {
            padding: 8px 15px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .filter-form button:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .reset-filter {
            background: #6c757d !important;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            color: white;
            font-weight: 600;
        }
        
        .reset-filter:hover {
            background: #5a6268 !important;
        }
        
        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #dc3545;
        }
        
        /* Actions */
        .actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 20px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            font-size: 14px;
        }
        
        .btn:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .btn-info {
            background: #17a2b8;
        }
        
        .btn-info:hover {
            background: #138496;
        }
        
        /* Table Container */
        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
            position: sticky;
            top: 0;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        /* Status Badge */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-proses {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-diambil {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 6px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 11px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
        }
        
        .btn-disabled {
            background: #6c757d;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .btn-disabled:hover {
            background: #6c757d;
            transform: none;
        }
        
        /* Messages */
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
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
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: white;
            border-radius: 10px;
            padding: 25px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .modal-header h3 {
            color: #dc3545;
            margin: 0;
            font-size: 20px;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #777;
            transition: color 0.3s;
        }
        
        .close-modal:hover {
            color: #dc3545;
        }
        
        .input-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: #dc3545;
            outline: none;
            box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.1);
        }
        
        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: flex-end;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .header-right {
                justify-content: center;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 8px 10px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .action-btn {
                width: 100%;
                margin-bottom: 5px;
            }
            
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-form input {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .modal-content {
                width: 95%;
                padding: 20px;
            }
            
            .modal-buttons {
                flex-direction: column;
            }
            
            .modal-buttons .btn {
                width: 100%;
            }
        }
        
        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #dc3545;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* No Data Styling */
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .no-data i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-left">
                <h1>Admin Panel - Sistem Surat Mahasiswa</h1>
                <p>Fakultas Pendidikan Seni dan Design - UPI</p>
            </div>
            <div class="header-right">
                <span>Halo, <?php echo $_SESSION['admin_username']; ?></span>
                <a href="?logout=true">Logout</a>
            </div>
        </header>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Section Filter -->
        <div class="filter-section">
            <h3>Filter Data</h3>
            <form method="GET" action="" class="filter-form">
                <label for="filter_tanggal">Tanggal:</label>
                <input type="date" id="filter_tanggal" name="filter_tanggal" value="<?php echo htmlspecialchars($filter_tanggal); ?>">
                <button type="submit">Filter</button>
                <?php if (!empty($filter_tanggal)): ?>
                    <a href="index.php" class="btn reset-filter">Reset Filter</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="stats-cards">
            <div class="stat-card">
                <h3>Total Pengajuan</h3>
                <div class="number"><?php echo $result->num_rows; ?></div>
            </div>
            <div class="stat-card">
                <h3>Dalam Proses</h3>
                <div class="number">
                    <?php 
                    $sql_proses = "SELECT COUNT(*) as total FROM surat WHERE status = 'dalam proses'";
                    if (!empty($filter_tanggal)) {
                        $sql_proses .= " AND DATE(created_at) = '$filter_tanggal'";
                    }
                    $result_proses = $conn->query($sql_proses);
                    echo $result_proses->fetch_assoc()['total'];
                    ?>
                </div>
            </div>
            <div class="stat-card">
                <h3>Dapat Diambil</h3>
                <div class="number">
                    <?php 
                    $sql_diambil = "SELECT COUNT(*) as total FROM surat WHERE status = 'dapat diambil'";
                    if (!empty($filter_tanggal)) {
                        $sql_diambil .= " AND DATE(created_at) = '$filter_tanggal'";
                    }
                    $result_diambil = $conn->query($sql_diambil);
                    echo $result_diambil->fetch_assoc()['total'];
                    ?>
                </div>
            </div>
        </div>
        
        <div class="actions">
            <button class="btn" onclick="openModal('addModal')">Tambah Data Surat</button>
            <a href="index.php" class="btn btn-secondary">Refresh Data</a>
            <a href="../index.php" class="btn btn-info">Kembali ke Website</a>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Mahasiswa</th>
                        <th>NIM</th>
                        <th>Email</th>
                        <th>Jenis Surat</th>
                        <th>Nomor Surat</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php $no = 1; ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_mahasiswa']); ?></td>
                                <td><?php echo htmlspecialchars($row['nim_mahasiswa']); ?></td>
                                <td><?php echo htmlspecialchars($row['email_mahasiswa']); ?></td>
                                <td><?php echo htmlspecialchars($row['jenis_surat']); ?></td>
                                <td><?php echo htmlspecialchars($row['nomor_surat'] ?: '-'); ?></td>
                                <td><?php echo $row['tanggal_bulan_tahun'] ? date('d/m/Y', strtotime($row['tanggal_bulan_tahun'])) : '-'; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $row['status'] == 'dalam proses' ? 'status-proses' : 'status-diambil'; ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($row['nomor_surat'] && $row['tanggal_bulan_tahun']): ?>
                                           
                                            <a href="cetak_surat.php?id=<?php echo $row['id']; ?>&format=pdf" class="action-btn btn-info" target="_blank">PDF</a>
                                        <?php else: ?>
                                          
                                            <button class="action-btn btn-disabled" disabled>PDF</button>
                                        <?php endif; ?>
                                        
                                        <button class="action-btn btn-warning" onclick="openNomorSuratModal(<?php echo $row['id']; ?>, '<?php echo $row['nomor_surat'] ?: ''; ?>', '<?php echo $row['tanggal_bulan_tahun'] ?: ''; ?>')">Nomor & Tanggal</button>
                                        
                                        <a href="?update_status=<?php echo $row['id']; ?>&status=<?php echo $row['status'] == 'dalam proses' ? 'dapat diambil' : 'dalam proses'; ?>&filter_tanggal=<?php echo $filter_tanggal; ?>" class="action-btn <?php echo $row['status'] == 'dalam proses' ? 'btn-success' : 'btn-warning'; ?>">
                                            <?php echo $row['status'] == 'dalam proses' ? 'Setuju' : 'Proses'; ?>
                                        </a>
                                        
                                        <button class="action-btn btn-warning" onclick="editSurat(<?php echo $row['id']; ?>)">Edit</button>
                                        
                                        <a href="?delete=<?php echo $row['id']; ?>&filter_tanggal=<?php echo $filter_tanggal; ?>" class="action-btn btn-secondary" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="no-data">
                                <div>📝</div>
                                <p>Tidak ada data surat</p>
                                <?php if (!empty($filter_tanggal)): ?>
                                    <p>Untuk tanggal: <?php echo date('d/m/Y', strtotime($filter_tanggal)); ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal Input Nomor Surat & Tanggal -->
    <div id="nomorSuratModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Input Nomor Surat & Tanggal</h3>
                <button class="close-modal" onclick="closeModal('nomorSuratModal')">&times;</button>
            </div>
            <form method="POST" action="process.php">
                <input type="hidden" name="action" value="update_nomor_tanggal">
                <input type="hidden" id="nomor_surat_id" name="id">
                
                <div class="input-group">
                    <label for="input_nomor_surat">Nomor Surat</label>
                    <input type="text" id="input_nomor_surat" name="nomor_surat" placeholder="Contoh: SK/2025/1234" required>
                    <small style="color: #666; font-size: 12px;">Format: [Kode]/[Tahun]/[Nomor]</small>
                </div>
                
                <div class="input-group">
                    <label for="input_tanggal_surat">Tanggal Surat</label>
                    <input type="date" id="input_tanggal_surat" name="tanggal_bulan_tahun" required>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('nomorSuratModal')">Batal</button>
                    <button type="submit" class="btn">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Tambah Data -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Tambah Data Surat</h3>
                <button class="close-modal" onclick="closeModal('addModal')">&times;</button>
            </div>
            <form method="POST" action="process.php">
                <input type="hidden" name="action" value="add">
                
                <div class="input-group">
                    <label for="nama_mahasiswa">Nama Mahasiswa</label>
                    <input type="text" id="nama_mahasiswa" name="nama_mahasiswa" required>
                </div>
                
                <div class="input-group">
                    <label for="nim_mahasiswa">NIM</label>
                    <input type="text" id="nim_mahasiswa" name="nim_mahasiswa" required>
                </div>
                
                <div class="input-group">
                    <label for="email_mahasiswa">Email</label>
                    <input type="email" id="email_mahasiswa" name="email_mahasiswa" required>
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
                    <label for="jenis_surat">Jenis Surat</label>
                    <select id="jenis_surat" name="jenis_surat" required>
                        <option value="">Pilih Jenis Surat</option>
                        <option value="Surat Keterangan">Surat Keterangan</option>
                        <option value="Surat Pernyataan Masih Kuliah">Surat Pernyataan Masih Kuliah</option>
                        <option value="Surat Keterangan/Rekomendasi">Surat Keterangan/Rekomendasi</option>
                    </select>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Batal</button>
                    <button type="submit" class="btn">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Edit Data -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Data Surat</h3>
                <button class="close-modal" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="POST" action="process.php">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" id="edit_id" name="id">
                
                <div class="input-group">
                    <label for="edit_nama_mahasiswa">Nama Mahasiswa</label>
                    <input type="text" id="edit_nama_mahasiswa" name="nama_mahasiswa" required>
                </div>
                
                <div class="input-group">
                    <label for="edit_nim_mahasiswa">NIM</label>
                    <input type="text" id="edit_nim_mahasiswa" name="nim_mahasiswa" required>
                </div>
                
                <div class="input-group">
                    <label for="edit_email_mahasiswa">Email</label>
                    <input type="email" id="edit_email_mahasiswa" name="email_mahasiswa" required>
                </div>
                
                <div class="input-group">
                    <label for="edit_jenjang">Jenjang</label>
                    <select id="edit_jenjang" name="jenjang" required>
                        <option value="">Pilih Jenjang</option>
                        <option value="D3">D3</option>
                        <option value="S1">S1</option>
                        <option value="S2">S2</option>
                        <option value="S3">S3</option>
                    </select>
                </div>
                
                <div class="input-group">
                    <label for="edit_semester">Semester</label>
                    <input type="number" id="edit_semester" name="semester" min="1" max="14" required>
                </div>
                
                <div class="input-group">
                    <label for="edit_program_studi">Program Studi</label>
                    <input type="text" id="edit_program_studi" name="program_studi" required>
                </div>
                
                <div class="input-group">
                    <label for="edit_alamat_lengkap_mahasiswa">Alamat Lengkap</label>
                    <textarea id="edit_alamat_lengkap_mahasiswa" name="alamat_lengkap_mahasiswa" rows="3" required></textarea>
                </div>
                
                <div class="input-group">
                    <label for="edit_tahun_akademik">Tahun Akademik</label>
                    <input type="text" id="edit_tahun_akademik" name="tahun_akademik" placeholder="Contoh: 2023/2024" required>
                </div>
                
                <div class="input-group">
                    <label for="edit_jenis_surat">Jenis Surat</label>
                    <select id="edit_jenis_surat" name="jenis_surat" required>
                        <option value="">Pilih Jenis Surat</option>
                        <option value="Surat Keterangan">Surat Keterangan</option>
                        <option value="Surat Pernyataan Masih Kuliah">Surat Pernyataan Masih Kuliah</option>
                        <option value="Surat Keterangan/Rekomendasi">Surat Keterangan/Rekomendasi</option>
                    </select>
                </div>
                
                <div class="input-group">
                    <label for="edit_status">Status</label>
                    <select id="edit_status" name="status" required>
                        <option value="dalam proses">Dalam Proses</option>
                        <option value="dapat diambil">Dapat Diambil</option>
                    </select>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Batal</button>
                    <button type="submit" class="btn">Update</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        function openNomorSuratModal(id, nomorSurat, tanggalSurat) {
            document.getElementById('nomor_surat_id').value = id;
            document.getElementById('input_nomor_surat').value = nomorSurat;
            document.getElementById('input_tanggal_surat').value = tanggalSurat;
            openModal('nomorSuratModal');
        }
        
        function editSurat(id) {
            // Show loading state
            const editBtn = event.target;
            const originalText = editBtn.innerHTML;
            editBtn.innerHTML = '<span class="loading"></span> Loading...';
            editBtn.disabled = true;
            
            // AJAX untuk mengambil data surat
            fetch('get_surat.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit_id').value = data.surat.id;
                        document.getElementById('edit_nama_mahasiswa').value = data.surat.nama_mahasiswa;
                        document.getElementById('edit_nim_mahasiswa').value = data.surat.nim_mahasiswa;
                        document.getElementById('edit_email_mahasiswa').value = data.surat.email_mahasiswa;
                        document.getElementById('edit_jenjang').value = data.surat.jenjang;
                        document.getElementById('edit_semester').value = data.surat.semester;
                        document.getElementById('edit_program_studi').value = data.surat.program_studi;
                        document.getElementById('edit_alamat_lengkap_mahasiswa').value = data.surat.alamat_lengkap_mahasiswa;
                        document.getElementById('edit_tahun_akademik').value = data.surat.tahun_akademik;
                        document.getElementById('edit_jenis_surat').value = data.surat.jenis_surat;
                        document.getElementById('edit_status').value = data.surat.status;
                        
                        openModal('editModal');
                    } else {
                        alert('Gagal mengambil data surat');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data');
                })
                .finally(() => {
                    // Reset button state
                    editBtn.innerHTML = originalText;
                    editBtn.disabled = false;
                });
        }
        
        // Tutup modal ketika klik di luar konten modal
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    closeModal(modal.id);
                }
            });
        }
        
        // Tutup modal dengan ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    if (modal.style.display === 'flex') {
                        closeModal(modal.id);
                    }
                });
            }
        });
        
        // Set today's date as default for date filter
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const filterDate = document.getElementById('filter_tanggal');
            if (filterDate && !filterDate.value) {
                filterDate.value = today;
            }
        });
    </script>
</body>
</html>