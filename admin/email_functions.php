<?php
// Include PHPMailer manual (tanpa Composer)
require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmailNotification($to, $subject, $body, $nama_mahasiswa) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to, $nama_mahasiswa);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email tidak terkirim: " . $mail->ErrorInfo);
        return false;
    }
}

function sendSuratApprovalEmail($email, $nama_mahasiswa, $jenis_surat, $nomor_surat) {
    $subject = "Surat $jenis_surat Telah Disetujui - FPSD UPI";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc3545; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            .info-box { background: white; padding: 15px; border-left: 4px solid #dc3545; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Fakultas Pendidikan Seni dan Desain</h2>
                <h3>Universitas Pendidikan Indonesia</h3>
            </div>
            <div class='content'>
                <h3>Halo $nama_mahasiswa,</h3>
                <p>Kami informasikan bahwa surat $jenis_surat Anda dengan detail berikut:</p>
                
                <div class='info-box'>
                    <p><strong>Nomor Surat:</strong> $nomor_surat</p>
                    <p><strong>Jenis Surat:</strong> $jenis_surat</p>
                    <p><strong>Status:</strong> ✅ Telah disetujui dan ditandatangani</p>
                </div>
                
                <p><strong>📌 Surat Anda sudah dapat diambil di:</strong></p>
                <p><strong>Fakultas Pendidikan Seni dan Desain UPI<br>
                Jl. Dr. Setiabudhi No.229, Bandung</strong></p>
                
                <p><strong>🕐 Waktu Pengambilan:</strong><br>
                Senin - Jumat: 09.00 - 16.00 WIB</p>
                
                <p><strong>📋 Persyaratan Pengambilan:</strong><br>
                - Menunjukkan email ini</p>
                
                <p>Jika ada pertanyaan, silakan hubungi administrasi FPSD.</p>
                
                <p>Terima kasih.</p>
            </div>
            <div class='footer'>
                <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
                <p>&copy; " . date('Y') . " FPSD UPI. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmailNotification($email, $subject, $body, $nama_mahasiswa);
}
?>