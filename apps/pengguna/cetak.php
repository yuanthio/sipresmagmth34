<?php
// Sertakan pustaka FPDF
session_start();
require('../../source/plugin/fpdf/fpdf.php');

// Termasuk file konfigurasi database
include '../../config/database.php';
include '../../config/function.php';

// Dapatkan data dari server
$id_mahasiswa = $_POST['id_mahasiswa'];

// Buat objek FPDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// Kop Surat
$pdf->SetFont('Times', 'B', 12);

$judul1 = 'FORM PENILAIAN PRAKTIK KERJA LAPANGAN';
$pdf->Cell(0, 10, '', 0, 1); // Spasi kosong
$pdf->Cell(0, 6, $judul1, 0, 1, 'C'); // Cetak teks pertama di posisi tengah

$judul2 = 'BPK PERWAKILAN PROVINSI DKI JAKARTA';
$pdf->Cell(0, 6, $judul2, 'B', 1, 'C'); // Cetak teks kedua di posisi tengah dengan garis bawah


// Mengambil informasi mahasiswa
$sql = "select * from tbl_mahasiswa where id_mahasiswa = $id_mahasiswa";
$hasil = mysqli_query($kon, $sql);
$data = mysqli_fetch_array($hasil);

// Mengambil informasi awal dan akhir magang
$awal_magang = $data['mulai_magang'];
$akhir_magang = $data['akhir_magang'];
$mulai_bulan = date("m", strtotime($awal_magang));
$akhir_bulan = date("m", strtotime($akhir_magang));
$mulai_hari = date("d", strtotime($awal_magang));
$akhir_hari = date("d", strtotime($akhir_magang));
$akhir_tahun = date("Y", strtotime($akhir_magang));

// Data mentor
$pdf->SetFont('Times', '', 12);
$pdf->Cell(0, 6, '', 0, 1); // Spasi kosong
$pdf->Cell(0, 6, 'Saya yang bertanda tangan dibawah ini:', 0, 1);
$pdf->Cell(60, 6, 'Nama', 0, 0);
$pdf->Cell(60, 6, ': ' . $data['mentor'], 0, 1);
$pdf->Cell(60, 6, 'NIP', 0, 0);
$pdf->Cell(60, 6, ': ' . $data['nip_mentor'], 0, 1);
$pdf->Cell(60, 6, 'Jabatan', 0, 0);
$pdf->Cell(60, 6, ': ' . $data['jabatan_mentor'], 0, 1);
$pdf->Cell(60, 6, 'Selaku Mentor / Supervisor', 0, 0);
$pdf->Cell(60, 6, '', 0, 1);
$pdf->Cell(60, 6, 'PKL pada Unit Kerja', 0, 0);
$pdf->Cell(60, 6, ': ' . $data['unit_kerja'], 0, 1);
$pdf->Cell(0, 5, '', 0, 1);

$pdf->MultiCell(0, 6, 'Setelah Memperhatikan, mengamati, serta mengevaluasi mahasiswa/siswi PKL yang namanya tercantum dibawah ini :', 0, 'J');

// Data siswa/mahasiswa PKL
$pdf->SetFont('Times', '', 12);
$pdf->Cell(60, 6, 'Nama', 0, 0);
$pdf->Cell(60, 6, ': ' . $data['nama'], 0, 1);
$pdf->Cell(60, 6, 'NIM / NIS', 0, 0);
$pdf->Cell(60, 6, ': ' . $data['nim'], 0, 1);
$pdf->Cell(60, 6, 'Universitas / Sekolah', 0, 0);
$pdf->Cell(60, 6, ': ' . $data['universitas'], 0, 1);
$pdf->Cell(60, 6, 'Program Studi / Jurusan', 0, 0);
$pdf->Cell(60, 6, ': ' . $data['jurusan'], 0, 1);
$pdf->Cell(60, 6, 'Alamat', 0, 0);
$pdf->Cell(60, 6, ': ' . $data['alamat'], 0, 1); 
$pdf->Cell(0, 5, '', 0, 1);

// Tambahan tulisan di bawah data mentor
$pdf->MultiCell(0, 6, 'Untuk pelaksanaan PKL dari tanggal ' . $mulai_hari . ' ' . MendapatkanAwalBulan($mulai_bulan) . ' sampai ' . $akhir_hari . ' ' . MendapatkanAkhirBulan($akhir_bulan) . ' ' . $akhir_tahun . ' bagi mahasiswa/i yang bersangkutan diberikan penilaian sebagai berikut :', 0, 'J');

// tabel Penilaian
$pdf->Cell(0, 7, '', 0, 1);
$pdf->SetFont('Times', 'B', 12);

// Jumlah Baris pada Tabel Penilaian (contoh 5 baris)
$jumlahBaris = 5;

// Lebar Sel
$lebarSel1 = 75;
$lebarSel2 = 75;

// Hitung Lebar Tabel
$lebarTabel = $lebarSel1 + $lebarSel2;

// Hitung Posisi X Tengah Tabel
$posisiXTengah = ($pdf->GetPageWidth() - $lebarTabel) / 2;

// Set Posisi X Tengah
$pdf->SetX($posisiXTengah);

$pdf->Cell($lebarSel1, 6, 'Kriteria Penilaian', 1, 0, 'C');
$pdf->Cell($lebarSel2, 6, 'Nilai yang Diberikan (antara 0-100)', 1, 1, 'C');

$pdf->SetFont('Times', '', 12);

// Mengambil data nilai dari hasil query
$nilaiKehadiran = $data['nilai_kehadiran']; 

// Mengambil data nilai dari tabel nilai
$sqlNilai = "SELECT * FROM tbl_nilai WHERE id_mahasiswa = $id_mahasiswa";
$hasilNilai = mysqli_query($kon, $sqlNilai);
$dataNilai = mysqli_fetch_array($hasilNilai);

$pdf->SetX($posisiXTengah);
$pdf->Cell($lebarSel1, 6, 'Kehadiran dan Kedisplinan', 1, 0, 'L');
$pdf->Cell($lebarSel2, 6, $nilaiKehadiran, 1, 1, 'C');

$pdf->SetX($posisiXTengah);
$pdf->Cell($lebarSel1, 6, 'Keaktifan dan Tanggung Jawab', 1, 0, 'L');
$pdf->Cell($lebarSel2, 6, empty($dataNilai['keaktifan']) ? 0 : $dataNilai['keaktifan'], 1, 1, 'C'); 

$pdf->SetX($posisiXTengah);
$pdf->Cell($lebarSel1, 6, 'Kreatifitas dan Inisiatif', 1, 0, 'L');
$pdf->Cell($lebarSel2, 6, empty($dataNilai['kreatifitas']) ? 0 : $dataNilai['kreatifitas'], 1, 1, 'C'); 

$pdf->SetX($posisiXTengah);
$pdf->Cell($lebarSel1, 6, 'Kepatuhan dan Loyalitas', 1, 0, 'L');
$pdf->Cell($lebarSel2, 6, empty($dataNilai['kepatuhan']) ? 0 : $dataNilai['kepatuhan'], 1, 1, 'C'); 

$pdf->SetX($posisiXTengah);
$pdf->Cell($lebarSel1, 6, 'Kepribadian dan Tingkah Laku', 1, 0, 'L');
$pdf->Cell($lebarSel2, 6, empty($dataNilai['tingkah_laku']) ? 0 : $dataNilai['tingkah_laku'], 1, 1, 'C'); 

$pdf->SetX($posisiXTengah);
$pdf->Cell($lebarSel1, 6, 'Keahlian', 1, 0, 'L');
$pdf->Cell($lebarSel2, 6, empty($dataNilai['keahlian']) ? 0 : $dataNilai['keahlian'], 1, 1, 'C'); 

$pdf->SetFont('Times', 'B', 12);
$pdf->SetX($posisiXTengah);
$pdf->Cell($lebarSel1, 6, 'Jumlah', 1, 0, 'R');
$pdf->SetFont('Times', '', 12);
$pdf->Cell($lebarSel2, 6, empty($dataNilai['jumlah']) ? 0 : $dataNilai['jumlah'], 1, 1, 'C'); 

$pdf->SetFont('Times', 'B', 12);
$pdf->SetX($posisiXTengah);
$pdf->Cell($lebarSel1, 6, 'Rata-rata', 1, 0, 'R');
$pdf->SetFont('Times', '', 12);
$pdf->Cell($lebarSel2, 6, empty($dataNilai['rata_rata']) ? 0 : $dataNilai['rata_rata'], 1, 1, 'C'); 


$pdf->Cell(0, 6, '', 0, 1);
$pdf->SetFont('Times', '', 12);
$pdf->MultiCell(0, 6, 'Dengan demikian penilaian diberikan atas dasar keadaan yang sebenarnya dan agar dapat dipergunakan sebagai mestinya,  serta akan ditinjau kembali jika ada kekeliruan dikemudian hari.', 0, 'J');

// Set zona waktu ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

setlocale(LC_TIME, 'id_ID'); // Set locale ke bahasa Indonesia

function translateMonth($englishMonth) {
    $monthTranslations = [
        'January' => 'Januari',
        'February' => 'Februari',
        'March' => 'Maret',
        'April' => 'April',
        'May' => 'Mei',
        'June' => 'Juni',
        'July' => 'Juli',
        'August' => 'Agustus',
        'September' => 'September',
        'October' => 'Oktober',
        'November' => 'November',
        'December' => 'Desember',
    ];

    return $monthTranslations[$englishMonth];
}

$date = new DateTime();
$translatedMonth = translateMonth($date->format('F'));

$pdf->Cell(320, 14, 'Jakarta, ' . $date->format('d') . ' ' . $translatedMonth . ' ' . $date->format('Y'), 0, 1, 'C');
$pdf->Cell(0, 15, '', 0, 1);
$pdf->SetFont('Times', '', 12);
$pdf->Cell(320, 0, $data['mentor'], 0, 1, 'C');
$pdf->Cell(0, 13, '', 0, 1);

$pdf->SetFont('Times', 'I', 12);
$pdf->MultiCell(0, 6, 'Catatan :', 0, 1);
$pdf->MultiCell(0, 7, 'Mohon hasil penelitian diserahkan kepada Subbagian Humas setelah kegiatan PKL berakhir.', 0, 1);

// Set zona waktu ke Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');

// Mendapatkan waktu sekarang
$tanggal_sekarang = date("Y-m-d H:i:s");

// Mengambil informasi admin
$kode_pengguna = $_SESSION['kode_pengguna']; // Pastikan ini sesuai dengan sesi yang Anda gunakan
$queryUser = "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'";
$resultUser = mysqli_query($kon, $queryUser);
$userInfo = mysqli_fetch_array($resultUser);
$level = $userInfo['level'];

// Mengambil nama admin
$queryAdmin = "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'";
$resultAdmin = mysqli_query($kon, $queryAdmin);
$adminInfo = mysqli_fetch_array($resultAdmin);
$nama_admin = $adminInfo['nama'];

// Menyimpan aktivitas ke tabel log
$aktivitas = "Cetak data penilaian kinerja mahasiswa";
$status = "berhasil"; // Sesuaikan ini dengan logika jika ada kesalahan
$queryLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) VALUES ('$tanggal_sekarang', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status')";
mysqli_query($kon, $queryLog);

// Output file PDF sebagai base64
$pdfData = $pdf->Output('S');
$pdfBase64 = base64_encode($pdfData);
echo $pdfBase64;

// Tutup koneksi database
mysqli_close($kon);
?>