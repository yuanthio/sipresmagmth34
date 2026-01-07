<?php
session_start();
if (isset($_POST['simpan_absensi'])) {

    include '../../config/database.php';

    function input($data)
    {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    $id_mahasiswa = input($_POST["id_mahasiswa"]);
    $tanggal = input($_POST["tanggal"]);
    $waktu = input($_POST["waktu"]);
    $status = input($_POST["status"]);
    $alasan = isset($_POST["alasan"]) ? input($_POST["alasan"]) : '';

    // Ambil nama mahasiswa
    $query_mahasiswa = "SELECT nama FROM tbl_mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'";
    $result_mahasiswa = mysqli_query($kon, $query_mahasiswa);
    $mahasiswa_data = mysqli_fetch_assoc($result_mahasiswa);
    $nama_mahasiswa = $mahasiswa_data['nama'];

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        mysqli_begin_transaction($kon);

        // Cek absensi sudah ada
        $query = "SELECT * FROM tbl_absensi WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = '$tanggal'";
        $result = mysqli_query($kon, $query);

        if (mysqli_num_rows($result) > 0) {
            mysqli_rollback($kon);
            header("Location:../../index.php?page=data_absensi&mulai=gagal_duplikat");
            exit;
        }

        // Ambil lokasi presensi kecuali jika status = 4 (Tidak Hadir)
        if ($status != "4") {
            $query_lokasi = "SELECT latitude, longitude, status_aktif FROM tbl_lokasi_presensi WHERE id_lokasi_presensi = 1";
            $result_lokasi = mysqli_query($kon, $query_lokasi);
            if (mysqli_num_rows($result_lokasi) > 0) {
                $lokasi_data = mysqli_fetch_assoc($result_lokasi);
                $latitude = ($lokasi_data['status_aktif'] == 1) ? $lokasi_data['latitude'] : '';
                $longitude = ($lokasi_data['status_aktif'] == 1) ? $lokasi_data['longitude'] : '';
            } else {
                $latitude = '';
                $longitude = '';
            }
        } else {
            $latitude = '';
            $longitude = '';
        }

        $simpan_absensi = false;
        $simpan_izin = true;

        // ================== STATUS ABSENSI ==================
        if ($status == "5") {
            // === WFA ===
            $sql_absensi = "INSERT INTO tbl_absensi (id_mahasiswa, status, waktu, tanggal, latitude, longitude, input_admin)
                            VALUES ('$id_mahasiswa', '$status', '$waktu', '$tanggal', '$latitude', '$longitude', 'input_admin')";
            $simpan_absensi = mysqli_query($kon, $sql_absensi);

            if (isset($_FILES['bukti_wfa']) && $_FILES['bukti_wfa']['error'] == UPLOAD_ERR_OK) {
                $target_dir = "../../apps/data_absensi/file_wfa/";
                $file_name = basename($_FILES["bukti_wfa"]["name"]);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_types = ['jpg','jpeg','png','doc','docx','pdf'];

                if (!in_array($file_ext, $allowed_types)) {
                    header("Location:../../index.php?page=data_absensi&mulai=gagal_tipe_file_wfa");
                    exit;
                }
                if ($_FILES["bukti_wfa"]["size"] > 1048576) {
                    header("Location:../../index.php?page=data_absensi&mulai=gagal_ukuran_file_wfa");
                    exit;
                }

                $unique_file_name = "wfa_" . time() . "_" . rand(100,999) . "." . $file_ext;
                $target_file = $target_dir . $unique_file_name;

                if (move_uploaded_file($_FILES["bukti_wfa"]["tmp_name"], $target_file)) {
                    $sql_bukti = "INSERT INTO tbl_bukti_wfa (id_mahasiswa, bukti_wfa, tanggal)
                                  VALUES ('$id_mahasiswa', '$unique_file_name', '$tanggal')";
                    mysqli_query($kon, $sql_bukti);
                } else {
                    header("Location:../../index.php?page=data_absensi&mulai=gagal_upload_wfa");
                    exit;
                }
            } else {
                header("Location:../../index.php?page=data_absensi&mulai=gagal_upload_wfa");
                exit;
            }

        } elseif ($status == "2") {
            // === IZIN ===
            $sql_absensi = "INSERT INTO tbl_absensi (id_mahasiswa, status, waktu, tanggal, latitude, longitude, input_admin)
                            VALUES ('$id_mahasiswa', '$status', '$waktu', '$tanggal', '$latitude', '$longitude', 'input_admin')";
            $simpan_absensi = mysqli_query($kon, $sql_absensi);

            if (isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['error'] == UPLOAD_ERR_OK) {
                $target_dir = "../../apps/pengguna/bukti_alasan/";
                $file_name = basename($_FILES["bukti_foto"]["name"]);
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_types = ['jpg','jpeg','png','gif'];

                if (!in_array($file_ext, $allowed_types)) {
                    header("Location:../../index.php?page=data_absensi&mulai=gagal_tipe_file");
                    exit;
                }
                if ($_FILES["bukti_foto"]["size"] > 1048576) {
                    header("Location:../../index.php?page=data_absensi&mulai=gagal_ukuran_file");
                    exit;
                }

                $unique_file_name = "izin_" . time() . "_" . rand(100,999) . "." . $file_ext;
                $target_file = $target_dir . $unique_file_name;

                if (move_uploaded_file($_FILES["bukti_foto"]["tmp_name"], $target_file)) {
                    $sql_izin = "INSERT INTO tbl_alasan (id_mahasiswa, alasan, tanggal, foto)
                                 VALUES ('$id_mahasiswa', '$alasan', '$tanggal', '$unique_file_name')";
                    $simpan_izin = mysqli_query($kon, $sql_izin);
                } else {
                    header("Location:../../index.php?page=data_absensi&mulai=gagal_upload");
                    exit;
                }
            } else {
                header("Location:../../index.php?page=data_absensi&mulai=gagal_upload");
                exit;
            }

        } else {
            // HADIR / TERLAMBAT / TIDAK HADIR
            $sql_absensi = "INSERT INTO tbl_absensi (id_mahasiswa, status, waktu, tanggal, latitude, longitude, input_admin)
                            VALUES ('$id_mahasiswa', '$status', '$waktu', '$tanggal', '$latitude', '$longitude', 'input_admin')";
            $simpan_absensi = mysqli_query($kon, $sql_absensi);
        }

        // ================== UPLOAD KAMERA ==================
        if (isset($_FILES['kamera']) && $_FILES['kamera']['error'] == UPLOAD_ERR_OK) {
            $target_dir_kamera = "../../apps/pengguna/kamera/";
            $original_name = basename($_FILES["kamera"]["name"]);
            $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $allowed_types = ['jpg','jpeg','png'];

            if (in_array($file_ext, $allowed_types) && $_FILES["kamera"]["size"] <= 1048576) {
                $nama_file_kamera = "kamera_" . time() . "_" . rand(100,999) . "." . $file_ext;
                $target_file = $target_dir_kamera . $nama_file_kamera;
                if (move_uploaded_file($_FILES["kamera"]["tmp_name"], $target_file)) {
                    $id_absensi_terakhir = mysqli_insert_id($kon);
                    mysqli_query($kon, "UPDATE tbl_absensi SET kamera = '$nama_file_kamera' WHERE id_absensi = '$id_absensi_terakhir'");
                }
            }
        }

        // ================== LOG AKTIVITAS ==================
        if ($simpan_absensi && $simpan_izin) {
            mysqli_commit($kon);
            $status_aktivitas = 'berhasil';
            $aktivitas = "Tambah data presensi mahasiswa ($nama_mahasiswa)";
        } else {
            mysqli_rollback($kon);
            $status_aktivitas = 'gagal';
            $aktivitas = "Tambah data presensi mahasiswa ($nama_mahasiswa) gagal";
        }

        date_default_timezone_set("Asia/Jakarta");
        $tanggal_sekarang = date("Y-m-d H:i:s");
        $kode_pengguna = $_SESSION['kode_pengguna'];
        $query_user = "SELECT level FROM tbl_user WHERE kode_pengguna='$kode_pengguna'";
        $result_user = mysqli_query($kon, $query_user);
        $user_data = mysqli_fetch_assoc($result_user);
        $level = $user_data['level'];

        $query_admin = "SELECT nama FROM tbl_admin WHERE kode_admin='$kode_pengguna'";
        $result_admin = mysqli_query($kon, $query_admin);
        $admin_data = mysqli_fetch_assoc($result_admin);
        $nama_admin = $admin_data['nama'];

        $sql_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                    VALUES ('$tanggal_sekarang', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status_aktivitas')";
        mysqli_query($kon, $sql_log);

        // Redirect
        if ($status_aktivitas == 'berhasil') {
            header("Location:../../index.php?page=data_absensi&mulai=berhasil");
        } else {
            header("Location:../../index.php?page=data_absensi&mulai=gagal");
        }
    }
}
?>

<?php
include '../../config/database.php';

$kamera_perangkat = 1; // default
$hasil = mysqli_query($kon, "SELECT kamera_perangkat FROM tbl_kamera LIMIT 1");
if ($row = mysqli_fetch_assoc($hasil)) {
    $kamera_perangkat = $row['kamera_perangkat'];
}
?>

<form action="apps/data_absensi/tambah.php" method="post" enctype="multipart/form-data"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="row">
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Nama Karyawan Magang :</label>
                <select class="form-control" id="id_mahasiswa" name="id_mahasiswa" required
                    oninvalid="this.setCustomValidity('Harap nama karyawan magang di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
                    <option value="" selected disabled>Pilih Karyawan Magang</option>
                    <?php
                    include '../../config/database.php';
                    if ($_SESSION["level"] == 'Mentor') {
                        $mentor_name = $_SESSION["nama_mentor"];
                        $query = "SELECT id_mahasiswa, nama FROM tbl_mahasiswa WHERE mulai_magang <= CURDATE() AND akhir_magang >= CURDATE() AND kode_mentor = (SELECT kode_mentor FROM tbl_mentor WHERE nama = '$mentor_name');";
                    } else {
                        $query = "SELECT id_mahasiswa, nama FROM tbl_mahasiswa WHERE mulai_magang <= CURDATE() AND akhir_magang >= CURDATE();";
                    }

                    $result = mysqli_query($kon, $query);
                    while ($data = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . $data['id_mahasiswa'] . "'>" . $data['nama'] . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Status :</label>
                <select class="form-control" id="status" name="status" required autofocus
                    oninvalid="this.setCustomValidity('Harap status presensi di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
                    <option value="" selected disabled>Pilih</option>
                    <option value="1">Hadir</option>
                    <option value="2">Izin</option>
                    <option value="3">Terlambat</option>
                    <option value="4">Tidak Hadir</option>
                    <option value="5">WFA</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Tanggal Presensi :</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control" value="" required autofocus
                    oninvalid="this.setCustomValidity('Harap tanggal presensi di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Waktu Presensi :</label>
                <input type="time" name="waktu" id="waktu" class="form-control" value="" required autofocus
                    oninvalid="this.setCustomValidity('Harap waktu presensi di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-12" id="text_wfa">
            <div class="form-group">
                <label>Bukti Arahan Atasan (jpg, jpeg, png, docx, doc, pdf) :</label>
                <input type="file" name="bukti_wfa" id="bukti_wfa" class="form-control" accept="*/*">
                <!-- Tempat untuk pratinjau gambar -->
                <div id="preview-container-wfa" style="margin-top: 10px;">
                    <img id="preview-image-wfa" src="" alt="Preview Gambar" class="img-thumbnail"
                        style="display: none; width: 150px; height: 150px; object-fit: cover; border: 1px solid #ccc; padding: 5px;">
                </div>
            </div>
        </div>
        <?php if ($kamera_perangkat == 1): ?>
            <div class="col-sm-12" id="foto_kamera_preview">
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-6">
                            <label>Upload Foto (Opsional):</label>
                            <input type="file" name="kamera" id="kamera" class="form-control" accept="image/*">
                        </div>
                        <div class="col-sm-6">
                            <label>Preview</label>
                            <div style="width: 100%;">
                                <img id="kamera-preview" src="apps/pengguna/kamera/foto_default.png" class="img-thumbnail"
                                    style="width: 130px; height: 130px; object-fit: cover; border: 2px solid #ccc;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="col-sm-12" id="text_alasan" style="display:none;">
            <div class="form-group">
                <label>Alasan :</label>
                <input type="text" name="alasan" id="alasan" class="form-control" value="" required autofocus
                    oninvalid="this.setCustomValidity('Harap alasan di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')" placeholder="Masukkan Alasan Kenapa Izin">
            </div>
            <div class="form-group">
                <label>Bukti Alasan:</label>
                <input type="file" name="bukti_foto" id="bukti_foto" class="form-control" accept="image/*">
                <!-- Tempat untuk pratinjau gambar -->
                <div id="preview-container" style="margin-top: 10px;">
                    <img id="preview-image" src="" alt="Preview Gambar"
                        style="display: none; width: 150px; height: 150px; object-fit: cover; border: 1px solid #ccc; padding: 5px;">
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="simpan_absensi" id="simpan_absensi" class="btn btn-success"><i
                        class="fa fa-plus"></i> Simpan</button>
                <button type="reset" class="btn btn-warning"><i class="fa fa-trash"></i> Reset</button>
            </div>
        </div>
    </div>
</form>

<script>
    const kameraInput = document.getElementById("kamera");
    const kameraPreview = document.getElementById("kamera-preview");

    if (kameraInput && kameraPreview) {
        kameraInput.addEventListener("change", function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    kameraPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                kameraPreview.src = "apps/pengguna/kamera/foto_default.png";
            }
        });
    }
</script>

<script>
    $(document).ready(function () {
        // Sembunyikan field tambahan saat load awal
        $("#text_alasan").hide();
        $("#text_wfa").hide();
        $("#foto_kamera_preview").hide();

        $("#status").change(function () {
            const status = $(this).val();

            // Default: tampil nama, status, tanggal, waktu
            $("#tanggal").closest(".col-sm-6").show();
            $("#waktu").closest(".col-sm-6").show();
            $("#text_alasan").hide();
            $("#alasan").attr("required", false);
            $("#text_wfa").hide();
            $("#bukti_wfa").attr("required", false);
            $("#foto_kamera_preview").hide();

            if (status == "1" || status == "3") { // Hadir / Terlambat
                <?php if ($kamera_perangkat == 1): ?>
                    if ($("#foto_kamera_preview").length) {
                        $("#foto_kamera_preview").show();
                    }
                <?php endif; ?>
            } else if (status == "2") { // Izin
                $("#text_alasan").show();
                $("#alasan").attr("required", true);
            } else if (status == "5") { // WFA
                $("#text_wfa").show();
                $("#bukti_wfa").attr("required", true);
            }
            // Status 4 (Tidak Hadir) atau belum dipilih -> tetap hanya Nama, Status, Tanggal, Waktu
        });

        // Reset form
        $('form').on('reset', function () {
            setTimeout(() => {
                $("#text_alasan").hide();
                $("#alasan").attr("required", false);
                $("#text_wfa").hide();
                $("#bukti_wfa").attr("required", false);
                $("#foto_kamera_preview").hide();
                $("#tanggal").closest(".col-sm-6").show();
                $("#waktu").closest(".col-sm-6").show();
            }, 10);
        });

        // Pratinjau gambar untuk bukti izin
        $("#bukti_foto").change(function (event) {
            const file = event.target.files[0];
            const previewImage = document.getElementById("preview-image");
            const reader = new FileReader();
            const allowedTypes = ["image/jpeg", "image/png", "image/gif"];

            if (file) {
                if (allowedTypes.includes(file.type)) {
                    reader.onload = function (e) {
                        previewImage.src = e.target.result;
                        previewImage.style.display = "block";
                    };
                    reader.readAsDataURL(file);
                } else {
                    previewImage.src = "apps/data_absensi/not_found/not_found.jpg";
                    previewImage.style.display = "block";
                }
            } else {
                previewImage.src = "";
                previewImage.style.display = "none";
            }
        });

        $("#bukti_wfa").change(function (event) {
            const file = event.target.files[0];
            const previewImage = document.getElementById("preview-image-wfa");
            const allowedImageTypes = ["image/jpeg", "image/png", "image/jpg"];

            if (file) {
                if (allowedImageTypes.includes(file.type)) {
                    // Jika file tipe gambar, tampilkan preview
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        previewImage.src = e.target.result;
                        previewImage.style.display = "block";
                    };
                    reader.readAsDataURL(file);
                } else {
                    // Bukan gambar, hide preview
                    previewImage.src = "";
                    previewImage.style.display = "none";
                }
            } else {
                previewImage.src = "";
                previewImage.style.display = "none";
            }
        });
    });
</script>

<script>
    const statusSelect = document.getElementById("status");
    const waktuInput = document.getElementById("waktu");

    statusSelect.addEventListener("change", function () {
        const selected = this.value;

        if (selected === "4") { // Tidak Hadir
            waktuInput.value = "00:00";
            waktuInput.disabled = true;
        } else {
            waktuInput.disabled = false;
            if (waktuInput.value === "00:00") {
                waktuInput.value = ""; // kosongkan jika sebelumnya 00:00
            }
        }
    });

    // Reset handler supaya semua kembali normal kalau di-reset
    document.querySelector('form').addEventListener('reset', function () {
        setTimeout(() => {
            waktuInput.disabled = false;
            waktuInput.value = "";
        }, 10);
    });
</script>

<!-- <script>
    let lokasiTersedia = false;

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                document.getElementById("latitude").value = position.coords.latitude;
                document.getElementById("longitude").value = position.coords.longitude;
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
            title: 'Browser tidak mendukung Geolocation',
            text: 'Silakan gunakan browser lain.',
        });
    }

    // Cek lokasi sebelum submit
    document.getElementById("simpan_absensi").addEventListener("click", function (event) {
        if (!lokasiTersedia) {
            event.preventDefault(); // Stop submit
            Swal.fire({
                icon: 'warning',
                title: '<span style="font-size: 1.5em;">Lokasi tidak tersedia!</span>',
                html: '<span style="font-size: 1.5em;">Pastikan Anda mengizinkan lokasi sebelum presensi.</span>',
                confirmButtonColor: '#3085d6'
            });
        }
    });
</script> -->