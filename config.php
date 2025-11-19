<?php
$host = "localhost";
$username = "rotz3716_si-fpsd";
$password = "MPWfpsd23$";
$database = "rotz3716_si-fpsd";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Konfigurasi Email
define('SMTP_HOST', 'mail.sleepwellindonesia.com');
define('SMTP_USERNAME', 'no-reply@sleepwellindonesia.com');
define('SMTP_PASSWORD', 'Otongkecil6a'); // Ganti dengan password email yang benar
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('FROM_EMAIL', 'no-reply@sleepwellindonesia.com');
define('FROM_NAME', 'FPSD Mail');

// Default password untuk admin: password
// Hash password menggunakan: password_hash('password', PASSWORD_DEFAULT)
?>