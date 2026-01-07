<?php
session_start();
include '../../config/database.php';

if (isset($_GET['id_absensi'])) {
    $id_absensi = $_GET['id_absensi'];

    // Ambil data absensi dulu untuk dapatkan id_mahasiswa, tanggal, nama, dan nama file kamera
    $query_get = "SELECT a.id_mahasiswa, a.tanggal, a.kamera, m.nama 
                  FROM tbl_absensi a
                  JOIN tbl_mahasiswa m ON a.id_mahasiswa = m.id_mahasiswa
                  WHERE a.id_absensi = '$id_absensi' LIMIT 1";
    $hasil_get = mysqli_query($kon, $query_get);
    $data = mysqli_fetch_assoc($hasil_get);

    if ($data) {
        $id_mahasiswa = $data['id_mahasiswa'];
        $tanggal = $data['tanggal'];
        $nama_mahasiswa = $data['nama'];
        $foto_kamera = $data['kamera'];

        // Mulai transaksi
        mysqli_query($kon, "START TRANSACTION");

        // === Hapus file kamera dari folder jika ada ===
        if (!empty($foto_kamera)) {
            $kamera_path = '../../apps/pengguna/kamera/' . $foto_kamera;
            if (file_exists($kamera_path)) {
                unlink($kamera_path);
            }
        }

        // === Ambil dan hapus file bukti alasan (jika ada) ===
        $query_foto = "SELECT foto FROM tbl_alasan WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = '$tanggal' LIMIT 1";
        $hasil_foto = mysqli_query($kon, $query_foto);
        $data_foto = mysqli_fetch_assoc($hasil_foto);

        if ($data_foto && !empty($data_foto['foto'])) {
            $file_path = '../../apps/pengguna/bukti_alasan/' . $data_foto['foto'];
            if (file_exists($file_path)) {
                unlink($file_path); // Hapus file alasan dari server
            }
        }

        // Hapus data alasan dari database
        $query_alasan = "DELETE FROM tbl_alasan WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = '$tanggal'";
        mysqli_query($kon, $query_alasan);

        // === Ambil dan hapus file bukti WFA (jika ada) ===
        $query_wfa = "SELECT bukti_wfa FROM tbl_bukti_wfa WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = '$tanggal' LIMIT 1";
        $hasil_wfa = mysqli_query($kon, $query_wfa);
        $data_wfa = mysqli_fetch_assoc($hasil_wfa);

        if ($data_wfa && !empty($data_wfa['bukti_wfa'])) {
            $file_wfa = '../../apps/data_absensi/file_wfa/' . $data_wfa['bukti_wfa'];
            if (file_exists($file_wfa)) {
                unlink($file_wfa); // hapus file bukti WFA dari server
            }
        }

        // Hapus data bukti WFA dari database
        $query_del_wfa = "DELETE FROM tbl_bukti_wfa WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = '$tanggal'";
        mysqli_query($kon, $query_del_wfa);

        // === Ambil dan hapus data kegiatan (jika ada) ===
        $query_kegiatan = "SELECT id_kegiatan, foto 
                           FROM tbl_kegiatan 
                           WHERE id_mahasiswa = '$id_mahasiswa' 
                             AND tanggal = '$tanggal' 
                           LIMIT 1";
        $hasil_kegiatan = mysqli_query($kon, $query_kegiatan);
        $data_kegiatan = mysqli_fetch_assoc($hasil_kegiatan);

        $hapus_kegiatan = false;
        if ($data_kegiatan) {
            $id_kegiatan = $data_kegiatan['id_kegiatan'];
            $foto_kegiatan = $data_kegiatan['foto'];

            if (!empty($foto_kegiatan)) {
                $file_kegiatan_path = '../../apps/data_kegiatan/foto_kegiatan/' . $foto_kegiatan;
                if (file_exists($file_kegiatan_path)) {
                    unlink($file_kegiatan_path);
                }
            }

            $query_del_kegiatan = "DELETE FROM tbl_kegiatan WHERE id_kegiatan = '$id_kegiatan'";
            mysqli_query($kon, $query_del_kegiatan);

            $hapus_kegiatan = true;
        }

        // === Hapus data absensi ===
        $query = "DELETE FROM tbl_absensi WHERE id_absensi = '$id_absensi'";
        $hasil = mysqli_query($kon, $query);

        // Logging
        $kode_pengguna = $_SESSION['kode_pengguna'];
        $user = mysqli_fetch_assoc(mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'"));
        $level = $user['level'];
        $admin = mysqli_fetch_assoc(mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'"));
        $nama_admin = $admin['nama'];
        date_default_timezone_set('Asia/Jakarta');
        $tanggal_log = date("Y-m-d H:i:s");

        if ($hapus_kegiatan) {
            $aktivitas = "Menghapus data presensi ($nama_mahasiswa)";
        } else {
            $aktivitas = "Menghapus data presensi ($nama_mahasiswa)";
        }

        if ($hasil) {
            mysqli_query($kon, "COMMIT");
            $status_aktivitas = "berhasil";
            header("Location:../../index.php?page=data_absensi&hapus=berhasil");
        } else {
            mysqli_query($kon, "ROLLBACK");
            $status_aktivitas = "gagal";
            header("Location:../../index.php?page=data_absensi&hapus=gagal");
        }

        $log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                VALUES ('$tanggal_log', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status_aktivitas')";
        mysqli_query($kon, $log);

    } else {
        header("Location:../../index.php?page=data_absensi&hapus=gagal");
    }
} else {
    header("Location:../../index.php?page=data_absensi&hapus=gagal");
}
?>
