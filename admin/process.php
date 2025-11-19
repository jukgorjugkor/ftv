<?php
session_start();
include '../config.php';
include 'email_functions.php'; // Include fungsi email

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'add') {
        // Tambah data surat baru
        $nama_mahasiswa = $_POST['nama_mahasiswa'];
        $nim_mahasiswa = $_POST['nim_mahasiswa'];
        $email_mahasiswa = $_POST['email_mahasiswa']; // Tambahan field email
        $jenjang = $_POST['jenjang'];
        $semester = $_POST['semester'];
        $program_studi = $_POST['program_studi'];
        $alamat_lengkap_mahasiswa = $_POST['alamat_lengkap_mahasiswa'];
        $tahun_akademik = $_POST['tahun_akademik'];
        $jenis_surat = $_POST['jenis_surat'];
        
        // Hitung lama masa studi otomatis
        $lama_masa_studi = ceil($semester / 2) . " tahun";
        
        // Set nilai default untuk IPK dan alasan berdasarkan jenis surat
        $ipk = null;
        $alasan_keperluan = null;
        
        if ($jenis_surat == 'Surat Keterangan/Rekomendasi') {
            $ipk = 3.50; // Default IPK untuk rekomendasi
        } elseif ($jenis_surat == 'Surat Keterangan') {
            $alasan_keperluan = 'Keperluan Administrasi';
        }
        
        $sql = "INSERT INTO surat (nama_mahasiswa, nim_mahasiswa, email_mahasiswa, jenjang, semester, program_studi, alamat_lengkap_mahasiswa, tahun_akademik, lama_masa_studi, ipk, alasan_keperluan, jenis_surat, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'dalam proses', NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssissssdsss", $nama_mahasiswa, $nim_mahasiswa, $email_mahasiswa, $jenjang, $semester, $program_studi, $alamat_lengkap_mahasiswa, $tahun_akademik, $lama_masa_studi, $ipk, $alasan_keperluan, $jenis_surat);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Data surat berhasil ditambahkan!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Gagal menambahkan data: " . $stmt->error;
            $_SESSION['message_type'] = "error";
        }
        
    } elseif ($action == 'edit') {
        // Edit data surat
        $id = $_POST['id'];
        $nama_mahasiswa = $_POST['nama_mahasiswa'];
        $nim_mahasiswa = $_POST['nim_mahasiswa'];
        $email_mahasiswa = $_POST['email_mahasiswa']; // Tambahan field email
        $jenjang = $_POST['jenjang'];
        $semester = $_POST['semester'];
        $program_studi = $_POST['program_studi'];
        $alamat_lengkap_mahasiswa = $_POST['alamat_lengkap_mahasiswa'];
        $tahun_akademik = $_POST['tahun_akademik'];
        $jenis_surat = $_POST['jenis_surat'];
        $status = $_POST['status'];
        
        // Update lama masa studi berdasarkan semester baru
        $lama_masa_studi = ceil($semester / 2) . " tahun";
        
        $sql = "UPDATE surat SET 
                nama_mahasiswa = ?, 
                nim_mahasiswa = ?, 
                email_mahasiswa = ?,
                jenjang = ?, 
                semester = ?, 
                program_studi = ?, 
                alamat_lengkap_mahasiswa = ?, 
                tahun_akademik = ?, 
                lama_masa_studi = ?,
                jenis_surat = ?, 
                status = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssissssssi", $nama_mahasiswa, $nim_mahasiswa, $email_mahasiswa, $jenjang, $semester, $program_studi, $alamat_lengkap_mahasiswa, $tahun_akademik, $lama_masa_studi, $jenis_surat, $status, $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Data surat berhasil diupdate!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Gagal mengupdate data: " . $stmt->error;
            $_SESSION['message_type'] = "error";
        }
        
    } elseif ($action == 'update_nomor_tanggal') {
        // Update nomor surat dan tanggal
        $id = $_POST['id'];
        $nomor_surat = $_POST['nomor_surat'];
        $tanggal_bulan_tahun = $_POST['tanggal_bulan_tahun'];
        
        $sql = "UPDATE surat SET 
                nomor_surat = ?, 
                tanggal_bulan_tahun = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nomor_surat, $tanggal_bulan_tahun, $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Nomor surat dan tanggal berhasil diupdate!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Gagal mengupdate nomor surat dan tanggal: " . $stmt->error;
            $_SESSION['message_type'] = "error";
        }
    }
}

header('Location: index.php');
exit;
?>