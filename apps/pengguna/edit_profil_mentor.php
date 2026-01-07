<?php
// Menghubungkan ke database
session_start();
include '../../config/database.php';
date_default_timezone_set('Asia/Jakarta'); // Set timezone ke Indonesia WIB

// Memulai transaksi
mysqli_query($kon, "START TRANSACTION");

// Memastikan kode mentor tersedia, baik dari POST saat menampilkan form atau setelah submit
$kode_mentor = isset($_POST['kode_mentor']) ? $_POST['kode_mentor'] : '';

if ($kode_mentor == '') {
    die('Kode mentor tidak ditemukan.');
}

// Mendapatkan data mentor berdasarkan kode_mentor
$sql = "SELECT * FROM tbl_mentor WHERE kode_mentor='$kode_mentor' LIMIT 1";
$hasil = mysqli_query($kon, $sql);
$data_mentor = mysqli_fetch_array($hasil);

// Mendapatkan data pengguna yang sedang login (misalnya level dan kode_pengguna dari tbl_user)
$kode_pengguna = $_SESSION['kode_pengguna'];
$sql_user = "SELECT * FROM tbl_user WHERE kode_pengguna='$kode_pengguna' LIMIT 1";
$hasil_user = mysqli_query($kon, $sql_user);
$data_user = mysqli_fetch_array($hasil_user);

$level = $data_user['level']; // Level pengguna (dalam kasus ini, levelnya adalah Mentor)
$nama_pengguna = $data_mentor['nama']; // Nama mentor dari tbl_mentor

// Fungsi untuk mencatat log aktivitas
function log_aktivitas($kon, $nama_pengguna, $level, $kode_pengguna, $aktivitas, $status)
{
    $tanggal = date("Y-m-d H:i:s"); // Tanggal saat ini (timezone Indonesia WIB)
    $sql_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                VALUES ('$tanggal', '$nama_pengguna', '$level', '$kode_pengguna', '$aktivitas', '$status')";
    mysqli_query($kon, $sql_log);
}

// Periksa apakah formulir telah dikirimkan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_profil_mentor'])) {
    $nama = $_POST['nama'];
    $nip = $_POST['nip'];
    $email = $_POST['email'];
    $foto_saat_ini = $_POST['foto_saat_ini'];
    $hapus_foto = isset($_POST['hapus_foto_flag']) && $_POST['hapus_foto_flag'] == '1';

    $foto = $foto_saat_ini;

    // Hapus foto jika diminta
    if ($hapus_foto && !empty($foto_saat_ini)) {
        $path_foto = "../../apps/pengguna/foto_mentor/" . $foto_saat_ini;
        if (file_exists($path_foto)) {
            unlink($path_foto); // hapus file
        }
        $foto = ""; // kosongkan nama file
    }

    // Jika upload foto baru
    if (!empty($_FILES['foto_baru']['name'])) {
        $foto_baru = $_FILES['foto_baru']['name'];
        $file_temp_name = $_FILES['foto_baru']['tmp_name'];
        $file_size = $_FILES['foto_baru']['size'];
        $max_size = 1048576;

        if ($file_size > $max_size) {
            log_aktivitas($kon, $nama_pengguna, $level, $kode_pengguna, "Edit profil akun pribadi", "gagal - ukuran file terlalu besar");
            header("Location:../../index.php?page=profil_mentor&edit=gagal_ukuran");
            exit;
        }

        $ekstensiFile = pathinfo($foto_baru, PATHINFO_EXTENSION);
        if (!in_array(strtolower($ekstensiFile), ['jpg', 'jpeg', 'png'])) {
            log_aktivitas($kon, $nama_pengguna, $level, $kode_pengguna, "Edit profil akun pribadi", "gagal - format file tidak valid");
            header("Location:../../index.php?page=profil_mentor&edit=gagal_format");
            exit;
        }

        $foto_baru_nama = preg_replace('/[^a-zA-Z0-9_.]/', '', pathinfo($foto_baru, PATHINFO_FILENAME));
        $direktoriUpload = '../../apps/pengguna/foto_mentor/';
        $foto_baru_unik = $foto_baru_nama . '.' . $ekstensiFile;
        $jalur_foto_baru = $direktoriUpload . $foto_baru_unik;

        if (!file_exists($direktoriUpload)) {
            mkdir($direktoriUpload, 0777, true);
        }

        if (move_uploaded_file($file_temp_name, $jalur_foto_baru)) {
            $foto = $foto_baru_unik;
        } else {
            log_aktivitas($kon, $nama_pengguna, $level, $kode_pengguna, "Edit profil akun pribadi", "gagal - gagal upload foto");
            echo "<div class='alert alert-danger'><strong>Gagal!</strong> Terjadi kesalahan saat mengunggah foto baru.</div>";
            $foto = $foto_saat_ini;
        }
    }

    // Update ke database
    $sql_update = "UPDATE tbl_mentor SET nama='$nama', nip='$nip', email='$email', foto='$foto' WHERE kode_mentor='$kode_mentor'";
    $edit_mentor = mysqli_query($kon, $sql_update);

    if ($edit_mentor) {
        log_aktivitas($kon, $nama_pengguna, $level, $kode_pengguna, "Edit profil akun pribadi", "berhasil");
        mysqli_query($kon, "COMMIT");
        header("Location:../../index.php?page=profil_mentor&edit=berhasil");
    } else {
        log_aktivitas($kon, $nama_pengguna, $level, $kode_pengguna, "Edit profil akun pribadi", "gagal - query gagal");
        mysqli_query($kon, "ROLLBACK");
        header("Location:../../index.php?page=profil_mentor&edit=gagal");
    }
}
?>

<form action="apps/pengguna/edit_profil_mentor.php" method="post" enctype="multipart/form-data"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <input type="hidden" name="kode_mentor" value="<?php echo $kode_mentor; ?>">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Nama Lengkap :</label>
                <input type="text" name="nama" class="form-control" placeholder="Masukan Nama Mentor"
                    value="<?php echo $data_mentor['nama']; ?>" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>NIP :</label>
                <input type="text" name="nip" class="form-control" placeholder="Masukan Nomor Induk Pegawai"
                    value="<?php echo $data_mentor['nip']; ?>" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Email :</label>
                <input type="email" name="email" class="form-control" placeholder="Masukan Email"
                    value="<?php echo $data_mentor['email']; ?>" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="row">
                <div class="col-sm-7">
                    <div id="msg"></div>
                    <label>Upload Foto Baru:</label>
                    <input type="file" name="foto_baru" class="file">
                    <div class="input-group my-3">
                        <input type="text" class="form-control" disabled placeholder="Upload File" id="file">
                        <div class="input-group-append">
                            <button type="button" id="pilih_foto" class="browse btn btn-info" style="margin-top: 10px;">
                                <i class="fa fa-search"></i> Pilih Foto
                            </button>
                            <a href="javascript:void(0);" id="hapus_foto"
                                class="btn btn-danger btn-circle <?php echo empty($data_mentor['foto']) ? 'disabled' : ''; ?>"
                                style="margin-top: 10px;" title="Hapus Foto">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </div>
                    <input type="hidden" name="hapus_foto_flag" id="hapus_foto_flag" value="0">
                </div>
                <div class="col-sm-5">
                    <label>Foto :</label><br>
                    <div style="width: 100%; text-align: center;">
                        <?php
                        if (empty($data_mentor['foto'])) {
                            $foto_url = "apps/pengguna/foto_mentor/foto_default.png";
                        } else {
                            $foto_url = "apps/pengguna/foto_mentor/" . $data_mentor['foto'];
                        }
                        ?>
                        <img class="img-thumbnail" src="<?php echo $foto_url; ?>" id="preview" width="90%" class="rounded"
                            alt="Silahkan isi foto anda" style="width: 130px; height: 130px; object-fit: cover;">
                        <input type="hidden" name="foto_saat_ini" value="<?php echo $data_mentor['foto']; ?>"
                            class="form-control" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="edit_profil_mentor" id="Submit" class="btn btn-warning"><i
                        class="fa fa-edit"></i> Update</button>
            </div>
        </div>
    </div>
</form>

<script>
    $(document).on("click", ".browse", function () {
        var file = $(this).parents().find(".file");
        file.trigger("click");
    });

    $('input[type="file"]').change(function (e) {
        var fileName = e.target.files[0].name;
        $("#file").val(fileName);

        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById("preview").src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);
    });

    // Fungsi untuk tombol hapus
    $("#hapus_foto").on("click", function () {
        // Ganti preview ke default
        $("#preview").attr("src", "apps/pengguna/foto_mentor/foto_default.png");

        // Set hidden input flag
        $("#hapus_foto_flag").val("1");

        // Nonaktifkan tombol hapus
        $(this).addClass("disabled");

        // Kosongkan input file dan nama file
        $("#file").val("");
        $('input[type="file"]').val(null);
    });
</script>