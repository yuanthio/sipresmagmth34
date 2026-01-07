-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2024 at 05:09 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `absensi_magang_coba`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_absensi`
--

CREATE TABLE `tbl_absensi` (
  `id_absensi` int(11) NOT NULL,
  `id_mahasiswa` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `waktu` time DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `konfirmasi_status` varchar(255) DEFAULT 'konfirmasi_gagal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_absensi`
--

INSERT INTO `tbl_absensi` (`id_absensi`, `id_mahasiswa`, `status`, `waktu`, `tanggal`, `konfirmasi_status`) VALUES
(480, 57, 1, '14:49:42', '2024-01-03', '✓'),
(481, 55, 1, '14:49:47', '2024-01-03', '✓'),
(482, 58, 1, '14:50:16', '2024-01-03', '✓'),
(483, 62, 1, '14:51:45', '2024-01-03', '✓'),
(484, 56, 1, '14:51:45', '2024-01-03', '✓'),
(485, 63, 1, '14:51:46', '2024-01-03', '✓'),
(486, 60, 1, '14:51:48', '2024-01-03', '✓'),
(487, 59, 1, '14:51:55', '2024-01-03', '✓'),
(488, 61, 1, '14:52:02', '2024-01-03', '✓'),
(489, 55, 1, '08:00:00', '2024-01-04', '✓'),
(490, 63, 1, '08:00:00', '2024-01-04', '✓'),
(491, 60, 1, '08:00:00', '2024-01-04', '✓'),
(492, 62, 1, '08:00:00', '2024-01-04', '✓'),
(493, 59, 1, '08:00:00', '2024-01-04', '✓'),
(494, 61, 1, '08:00:00', '2024-01-04', '✓'),
(495, 58, 1, '08:00:00', '2024-01-04', '✓'),
(497, 57, 1, '08:00:00', '2024-01-04', '✓'),
(520, 62, 1, '07:10:00', '2024-01-05', '✓'),
(522, 60, 1, '07:15:00', '2024-01-05', '✓'),
(523, 58, 1, '07:18:00', '2024-01-05', '✓'),
(524, 59, 1, '07:20:00', '2024-01-05', '✓'),
(525, 61, 1, '07:35:00', '2024-01-05', '✓'),
(527, 57, 1, '07:40:00', '2024-01-05', '✓'),
(528, 55, 1, '07:41:00', '2024-01-05', '✓'),
(529, 63, 1, '07:42:00', '2024-01-05', '✓'),
(536, 56, 1, '08:07:57', '2024-01-05', 'konfirmasi_gagal'),
(537, 65, 1, '08:08:39', '2024-01-05', '✓'),
(555, 67, 1, '08:15:01', '2024-01-05', '✓'),
(610, 65, 1, '07:00:38', '2024-01-08', '✓'),
(611, 55, 1, '07:05:04', '2024-01-08', '✓'),
(612, 60, 1, '07:09:39', '2024-01-08', '✓'),
(613, 59, 1, '07:10:28', '2024-01-08', '✓'),
(614, 62, 1, '07:29:59', '2024-01-08', '✓'),
(615, 58, 1, '07:44:55', '2024-01-08', '✓'),
(616, 57, 1, '07:49:38', '2024-01-08', '✓'),
(617, 63, 1, '08:10:21', '2024-01-08', '✓'),
(618, 61, 1, '08:15:07', '2024-01-08', '✓'),
(619, 56, 3, '23:59:59', '2024-01-08', 'konfirmasi_gagal'),
(625, 59, 1, '07:03:14', '2024-01-09', '✓'),
(626, 60, 1, '07:14:24', '2024-01-09', '✓'),
(627, 58, 1, '07:18:50', '2024-01-09', '✓'),
(628, 62, 1, '07:23:10', '2024-01-09', '✓'),
(629, 65, 1, '07:25:04', '2024-01-09', '✓'),
(630, 55, 1, '07:54:54', '2024-01-09', '✓'),
(631, 61, 1, '08:24:53', '2024-01-09', 'X'),
(632, 57, 1, '08:55:30', '2024-01-09', '✓'),
(633, 63, 1, '08:55:34', '2024-01-09', '✓'),
(634, 56, 3, '23:59:59', '2024-01-09', 'konfirmasi_gagal'),
(636, 59, 1, '07:09:58', '2024-01-10', '✓'),
(637, 60, 1, '07:16:54', '2024-01-10', 'X'),
(638, 62, 1, '07:27:38', '2024-01-10', '✓'),
(639, 58, 1, '07:38:44', '2024-01-10', '✓'),
(640, 65, 1, '07:48:08', '2024-01-10', 'konfirmasi_gagal'),
(641, 61, 1, '07:55:59', '2024-01-10', '✓'),
(642, 55, 1, '07:58:44', '2024-01-10', '✓'),
(643, 63, 1, '08:33:17', '2024-01-10', '✓'),
(644, 57, 1, '08:55:10', '2024-01-10', '✓'),
(645, 68, 1, '07:13:00', '2024-01-10', '✓'),
(646, 56, 3, '23:59:43', '2024-01-10', 'konfirmasi_gagal'),
(647, 60, 1, '07:05:03', '2024-01-11', 'konfirmasi_gagal'),
(648, 59, 1, '07:14:43', '2024-01-11', 'X'),
(649, 58, 1, '07:20:44', '2024-01-11', 'X'),
(650, 62, 1, '07:30:51', '2024-01-11', 'X'),
(651, 63, 1, '07:40:30', '2024-01-11', 'X'),
(652, 65, 1, '08:09:26', '2024-01-11', 'konfirmasi_gagal'),
(653, 61, 1, '08:20:21', '2024-01-11', 'X'),
(654, 57, 1, '08:30:07', '2024-01-11', 'konfirmasi_gagal'),
(655, 55, 1, '08:30:52', '2024-01-11', 'X'),
(656, 68, 1, '08:45:04', '2024-01-11', 'X'),
(657, 68, 3, '00:00:00', '2024-01-12', 'X');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_admin`
--

CREATE TABLE `tbl_admin` (
  `id_admin` int(11) NOT NULL,
  `kode_admin` varchar(4) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `nip` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_admin`
--

INSERT INTO `tbl_admin` (`id_admin`, `kode_admin`, `nama`, `nip`, `email`) VALUES
(3, 'A002', 'Kautsar Aditya Wicaksana', '128709344576227', 'humasbpkdki@gmail.com'),
(8, 'A008', 'Muhammad Rafa Subhannallah', '065120038357659', 'rafasubhanallah21@gmail.com'),
(10, 'A009', 'Yuanthio Virly', '06512003831', 'yuanthio31@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_alasan`
--

CREATE TABLE `tbl_alasan` (
  `id_alasan` int(11) NOT NULL,
  `id_mahasiswa` int(11) DEFAULT NULL,
  `alasan` varchar(255) DEFAULT NULL,
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_alasan`
--

INSERT INTO `tbl_alasan` (`id_alasan`, `id_mahasiswa`, `alasan`, `tanggal`) VALUES
(53, 57, '', '2024-01-02'),
(54, 58, '', '2024-01-02'),
(55, 55, '', '2024-01-02'),
(57, 57, '', '2024-01-03'),
(58, 58, '', '2024-01-03'),
(59, 55, '', '2024-01-03'),
(64, 63, '', '2024-01-02'),
(65, 63, '', '2024-01-03'),
(66, 61, '', '2024-01-03'),
(67, 59, '', '2024-01-02'),
(68, 60, '', '2024-01-03'),
(69, 59, '', '2024-01-03'),
(70, 62, '', '2024-01-03'),
(71, 58, '', '2024-01-03'),
(72, 55, '', '2024-01-03'),
(73, 55, '', '2024-01-03'),
(74, 58, '', '2024-01-04'),
(75, 63, '', '2024-01-04'),
(77, 59, '', '2024-01-04'),
(78, 62, '', '2024-01-04'),
(79, 60, '', '2024-01-04'),
(80, 55, '', '2024-01-04'),
(82, 57, '', '2024-01-04'),
(84, 60, '', '2024-01-05'),
(85, 58, '', '2024-01-05'),
(86, 59, '', '2024-01-05'),
(87, 61, '', '2024-01-05'),
(88, 57, '', '2024-01-05'),
(89, 55, '', '2024-01-05'),
(90, 63, '', '2024-01-05'),
(91, 56, '', '2024-01-05'),
(92, 65, '', '2024-01-05'),
(96, 68, '', '2024-01-08'),
(98, 56, '', '2024-01-10');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_kegiatan`
--

CREATE TABLE `tbl_kegiatan` (
  `id_kegiatan` int(11) NOT NULL,
  `id_mahasiswa` int(11) DEFAULT NULL,
  `kegiatan` varchar(255) DEFAULT NULL,
  `waktu_awal` time DEFAULT NULL,
  `waktu_akhir` time DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `hari` varchar(255) DEFAULT 'Senin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_kegiatan`
--

INSERT INTO `tbl_kegiatan` (`id_kegiatan`, `id_mahasiswa`, `kegiatan`, `waktu_awal`, `waktu_akhir`, `tanggal`, `hari`) VALUES
(477, 63, 'Menyusun Laporan Keuangan', '09:30:00', '14:00:00', '2024-01-03', 'Wednesday'),
(478, 55, 'verifikasi sertifikat staff', '12:00:00', '16:00:00', '2024-01-03', 'Wednesday'),
(479, 56, 'Meeting', '14:52:00', '15:52:00', '2024-01-03', 'Wednesday'),
(480, 58, 'membuat list daftar barang miliki negara (DBR)', '08:00:00', '16:00:00', '2024-01-03', 'Wednesday'),
(481, 57, 'Merapihkan dan menscan transaksi laporan keuangan', '09:30:00', '11:30:00', '2024-01-03', 'Wednesday'),
(482, 59, 'Mengarsip', '08:00:00', '16:00:00', '2024-01-03', 'Wednesday'),
(483, 62, 'Mengarsip', '08:00:00', '16:00:00', '2024-01-03', 'Wednesday'),
(484, 61, 'sosialisasi spresmagmth', '08:00:00', '16:00:00', '2024-01-03', 'Wednesday'),
(485, 60, 'sosialisasi sipresmagmth34', '08:00:00', '16:00:00', '2024-01-03', 'Wednesday'),
(486, 55, 'Shipper dokumen, Pemisahan Penetapan Angka Kredit Integrasi Kepala Perwakilan Provinsi DKI Jakarta', '08:00:00', '09:00:00', '2024-01-04', 'Thursday'),
(487, 59, 'Mengarsip dan merapikan berkas-berkas', '07:50:00', '16:00:00', '2024-01-04', 'Thursday'),
(488, 62, 'Merapikan berkas-berkas, dan mengarsip', '07:50:00', '16:00:00', '2024-01-04', 'Thursday'),
(489, 57, 'Membantu dalam penyusunan laporan keuangan', '09:20:00', '14:50:00', '2024-01-04', 'Thursday'),
(490, 63, 'menyusun laporan keuangan', '09:00:00', '15:00:00', '2024-01-04', 'Thursday'),
(491, 58, 'menyelesaikan membuat list daftar barang miliki negara (DBR) di lantai 6', '08:00:00', '15:27:00', '2024-01-04', 'Thursday'),
(492, 61, 'membereskan meja dan map yang tidak terpakaj', '08:00:00', '15:45:00', '2024-01-04', 'Thursday'),
(493, 60, 'Merapihkan berkas yang tidak terpakai', '08:05:00', '15:50:00', '2024-01-04', 'Thursday'),
(494, 65, 'Briefing PKL/magang', '09:00:00', '10:00:00', '2024-01-05', 'Friday'),
(496, 59, 'Merapihkan lemari,mengarsip dan mengetik', '07:58:00', '16:00:00', '2024-01-05', 'Friday'),
(497, 62, 'Mengarsip,Merapikan Lemari, dan Mengetik', '07:58:00', '16:00:00', '2024-01-05', 'Friday'),
(498, 58, 'Mengerjakan bagian DBR selanjutnya, Mengerjakan bagian DBR selanjutnya', '08:00:00', '15:33:00', '2024-01-05', 'Friday'),
(499, 55, 'Input Data Konfirmasi Status Kawin dan Pajak PPN PN 2024', '08:00:00', '16:00:00', '2024-01-05', 'Friday'),
(500, 60, 'mengantar proyektor', '08:00:00', '16:00:00', '2024-01-05', 'Friday'),
(501, 57, 'Menyusun dan merapihkan file hasil laporan keuangan', '08:30:00', '15:05:00', '2024-01-05', 'Friday'),
(502, 63, 'menyusun KK', '09:00:00', '15:30:00', '2024-01-05', 'Friday'),
(503, 61, 'membagikan kalender dan meminta tanda tangan kasubag keuangan', '08:00:00', '16:00:00', '2024-01-05', 'Friday'),
(504, 67, 'Briefing Magang', '09:00:00', '10:00:00', '2024-01-05', 'Friday'),
(506, 68, 'Testing, Pemantauan', '06:02:00', '06:10:00', '2024-01-08', 'Monday'),
(507, 55, 'Restorasi sistem zoom pegawai, Input Data Konfirmasi Status Kawin dan Pajak PPN PN 2024 BPK DKI Jakarta', '08:30:00', '12:00:00', '2024-01-08', 'Monday'),
(508, 62, 'Mengarsip berkas-berkas (surat masuk,surat keluar,dan nota dinas) serta mengetik surat', '08:00:00', '16:00:00', '2024-01-08', 'Monday'),
(509, 59, 'Mengetik dan meng arsip surat-surat serta merapihkan dokumen', '08:00:00', '16:00:00', '2024-01-08', 'Monday'),
(510, 65, 'Mengisi sheet Konfirmasi Status Kawin/Pajak PPNPN 2024', '09:00:00', '15:20:00', '2024-01-08', 'Monday'),
(511, 60, 'Mengantar dokumen dan mengecek file', '08:00:00', '16:00:00', '2024-01-08', 'Monday'),
(512, 57, 'menyusun dan menginput data laporan realisasi anggaran', '09:00:00', '14:22:00', '2024-01-08', 'Monday'),
(513, 63, 'Menyusun dan Input data LRA (rincian LK)', '09:00:00', '15:55:00', '2024-01-08', 'Monday'),
(514, 58, 'Mengikuti rangkaian hut bpk ri dan menyelesaikan tugas DBR', '08:00:00', '15:11:00', '2024-01-08', 'Monday'),
(515, 61, 'meminta ttd dan meriksa dokumen', '08:00:00', '16:00:00', '2024-01-08', 'Monday'),
(516, 68, 'Sedang testing aplikasi', '07:18:00', '07:18:00', '2024-01-09', 'Tuesday'),
(517, 59, 'Mengarsip data-data 2022-2023, dan mengarsip nota dinas', '08:00:00', '16:00:00', '2024-01-09', 'Tuesday'),
(518, 62, 'mengarsip data-data 2022-2023, dan mengarsip nota dinas 2024', '08:00:00', '16:00:00', '2024-01-09', 'Tuesday'),
(519, 60, 'Mengecek file', '08:00:00', '16:00:00', '2024-01-09', 'Tuesday'),
(520, 57, 'Mengecheck dan menginput data laporan realisasi anggaran', '08:30:00', '15:01:00', '2024-01-09', 'Tuesday'),
(521, 65, 'Re-check sheet Pajak Pegawai PNPPN BPK DKI Jakarta, belajar sistem printer baru di ruangan.', '10:05:00', '15:05:00', '2024-01-09', 'Tuesday'),
(522, 63, 'Menyurun Laporan Keuangan (LRA)', '09:00:00', '15:00:00', '2024-01-09', 'Tuesday'),
(523, 58, 'Mengikuti rangkaian hut bpk ri dan menyelesaikan tugas DBR', '08:08:00', '16:08:00', '2024-01-09', 'Tuesday'),
(524, 55, 'Cek Printer, Cek Dokumen PP PN DKI Jakarta', '08:13:00', '14:13:00', '2024-01-09', 'Tuesday'),
(525, 61, 'meriksa tanggal dokumen', '08:00:00', '16:00:00', '2024-01-10', 'Wednesday'),
(526, 68, 'Testing aplikasi', '07:00:00', '08:00:00', '2024-01-10', 'Wednesday'),
(527, 57, 'Melihat donor darah dan merapihkan file di subbag keuangan', '09:00:00', '15:00:00', '2024-01-10', 'Wednesday'),
(528, 58, 'melakukan rename bukti foto barang milik negara (BMN) untuk lantai 6 - 8', '08:15:00', '15:15:00', '2024-01-10', 'Wednesday'),
(529, 59, 'Mengarsip,membuat ppt serta membantu mempersiapkan rapat', '08:00:00', '16:00:00', '2024-01-10', 'Wednesday'),
(530, 62, 'Mengarsip,Membantu mempersiapkan rapat,membuat ppt', '08:00:00', '16:00:00', '2024-01-10', 'Wednesday'),
(531, 55, 'Arsiparis Donor Darah HUT BPK DKI Jakarta, Dokumentasi Presensi Unit Kerja dan Staf untuk Donor Darah', '08:50:00', '12:00:00', '2024-01-10', 'Wednesday'),
(532, 65, 'Arsiparis peserta pendonor darah', '10:30:00', '13:40:00', '2024-01-10', 'Wednesday'),
(533, 60, 'Mengantar dokumen dan mengantar berkas yang di print', '08:00:00', '16:00:00', '2024-01-10', 'Wednesday'),
(534, 63, 'merapikan dokumen dan mengikuti kegiayan donor darah', '08:23:00', '13:23:00', '2024-01-10', 'Wednesday');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_laporan`
--

CREATE TABLE `tbl_laporan` (
  `id_laporan` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `kode_mahasiswa` varchar(4) NOT NULL,
  `universitas` varchar(255) NOT NULL,
  `hari` varchar(20) NOT NULL,
  `tanggal` date NOT NULL,
  `ukuran_file` varchar(20) NOT NULL,
  `file_laporan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_mahasiswa`
--

CREATE TABLE `tbl_mahasiswa` (
  `id_mahasiswa` int(11) NOT NULL,
  `kode_mentor` varchar(4) DEFAULT NULL,
  `kode_mahasiswa` varchar(4) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `universitas` varchar(255) DEFAULT NULL,
  `jurusan` varchar(255) DEFAULT NULL,
  `nim` varchar(255) DEFAULT NULL,
  `nip_mentor` varchar(255) DEFAULT NULL,
  `mulai_magang` date DEFAULT NULL,
  `akhir_magang` date DEFAULT NULL,
  `unit_kerja` varchar(255) NOT NULL,
  `mentor` varchar(255) NOT NULL,
  `jabatan_mentor` varchar(255) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `no_telp` varchar(255) DEFAULT NULL,
  `status_magang` varchar(30) NOT NULL,
  `nilai_kehadiran` int(11) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_mahasiswa`
--

INSERT INTO `tbl_mahasiswa` (`id_mahasiswa`, `kode_mentor`, `kode_mahasiswa`, `nama`, `universitas`, `jurusan`, `nim`, `nip_mentor`, `mulai_magang`, `akhir_magang`, `unit_kerja`, `mentor`, `jabatan_mentor`, `alamat`, `no_telp`, `status_magang`, `nilai_kehadiran`, `foto`) VALUES
(55, 'S019', 'M055', 'Yuda Pramudia', 'Universitas Gadjah Mada', 'Filsafat', '475389', '197605202007081001', '2024-01-02', '2024-02-02', 'Subbag SDM', 'Surya Pujoyono', 'Kasubbag. SDM', 'Jalan Volley Ujung, Jakasampurna, Bekasi Barat, Kota Bekasi.', '0895334598080', 'Aktif', 89, 'WhatsApp Image 2024-01-03 at 2.57.45 PM.jpeg'),
(56, 'S006', 'M056', 'Rafa Subh', 'BPK', 'Fakultas hehehe', '18181818', '128709344576227', '2024-01-01', '2211-12-03', 'Subbag Humas', 'Kautsar Aditya Wicaksana', 'Kasubbag. Humas', 'Planet Mars', '087738342829', 'Aktif', 100, 'download.jpeg'),
(57, 'S020', 'M057', 'Fauzan Abdul Naser', 'Universitas Diponegoro', 'Fakultas Ekonomika dan Bisnis/Ekonomi', '12020121140177', '198011282008081001', '2024-01-02', '2024-02-02', 'Subbag Keuangan', 'Hery Kurniawan', 'Kasubbag. Keuangan', 'Asana Residence Cibubur, Jl. Akses Tol Cimanggis, Cikeas Udik, Kec. Gn. Putri, Kabupaten Bogor, Jawa Barat 16966', '081288148861', 'Aktif', 100, 'WhatsApp Image 2024-01-03 at 2.42.59 PM.jpeg'),
(58, 'S021', 'M058', 'Putriku Hanna Puang Saragih Manihuruk', 'Universitas Diponegoro', 'Fakultas Ekonomika dan Bisnis / S1 Akuntansi', '12030121130155', '197509242010051001', '2024-01-03', '2024-02-03', 'Subbag Umum dan TI', 'Irvan Diary', 'Kasubbag. Umum dan TI', 'Jalan Mutiara Kalimaya I/21, Sumur Batu, Kemayoran, Jakarta Pusat.', '081291896680', 'Aktif', 88, 'WhatsApp Image 2024-01-03 at 2.57.50 PM.jpeg'),
(59, 'S022', 'M059', 'Fitriah Octaviani', 'SMK Negeri 15 Jakarta', 'Otomasi Tata Kelola Perkantoran', '11240', '198004152006042002', '2024-01-02', '2024-03-29', 'Subbag TU Kalan', 'Amanatun Khasanah', 'Kasubbag. TU Kalan', 'Jakarta', '085772620854', 'Aktif', 86, 'WhatsApp Image 2024-01-03 at 2.59.20 PM.jpeg'),
(60, 'S021', 'M060', 'Shakilah', 'SMK Negeri 15 Jakarta', 'Otomasi Tata Kelola Perkantoran', '11387', '197509242010051001', '2024-01-02', '2024-03-29', 'Subbag Umum dan TI', 'Irvan Diary', 'Kasubbag. Umum dan TI', 'Jakarta', '083871151592', 'Aktif', 83, 'WhatsApp Image 2024-01-03 at 2.59.22 PM.jpeg'),
(61, 'S023', 'M061', 'Chikal Aulia Putri', 'SMK Negeri 15 Jakarta', 'Otomasi Tata Kelola Perkantoran', '11218', '197111241999031006', '2024-01-02', '2024-03-29', 'Subbag Hukum', 'Awaluddin', 'Kasubbag. Hukum', 'Jakarta', '085924599684', 'Aktif', 71, 'WhatsApp Image 2024-01-03 at 2.58.37 PM.jpeg'),
(62, 'S022', 'M062', 'Nabilla Sari', 'SMK Negeri 15 Jakarta', 'Otomasi Tata Kelola Perkantoran', '11316', '198004152006042002', '2024-01-02', '2024-03-29', 'Subbag TU Kalan', 'Amanatun Khasanah', 'Kasubbag. TU Kalan', 'Jakarta', '083819763355', 'Aktif', 86, 'WhatsApp Image 2024-01-03 at 2.59.49 PM.jpeg'),
(63, 'S020', 'M063', 'Azzahrah Mulia Shafa Ba Shaiba', 'Institut Pertanian Bogor', 'Ilmu Ekonomi', 'H1401211062', '198011282008081001', '2024-01-02', '2024-01-21', 'Subbag Keuangan', 'Hery Kurniawan', 'Kasubbag. Keuangan', 'Jl. Hanila I No 18, Cibodas Baru, Cibodas', '081311831744', 'Tidak Aktif', 86, 'Azzahrah Mulia Shafa Ba Shaiba_H1401211062 - AZZAHRAH MULIA SHAFA BA SHAIBA.jpg'),
(65, 'S019', 'M065', 'Rekyan Nasywa Nurlaila', 'Universitas Indonesia', 'Fakultas Ekonomi dan Bisnis / Akuntansi', '2106655955', '197605202007081001', '2024-01-05', '2024-02-05', 'Subbag SDM', 'Surya Pujoyono', 'Kasubbag. SDM', 'Vila Nusa Indah Blok EE.3/9, Bojong Kulur, Gunung Putri, Kabupaten Bogor', '081280784673', 'Aktif', 100, 'Foto Pas Berwarna Rekyan Nasywa - Rekyan Nasywa.jpeg'),
(67, 'S006', 'M067', 'Lusy Puspitasari', 'Universitas Presiden', 'Fakultas Humaniora/Ilmu Komunikasi', '009202100111', '128709344576227', '2024-02-01', '2024-06-28', 'Subbag Humas', 'Kautsar Aditya Wicaksana', 'Kasubbag. Humas', 'Jalan tanjung Pura, Pegadungan, Kali Deres, Jakarta Barat', '081295080667', 'Aktif', 100, 'Lusy Puspitasari - LUSY PUSPITASARI.jpeg'),
(68, 'S006', 'M068', 'Yuanthio Virly', 'Pakuan', 'Ilmu Komputer', '065120038', '128709344576227', '2024-01-06', '2024-01-13', 'Subbag Humas', 'Kautsar Aditya Wicaksana', 'Kasubbag. Humas', 'Margabhakti, RT002/001, Kertamaya, Bogor Selatan', '0895336035920', 'Tidak Aktif', 33, 'profile.jpg'),
(70, 'S006', 'M069', 'Salma Khairun Nisa', 'Universitas Diponegoro', 'Informasi dan Humas / Perpustakaan', '40020620650103', '128709344576227', '2023-09-04', '2024-01-04', 'Subbag Humas', 'Kautsar Aditya Wicaksana', 'Kasubbag. Humas', 'Kp. Padurenan, cibinong, bogor Kelurahan Pabuaran , dusun Kp. Padurenan RT\r\n004 RW 002 Kec. Cibinong, Kab. Bogor, Prop. Jawa Barat Kode pos 16916', '089608190455', 'Tidak Aktif', 0, 'foto_default.png');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_mentor`
--

CREATE TABLE `tbl_mentor` (
  `id_mentor` int(11) NOT NULL,
  `kode_mentor` varchar(4) DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `nip` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `unit_kerja` varchar(30) NOT NULL,
  `jabatan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_mentor`
--

INSERT INTO `tbl_mentor` (`id_mentor`, `kode_mentor`, `nama`, `nip`, `email`, `unit_kerja`, `jabatan`) VALUES
(6, 'S006', 'Kautsar Aditya Wicaksana', '128709344576227', 'humasbpkdki@gmail.com', 'Subbag Humas', 'Kasubbag. Humas'),
(19, 'S019', 'Surya Pujoyono', '197605202007081001', 'surya.pujoyono@bpk.go.id', 'Subbag SDM', 'Kasubbag. SDM'),
(20, 'S020', 'Hery Kurniawan', '198011282008081001', 'hery.kurniawan@bpk.go.id', 'Subbag Keuangan', 'Kasubbag. Keuangan'),
(21, 'S021', 'Irvan Diary', '197509242010051001', 'irvan.diary@bpk.go.id', 'Subbag Umum dan TI', 'Kasubbag. Umum dan TI'),
(22, 'S022', 'Amanatun Khasanah', '198004152006042002', 'amanatun.khasanah@bpk.go.id', 'Subbag TU Kalan', 'Kasubbag. TU Kalan'),
(23, 'S023', 'Awaluddin', '197111241999031006', 'awaluddin@bpk.go.id', 'Subbag Hukum', 'Kasubbag. Hukum');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_nilai`
--

CREATE TABLE `tbl_nilai` (
  `id_nilai` int(11) NOT NULL,
  `id_mahasiswa` int(11) DEFAULT NULL,
  `kehadiran` int(11) DEFAULT NULL,
  `keaktifan` int(11) DEFAULT NULL,
  `kreatifitas` int(11) DEFAULT NULL,
  `kepatuhan` int(11) DEFAULT NULL,
  `tingkah_laku` int(11) DEFAULT NULL,
  `keahlian` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `rata_rata` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_setting_absensi`
--

CREATE TABLE `tbl_setting_absensi` (
  `id_waktu` int(11) DEFAULT NULL,
  `mulai_absen` time DEFAULT NULL,
  `akhir_absen` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_setting_absensi`
--

INSERT INTO `tbl_setting_absensi` (`id_waktu`, `mulai_absen`, `akhir_absen`) VALUES
(1, '07:00:00', '09:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_site`
--

CREATE TABLE `tbl_site` (
  `id_site` int(11) DEFAULT NULL,
  `nama_instansi` varchar(255) DEFAULT NULL,
  `pimpinan` varchar(255) DEFAULT NULL,
  `kep_sekretariat` varchar(255) DEFAULT NULL,
  `nip_kep_sekretariat` varchar(30) NOT NULL,
  `no_telp` varchar(255) DEFAULT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_site`
--

INSERT INTO `tbl_site` (`id_site`, `nama_instansi`, `pimpinan`, `kep_sekretariat`, `nip_kep_sekretariat`, `no_telp`, `alamat`, `website`, `logo`) VALUES
(1, 'BPK Perwakilan Provinsi DKI Jakarta', 'Ayub Amali S.E., M.M., Ak., CA., CSFA', 'Ahmad Havid S.E., M.M.', '197602102002121004', '021-79180560', 'JL. MT. Haryono Kav.34 Pancoran, Jakarta Selatan 12770', 'jakarta.bpk.go.id', 'logo-bpk.png');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_suket`
--

CREATE TABLE `tbl_suket` (
  `id_suket` int(11) NOT NULL,
  `id_mahasiswa` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `universitas` varchar(255) NOT NULL,
  `jenis_data` varchar(255) NOT NULL,
  `hari` varchar(20) NOT NULL,
  `tanggal` date NOT NULL,
  `ukuran_file` varchar(20) NOT NULL,
  `file_suket` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id_user` int(11) NOT NULL,
  `kode_pengguna` varchar(4) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`id_user`, `kode_pengguna`, `username`, `password`, `level`) VALUES
(29, 'A002', 'kautsar', 'e10adc3949ba59abbe56e057f20f883e', 'Admin'),
(73, 'A008', 'rafasubh', '65164c54ee1f1dc15bb3f8e5b08034f0', 'Admin'),
(80, 'S006', 'kautsar34', 'e10adc3949ba59abbe56e057f20f883e', 'Mentor'),
(89, 'S009', NULL, NULL, NULL),
(115, 'S019', 'surya.pujoyono', '808e1c18de1d30b365bc1087f2de208a', 'Mentor'),
(116, 'M055', 'yuda', '7fd4e0116fcfb042cd9832d6bb862d3b', 'Mahasiswa'),
(117, 'S020', 'Hery.kurniawan', '272bfa66ae9552d41f3df68a0e27e54e', 'Mentor'),
(118, 'S021', 'irvan.diary', 'aa62bdc4aff5da2ede5dbb2404faae70', 'Mentor'),
(119, 'S022', 'amanatun.khasanah', '9f358e29dbeefbfd09e43c1ebc5199e4', 'Mentor'),
(120, 'S023', 'awaluddin', 'f3558ee3cbba8a293eb256dca2650ff1', 'Mentor'),
(121, 'M056', 'rafasubh', '5ead5be94d104309ed69aa89d828adc9', 'Mahasiswa'),
(122, 'M057', 'fauzan', 'd70f4fd4b5f72a9444814843f7558bd8', 'Mahasiswa'),
(123, 'M058', 'putriku', '1cc90cef0ff88b7b5910b218c644594d', 'Mahasiswa'),
(124, 'M059', 'fitriah', '5cc4d6c92fa8827bcd560bde7d61c2bc', 'Mahasiswa'),
(125, 'M060', 'shakilah', '0d23f5181c4ffb783757dd0eb9df9e97', 'Mahasiswa'),
(126, 'M061', 'chikal', '3e926e6d438ef78e4a65b81d44674ced', 'Mahasiswa'),
(127, 'M062', 'nabilla', 'f3007830d40c64e84dde601ce7f8d6b2', 'Mahasiswa'),
(128, 'M063', 'azzahra', '2300f8f7a4513d0961b7f180b0c2aaa0', 'Mahasiswa'),
(130, 'M065', 'rekyan.nurlaila', 'ea68ad3b7c0040d18f0121cab80dc66c', 'Mahasiswa'),
(132, 'M067', 'lusy.puspitasari', '5304c56acd71ac629081b0bec6655760', 'Mahasiswa'),
(133, 'M068', 'yuan', '27b8e746ba90a211c90b652aeac29e7d', 'Mahasiswa'),
(135, 'M069', 'salma.nisa', 'c76b616101981208b872f6f9bb58104e', 'Mahasiswa'),
(136, 'A009', 'yuanthio', '97effd4841a369613debf5442990ee22', 'Admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_absensi`
--
ALTER TABLE `tbl_absensi`
  ADD PRIMARY KEY (`id_absensi`),
  ADD KEY `tbl_absensi_ibfk1_1` (`id_mahasiswa`);

--
-- Indexes for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD KEY `kode_admin` (`kode_admin`);

--
-- Indexes for table `tbl_alasan`
--
ALTER TABLE `tbl_alasan`
  ADD PRIMARY KEY (`id_alasan`),
  ADD KEY `tbl_alasan_ibfk1_1` (`id_mahasiswa`);

--
-- Indexes for table `tbl_kegiatan`
--
ALTER TABLE `tbl_kegiatan`
  ADD PRIMARY KEY (`id_kegiatan`),
  ADD KEY `tbl_kegiatan_ibfk1_1` (`id_mahasiswa`);

--
-- Indexes for table `tbl_laporan`
--
ALTER TABLE `tbl_laporan`
  ADD PRIMARY KEY (`id_laporan`);

--
-- Indexes for table `tbl_mahasiswa`
--
ALTER TABLE `tbl_mahasiswa`
  ADD PRIMARY KEY (`id_mahasiswa`),
  ADD KEY `kode_mahasiswa` (`kode_mahasiswa`);

--
-- Indexes for table `tbl_mentor`
--
ALTER TABLE `tbl_mentor`
  ADD PRIMARY KEY (`id_mentor`),
  ADD UNIQUE KEY `unique_kode_mentor` (`kode_mentor`);

--
-- Indexes for table `tbl_nilai`
--
ALTER TABLE `tbl_nilai`
  ADD PRIMARY KEY (`id_nilai`),
  ADD UNIQUE KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD UNIQUE KEY `unique_id_mahasiswa` (`id_mahasiswa`),
  ADD UNIQUE KEY `id_mahasiswa_2` (`id_mahasiswa`);

--
-- Indexes for table `tbl_suket`
--
ALTER TABLE `tbl_suket`
  ADD PRIMARY KEY (`id_suket`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `kode_pengguna` (`kode_pengguna`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_absensi`
--
ALTER TABLE `tbl_absensi`
  MODIFY `id_absensi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=658;

--
-- AUTO_INCREMENT for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_alasan`
--
ALTER TABLE `tbl_alasan`
  MODIFY `id_alasan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `tbl_kegiatan`
--
ALTER TABLE `tbl_kegiatan`
  MODIFY `id_kegiatan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=535;

--
-- AUTO_INCREMENT for table `tbl_laporan`
--
ALTER TABLE `tbl_laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `tbl_mahasiswa`
--
ALTER TABLE `tbl_mahasiswa`
  MODIFY `id_mahasiswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `tbl_mentor`
--
ALTER TABLE `tbl_mentor`
  MODIFY `id_mentor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `tbl_nilai`
--
ALTER TABLE `tbl_nilai`
  MODIFY `id_nilai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT for table `tbl_suket`
--
ALTER TABLE `tbl_suket`
  MODIFY `id_suket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_absensi`
--
ALTER TABLE `tbl_absensi`
  ADD CONSTRAINT `tbl_absensi_ibfk1_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbl_mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD CONSTRAINT `tbl_admin_ibfk_1` FOREIGN KEY (`kode_admin`) REFERENCES `tbl_user` (`kode_pengguna`);

--
-- Constraints for table `tbl_alasan`
--
ALTER TABLE `tbl_alasan`
  ADD CONSTRAINT `tbl_alasan_ibfk1_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbl_mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tbl_kegiatan`
--
ALTER TABLE `tbl_kegiatan`
  ADD CONSTRAINT `tbl_kegiatan_ibfk1_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbl_mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
