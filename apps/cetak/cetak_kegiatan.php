<?php
// Mulai session PHP
session_start();

// Sertakan berkas konfigurasi database
include '../../config/database.php';

// Ambil parameter dari URL
$id_mahasiswa = $_GET["id_mahasiswa"];
$tanggal_awal = $_GET["tanggal_awal"];
$tanggal_akhir = $_GET["tanggal_akhir"];

// Set header untuk tampilan PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="'.$namafile.'"');

// Sertakan library FPDF
require('../../source/plugin/fpdf/fpdf.php');
$pdf = new FPDF('P', 'mm', 'Letter');

// Sertakan berkas konfigurasi lainnya dan ambil data yang diperlukan
include '../../config/database.php';
include '../../config/function.php';
$query = mysqli_query($kon, "select * from tbl_site limit 1");
$row = mysqli_fetch_array($query);
$pembimbing = $row['pembimbing'];

// Tambahkan halaman pertama PDF
$pdf->AddPage();
$pdf->Image('../../apps/pengaturan/logo/'.$row['logo'],15,5,20,20);
$pdf->SetFont('Arial','B',18);
$pdf->Cell(0,7,strtoupper($row['nama_instansi']),0,1,'C');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(0,7,$row['alamat'].', Telp '.$row['no_telp'],0,1,'C');
$pdf->Cell(0,7,$row['website'],0,1,'C');

// Membuat garis (line)
$pdf->SetLineWidth(1);
$pdf->Line(10,31,206,31);
$pdf->SetLineWidth(0);
$pdf->Line(10,32,206,32);

// Mendapatkan data mahasiswa
$sql="select * from tbl_mahasiswa where id_mahasiswa=$id_mahasiswa";
$hasil=mysqli_query($kon,$sql);
$data = mysqli_fetch_array($hasil);

// Tambahkan judul dan info mahasiswa ke PDF
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,5,'',0,1,'C');
$pdf->Cell(0,7,'JURNAL KEGIATAN HARIAN',0,1,'C');
$pdf->Cell(0,5,'',0,1,'C');
$pdf->Cell(0,5,'',0,1,'C');
$pdf->SetFont('Arial','',10);
$pdf->Cell(35,6,'Nama ',0,0);
$pdf->Cell(31,6,': '.$data['nama'],0,1);
$pdf->Cell(35,6,'NIM / NIS ',0,0);
$pdf->Cell(31,6,': '.$data['nim'],0,1);
$pdf->Cell(35,6,'Universitas / Sekolah ',0,0);
$pdf->Cell(31,6,': '.$data['universitas'],0,1);
$pdf->Cell(35,6,'Jurusan ',0,0);
$pdf->Cell(31,6,': '.$data['jurusan'],0,1);

// Membuat header tabel
$pdf->Cell(10,3,'',0,1);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(10,6,'No',1,0,'C');
$pdf->Cell(20,6,'Hari',1,0,'C');
$pdf->Cell(34,6,'Tanggal',1,0,'C');
$pdf->Cell(25,6,'Jam',1,0,'C');
$pdf->Cell(105,6,'Kegiatan',1,1,'C');

$pdf->SetFont('Arial','',10);
$no = 0;

// Mengambil data kegiatan sesuai dengan tanggal
$sql = "SELECT tbl_kegiatan.id_kegiatan, tbl_kegiatan.id_mahasiswa, 
    DATE_FORMAT(tbl_kegiatan.tanggal, '%d-%M-%Y') AS tanggal, 
    DAYNAME(tbl_kegiatan.tanggal) AS hari, 
    TIME_FORMAT(tbl_kegiatan.waktu_awal, '%H:%i') AS waktu_awal, 
    TIME_FORMAT(tbl_kegiatan.waktu_akhir, '%H:%i') AS waktu_akhir,
    tbl_kegiatan.kegiatan
    FROM tbl_kegiatan 
    WHERE tbl_kegiatan.id_mahasiswa = '$id_mahasiswa'
    AND tbl_kegiatan.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir' 
    ORDER BY tbl_kegiatan.tanggal ASC";
$hasil = mysqli_query($kon, $sql);

while ($data = mysqli_fetch_assoc($hasil)) {
    $hari = $data["hari"];
    $tgl = date("d", strtotime($data['tanggal']));
    $bulan = date("m", strtotime($data['tanggal']));
    $tahun = date("Y", strtotime($data['tanggal']));
    $waktu_awal = $data['waktu_awal'];
    $waktu_akhir = $data['waktu_akhir'];
    $no++;

    // Hitung tinggi "Kegiatan" secara manual
    $tinggi_kegiatan = ceil($pdf->getStringWidth($data["kegiatan"]) / 105) * 6; // 105 adalah lebar kolom "Kegiatan"

    // Mengatur tinggi kolom lainnya sesuai dengan tinggi "Kegiatan"
    $pdf->Cell(10, $tinggi_kegiatan, $no, 1, 0, 'C');
    $pdf->Cell(20, $tinggi_kegiatan, MendapatkanHari($hari), 1, 0, 'C');
    $pdf->Cell(34, $tinggi_kegiatan, $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun . '', 1, 0, 'C');
    $pdf->Cell(25, $tinggi_kegiatan, $waktu_awal . ' - ' . $waktu_akhir, 1, 0, 'C');

    // Menggunakan MultiCell untuk kolom "Kegiatan"
    $pdf->MultiCell(105, 6, $data["kegiatan"], 1, 'C');
}

// penanda tangan
$tanggal=date('Y-m-d');

// Mengambil informasi mentor
$sqlMentor = "SELECT mentor FROM tbl_mahasiswa WHERE id_mahasiswa = $id_mahasiswa";
$hasilMentor = mysqli_query($kon, $sqlMentor);
$dataMentor = mysqli_fetch_array($hasilMentor);
$mentor = $dataMentor['mentor'];

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(340, 15, '', 0, 1, 'C');
$pdf->Cell(340, 12, '', 0, 1, 'C');
$pdf->Cell(340, 0, 'Pembimbing Magang', 0, 1, 'C');
$pdf->Cell(340, 50, $mentor, 0, 1, 'C');

// Generate nama file
$kueri = "select nama from tbl_mahasiswa where id_mahasiswa=$id_mahasiswa";
$hasilsql = mysqli_query($kon, $kueri);
$hasilnama = mysqli_fetch_array($hasilsql);
$nama = $hasilnama['nama'];
$namafile = 'Kegiatan Harian-'.$nama.'-'.date('YmdHis').'.pdf';

// Output file PDF ke direktori dan tampilkan
$pdf->Output('files/'.$namafile, 'F');
readfile('files/'.$namafile);
?>
