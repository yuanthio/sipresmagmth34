<?php
// Memulai sesi PHP (jika belum dimulai)
session_start();

// Memeriksa apakah tombol "ubah_aplikasi" diklik
if (isset($_POST['ubah_aplikasi'])) {
    // Memasukkan file konfigurasi database
    include '../../config/database.php';

    // Fungsi untuk membersihkan dan memvalidasi input
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // Memeriksa metode permintaan (POST)
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Memulai transaksi database
        mysqli_query($kon, "START TRANSACTION");

        // Mengambil nilai dari $_POST dan membersihkan serta memvalidasi input
        $id_site = input($_POST["id"]); // Mengambil nilai id dari $_POST
        $nama_instansi = input($_POST["nama_instansi"]);
        $pimpinan = input($_POST["pimpinan"]);
        $kep_sekretariat = input($_POST["kep_sekretariat"]);
        $nip_kep_sekretariat = input($_POST["nip_kep_sekretariat"]);
        $no_telp = input($_POST["no_telp"]);
        $alamat = input($_POST["alamat"]);
        $website = input($_POST["website"]);
        $logo_sebelumnya = input($_POST["logo_sebelumnya"]);
        $logo = $_FILES['logo']['name'];

        // Mengelompokkan ekstensi yang diperbolehkan
        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg');
        $x = explode('.', $logo);
        $ekstensi = strtolower(end($x));
        $ukuran = $_FILES['logo']['size'];
        $file_tmp = $_FILES['logo']['tmp_name'];

        // Memeriksa apakah file logo diunggah
        if (!empty($logo)) {
            // Memeriksa ekstensi file apakah sesuai
            if (in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
                // Mengupload logo yang baru
                move_uploaded_file($file_tmp, 'logo/' . $logo);

                // Menghapus logo sebelumnya
                unlink("logo/" . $logo_sebelumnya);

                // Membuat query SQL untuk mengupdate data aplikasi
                $sql = "UPDATE tbl_site SET
                nama_instansi='$nama_instansi',
                pimpinan='$pimpinan',
                kep_sekretariat='$kep_sekretariat',
                nip_kep_sekretariat='$nip_kep_sekretariat',
                no_telp='$no_telp',
                alamat='$alamat',
                website='$website',
                logo='$logo'
                WHERE id_site=$id_site";
            } else {
                // Jika ekstensi file tidak diizinkan, kembalikan pesan kesalahan
                header("Location:../../index.php?page=pengaturan&edit=gagal&error=Ekstensi file tidak diizinkan");
                exit;
            }
        } else {
            // Jika tidak ada file yang diunggah, update data lainnya kecuali logo
            $sql = "UPDATE tbl_site SET
            nama_instansi='$nama_instansi',
            pimpinan='$pimpinan',
            kep_sekretariat='$kep_sekretariat',
            nip_kep_sekretariat='$nip_kep_sekretariat',
            no_telp='$no_telp',
            alamat='$alamat',
            website='$website'
            WHERE id_site=$id_site";
        }

        // Mengeksekusi query SQL
        $update_profil_aplikasi = mysqli_query($kon, $sql);

        // Mendapatkan data pengguna yang login
        $kode_pengguna = $_SESSION['kode_pengguna']; // Ambil kode pengguna dari session
        $query_user = "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'";
        $result_user = mysqli_query($kon, $query_user);
        $row_user = mysqli_fetch_assoc($result_user);
        $level = $row_user['level'];

        // Mendapatkan nama admin
        $query_admin = "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'";
        $result_admin = mysqli_query($kon, $query_admin);
        $row_admin = mysqli_fetch_assoc($result_admin);
        $nama = $row_admin['nama'];

        // Mendapatkan tanggal saat ini
        date_default_timezone_set('Asia/Jakarta');
        $tanggal = date('Y-m-d H:i:s'); // Format: YYYY-MM-DD HH:MM:SS

        // Memeriksa apakah query berhasil atau tidak
        if ($update_profil_aplikasi) {
            // Jika berhasil, commit transaksi database
            mysqli_query($kon, "COMMIT");

            // Catat aktivitas ke tabel log
            $aktivitas = "Edit profil aplikasi";
            $status = "berhasil";
            $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                          VALUES ('$tanggal', '$nama', '$level', '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $log_query);

            header("Location:../../index.php?page=pengaturan&edit=berhasil");
        } else {
            // Jika gagal, rollback transaksi database
            mysqli_query($kon, "ROLLBACK");

            // Catat aktivitas ke tabel log
            $aktivitas = "Edit profil aplikasi";
            $status = "gagal";
            $log_query = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                          VALUES ('$tanggal', '$nama', '$level', '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $log_query);

            header("Location:../../index.php?page=pengaturan&edit=gagal");
        }
    }
}
?>
