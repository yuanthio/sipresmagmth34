<?php
// Sertakan pustaka FPDF
require('../../source/plugin/fpdf/fpdf.php');

// Termasuk file konfigurasi database
include '../../config/database.php';
include '../../config/function.php';

// Dapatkan data dari server
$id_mahasiswa = $_POST['id_mahasiswa'];

// Mengambil informasi instansi dari database
$query = mysqli_query($kon, "select * from tbl_site limit 1");
$row = mysqli_fetch_array($query);

// Buat objek FPDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// Kop Surat
$pdf->SetFont('Times', 'B', 12);
$pdf->Cell(0, 10, '', 0, 1); 
// Tambahkan gambar dengan posisi rata tengah
$pdf->Image('../../apps/pengaturan/logo/' . $row['logo'], 95, 5, 20, 20);
$pdf->Cell(0, 5, '', 0, 1); 
$pdf->Cell(0, 6, 'BADAN PEMERIKSA KEUANGAN REPUBLIK INDONESIA', 0, 1, 'C');
$pdf->Cell(0, 6, 'PERWAKILAN PROVINSI DKI JAKARTA', 0, 1, 'C');

// Menetapkan alamat instansi dan informasi kontak
$pdf->SetFont('Times', '', 12);
$pdf->Cell(0, 7, 'Jl. MT.Haryono Kav. 34 Jakarta Selatan' . ', Telp. ' . $row['no_telp'], 0, 1, 'C');

// Menggambar garis di bawah informasi instansi
$pdf->SetLineWidth(0.8);  // Ketebalan garis atas
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Cell(0, 0.5, '', 0, 1); 
$pdf->SetLineWidth(0.5);  // Ketebalan garis bawah
$pdf->Line(10, $pdf->GetY() + 0.5, 200, $pdf->GetY() + 0.5);

// Menambahkan jarak
$pdf->Cell(0, 5, '', 0, 1);  // Tambahkan beberapa baris kosong

$pdf->SetFont('Times', 'B', 12);
$pdf->Cell(0, 6, 'SURAT KETERANGAN', 0, 1, 'C');

$pdf->SetFont('Times', '', 12);
$dateNow = new DateTime();
$monthYear = $dateNow->format('n/Y'); // Format bulan dan tahun (n: angka bulan, Y: tahun empat digit)
$text = 'No.     /S.Ket/XVIII.JKT.1/' . $monthYear;
$pdf->Cell(0, 7, $text, 0, 1, 'C');

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

$pdf->Cell(0, 10, '', 0, 1);
$pdf->MultiCell(0, 7, 'Yang bertanda tangan dibawah ini :', 0, 1);

// Data sekre
$pdf->SetFont('Times', '', 12);

$pdf->SetX(20);
$pdf->Cell(44, 7, 'Nama', 0, 0);
$pdf->Cell(44, 7, ': ' . $row['kep_sekretariat'], 0, 1);

$pdf->SetX(20);
$pdf->Cell(44, 7, 'Jabatan', 0, 0);
$pdf->Cell(44, 7, ': Kepala Sekretariat Perwakilan', 0, 1);

$pdf->MultiCell(0, 7, 'Menerangkan bahwa :', 0, 1);

// Data Karyawan magang
$pdf->SetFont('Times', '', 12);
$pdf->SetX(20);
$pdf->Cell(44, 7, 'Nama', 0, 0);
$pdf->Cell(44, 7, ': ' . $data['nama'], 0, 1);
$pdf->SetX(20);
$pdf->Cell(44, 7, 'NIM / NIS', 0, 0);
$pdf->Cell(44, 7, ': ' . $data['nim'], 0, 1);
$pdf->SetX(20);
$pdf->Cell(44, 7, 'Program / Jurusan', 0, 0);
$pdf->Cell(44, 7, ': ' . $data['jurusan'], 0, 1);
$pdf->SetX(20);
$pdf->Cell(44, 7, 'Universitas / Sekolah', 0, 0);
$pdf->Cell(44, 7, ': ' . $data['universitas'], 0, 1);
$pdf->Cell(0, 10, '', 0, 1);
$pdf->MultiCell(0, 7, 'Adalah benar telah melakukan Praktik Kerja Lapangan di Badan Pemeriksa Keuangan Republik Indonesia Perwakilan Provinsi DKI Jakarta pada tanggal ' . $mulai_hari . ' ' . MendapatkanAwalBulan($mulai_bulan) . ' sd ' . $akhir_hari . ' ' . MendapatkanAkhirBulan($akhir_bulan) . ' ' . $akhir_tahun . '. Demikian surat keterangan ini kami buat dengan sebenarnya agar dapat dipergunakan sebagai mestinya.', 0, 'J');

$pdf->Cell(0, 10, '', 0, 1);
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

$pdf->Cell(300, 10, 'Jakarta, ' . $date->format('d') . ' ' . $translatedMonth . ' ' . $date->format('Y'), 0, 1, 'C');

$pdf->Cell(300, 5, 'Kepala Sekretariat', 0, 1, 'C');
$pdf->Cell(300, 5, 'Perwakilan Provinsi DKI Jakarta', 0, 1, 'C');
$pdf->Cell(0, 20, '', 0, 1);

$pdf->Cell(300, 5, '' . $row['kep_sekretariat'], 0, 1, 'C');
$pdf->Cell(300, 5, 'NIP. ' . $row['nip_kep_sekretariat'], 0, 1, 'C');

// Output file PDF sebagai base64
$pdfData = $pdf->Output('S');
$pdfBase64 = base64_encode($pdfData);
echo $pdfBase64;
