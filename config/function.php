<?php

function MendapatkanBulan($bulan)
{
    switch ($bulan) {
        case 1:
            return "Januari";
        case 2:
            return "Februari";
        case 3:
            return "Maret";
        case 4:
            return "April";
        case 5:
            return "Mei";
        case 6:
            return "Juni";
        case 7:
            return "Juli";
        case 8:
            return "Agustus";
        case 9:
            return "September";
        case 10:
            return "Oktober";
        case 11:
            return "November";
        case 12:
            return "Desember";
        default:
            return "Bulan tidak valid";
    }
}
?>

<?php
function MendapatkanHari($hari)
{
    switch ($hari) {
        case "Monday":
            return "Senin";
        case "Tuesday":
            return "Selasa";
        case "Wednesday":
            return "Rabu";
        case "Thursday":
            return "Kamis";
        case "Friday":
            return "Jumat";
        case "Saturday":
            return "Sabtu";
        case "Sunday":
            return "Minggu";
        default:
            return "Hari tidak diketahui";
    }
}
?>

<?php
function AbsensiOtomatis($sql)
{
    include 'database.php';

    // Cek apakah pengguna yang sedang login adalah mentor
    if ($_SESSION["level"] == 'Mentor') {
        $mentor_name = $_SESSION["nama_mentor"];

        $sql = "SELECT tbl_mahasiswa.id_mahasiswa, tbl_mahasiswa.nama, tbl_mahasiswa.universitas, 
            tbl_mahasiswa.mulai_magang, tbl_mahasiswa.akhir_magang, tbl_absensi.id_absensi, 
            (CASE
                WHEN tbl_absensi.status IS NULL THEN 'Belum Absensi'
                WHEN tbl_absensi.status = 1 THEN 'Hadir'
                WHEN tbl_absensi.status = 2 THEN 'Izin'
                WHEN tbl_absensi.status = 3 THEN 'Terlambat'
                WHEN tbl_absensi.status = 5 THEN 'WFA'
            ELSE 'Tidak Hadir' END) AS status, 
            (CASE
                WHEN tbl_absensi.waktu IS NULL THEN 'Belum'
                ELSE tbl_absensi.waktu END) AS waktu,
            DATE_FORMAT(CURDATE(), '%W') AS hari,
            DATE_FORMAT(CURDATE(), '%Y-%m-%d') AS tanggal
            FROM tbl_mahasiswa LEFT JOIN tbl_absensi ON 
                tbl_absensi.id_mahasiswa = tbl_mahasiswa.id_mahasiswa 
            AND tbl_absensi.tanggal = CURDATE() 
            WHERE tbl_mahasiswa.mulai_magang <= CURDATE() AND
                tbl_mahasiswa.akhir_magang >= CURDATE() AND
                tbl_mahasiswa.kode_mentor = (SELECT kode_mentor FROM tbl_mentor WHERE nama = '$mentor_name')
                ORDER BY tbl_mahasiswa.nama ASC;";
    } else {
        // Jika yang login bukan mentor, tampilkan semua data absensi
        $sql = "SELECT tbl_mahasiswa.id_mahasiswa, tbl_mahasiswa.nama, tbl_mahasiswa.universitas, 
            tbl_mahasiswa.mulai_magang, tbl_mahasiswa.akhir_magang, tbl_absensi.id_absensi, 
            (CASE
                WHEN tbl_absensi.status IS NULL THEN 'Belum Absensi'
                WHEN tbl_absensi.status = 1 THEN 'Hadir'
                WHEN tbl_absensi.status = 2 THEN 'Izin'
                WHEN tbl_absensi.status = 3 THEN 'Terlambat'
                WHEN tbl_absensi.status = 5 THEN 'WFA'
            ELSE 'Tidak Hadir' END) AS status, 
            (CASE
                WHEN tbl_absensi.waktu IS NULL THEN 'Belum'
                ELSE tbl_absensi.waktu END) AS waktu,
            DATE_FORMAT(CURDATE(), '%W') AS hari,
            DATE_FORMAT(CURDATE(), '%Y-%m-%d') AS tanggal
            FROM tbl_mahasiswa LEFT JOIN tbl_absensi ON 
                tbl_absensi.id_mahasiswa = tbl_mahasiswa.id_mahasiswa 
            AND tbl_absensi.tanggal = CURDATE() 
            WHERE tbl_mahasiswa.mulai_magang <= CURDATE() AND
                tbl_mahasiswa.akhir_magang >= CURDATE()
                ORDER BY tbl_mahasiswa.nama ASC;";
    }

    return $sql;
}
?>

<?php
function PencarianAbsensi($nama, $tanggal_awal, $tanggal_akhir)
{
    include 'database.php';

    $hari_ini = date('Y-m-d');
    $nama_filter = $nama != '' ? "AND tbl_mahasiswa.nama LIKE '%$nama%'" : '';

    if ($tanggal_awal == $hari_ini && $tanggal_akhir == $hari_ini) {
        // Pencarian untuk hari ini - tampilkan semua mahasiswa aktif
        $sql = "SELECT 
            tbl_mahasiswa.id_mahasiswa,
            tbl_mahasiswa.nama,
            tbl_mahasiswa.universitas,
            tbl_absensi.id_absensi,
            tbl_absensi.tanggal,
            CASE 
                WHEN tbl_absensi.status IS NULL THEN 'Belum Absensi'
                WHEN tbl_absensi.status = 1 THEN 'Hadir'
                WHEN tbl_absensi.status = 2 THEN 'Izin'
                WHEN tbl_absensi.status = 3 THEN 'Terlambat'
                WHEN tbl_absensi.status = 4 THEN 'Tidak Hadir'
                WHEN tbl_absensi.status = 5 THEN 'WFA'
                ELSE 'Belum Absensi'
            END AS status,
            CASE 
                WHEN tbl_absensi.waktu IS NULL THEN 'Belum'
                ELSE tbl_absensi.waktu
            END AS waktu,
            DATE_FORMAT(COALESCE(tbl_absensi.tanggal, CURDATE()), '%W') AS hari
        FROM tbl_mahasiswa 
        LEFT JOIN tbl_absensi 
            ON tbl_absensi.id_mahasiswa = tbl_mahasiswa.id_mahasiswa 
            AND tbl_absensi.tanggal = '$hari_ini'
        WHERE tbl_mahasiswa.mulai_magang <= '$hari_ini'
            AND tbl_mahasiswa.akhir_magang >= '$hari_ini'
            $nama_filter
        ORDER BY tbl_mahasiswa.nama ASC";
    } else {
        // Pencarian untuk tanggal selain hari ini - hanya tampilkan yang sudah absen
        $sql = "SELECT 
            tbl_mahasiswa.id_mahasiswa,
            tbl_mahasiswa.nama,
            tbl_mahasiswa.universitas,
            tbl_absensi.id_absensi,
            tbl_absensi.tanggal,
            CASE 
                WHEN tbl_absensi.status = 1 THEN 'Hadir'
                WHEN tbl_absensi.status = 2 THEN 'Izin'
                WHEN tbl_absensi.status = 3 THEN 'Terlambat'
                WHEN tbl_absensi.status = 4 THEN 'Tidak Hadir'
                WHEN tbl_absensi.status = 5 THEN 'WFA'
                ELSE 'Belum Absensi'
            END AS status,
            tbl_absensi.waktu,
            DATE_FORMAT(tbl_absensi.tanggal, '%W') AS hari
        FROM tbl_mahasiswa 
        INNER JOIN tbl_absensi 
            ON tbl_absensi.id_mahasiswa = tbl_mahasiswa.id_mahasiswa 
            AND tbl_absensi.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
        WHERE tbl_mahasiswa.mulai_magang <= '$tanggal_akhir'
            AND tbl_mahasiswa.akhir_magang >= '$tanggal_awal'
            $nama_filter
        ORDER BY tbl_mahasiswa.nama ASC";
    }

    return $sql;
}

?>

<?php
function EditAbsensi($id_absensi)
{
    include 'database.php';
    $sql = "SELECT tbl_absensi.id_absensi, tbl_absensi.id_mahasiswa, 
    tbl_absensi.status, tbl_absensi.waktu, tbl_absensi.tanggal, 
    COALESCE(tbl_alasan.tanggal, tbl_absensi.tanggal) as tanggal_alasan 
    FROM tbl_absensi LEFT JOIN tbl_alasan ON tbl_absensi.id_absensi = tbl_alasan.id_alasan 
    WHERE tbl_absensi.tanggal = tbl_alasan.tanggal OR tbl_alasan.tanggal IS NULL 
    AND tbl_absensi.id_absensi = '$id_absensi';";
    return $sql;
}
?>

<?php
function DataKegiatan($sql)
{
    include 'database.php';
    $sql = "SELECT tbl_mahasiswa.id_mahasiswa, tbl_mahasiswa.nama, 
            tbl_mahasiswa.universitas, tbl_kegiatan.id_kegiatan, 
            tbl_kegiatan.kegiatan, tbl_kegiatan.tanggal, 
            tbl_kegiatan.foto,  -- tambahkan kolom foto ke query
            tbl_kegiatan.waktu_awal,  -- tambahkan kolom waktu_awal
            tbl_kegiatan.waktu_akhir,  -- tambahkan kolom waktu_akhir
            DATE_FORMAT(tbl_kegiatan.tanggal, '%W') AS hari, 
            CONCAT(SUBSTRING(tbl_kegiatan.waktu_awal, 1, 5), ' - ', SUBSTRING(tbl_kegiatan.waktu_akhir, 1, 5)) AS waktu
            FROM tbl_mahasiswa JOIN tbl_kegiatan ON 
            tbl_mahasiswa.id_mahasiswa = tbl_kegiatan.id_mahasiswa 
            ORDER BY tbl_kegiatan.tanggal DESC";
    return $sql;
}

?>

<?php
function CariKegiatan($nama, $tanggal_awal, $tanggal_akhir, $mentor_name)
{
    include 'database.php';
    $sql = "SELECT tbl_mahasiswa.id_mahasiswa, tbl_mahasiswa.nama, 
            tbl_mahasiswa.universitas, tbl_kegiatan.id_kegiatan, 
            tbl_kegiatan.kegiatan, tbl_kegiatan.tanggal, 
            tbl_kegiatan.foto, -- tambahkan kolom foto
            tbl_kegiatan.waktu_awal, -- tambahkan kolom waktu_awal
            tbl_kegiatan.waktu_akhir, -- tambahkan kolom waktu_akhir
            DATE_FORMAT(tbl_kegiatan.tanggal, '%W') AS hari, 
            CONCAT(SUBSTRING(tbl_kegiatan.waktu_awal, 1, 5), ' - ', SUBSTRING(tbl_kegiatan.waktu_akhir, 1, 5)) AS waktu
            FROM tbl_mahasiswa 
            JOIN tbl_kegiatan ON tbl_mahasiswa.id_mahasiswa = tbl_kegiatan.id_mahasiswa";

    if (!empty($mentor_name)) {
        $sql .= " WHERE tbl_mahasiswa.kode_mentor = (SELECT kode_mentor FROM tbl_mentor WHERE nama = '$mentor_name')";
    }

    if (!empty($nama)) {
        $sql .= " AND UPPER(tbl_mahasiswa.nama) LIKE UPPER('%$nama%')";
    }

    if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
        $sql .= " AND tbl_kegiatan.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
    }

    $sql .= " ORDER BY tbl_kegiatan.tanggal DESC";

    return $sql;
}

?>

<?php
function MenampilkanKegiatan($id_mahasiswa)
{
    include 'database.php';
    $sql = "SELECT tbl_kegiatan.id_kegiatan, tbl_kegiatan.id_mahasiswa, 
    DATE_FORMAT(tbl_kegiatan.tanggal, '%d-%M-%Y') AS tanggal, 
    DAYNAME(tbl_kegiatan.tanggal) AS hari, 
    GROUP_CONCAT(CONCAT(tbl_kegiatan.kegiatan, 
    ' (', tbl_kegiatan.waktu_awal, ' - ', tbl_kegiatan.waktu_akhir, ')') 
    SEPARATOR ', ') AS kegiatan 
    FROM tbl_kegiatan WHERE tbl_kegiatan.id_mahasiswa = '$id_mahasiswa' 
    GROUP BY tbl_kegiatan.tanggal, tbl_kegiatan.id_mahasiswa 
    ORDER BY tbl_kegiatan.tanggal DESC";
    return $sql;
}
?>

<?php
function MencarikanKegiatan($id_mahasiswa, $tanggal_awal, $tanggal_akhir)
{
    include 'database.php';
    $sql = "SELECT tbl_kegiatan.id_kegiatan, tbl_kegiatan.id_mahasiswa, 
    DATE_FORMAT(tbl_kegiatan.tanggal, '%d-%M-%Y') AS tanggal, 
    DAYNAME(tbl_kegiatan.tanggal) AS hari, 
    GROUP_CONCAT(CONCAT(tbl_kegiatan.kegiatan, 
    ' (', tbl_kegiatan.waktu_awal, ' - ', tbl_kegiatan.waktu_akhir, ')') 
    SEPARATOR ', ') AS kegiatan 
    FROM tbl_kegiatan WHERE tbl_kegiatan.id_mahasiswa = '$id_mahasiswa'
    AND tbl_kegiatan.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir' 
    GROUP BY tbl_kegiatan.tanggal, tbl_kegiatan.id_mahasiswa 
    ORDER BY tbl_kegiatan.tanggal DESC";
    return $sql;
}
?>

<?php
function WaktuKegiatan($string_kegiatan)
{
    $array_kegiatan = explode(", ", $string_kegiatan);
    $kegiatan = array();
    foreach ($array_kegiatan as $kgt) {
        $kgt_array = explode(" (", $kgt);
        if (isset($kgt_array[1])) {
            $waktu_kegiatan = trim($kgt_array[1], ")");
            $waktu_array = explode(" - ", $waktu_kegiatan);
            if (isset($waktu_array[1])) {
                $waktu_awal = trim($waktu_array[0]);
                $waktu_akhir = trim($waktu_array[1]);
                $waktu_awal = date('H:i', strtotime($waktu_awal));
                $waktu_akhir = date('H:i', strtotime($waktu_akhir));
                $kegiatan[] = $waktu_awal . " - " . $waktu_akhir;
            }
        }
    }
    foreach ($kegiatan as $kegiatan) {
        echo $kegiatan . ' </br>';
    }
}
?>

<?php
function BarisKegiatan($string_kegiatan)
{
    $array_kegiatan = explode(", ", $string_kegiatan);
    $kegiatan = array();
    foreach ($array_kegiatan as $kgt) {
        $kgt_array = explode(" (", $kgt);
        $nama_kegiatan = trim($kgt_array[0]);
        $kegiatan[] = $nama_kegiatan;
    }

    foreach ($kegiatan as $kegiatan) {
        echo $kegiatan . ' </br>';
    }
}
?>

<?php

function FormatWaktu($waktu_awal, $waktu_akhir)
{
    $waktu_awal_arr = explode(', ', $waktu_awal);
    $waktu_akhir_arr = explode(', ', $waktu_akhir);
    $formatted_waktu = [];

    for ($i = 0; $i < count($waktu_awal_arr); $i++) {
        if (isset($waktu_awal_arr[$i]) && isset($waktu_akhir_arr[$i])) {
            $formatted_waktu[] = trim($waktu_awal_arr[$i]) . " - " . trim($waktu_akhir_arr[$i]);
        }
    }

    return implode(' , ', $formatted_waktu);
}

?>

<?php
function GetKeteranganIzin($tanggal, $id_mahasiswa)
{
    global $kon;
    $sql = "SELECT alasan FROM tbl_alasan WHERE tanggal = '$tanggal' AND id_mahasiswa = $id_mahasiswa";
    $hasil = mysqli_query($kon, $sql);
    $data = mysqli_fetch_array($hasil);

    if ($data) {
        return $data['alasan'];
    } else {
        return '-';
    }
}
?>

<?php
function MendapatkanAwalBulan($mulai_bulan)
{
    switch ($mulai_bulan) {
        case 1:
            return "Januari";
        case 2:
            return "Februari";
        case 3:
            return "Maret";
        case 4:
            return "April";
        case 5:
            return "Mei";
        case 6:
            return "Juni";
        case 7:
            return "Juli";
        case 8:
            return "Agustus";
        case 9:
            return "September";
        case 10:
            return "Oktober";
        case 11:
            return "November";
        case 12:
            return "Desember";
        default:
            return "Bulan tidak valid";
    }
}

function MendapatkanAkhirBulan($akhir_bulan)
{
    switch ($akhir_bulan) {
        case 1:
            return "Januari";
        case 2:
            return "Februari";
        case 3:
            return "Maret";
        case 4:
            return "April";
        case 5:
            return "Mei";
        case 6:
            return "Juni";
        case 7:
            return "Juli";
        case 8:
            return "Agustus";
        case 9:
            return "September";
        case 10:
            return "Oktober";
        case 11:
            return "November";
        case 12:
            return "Desember";
        default:
            return "Bulan tidak valid";
    }
}
?>

<?php
function StatusAbsensi($status)
{
    switch ($status) {
        case 1:
            $status = "Hadir";
            break;
        case 2:
            $status = "Izin";
            break;
        case 3:
            $status = "Terlambat";
            break;
        case 4:
            $status = "Tidak Hadir";
            break;
    }
    return $status;
}
?>