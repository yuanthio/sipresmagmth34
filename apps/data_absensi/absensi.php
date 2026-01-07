<?php
session_start();
if (isset($_POST['submit_absensi'])) {
    include '../../config/database.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        mysqli_query($kon, "START TRANSACTION");

        function input($data)
        {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        // Mengambil data dari POST
        $id_mahasiswa = $_POST['id_mahasiswa'];
        $id_absensi = $_POST['id_absensi'];
        $id_alasan = $_POST['id_alasan'];
        $status = $_POST["status"];
        $tanggal = $_POST["tanggal"];
        $waktu = $_POST["waktu"];
        $alasan = $_POST["alasan"];
        $latitude = $_POST["latitude"];
        $longitude = $_POST["longitude"];
        $status_lama = $_POST["status_lama"]; // status sebelum diedit

        // Ambil data latitude, longitude, dan status_aktif dari tbl_lokasi_presensi
        $query_lokasi = "SELECT latitude, longitude, status_aktif FROM tbl_lokasi_presensi LIMIT 1";
        $result_lokasi = mysqli_query($kon, $query_lokasi);

        if ($result_lokasi && $row_lokasi = mysqli_fetch_assoc($result_lokasi)) {
            if ($row_lokasi['status_aktif'] == 1) {
                $latitude = $row_lokasi['latitude'];
                $longitude = $row_lokasi['longitude'];
            } else {
                $latitude = '';
                $longitude = '';
            }
        } else {
            // Jika data lokasi tidak ada, kosongkan juga
            $latitude = '';
            $longitude = '';
            echo "<div class='alert alert-danger'>Data lokasi tidak ditemukan.</div>";
        }

        // Jika status adalah "Tidak Hadir", set waktu ke 00:00:00
        if ($status == 4) {
            $waktu = "00:00:00";
        }

        // Query untuk simpan atau update data absensi dengan latitude dan longitude dari tabel tbl_lokasi_presensi
        if (empty($id_absensi)) {
            $sql = "INSERT INTO tbl_absensi (id_mahasiswa, status, tanggal, waktu, latitude, longitude, input_admin)
            VALUES ('$id_mahasiswa', '$status', '$tanggal', '$waktu', '$latitude', '$longitude', 'input_admin')";
        } else {
            $sql = "UPDATE tbl_absensi SET 
            id_mahasiswa = '$id_mahasiswa', 
            status = '$status', 
            tanggal = '$tanggal', 
            waktu = '$waktu',
            latitude = '$latitude',
            longitude = '$longitude',
            input_admin = 'input_admin'
            WHERE id_absensi = '$id_absensi'";
        }

        $simpan_absensi = mysqli_query($kon, $sql);

        if ($simpan_absensi && empty($id_absensi)) {
            $id_absensi = mysqli_insert_id($kon);
        }

        if (isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['error'] == 0) {
            $target_dir = '../../apps/pengguna/bukti_alasan/';
            $file_name = basename($_FILES['bukti_foto']['name']); // Nama file asli
            $file_tmp = $_FILES['bukti_foto']['tmp_name'];
            $file_size = $_FILES['bukti_foto']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $file_path = $target_dir . $file_name; // Tetap pakai nama asli

            // Validasi tipe file (hanya jpg, jpeg, png, gif)
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($file_ext, $allowed_types)) {
                header("Location:../../index.php?page=data_absensi&edit=gagal_tipe_file");
                exit;
            }

            // Validasi ukuran file maksimal 1MB
            if ($file_size > 1048576) {
                header("Location:../../index.php?page=data_absensi&edit=gagal_ukuran_file");
                exit;
            }

            // Jika ada id_alasan berarti update, cek foto lama
            if (!empty($id_alasan)) {
                $sql_get_foto = "SELECT foto FROM tbl_alasan WHERE id_alasan = '$id_alasan'";
                $result = mysqli_query($kon, $sql_get_foto);
                $row = mysqli_fetch_assoc($result);
                $old_file = $target_dir . $row['foto'];

                // Hapus foto lama jika file ada
                if (!empty($row['foto']) && file_exists($old_file)) {
                    unlink($old_file);
                }
            }

            // Pindahkan file
            if (move_uploaded_file($file_tmp, $file_path)) {
                // Simpan ke database
                if (empty($id_alasan)) {
                    $sql = "INSERT INTO tbl_alasan (id_mahasiswa, alasan, tanggal, foto) 
                            VALUES ('$id_mahasiswa', '$alasan', '$tanggal', '$file_name')";
                } else {
                    $sql = "UPDATE tbl_alasan SET 
                            id_mahasiswa = '$id_mahasiswa',  
                            alasan = '$alasan', 
                            tanggal = '$tanggal', 
                            foto = '$file_name'
                            WHERE id_alasan = '$id_alasan'";
                }
                $simpan_izin = mysqli_query($kon, $sql);
            } else {
                header("Location:../../index.php?page=data_absensi&edit=gagal_upload");
                exit;
            }
        } else {
            // Tidak upload foto, hanya simpan alasan
            if (empty($id_alasan)) {
                $sql = "INSERT INTO tbl_alasan (id_mahasiswa, alasan, tanggal) 
                        VALUES ('$id_mahasiswa', '$alasan', '$tanggal')";
            } else {
                $sql = "UPDATE tbl_alasan SET
                        id_mahasiswa = '$id_mahasiswa',  
                        alasan = '$alasan', 
                        tanggal = '$tanggal' 
                        WHERE id_alasan = '$id_alasan'";
            }
            $simpan_izin = mysqli_query($kon, $sql);
        }

        $simpan_wfa = true; // default

        if ($status == 5) {
            if (isset($_FILES['bukti_wfa']) && $_FILES['bukti_wfa']['error'] == 0) {
                $target_dir_wfa = '../../apps/data_absensi/file_wfa/';
                $file_name_wfa = basename($_FILES['bukti_wfa']['name']);
                $file_tmp_wfa = $_FILES['bukti_wfa']['tmp_name'];
                $file_size_wfa = $_FILES['bukti_wfa']['size'];
                $file_ext_wfa = strtolower(pathinfo($file_name_wfa, PATHINFO_EXTENSION));
                $file_path_wfa = $target_dir_wfa . $file_name_wfa;

                // Validasi file (hanya pdf, doc, docx, jpg, jpeg, png)
                $allowed_wfa = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
                if (!in_array($file_ext_wfa, $allowed_wfa)) {
                    header("Location:../../index.php?page=data_absensi&edit=gagal_tipe_wfa");
                    exit;
                }

                // Validasi ukuran (max 2MB misalnya)
                if ($file_size_wfa > 1048576) {
                    header("Location:../../index.php?page=data_absensi&edit=gagal_ukuran_wfa");
                    exit;
                }

                // Cek apakah ada record sebelumnya
                $cek_wfa = mysqli_query($kon, "SELECT bukti_wfa FROM tbl_bukti_wfa 
                                        WHERE id_mahasiswa='$id_mahasiswa' AND tanggal='$tanggal' LIMIT 1");
                if ($row_wfa = mysqli_fetch_assoc($cek_wfa)) {
                    $old_wfa = $row_wfa['bukti_wfa'];
                    if (!empty($old_wfa) && file_exists($target_dir_wfa . $old_wfa)) {
                        unlink($target_dir_wfa . $old_wfa);
                    }
                    // Update record
                    if (move_uploaded_file($file_tmp_wfa, $file_path_wfa)) {
                        $sql_wfa = "UPDATE tbl_bukti_wfa SET bukti_wfa='$file_name_wfa' 
                            WHERE id_mahasiswa='$id_mahasiswa' AND tanggal='$tanggal'";
                        $simpan_wfa = mysqli_query($kon, $sql_wfa);
                    }
                } else {
                    // Insert record baru
                    if (move_uploaded_file($file_tmp_wfa, $file_path_wfa)) {
                        $sql_wfa = "INSERT INTO tbl_bukti_wfa (id_mahasiswa, tanggal, bukti_wfa) 
                            VALUES ('$id_mahasiswa','$tanggal','$file_name_wfa')";
                        $simpan_wfa = mysqli_query($kon, $sql_wfa);
                    }
                }
            }
        }

        // Memeriksa apakah absensi dan alasan tersimpan dengan benar
        if ($simpan_absensi && $simpan_izin && $simpan_wfa) {
            mysqli_query($kon, "COMMIT");
            $status_aktivitas = "berhasil";
            header("Location:../../index.php?page=data_absensi&edit=berhasil");
        } else {
            mysqli_query($kon, "ROLLBACK");
            $status_aktivitas = "gagal";
            header("Location:../../index.php?page=data_absensi&edit=gagal");
        }

        // Hapus data WFA jika status berubah dari WFA (5) ke status lain
        if ($status_lama == 5 && $status != 5) {
            // Ambil file lama
            $q_wfa = mysqli_query($kon, "SELECT bukti_wfa FROM tbl_bukti_wfa WHERE id_mahasiswa='$id_mahasiswa' AND tanggal='$tanggal' LIMIT 1");
            if ($row_wfa = mysqli_fetch_assoc($q_wfa)) {
                $old_file_wfa = $row_wfa['bukti_wfa'];
                $path_wfa = "../../apps/data_absensi/file_wfa/" . $old_file_wfa;

                // Hapus file jika ada
                if (!empty($old_file_wfa) && file_exists($path_wfa)) {
                    unlink($path_wfa);
                }

                // Hapus record dari database
                mysqli_query($kon, "DELETE FROM tbl_bukti_wfa WHERE id_mahasiswa='$id_mahasiswa' AND tanggal='$tanggal'");
            }
        }

        if ($status != 2 && $id_alasan != "") {
            // Mengambil foto alasan jika ada
            $query_foto = "SELECT foto FROM tbl_alasan WHERE id_alasan = '$id_alasan' LIMIT 1";
            $hasil_foto = mysqli_query($kon, $query_foto);
            $data_foto = mysqli_fetch_assoc($hasil_foto);

            if ($data_foto && !empty($data_foto['foto'])) {
                $file_path = '../../apps/pengguna/bukti_alasan/' . $data_foto['foto'];
                if (file_exists($file_path)) {
                    unlink($file_path); // Hapus file dari server
                }
            }

            // Hapus alasan jika status bukan Izin
            $hapus_alasan_query = "DELETE FROM tbl_alasan WHERE id_alasan = '$id_alasan'";
            $hapus_alasan = mysqli_query($kon, $hapus_alasan_query);
            if (!$hapus_alasan) {
                echo "<div class='alert alert-danger'>Gagal menghapus alasan.</div>";
            }
        }

        // Hapus file kamera jika status lama Hadir (1) atau Terlambat (3) lalu berubah ke status lain
        if (in_array($status_lama, [1, 3]) && !in_array($status, [1, 3]) && !empty($id_absensi)) {
            $query_foto_kamera = mysqli_query($kon, "SELECT kamera FROM tbl_absensi WHERE id_absensi='$id_absensi'");
            $data_foto_kamera = mysqli_fetch_assoc($query_foto_kamera);
            $file_kamera = $data_foto_kamera['kamera'];

            if (!empty($file_kamera)) {
                $path_kamera = '../../apps/pengguna/kamera/' . $file_kamera;
                if (file_exists($path_kamera)) {
                    unlink($path_kamera); // hapus file fisik
                }
            }

            // Kosongkan kolom kamera di database
            mysqli_query($kon, "UPDATE tbl_absensi SET kamera='' WHERE id_absensi='$id_absensi'");
        }

        if (in_array($status, [1, 3]) && isset($_FILES['kamera_baru']) && $_FILES['kamera_baru']['error'] == 0) {
            $target_dir_kamera = '../../apps/pengguna/kamera/';
            $file_tmp = $_FILES['kamera_baru']['tmp_name'];
            $file_ext = strtolower(pathinfo($_FILES['kamera_baru']['name'], PATHINFO_EXTENSION));
            $file_size = $_FILES['kamera_baru']['size'];

            $allowed = ['jpg', 'jpeg', 'png'];
            if (!in_array($file_ext, $allowed)) {
                header("Location:../../index.php?page=data_absensi&edit=gagal_tipe_kamera");
                exit;
            }

            if ($file_size > 1048576) {
                header("Location:../../index.php?page=data_absensi&edit=gagal_ukuran_kamera");
                exit;
            }

            // ✅ Format nama: kamera_TIMESTAMP_RANDOM.ekstensi
            $timestamp = time();
            $random = rand(100, 999);
            $nama_file_baru = 'kamera_' . $timestamp . '_' . $random . '.' . $file_ext;
            $path_baru = $target_dir_kamera . $nama_file_baru;

            // 🔄 Hapus foto lama
            $query_old = mysqli_query($kon, "SELECT kamera FROM tbl_absensi WHERE id_absensi='$id_absensi'");
            $row_old = mysqli_fetch_assoc($query_old);
            $foto_lama = $row_old['kamera'];
            if (!empty($foto_lama) && file_exists($target_dir_kamera . $foto_lama)) {
                unlink($target_dir_kamera . $foto_lama);
            }

            // ✅ Simpan file baru
            if (move_uploaded_file($file_tmp, $path_baru)) {
                mysqli_query($kon, "UPDATE tbl_absensi SET kamera='$nama_file_baru' WHERE id_absensi='$id_absensi'");
            } else {
                header("Location:../../index.php?page=data_absensi&edit=gagal_upload_kamera");
                exit;
            }
        }

        // Ambil nama mahasiswa setelah menyimpan absensi
        $query_nama_mahasiswa = "SELECT nama FROM tbl_mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'";
        $hasil_nama_mahasiswa = mysqli_query($kon, $query_nama_mahasiswa);
        $data_nama_mahasiswa = mysqli_fetch_assoc($hasil_nama_mahasiswa);
        $nama_mahasiswa = $data_nama_mahasiswa['nama'];

        // Log Aktivitas
        date_default_timezone_set('Asia/Jakarta');
        $tanggal_sekarang = date("Y-m-d H:i:s"); // Menggunakan timezone Indonesia WIB

        // Mengambil data pengguna yang login (Admin)
        $kode_pengguna = $_SESSION['kode_pengguna'];
        $query_user = "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'";
        $hasil_user = mysqli_query($kon, $query_user);
        $data_user = mysqli_fetch_assoc($hasil_user);
        $level_pengguna = $data_user['level'];

        // Mengambil data admin berdasarkan kode_pengguna
        $query_admin = "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'";
        $hasil_admin = mysqli_query($kon, $query_admin);
        $data_admin = mysqli_fetch_assoc($hasil_admin);
        $nama_admin = $data_admin['nama'];

        // Menyimpan log aktivitas ke tabel tbl_log_aktivitas
        $aktivitas = "Edit data presensi mahasiswa ($nama_mahasiswa)";
        $sql_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                    VALUES ('$tanggal_sekarang', '$nama_admin', '$level_pengguna', '$kode_pengguna', '$aktivitas', '$status_aktivitas')";
        mysqli_query($kon, $sql_log);
    }
}
?>

<?php
$id_absensi = $_POST['id_absensi'];
include '../../config/database.php';
include '../../config/function.php';

$sql = EditAbsensi($id_absensi);
$result = $kon->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $id_mahasiswa = $row['id_mahasiswa'];
    $status = $row['status'];
    $tanggal = $row['tanggal'];
    $waktu = $row['waktu'];
} else {
    $id_mahasiswa = "";
    $status = "";
    date_default_timezone_set("Asia/Jakarta");
    $tanggal = date("Y-m-d");
    $waktu = date("H:i:s");
}
?>

<?php
include '../../config/database.php';

$query = "SELECT id_alasan, alasan FROM tbl_alasan WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = '$tanggal';";
$result = $kon->query($query);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $id_alasan = $row['id_alasan'];
    $alasan = $row['alasan'];
} else {
    $id_alasan = "";
    $alasan = "";
}
?>

<?php
include '../../config/database.php';

$query = "SELECT id_alasan, alasan, foto FROM tbl_alasan WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = '$tanggal';";
$result = $kon->query($query);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $id_alasan = $row['id_alasan'];
    $alasan = $row['alasan'];
    $foto = $row['foto']; // ambil nama file fotonya
} else {
    $id_alasan = "";
    $alasan = "";
    $foto = "";
}
?>

<?php
$foto_kamera = '';
$tampilkan_foto_kamera = false;
$kamera_url = 'apps/pengguna/kamera/foto_default.png'; // default foto

if (!empty($_POST['id_absensi'])) {
    $id_absensi = $_POST['id_absensi'];
    $q_foto = mysqli_query($kon, "SELECT kamera FROM tbl_absensi WHERE id_absensi='$id_absensi' LIMIT 1");
    $r_foto = mysqli_fetch_assoc($q_foto);

    if ($r_foto && !empty($r_foto['kamera'])) {
        $foto_kamera = $r_foto['kamera'];
        $file_path = "../../apps/pengguna/kamera/" . $foto_kamera;
        $file_url = "apps/pengguna/kamera/" . $foto_kamera;

        if (file_exists($file_path)) {
            $tampilkan_foto_kamera = true;
            $kamera_url = $file_url;
        }
    }
}
?>

<?php
// Ambil data WFA
$query_wfa = "SELECT id_bukti_wfa, bukti_wfa FROM tbl_bukti_wfa WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = '$tanggal'";
$result_wfa = $kon->query($query_wfa);

if ($result_wfa->num_rows > 0) {
    $row_wfa = $result_wfa->fetch_assoc();
    $id_bukti_wfa = $row_wfa['id_bukti_wfa'];
    $bukti_wfa = $row_wfa['bukti_wfa'];
    $wfa_path = 'apps/data_absensi/file_wfa/' . $bukti_wfa;
    $ext = strtolower(pathinfo($bukti_wfa, PATHINFO_EXTENSION));
} else {
    $id_bukti_wfa = "";
    $bukti_wfa = "";
    $wfa_path = 'apps/data_absensi/file_wfa/gambar_default/No_gambar.jpg';
    $ext = "";
}
?>

<?php
include '../../config/database.php';

$kamera_perangkat = 1; // Default aktif
$hasil = mysqli_query($kon, "SELECT kamera_perangkat FROM tbl_kamera LIMIT 1");
if ($row = mysqli_fetch_assoc($hasil)) {
    $kamera_perangkat = $row['kamera_perangkat'];
}
?>

<form action="apps/data_absensi/absensi.php" method="post" enctype="multipart/form-data"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">

                <!-- Input untuk menyimpan id untuk proses query  -->
                <input type="hidden" name="id_mahasiswa" value="<?php echo $_POST['id_mahasiswa']; ?>">
                <input type="hidden" name="id_absensi" value="<?php echo $_POST['id_absensi']; ?>">
                <input type="hidden" name="id_alasan" value="<?php echo $id_alasan; ?>">
                <input type="hidden" id="latitude" name="latitude">
                <input type="hidden" id="longitude" name="longitude">
                <input type="hidden" name="status_lama" value="<?php echo $status; ?>">
                <!-- Input untuk menyimpan id untuk proses query -->

                <label>Tanggal Presensi :</label>
                <input type="date" name="tanggal" class="form-control" value="<?php echo $tanggal; ?>" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Waktu Presensi :</label>
                <input type="time" name="waktu" class="form-control" value="<?php echo $waktu; ?>" required
                    oninvalid="this.setCustomValidity('Harap waktu presensi di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Status Presensi :</label>
                <select class="form-control" id="status" name="status" required
                    oninvalid="this.setCustomValidity('Harap status presensi di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
                    <option value="0" <?php if ($status == 0)
                        echo 'selected'; ?>>Pilih</option>
                    <option value="1" <?php if ($status == 1)
                        echo 'selected'; ?>>Hadir</option>
                    <option value="2" <?php if ($status == 2)
                        echo 'selected'; ?>>Izin</option>
                    <option value="3" <?php if ($status == 3)
                        echo 'selected'; ?>>Terlambat</option>
                    <option value="4" <?php if ($status == 4)
                        echo 'selected'; ?>>Tidak Hadir</option>
                    <option value="5" <?php if ($status == 5)
                        echo 'selected'; ?>>WFA</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6" id="text_wfa" style="display:none;">
            <div class="form-group">
                <label>Bukti Arahan Atasan (jpg, jpeg, png, docx, doc, pdf) :</label>
                <input type="file" name="bukti_wfa" id="bukti_wfa" class="form-control" accept="*/*">

                <?php
                $icon_path = '';
                if ($ext === 'pdf') {
                    $icon_path = 'apps/data_absensi/extensi_file/pdf.png';
                } elseif ($ext === 'doc') {
                    $icon_path = 'apps/data_absensi/extensi_file/doc.png';
                } elseif ($ext === 'docx') {
                    $icon_path = 'apps/data_absensi/extensi_file/docx.png';
                }

                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    // Jika file berupa gambar → tampilkan preview langsung
                    echo '<div id="preview-container-wfa" style="margin-top:10px;">';
                    echo '<img src="' . $wfa_path . '" class="img-thumbnail" style="width:150px;height:150px;object-fit:cover;border:1px solid #ccc;padding:5px;">';
                    echo '</div>';
                } elseif (in_array($ext, ['pdf', 'doc', 'docx'])) {
                    echo '<div id="preview-container-wfa" style="margin-top:10px;display:none;"></div>';
                    echo '<div style="display:flex;align-items:center;margin-top:10px;">';
                    if ($icon_path) {
                        echo '<img src="' . $icon_path . '" width="45" style="margin-right:5px; background-color: white; border-radius: 5px;">';
                    }
                    echo '<input type="text" class="form-control" id="bukti_wfa_nama" 
                        value="' . $bukti_wfa . '" readonly>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        <div class="col-sm-6" id="text_alasan" style="display:none;">
            <div class="form-group">
                <label>Alasan :</label>
                <input type="text" name="alasan" id="alasan" class="form-control" value="<?php echo $alasan; ?>"
                    placeholder="Masukkan alasan" required
                    oninvalid="this.setCustomValidity('Harap alasan di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
            <div class="form-group">
                <label>Bukti Alasan:</label>
                <input type="file" name="bukti_foto" id="bukti_foto" class="form-control" accept="image/*" required
                    oninvalid="this.setCustomValidity('Harap bukti alasan di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
                <div id="preview-container" style="margin-top: 10px;">
                    <?php
                    $default_foto = 'apps/pengguna/bukti_alasan/No_gambar.jpg';
                    $foto_path = !empty($foto) ? 'apps/pengguna/bukti_alasan/' . $foto : $default_foto;
                    ?>
                    <img id="preview-image" src="<?php echo $foto_path; ?>" alt="Preview Gambar" class="img-thumbnail"
                        style="width: 150px; height: 150px; object-fit: cover; border: 1px solid #ccc; padding: 5px;">
                </div>
            </div>
        </div>
        <?php if ($kamera_perangkat == 1): ?>
            <div class="col-sm-6" id="foto_kamera_preview"
                style="<?php echo (in_array($status, [1, 3])) ? '' : 'display: none;'; ?>">
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-7">
                            <label>Upload Foto (Opsional):</label>
                            <input type="file" name="kamera_baru" id="kamera_baru" class="form-control" accept="image/*">
                        </div>
                        <div class="col-sm-5">
                            <label>Preview</label>
                            <div style="width: 100%;">
                                <img id="kamera-preview" src="<?php echo $kamera_url; ?>" class="img-thumbnail"
                                    style="width: 130px; height: 130px; object-fit: cover; border: 2px solid #ccc;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="submit_absensi" id="submit_absensi" class="btn btn-success"><i
                        class="fa fa-clock-o"></i> Simpan</button>
            </div>
        </div>
    </div>
</form>

<script>
    $(document).ready(function () {
        // Cek status pada saat halaman dimuat
        let status = $("#status").val();
        if (status == "2") {
            $("#text_alasan").show();
            $("#alasan").attr("required", true);
        } else {
            $("#text_alasan").hide();
            $("#alasan").attr("required", false);
        }

        let foto = "<?php echo isset($row['foto']) ? $row['foto'] : ''; ?>";
        if (foto) {
            $('#preview-image').attr('src', 'apps/pengguna/bukti_alasan/' + foto).show();
        }

        function toggleAlasanField(status) {
            if (status == "2") {
                // Izin
                $("#text_alasan").show();
                $("#alasan").attr("required", true);
                $("#bukti_foto").attr("required", true);
            } else {
                $("#text_alasan").hide();
                $("#alasan").removeAttr("required");
                $("#bukti_foto").removeAttr("required");
            }

            if (status === "1" || status === "3") {
                // Hadir / Terlambat
                $("#foto_kamera_preview").show();
            } else {
                $("#foto_kamera_preview").hide();
            }

            if (status == "4") {
                // Tidak hadir
                $("input[name='waktu']").val("00:00:00").prop("disabled", true);
            } else {
                $("input[name='waktu']").prop("disabled", false);
                if ($("input[name='waktu']").val() === "00:00:00") {
                    $("input[name='waktu']").val(""); // kosongkan jika sebelumnya dari tidak hadir
                }
            }

            if (status == "5") {
                // WFA
                $("#text_wfa").show();
                $("#bukti_wfa").attr("required", true);
            } else {
                $("#text_wfa").hide();
                $("#bukti_wfa").removeAttr("required");
            }
        }

        // Panggil saat halaman pertama kali dimuat
        toggleAlasanField($("#status").val());

        // Panggil ulang setiap status berubah
        $("#status").change(function () {
            toggleAlasanField($(this).val());
        });

        $("#bukti_wfa").change(function () {
            const file = this.files[0];
            if (file) {
                const fileName = file.name;
                const fileExt = fileName.split('.').pop().toLowerCase();
                const imageTypes = ['jpg', 'jpeg', 'png'];
                const docTypes = ['pdf', 'doc', 'docx'];

                if (imageTypes.includes(fileExt)) {
                    // Preview gambar
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        $('#preview-container-wfa').html(
                            '<img src="' + event.target.result +
                            '" class="img-thumbnail" style="width:150px;height:150px;object-fit:cover;border:1px solid #ccc;padding:5px;">'
                        ).show();
                    };
                    reader.readAsDataURL(file);

                    $("#bukti_wfa_nama").closest('div').hide(); // sembunyikan input nama file
                } else if (docTypes.includes(fileExt)) {
                    // Tentukan ikon berdasarkan ekstensi
                    let icon = '';
                    if (fileExt === 'pdf') icon = 'pdf.png';
                    if (fileExt === 'doc') icon = 'doc.png';
                    if (fileExt === 'docx') icon = 'docx.png';

                    // Sembunyikan preview
                    $('#preview-container-wfa').hide();

                    // Tampilkan ikon + nama file
                    $("#bukti_wfa_nama").val(fileName).show();
                    if ($("#bukti_wfa_nama").prev("img").length) {
                        $("#bukti_wfa_nama").prev("img").attr("src", "apps/data_absensi/extensi_file/" + icon);
                    } else {
                        $("#bukti_wfa_nama").before('<img src="apps/data_absensi/extensi_file/' + icon + '" width="20" height="20" style="margin-right:5px;">');
                    }
                    $("#bukti_wfa_nama").closest('div').show();
                } else {
                    // File lain
                    $('#preview-container-wfa').hide();
                    $("#bukti_wfa_nama").val(fileName).show();
                    $("#bukti_wfa_nama").prev("img").remove();
                }
            } else {
                // Jika batal pilih
                $('#preview-container-wfa').hide();
                $("#bukti_wfa_nama").val("Tidak ada file").show();
                $("#bukti_wfa_nama").prev("img").remove();
            }
        });

        $("#bukti_wfa").change(function () {
            const file = this.files[0];
            if (file) {
                $("#bukti_wfa_nama").val(file.name); // tampilkan nama file baru
            } else {
                $("#bukti_wfa_nama").val("Tidak ada file"); // kalau batal pilih
            }
        });

        // Preview gambar sebelum upload
        $("#bukti_foto").change(function (e) {
            const file = this.files[0];
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];

            if (file) {
                if (allowedTypes.includes(file.type)) {
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        $('#preview-image').attr('src', event.target.result).show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    // Jika tipe file tidak sesuai, tampilkan not_found.jpg
                    $('#preview-image').attr('src', 'apps/data_absensi/not_found/not_found.jpg').show();
                }
            } else {
                // Jika tidak ada file dipilih
                $('#preview-image').hide();
            }
        });

        $(".file").change(function () {
            let file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    $("#preview").attr("src", e.target.result);
                }
                reader.readAsDataURL(file);
                $("#file").val(file.name);
            }
        });

        // Tombol "Pilih" akan memicu input file
        $("#pilih_foto").click(function () {
            $(".file").click();
        });

        $("#kamera_baru").change(function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $("#kamera-preview").attr("src", e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>

<script>
    document.getElementById("status").addEventListener("change", function () {
        const waktuInput = document.querySelector('input[name="waktu"]');

        if (this.value === "4") { // Tidak Hadir
            waktuInput.value = "00:00";
            waktuInput.disabled = true;
        } else {
            waktuInput.disabled = false;
            if (waktuInput.value === "00:00") {
                waktuInput.value = ""; // kosongkan jika sebelumnya dari tidak hadir
            }
        }
    });
</script>

<!-- <script>
    let lokasiTersedia = false;

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                $("#latitude").val(position.coords.latitude);
                $("#longitude").val(position.coords.longitude);
                lokasiTersedia = true;
                console.log("Lokasi didapat: ", position.coords.latitude, position.coords.longitude);
            },
            function (error) {
                console.warn(`ERROR(${error.code}): ${error.message}`);
                Swal.fire({
                    icon: 'warning',
                    title: '<span style="font-size: 1.5em;">Lokasi tidak diizinkan!</span>',
                    html: '<span style="font-size: 1.5em;">Silakan izinkan akses lokasi untuk melakukan presensi.</span>',
                    confirmButtonColor: '#3085d6'
                });
                lokasiTersedia = false;
            },
            {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            }
        );
    } else {
        Swal.fire({
            icon: 'error',
            title: '<span style="font-size: 1.5em;">Geolocation tidak didukung!</span>',
            html: '<span style="font-size: 1.5em;">Browser kamu tidak mendukung fitur lokasi.</span>',
            confirmButtonColor: '#3085d6'
        });
        lokasiTersedia = false;
    }

    // Cek lokasi sebelum submit
    document.getElementById("submit_absensi").addEventListener("click", function (e) {
        if (!lokasiTersedia) {
            e.preventDefault(); // Mencegah submit
            Swal.fire({
                icon: 'error',
                title: '<span style="font-size: 1.5em;">Presensi gagal!</span>',
                html: '<span style="font-size: 1.5em;">Lokasi belum aktif. Silakan aktifkan lokasi dulu!</span>',
                confirmButtonColor: '#d33'
            });
        }
    });
</script> -->