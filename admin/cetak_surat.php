<?php
session_start();
include '../config.php';

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !isset($_GET['format'])) {
    die("Parameter tidak valid");
}

$id = $_GET['id'];
$format = $_GET['format'];

// Ambil data surat
$sql = "SELECT * FROM surat WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Data surat tidak ditemukan");
}

$surat = $result->fetch_assoc();

// Cek apakah nomor surat dan tanggal sudah diisi
if (empty($surat['nomor_surat']) || empty($surat['tanggal_bulan_tahun'])) {
    die("Nomor surat dan tanggal harus diisi terlebih dahulu");
}

if ($format == 'pdf') {
    // Include FPDF 1.86
    require_once('../fpdf186/fpdf.php');
    
    // Buat PDF berdasarkan jenis surat
    switch($surat['jenis_surat']) {
        case 'Surat Keterangan':
            generateSuratKeteranganPDF($surat);
            break;
        case 'Surat Pernyataan Masih Kuliah':
            generateSuratPernyataanPDF($surat);
            break;
        case 'Surat Keterangan/Rekomendasi':
            generateSuratRekomendasiPDF($surat);
            break;
        default:
            die("Jenis surat tidak dikenali");
    }
} else {
    // Untuk format DOCX, kita berikan HTML sederhana
    header('Content-Type: text/html; charset=utf-8');
    echo generateSuratHTML($surat);
}

// ===== FUNGSI PDF =====

function generateSuratKeteranganPDF($surat) {
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetMargins(25, 40, 25); // Top margin diperbesar untuk space kop surat
    
    // Space kosong untuk kop surat yang sudah ada
    $pdf->Ln(35); // Tambahkan space kosong di atas
    
    // Set font Times New Roman untuk seluruh dokumen
    $pdf->SetFont('Times', '', 12);
    
    // Header - SURAT KETERANGAN - CENTER
    $pdf->SetFont('Times', 'B', 14);
    $pdf->Cell(0, 5, 'SURAT KETERANGAN', 0, 1, 'C');
    
    // Nomor Surat - CENTER di bawah judul
    $pdf->SetFont('Times', '', 12);
    $pdf->Cell(0, 6, 'Nomor : ' . $surat['nomor_surat'], 0, 1, 'C');
    $pdf->Ln(8);
    
    // Konten pembuka
    $pdf->MultiCell(0, 6, 'Yang bertanda tangan di bawah ini,');
    $pdf->Ln(2);
    
    // Data Pejabat
    $pdf->Cell(40, 6, 'Nama', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->SetFont('Times', 'B', 12);
    $pdf->Cell(0, 6, 'Dr. Hery Supiarza, M.Pd.', 0, 1);
    
    $pdf->SetFont('Times', '', 12);
    $pdf->Cell(40, 6, 'NIP', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, '197207212014091004', 0, 1);
    
    $pdf->Cell(40, 6, 'Pangkat dan Gol.', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, 'Pembina - IV/a', 0, 1);
    
    $pdf->Cell(40, 6, 'Jabatan', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, 'Wakil Dekan Bidang Kemahasiswaan dan Kemitraan', 0, 1);
    
    $pdf->Ln(6);
    $pdf->MultiCell(0, 6, 'dengan ini menerangkan bahwa,');
    $pdf->Ln(2);
    
    // Data Mahasiswa
    $pdf->Cell(40, 6, 'Nama', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->SetFont('Times', 'B', 12);
    $pdf->Cell(0, 6, $surat['nama_mahasiswa'], 0, 1);
    
    $pdf->SetFont('Times', '', 12);
    $pdf->Cell(40, 6, 'NIM', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $surat['nim_mahasiswa'], 0, 1);
    
    $pdf->Cell(40, 6, 'Jenjang/Semester', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $surat['jenjang'] . '/' . $surat['semester'], 0, 1);
    
    $pdf->Cell(40, 6, 'Program Studi', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $surat['program_studi'], 0, 1);
    
    $pdf->Cell(40, 6, 'Alamat', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->MultiCell(0, 6, $surat['alamat_lengkap_mahasiswa']);
    
    $pdf->Ln(6);
    
    // Isi surat
    $isi = "adalah benar yang bersangkutan tercatat sebagai mahasiswa pada Program Studi " . $surat['program_studi'] . " FPSD UPI, terhitung Semester Ganjil Tahun Akademik " . $surat['tahun_akademik'] . " dan sampai saat ini masih aktif/terdaftar.";
    $pdf->MultiCell(0, 6, $isi);
    
    $pdf->Ln(6);
    
    $penutup = "Demikian surat keterangan ini dibuat atas permintaan yang bersangkutan untuk keperluan " . ($surat['alasan_keperluan'] ?: '-') . ".";
    $pdf->MultiCell(0, 6, $penutup);
    
    $pdf->Ln(15);
    
    // Tanda tangan - ALIGN LEFT but positioned on right side
    $rightMargin = 120; // Position from left to push to right
    $pdf->SetX($rightMargin);
    $pdf->Cell(0, 6, 'Bandung, ' . date('d F Y', strtotime($surat['tanggal_bulan_tahun'])), 0, 1, 'L');
    
    $pdf->SetX($rightMargin);
    $pdf->Cell(0, 6, 'Wakil Dekan Bidang Kemahasiswaan', 0, 1, 'L');
    
    $pdf->SetX($rightMargin);
    $pdf->Cell(0, 6, 'Dan Kemitraan,', 0, 1, 'L');
    
    $pdf->Ln(20);
    
    $pdf->SetX($rightMargin);
    $pdf->SetFont('Times', 'B', 12);
    $pdf->Cell(0, 6, 'Hery Supiarza', 0, 1, 'L');
    
    // Output PDF dengan nama file yang bagus
    $filename = 'Surat_Keterangan_' . $surat['nim_mahasiswa'] . '_' . str_replace(' ', '_', $surat['nama_mahasiswa']) . '.pdf';
    $pdf->Output('I', $filename);
}

function generateSuratRekomendasiPDF($surat) {
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetMargins(25, 35, 25); // Top margin diperbesar untuk space kop surat
    
    // Space kosong untuk kop surat yang sudah ada
    $pdf->Ln(40); // Tambahkan space kosong di atas
    
    // Set font Times New Roman
    $pdf->SetFont('Times', '', 12);
    
    // Header - CENTER
    $pdf->SetFont('Times', 'B', 14);
    $pdf->Cell(0, 5, 'SURAT KETERANGAN/REKOMENDASI', 0, 1, 'C');
    
    // Nomor Surat - CENTER di bawah judul
    $pdf->SetFont('Times', '', 12);
    $pdf->Cell(0, 5, 'Nomor : ' . $surat['nomor_surat'], 0, 1, 'C');
    $pdf->Ln(8);
    
    // Konten pembuka
    $pdf->MultiCell(0, 5, 'Yang bertanda tangan di bawah ini,');
    $pdf->Ln(2);
    
    // Data Pejabat
    $pdf->Cell(40, 6, 'Nama', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->SetFont('Times', 'B', 12);
    $pdf->Cell(0, 6, 'Dr. Hery Supiarza, M.Pd.', 0, 1);
    
    $pdf->SetFont('Times', '', 12);
    $pdf->Cell(40, 6, 'NIP', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, '197207212014091004', 0, 1);
    
    $pdf->Cell(40, 6, 'Pangkat dan Gol.', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, 'Pembina - IV/a', 0, 1);
    
    $pdf->Cell(40, 6, 'Jabatan', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, 'Wakil Dekan Bidang Kemahasiswaan dan Kemitraan', 0, 1);
    
    $pdf->Ln(6);
    $pdf->MultiCell(0, 6, 'dengan ini menerangkan bahwa,');
    $pdf->Ln(2);
    
    // Data Mahasiswa
    $pdf->Cell(40, 6, 'Nama', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->SetFont('Times', 'B', 12);
    $pdf->Cell(0, 6, $surat['nama_mahasiswa'], 0, 1);
    
    $pdf->SetFont('Times', '', 12);
    $pdf->Cell(40, 6, 'NIM', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $surat['nim_mahasiswa'], 0, 1);
    
    $pdf->Cell(40, 6, 'Program Studi', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $surat['program_studi'], 0, 1);
    
    $pdf->Cell(40, 6, 'Jenjang/Semester', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $surat['jenjang'] . '/' . $surat['semester'], 0, 1);
    
    $pdf->Cell(40, 6, 'IPK', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $surat['ipk'] ?: '-', 0, 1);
    
    $pdf->Ln(6);
    
    // Isi surat
    $pdf->MultiCell(0, 6, 'adalah benar - benar mahasiswa pada Program studi seperti tersebut di atas, dan sepengetahuan kami mahasiswa bersangkutan');
    $pdf->Ln(5);
    
    $pdf->Cell(10, 6, '', 0, 0);
    $pdf->Cell(5, 6, '(1)', 0, 0);
    $pdf->Cell(0, 6, 'tidak sedang cuti akademik;', 0, 1);
    
    $pdf->Cell(10, 6, '', 0, 0);
    $pdf->Cell(5, 6, '(2)', 0, 0);
    $pdf->Cell(0, 6, 'tidak sedang mengusulkan dan menerima beasiswa lainnya;', 0, 1);
    
    $pdf->Cell(10, 6, '', 0, 0);
    $pdf->Cell(5, 6, '(3)', 0, 0);
    $pdf->Cell(0, 6, 'bukan mahasiswa kerjasama;', 0, 1);
    
    $pdf->Cell(10, 6, '', 0, 0);
    $pdf->Cell(5, 6, '(4)', 0, 0);
    $pdf->Cell(0, 6, 'tidak melanggar tata tertib kampus.', 0, 1);
    
    $pdf->Ln(6);
    
    $penutup = "Demikian surat keterangan/rekomendasi ini kami buat sebagai syarat pengajuan/permohonan persyaratan Beasiswa Jenius Tahun 2025.";
    $pdf->MultiCell(0, 6, $penutup);
    
    $pdf->Ln(15);
    
    // Tanda tangan - ALIGN LEFT but positioned on right side
    $rightMargin = 120; // Position from left to push to right
    $pdf->SetX($rightMargin);
    $pdf->Cell(0, 6, 'Bandung, ' . date('d F Y', strtotime($surat['tanggal_bulan_tahun'])), 0, 1, 'L');
    
    $pdf->SetX($rightMargin);
    $pdf->Cell(0, 6, 'Wakil Dekan Bidang Kemahasiswaan', 0, 1, 'L');
    
    $pdf->SetX($rightMargin);
    $pdf->Cell(0, 6, 'dan Kemitraan,', 0, 1, 'L');
    
    $pdf->Ln(20);
    
    $pdf->SetX($rightMargin);
    $pdf->SetFont('Times', 'B', 12);
    $pdf->Cell(0, 6, 'Hery Supiarza', 0, 1, 'L');
    
    // Output PDF dengan nama file yang bagus
    $filename = 'Surat_Rekomendasi_' . $surat['nim_mahasiswa'] . '_' . str_replace(' ', '_', $surat['nama_mahasiswa']) . '.pdf';
    $pdf->Output('I', $filename);
}

function generateSuratPernyataanPDF($surat) {
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetMargins(25, 0, 25); // Margin top = 0
    
    // Langsung mulai dari posisi Y yang sangat kecil
    $pdf->SetY(5); // Mulai dari 5mm dari atas
    
    // Set font Arial untuk kop surat
    $pdf->SetFont('Arial', '', 9);
    
    // Kop Surat - diposisikan agak ke center
    $startX = 35; // Mulai dari posisi lebih ke tengah
    
    $pdf->SetX($startX);
    $pdf->Cell(40, 6, 'LAMPIRAN I :', 0, 0, 'L');
    $pdf->Cell(0, 6, 'SURAT EDARAN BERSAMA MENTERI KEUANGAN DAN', 0, 1, 'L');
    
    $pdf->SetX($startX + 40); // Indent untuk line kedua
    $pdf->Cell(0, 6, 'KEPALA BADAN ADMINISTRASI KEPEGAWAIAN NEGARA', 0, 1, 'L');
    
    $pdf->Ln(0); // Spasi kecil
    
    $pdf->SetX($startX + 40); // Indent untuk nomor
    $pdf->Cell(25, 6, 'NOMOR', 0, 0, 'L');
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, 'SE-138/DJA/I/O/7/1980', 0, 1, 'L');
    
    $pdf->SetX($startX + 70); // Indent untuk line kedua nomor
    $pdf->Cell(0, 6, 'SE/117/1980', 0, 1, 'L');
    
    $pdf->SetX($startX + 40); // Indent untuk nomor
    $pdf->Cell(25, 6, 'NOMOR', 0, 0, 'L');
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, '19/SE/1980', 0, 1, 'L');
    
    $pdf->SetX($startX + 40); // Indent untuk tanggal
    $pdf->Cell(25, 6, 'TANGGAL', 0, 0, 'L');
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, '7 JULI 1980', 0, 1, 'L');
    
  // Garis tipis dan tebal di bawah kop surat - dari tengah ke kanan
    $pdf->Ln(0.05); // Spasi kecil sebelum garis
    $y = $pdf->GetY(); // Dapatkan posisi Y saat ini
    
    // Garis tipis - dari tengah (90mm) ke kanan (185mm)
    $pdf->SetLineWidth(0.05);
  $pdf->Line(75, $y, 185, $y);        // Dari 60mm ke kanan
    
    // Garis tebal - dari tengah (90mm) ke kanan (185mm)
    $pdf->SetLineWidth(0.05);
    $pdf->Line(75, $y + 1, 185, $y + 1);
    
    $pdf->Ln(5); // Spasi kecil setelah garis
    
    // Set font Times New Roman untuk isi surat
    $pdf->SetFont('Times', '', 12);
    
    // Header - CENTER
    $pdf->SetFont('Times', 'B', 14);
    $pdf->Cell(0, 5, 'SURAT PERNYATAAN MASIH KULIAH', 0, 1, 'C');
    
    // Nomor Surat - CENTER di bawah judul
    $pdf->SetFont('Times', '', 12);
    $pdf->Cell(0, 6, 'Nomor : ' . $surat['nomor_surat'], 0, 1, 'C');
    $pdf->Ln(3);
    
    // Konten pembuka
    $pdf->MultiCell(0, 6, 'Yang bertanda tangan di bawah ini');
    $pdf->Ln(2);
    
    // Data Pejabat dengan numbering
    $pdf->Cell(10, 6, '1.', 0, 0);
    $pdf->Cell(45, 6, 'Nama', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->SetFont('Times', 'B', 12);
    $pdf->Cell(0, 6, 'Dr. Hery Supiarza, M.Pd.', 0, 1);
    
    $pdf->SetFont('Times', '', 12);
    $pdf->Cell(10, 6, '2.', 0, 0);
    $pdf->Cell(45, 6, 'N I P', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, '197207212014091004', 0, 1);
    
    $pdf->Cell(10, 6, '3.', 0, 0);
    $pdf->Cell(45, 6, 'Pangkat/Golongan/Ruang', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, 'Pembina - IV/a', 0, 1);
    
    $pdf->Cell(10, 6, '4.', 0, 0);
    $pdf->Cell(45, 6, 'Jabatan', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->MultiCell(0, 6, 'Wakil Dekan Bidang Kemahasiswaan dan Kemitraan');
    
    $pdf->Cell(10, 6, '5.', 0, 0);
    $pdf->Cell(45, 6, 'Fakultas/Universitas', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->MultiCell(0, 6, 'Fakultas Pendidikan Seni dan Desain Universitas Pendidikan Indonesia');
    
    $pdf->Ln(6);
    $pdf->MultiCell(0, 6, 'menyatakan dengan sesungguhnya bahwa :');
    $pdf->Ln(2);
    
    // Data Mahasiswa dengan numbering
    $lama_studi = $surat['lama_masa_studi'] ?: (ceil($surat['semester'] / 2) . " tahun");
    
    $pdf->Cell(10, 6, '6.', 0, 0);
    $pdf->Cell(50, 6, 'Nama Mahasiswa', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->SetFont('Times', 'B', 12);
    $pdf->Cell(0, 6, $surat['nama_mahasiswa'], 0, 1);
    
    $pdf->SetFont('Times', '', 12);
    $pdf->Cell(10, 6, '7.', 0, 0);
    $pdf->Cell(50, 6, 'NIM', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $surat['nim_mahasiswa'], 0, 1);
    
    $pdf->Cell(10, 6, '8.', 0, 0);
    $pdf->Cell(50, 6, 'Departemen/Program', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $surat['program_studi'], 0, 1);
    
    $pdf->Cell(10, 6, '9.', 0, 0);
    $pdf->Cell(50, 6, 'Jenjang/Semester', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $surat['jenjang'] . '/' . $surat['semester'], 0, 1);
    
    $pdf->Cell(10, 6, '10.', 0, 0);
    $pdf->Cell(50, 6, 'Lama masa studi', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $lama_studi, 0, 1);
    
    $pdf->Cell(10, 6, '11.', 0, 0);
    $pdf->Cell(50, 6, 'Pada tahun ajaran', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, $surat['tahun_akademik'], 0, 1);
    
    $pdf->Ln(6);
    $pdf->MultiCell(0, 6, 'benar-benar mahasiswa kami, dan wali anak tersebut adalah,');
    $pdf->Ln(2);
    
    // Data Orang Tua dengan numbering
    $pdf->Cell(10, 6, '12.', 0, 0);
    $pdf->Cell(45, 6, 'Nama orang tua', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->SetFont('Times', 'B', 12);
    $pdf->Cell(0, 6, 'Shinta Praffthri Agustiana, S.Kom.', 0, 1);
    
    $pdf->SetFont('Times', '', 12);
    $pdf->Cell(10, 6, '13.', 0, 0);
    $pdf->Cell(45, 6, 'NIP/NRP', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, '197809102001122002', 0, 1);
    
    $pdf->Cell(10, 6, '14.', 0, 0);
    $pdf->Cell(45, 6, 'Pangkat, Gol./Ruang', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, 'Penata Tingkat I - III/d', 0, 1);
    
    $pdf->Cell(10, 6, '15.', 0, 0);
    $pdf->Cell(45, 6, 'Instansi', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, 'Universitas Pendidikan Indonesia', 0, 1);
    
    $pdf->Cell(10, 6, '16.', 0, 0);
    $pdf->Cell(45, 6, 'Alamat', 0, 0);
    $pdf->Cell(5, 6, ':', 0, 0);
    $pdf->Cell(0, 6, 'Komplek Pondok Mutiara X No. 15 Cimahi.', 0, 1);
    
    $pdf->Ln(6);
    
    // Penutup
    $penutup = "Demikian surat pernyataan ini dibuat dengan sesungguhnya, apabila di kemudian hari surat pernyataan ini tidak benar yang mengakibatkan kerugian terhadap Negara Republik Indonesia, maka saya bersedia menanggung kerugian tersebut.";
    $pdf->MultiCell(0, 6, $penutup);
    
    $pdf->Ln(8);
    
    // Tanda tangan - ALIGN LEFT but positioned on right side
    $rightMargin = 120; // Position from left to push to right
    $pdf->SetX($rightMargin);
    $pdf->Cell(0, 6, 'Bandung, ' . date('d F Y', strtotime($surat['tanggal_bulan_tahun'])), 0, 1, 'L');
    
    $pdf->SetX($rightMargin);
    $pdf->Cell(0, 6, 'Wakil Dekan Bidang Kemahasiswaan', 0, 1, 'L');
    
    $pdf->SetX($rightMargin);
    $pdf->Cell(0, 6, 'dan Kemitraan,', 0, 1, 'L');
    
    $pdf->Ln(20);
    
    $pdf->SetX($rightMargin);
    $pdf->SetFont('Times', 'B', 12);
    $pdf->Cell(0, 6, 'Hery Supiarza', 0, 1, 'L');
    
    // Output PDF dengan nama file yang bagus
    $filename = 'Surat_Pernyataan_' . $surat['nim_mahasiswa'] . '_' . str_replace(' ', '_', $surat['nama_mahasiswa']) . '.pdf';
    $pdf->Output('I', $filename);
}

// Fungsi untuk HTML (fallback)
function generateSuratHTML($surat) {
    return "
    <html>
    <body>
        <h1>Surat " . $surat['jenis_surat'] . "</h1>
        <p>Untuk mendapatkan file PDF, pastikan FPDF sudah terinstall dengan benar.</p>
        <p>NIM: " . $surat['nim_mahasiswa'] . "</p>
        <p>Nama: " . $surat['nama_mahasiswa'] . "</p>
    </body>
    </html>";
}
?>