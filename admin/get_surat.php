<?php
session_start();
include '../config.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $sql = "SELECT * FROM surat WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $surat = $result->fetch_assoc();
        echo json_encode(['success' => true, 'surat' => $surat]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
}
?>