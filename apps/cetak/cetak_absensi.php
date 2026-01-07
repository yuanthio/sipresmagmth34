<?php
// Mulai sesi PHP
session_start();

// Termasuk file konfigurasi database
include '../../config/database.php';

// Mengambil parameter dari URL
$id_mahasiswa = $_GET["id_mahasiswa"];
$tanggal_awal = $_GET["tanggal_awal"];
$tanggal_akhir = $_GET["tanggal_akhir"];

// Menetapkan header untuk tipe konten PDF
header('Content-Type: application/pdf');

// Menetapkan header untuk menampilkan PDF di browser dan memberi nama file
header('Content-Disposition: inline; filename="' . $namafile . '"');

// Memasukkan library FPDF
require('../../source/plugin/fpdf/fpdf.php');

// Membuat objek PDF dengan ukuran halaman Letter
$pdf = new FPDF('P', 'mm', 'A4');

// Termasuk file konfigurasi lainnya
include '../../config/database.php';
include '../../config/function.php';

// Mengambil informasi instansi dari database
$query = mysqli_query($kon, "select * from tbl_site limit 1");
$row = mysqli_fetch_array($query);
$pembimbing = $row['pembimbing'];

// Menambahkan halaman ke PDF
$pdf->AddPage();

// Menambahkan logo instansi
$pdf->Image('../../apps/pengaturan/logo/' . $row['logo'], 15, 5, 20, 20);

// Menetapkan font dan judul instansi
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 7, strtoupper($row['nama_instansi']), 0, 1, 'C');

// Menetapkan alamat instansi dan informasi kontak
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 7, $row['alamat'] . ', Telp ' . $row['no_telp'], 0, 1, 'C');
$pdf->Cell(0, 7, $row['website'], 0, 1, 'C');

// Menggambar garis di bawah informasi instansi
$pdf->SetLineWidth(1);
$pdf->Line(10, 31, 200, 31);
$pdf->SetLineWidth(0);
$pdf->Line(10, 32, 200, 32);

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

// Menampilkan judul dan informasi mahasiswa
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 5, '', 0, 1, 'C');
$pdf->Cell(0, 7, 'DAFTAR HADIR KARYAWAN MAGANG', 0, 1, 'C');
$pdf->Cell(0, 7, 'PERIODE MAGANG ' . $mulai_hari . ' ' . MendapatkanAwalBulan($mulai_bulan) . ' - ' . $akhir_hari . ' ' . MendapatkanAkhirBulan($akhir_bulan) . ' ' . $akhir_tahun, 0, 1, 'C');
$pdf->Cell(0, 5, '', 0, 1, 'C');
$pdf->Cell(0, 5, '', 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(35, 6, 'Nama', 0, 0);
$pdf->Cell(31, 6, ': ' . $data['nama'], 0, 1);
$pdf->Cell(35, 6, 'NIM / NIS', 0, 0);
$pdf->Cell(31, 6, ': ' . $data['nim'], 0, 1);
$pdf->Cell(35, 6, 'Universitas / Sekolah', 0, 0);
$pdf->Cell(31, 6, ': ' . $data['universitas'], 0, 1);
$pdf->Cell(35, 6, 'Jurusan', 0, 0);
$pdf->Cell(31, 6, ': ' . $data['jurusan'], 0, 1);

// Mengatur spasi antara info mahasiswa dan daftar hadir
$pdf->Cell(10, 3, '', 0, 1);

// Fungsi untuk memotong teks menjadi beberapa baris
function potongTeks($pdf, $teks, $lebar) {
    $potongan = [];
    $kata = explode(' ', $teks);
    $baris = '';

    foreach ($kata as $kata) {
        if ($pdf->GetStringWidth($baris . ' ' . $kata) > $lebar) {
            $potongan[] = $baris;
            $baris = $kata;
        } else {
            $baris .= ($baris ? ' ' : '') . $kata;
        }
    }
    $potongan[] = $baris;

    return $potongan;
}

// Menampilkan tabel daftar hadir
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 6, 'No', 1, 0, 'C');
$pdf->Cell(15, 6, 'Hari', 1, 0, 'C');
$pdf->Cell(33, 6, 'Tanggal', 1, 0, 'C');
$pdf->Cell(15, 6, 'Waktu', 1, 0, 'C');
$pdf->Cell(20, 6, 'Kehadiran', 1, 0, 'C');
$pdf->Cell(15, 6, 'Status', 1, 0, 'C');

// Tentukan lebar dinamis untuk kolom "Kegiatan"
$lebarKegiatan = 41; // Atur lebar awal untuk kolom "Kegiatan"
$pdf->Cell($lebarKegiatan, 6, 'Kegiatan', 1, 0, 'C');
$pdf->Cell(41, 6, 'Keterangan Izin', 1, 1, 'C'); // Tambahkan kolom baru

$pdf->SetFont('Arial', '', 10);

$no = 0;

// Fungsi untuk memotong teks menjadi beberapa baris
function potongTeksUntukCell($pdf, $teks, $lebar) {
    $potongan = [];
    $kata = explode(' ', $teks);
    $baris = '';

    foreach ($kata as $kata) {
        if ($pdf->GetStringWidth($baris . ' ' . $kata) > $lebar) {
            $potongan[] = $baris;
            $baris = $kata;
        } else {
            $baris .= ($baris ? ' ' : '') . $kata;
        }
    }
    $potongan[] = $baris;

    return $potongan;
}

// Mengambil data absensi mahasiswa
$sql = "SELECT tbl_absensi.id_absensi, tbl_absensi.id_mahasiswa, tbl_absensi.status, tbl_absensi.tanggal, tbl_absensi.waktu,
    DATE_FORMAT(tbl_absensi.tanggal, '%W') AS hari 
    FROM tbl_absensi WHERE tbl_absensi.id_mahasiswa = $id_mahasiswa AND 
    tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
    ORDER BY tanggal ASC";
$hasil = mysqli_query($kon, $sql);

while ($data = mysqli_fetch_assoc($hasil)) {
    $waktu = date("H:i", strtotime($data['waktu'])); // Format waktu dari 13:00 ke 00:00
    $status = $data['status'];
    $hari = $data['hari'];
    $tgl = date("d", strtotime($data['tanggal']));
    $bulan = date("m", strtotime($data['tanggal']));
    $tahun = date("Y", strtotime($data['tanggal']));

    // Menambahkan kegiatan pada tanggal yang sama
    $kegiatanSql = "SELECT GROUP_CONCAT(kegiatan SEPARATOR ', ') AS kegiatan FROM tbl_kegiatan WHERE id_mahasiswa = $id_mahasiswa AND tanggal = '" . $data['tanggal'] . "'";
    $kegiatanResult = mysqli_query($kon, $kegiatanSql);
    $kegiatanData = mysqli_fetch_assoc($kegiatanResult);
    $kegiatan = $kegiatanData['kegiatan'];

    // Mengambil data keterangan izin dari tabel alasan
    $alasanSql = "SELECT alasan FROM tbl_alasan WHERE id_mahasiswa = $id_mahasiswa AND tanggal = '" . $data['tanggal'] . "'";
    $alasanResult = mysqli_query($kon, $alasanSql);
    $alasanData = mysqli_fetch_assoc($alasanResult);
    $keteranganIzin = $alasanData['alasan'];

    if ($waktu == '00:00') {
        $waktu = '-';
    }

    $no++;

    // Mengatur status berdasarkan kondisi yang telah ditentukan
    switch ($status) {
        case 1: // Hadir
            $statusColumn = ($kegiatan != "-") ? 'O' : 'X';
            break;
        case 2: // Izin
            $statusColumn = ($kegiatan != "-") ? 'O' : 'X';
            break;
        case 3: // Terlambat
            $statusColumn = ($kegiatan != "-") ? 'X' : 'X';
            break;
        case 4: // Tidak Hadir
            $statusColumn = ($kegiatan != "-") ? 'X' : 'X';
            break;
        default:
            $statusColumn = '-';
            break;
    }

    // Potong teks untuk kolom "Kegiatan" dan "Keterangan Izin"
    $potonganKegiatan = potongTeksUntukCell($pdf, $kegiatan, $lebarKegiatan);
    $potonganKeteranganIzin = potongTeksUntukCell($pdf, $keteranganIzin, 41);

    // Tentukan jumlah baris maksimum dari kedua kolom tersebut
    $jumlahBaris = max(count($potonganKegiatan), count($potonganKeteranganIzin));
    $tinggiBaris = 6;

    for ($i = 0; $i < $jumlahBaris; $i++) {
        if ($i == 0) {
            $pdf->Cell(10, $tinggiBaris * $jumlahBaris, $no, 1, 0, 'C');
            $pdf->Cell(15, $tinggiBaris * $jumlahBaris, MendapatkanHari($hari), 1, 0, 'C');
            $pdf->Cell(33, $tinggiBaris * $jumlahBaris, $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun, 1, 0, 'C');
            $pdf->Cell(15, $tinggiBaris * $jumlahBaris, $waktu, 1, 0, 'C');
            $pdf->Cell(20, $tinggiBaris * $jumlahBaris, StatusAbsensi($status), 1, 0, 'C');
            $pdf->Cell(15, $tinggiBaris * $jumlahBaris, $statusColumn, 1, 0, 'C');
        } else {
            $pdf->Cell(108, $tinggiBaris, '', 0, 0, 'C'); // Tambahkan sel kosong untuk mengisi kolom sebelumnya
        }

        // Gabungkan teks dari kolom "Kegiatan" dan "Keterangan Izin" dalam satu sel
        $teksKolomKegiatan = isset($potonganKegiatan[$i]) ? $potonganKegiatan[$i] : '';
        $teksKolomKeteranganIzin = isset($potonganKeteranganIzin[$i]) ? $potonganKeteranganIzin[$i] : '';
        $pdf->Cell($lebarKegiatan, $tinggiBaris, $teksKolomKegiatan, 1, 0, 'C');
        $pdf->Cell(41, $tinggiBaris, $teksKolomKeteranganIzin, 1, 1, 'C');
    }
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

$kueri = "select nama from tbl_mahasiswa where id_mahasiswa = $id_mahasiswa";
$hasilsql = mysqli_query($kon, $kueri);
$hasilnama = mysqli_fetch_array($hasilsql);
$nama = $hasilnama['nama'];

$namafile = 'Absensi-' . $nama . '-' . date('YmdHis') . '.pdf';

// Menyimpan PDF ke file dan menampilkannya
$pdf->Output('files/' . $namafile, 'F');
readfile('files/' . $namafile);
?>