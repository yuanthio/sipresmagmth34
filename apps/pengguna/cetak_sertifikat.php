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

// Mengambil informasi mahasiswa
$sql = "select * from tbl_mahasiswa where id_mahasiswa = $id_mahasiswa";
$hasil = mysqli_query($kon, $sql);
$data = mysqli_fetch_array($hasil);

// Ekstrak nama mahasiswa
$nama_mahasiswa = $data['nama'];

// Mengambil informasi awal dan akhir magang
$awal_magang = $data['mulai_magang'];
$akhir_magang = $data['akhir_magang'];
$mulai_bulan = date("m", strtotime($awal_magang));
$akhir_bulan = date("m", strtotime($akhir_magang));
$mulai_hari = date("d", strtotime($awal_magang));
$akhir_hari = date("d", strtotime($akhir_magang));
$akhir_tahun = date("Y", strtotime($akhir_magang));

// Buat objek FPDF dengan orientasi lanskap dan ukuran kertas A4
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// Set Auto Page Break agar bisa menggulir halaman (jika perlu)
$pdf->SetAutoPageBreak(true, 10);

//  garis 1
$margin = 5;
$pdf->SetLineWidth(5.5);  // Ketebalan garis
$pdf->SetFillColor(179, 128, 1); // Warna abu-abu untuk background

$pdf->Rect($margin, $margin, $pdf->GetPageWidth() - 2*$margin, 1, 'F'); // Menggunakan Rect untuk garis dan memberikan warna
$pdf->Rect($margin, $pdf->GetPageHeight() - $margin - 1, $pdf->GetPageWidth() - 2*$margin, 1, 'F');
$pdf->Rect($margin, $margin, 1, $pdf->GetPageHeight() - 2*$margin, 'F');
$pdf->Rect($pdf->GetPageWidth() - $margin - 1, $margin, 1, $pdf->GetPageHeight() - 2*$margin, 'F');

//  garis 2
$margin = 6;
$pdf->SetLineWidth(5.5);  // Ketebalan garis
$pdf->SetFillColor(179, 128, 1); // Warna abu-abu untuk background

$pdf->Rect($margin, $margin, $pdf->GetPageWidth() - 2*$margin, 1, 'F'); // Menggunakan Rect untuk garis dan memberikan warna
$pdf->Rect($margin, $pdf->GetPageHeight() - $margin - 1, $pdf->GetPageWidth() - 2*$margin, 1, 'F');
$pdf->Rect($margin, $margin, 1, $pdf->GetPageHeight() - 2*$margin, 'F');
$pdf->Rect($pdf->GetPageWidth() - $margin - 1, $margin, 1, $pdf->GetPageHeight() - 2*$margin, 'F');

// baris 3
$margin = 7;
$pdf->SetLineWidth(5.5);  // Ketebalan garis
$pdf->SetFillColor(179, 128, 1); // Warna abu-abu untuk background

$pdf->Rect($margin, $margin, $pdf->GetPageWidth() - 2*$margin, 1, 'F'); // Menggunakan Rect untuk garis dan memberikan warna
$pdf->Rect($margin, $pdf->GetPageHeight() - $margin - 1, $pdf->GetPageWidth() - 2*$margin, 1, 'F');
$pdf->Rect($margin, $margin, 1, $pdf->GetPageHeight() - 2*$margin, 'F');
$pdf->Rect($pdf->GetPageWidth() - $margin - 1, $margin, 1, $pdf->GetPageHeight() - 2*$margin, 'F');

// baris 4
$margin = 9;
$pdf->SetLineWidth(5.5);  // Ketebalan garis
$pdf->SetFillColor(179, 128, 1); // Warna abu-abu untuk background

$pdf->Rect($margin, $margin, $pdf->GetPageWidth() - 2*$margin, 1, 'F'); // Menggunakan Rect untuk garis dan memberikan warna
$pdf->Rect($margin, $pdf->GetPageHeight() - $margin - 1, $pdf->GetPageWidth() - 2*$margin, 1, 'F');
$pdf->Rect($margin, $margin, 1, $pdf->GetPageHeight() - 2*$margin, 'F');
$pdf->Rect($pdf->GetPageWidth() - $margin - 1, $margin, 1, $pdf->GetPageHeight() - 2*$margin, 'F');

// Kop Surat
$pdf->SetFont('Times', 'B', 28);
$pdf->SetFillColor(24, 18, 92); // Warna biru tua untuk background
$pdf->Image('../../apps/pengaturan/logo/' . $row['logo'], 139, 13, 20, 20); 
$pdf->Cell(0, 25, '', 0, 1); 
$pdf->Cell(0, 10, 'BADAN PEMERIKSA KEUANGAN', 0, 1, 'C');
$pdf->Cell(0, 10, 'PERWAKILAN PROVINSI DKI JAKARTA', 0, 1, 'C');
$pdf->Cell(0, 7, '', 0, 1); 
$pdf->SetFont('Times', '', 36);
$pdf->Cell(0, 14, 'SERTIFIKAT', 0, 1, 'C');
$pdf->SetFont('Times', 'B', 16);
$pdf->Cell(0, 8, 'Diberikan Kepada :', 0, 1, 'C');
$pdf->Cell(0, 7, '', 0, 1); 
$pdf->SetFont('Times', 'I', 30);
$pdf->Cell(0, 14, $data['nama'], 0, 1, 'C');

$pdf->Cell(0, 7, '', 0, 1); 
$pdf->SetFont('Times', '', 16);
$pdf->Cell(0, 8, 'Telah Melaksanakan Praktik Kerja Lapangan di', 0, 1, 'C');
$pdf->Cell(0, 8, 'BPK Perwakilan Provinsi DKI Jakarta', 0, 1, 'C');
$pdf->Cell(0, 8, 'Terhitung mulai tanggal ' . $mulai_hari . ' ' . MendapatkanAwalBulan($mulai_bulan) . ' s.d. ' . $akhir_hari . ' ' . MendapatkanAkhirBulan($akhir_bulan) . ' ' . $akhir_tahun . ' ', 0, 1, 'C');

$pdf->Cell(0, 6, '', 0, 1); 
$pdf->SetFont('Times', 'B', 16);
$pdf->Cell(0, 14, 'Kepala Sekretariat,', 0, 1, 'C'); 

$pdf->Cell(0, 20, '', 0, 1); 
$pdf->Cell(0, 8, $row['kep_sekretariat'], 0, 1, 'C');
$pdf->Cell(0, 8, 'NIP. ' . $row['nip_kep_sekretariat'], 0, 1, 'C');

// Output file PDF sebagai base64
$pdfData = $pdf->Output('S');
$pdfBase64 = base64_encode($pdfData);
echo $pdfBase64;

?>
