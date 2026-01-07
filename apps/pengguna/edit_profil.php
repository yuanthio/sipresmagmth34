<?php
include '../../config/database.php';
date_default_timezone_set('Asia/Jakarta');
mysqli_query($kon, "START TRANSACTION");

$kode_mahasiswa = isset($_POST['kode_mahasiswa']) ? $_POST['kode_mahasiswa'] : '';

if ($kode_mahasiswa == '') {
    die('Kode mahasiswa tidak ditemukan.');
}

$sql = "SELECT * FROM tbl_mahasiswa WHERE kode_mahasiswa='$kode_mahasiswa' LIMIT 1";
$hasil = mysqli_query($kon, $sql);
$data = mysqli_fetch_array($hasil);

$queryLevel = "SELECT level FROM tbl_user WHERE kode_pengguna='$kode_mahasiswa' LIMIT 1";
$resultLevel = mysqli_query($kon, $queryLevel);
$dataLevel = mysqli_fetch_assoc($resultLevel);
$level = $dataLevel['level'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_profil'])) {
    $nama = $_POST['nama'];
    $universitas = $_POST['universitas'];
    $jurusan = $_POST['jurusan'];
    $nim = $_POST['nim'];
    $no_telp = $_POST['no_telp'];
    $email = $_POST['email'];
    $alamat = $_POST['alamat'];
    $foto_saat_ini = $_POST['foto_saat_ini'];
    $hapus_foto = isset($_POST['hapus_foto']) ? $_POST['hapus_foto'] : '0';

    // Jika ada unggahan foto baru
    if (!empty($_FILES['foto_baru']['name'])) {
        $foto_baru = $_FILES['foto_baru']['name'];
        $file_temp_name = $_FILES['foto_baru']['tmp_name'];
        $ukuranFile = $_FILES['foto_baru']['size'];
        $ekstensiFile = pathinfo($foto_baru, PATHINFO_EXTENSION);

        if (!in_array(strtolower($ekstensiFile), ['jpg', 'jpeg', 'png'])) {
            $status = 'gagal_format';
            $aktivitas = "Gagal edit profil - format file tidak valid";
            $tanggal = date('Y-m-d H:i:s');
            $log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                    VALUES ('$tanggal', '$nama', '$level', '$kode_mahasiswa', '$aktivitas', '$status')";
            mysqli_query($kon, $log);
            header("Location:../../index.php?page=profil&edit=gagal_format");
            exit;
        }

        if ($ukuranFile > 1048576) {
            $status = 'gagal_ukuran';
            $aktivitas = "Gagal edit profil - ukuran file terlalu besar";
            $tanggal = date('Y-m-d H:i:s');
            $log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                    VALUES ('$tanggal', '$nama', '$level', '$kode_mahasiswa', '$aktivitas', '$status')";
            mysqli_query($kon, $log);
            header("Location:../../index.php?page=profil&edit=gagal_ukuran");
            exit;
        }

        $foto_baru_nama = preg_replace('/[^a-zA-Z0-9_.]/', '', pathinfo($foto_baru, PATHINFO_FILENAME));
        $direktoriUpload = '../../apps/mahasiswa/foto/';
        $foto_baru_unik = $foto_baru_nama . '.' . $ekstensiFile;
        $jalur_foto_baru = $direktoriUpload . $foto_baru_unik;

        if (!file_exists($direktoriUpload))
            mkdir($direktoriUpload, 0777, true);

        if (move_uploaded_file($file_temp_name, $jalur_foto_baru)) {
            // Hapus foto lama jika ada
            if (!empty($foto_saat_ini) && file_exists($direktoriUpload . $foto_saat_ini)) {
                unlink($direktoriUpload . $foto_saat_ini);
            }
            $foto = $foto_baru_unik;
        } else {
            $status = 'gagal_upload';
            $aktivitas = "Gagal edit profil - gagal mengunggah foto";
            $tanggal = date('Y-m-d H:i:s');
            $log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                    VALUES ('$tanggal', '$nama', '$level', '$kode_mahasiswa', '$aktivitas', '$status')";
            mysqli_query($kon, $log);
            header("Location:../../index.php?page=profil&edit=gagal_upload");
            exit;
        }

    } elseif ($hapus_foto == '1') {
        if (!empty($foto_saat_ini) && file_exists("../../apps/mahasiswa/foto/" . $foto_saat_ini)) {
            unlink("../../apps/mahasiswa/foto/" . $foto_saat_ini);
        }
        $foto = '';
    } else {
        $foto = $foto_saat_ini;
    }

    $sql_update = "UPDATE tbl_mahasiswa SET nama='$nama', universitas='$universitas', jurusan='$jurusan', nim='$nim', no_telp='$no_telp', email='$email', alamat='$alamat', foto='$foto' WHERE kode_mahasiswa='$kode_mahasiswa'";
    $edit_mahasiswa = mysqli_query($kon, $sql_update);

    $tanggal = date('Y-m-d H:i:s');
    $aktivitas = "Edit profil akun pribadi";

    // Perbarui session jika update berhasil
    if ($edit_mahasiswa) {
        mysqli_query($kon, "COMMIT");
        $status = 'berhasil';

        // Update session foto dan nama jika ada perubahan
        $_SESSION['foto'] = $foto;
        $_SESSION['nama_mahasiswa'] = $nama;
    } else {
        mysqli_query($kon, "ROLLBACK");
        $status = 'gagal';
    }

    $log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
            VALUES ('$tanggal', '$nama', '$level', '$kode_mahasiswa', '$aktivitas', '$status')";
    mysqli_query($kon, $log);

    header("Location:../../index.php?page=profil&edit=$status");
}
?>

<form action="apps/pengguna/edit_profil.php" method="post" enctype="multipart/form-data"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <input type="hidden" name="kode_mahasiswa" value="<?php echo $kode_mahasiswa; ?>">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Nama Lengkap :</label>
                <input type="text" name="nama" class="form-control" placeholder="Masukan Nama Mahasiswa"
                    value="<?php echo $data['nama']; ?>" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Universitas / Sekolah :</label>
                <input type="text" name="universitas" class="form-control" placeholder="Masukan Nama Universitas"
                    value="<?php echo $data['universitas']; ?>" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Jurusan :</label>
                <input type="text" name="jurusan" class="form-control" placeholder="Masukan Nama Jurusan"
                    value="<?php echo $data['jurusan']; ?>" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>NIM / NIS :</label>
                <input type="text" name="nim" class="form-control" placeholder="Masukan Nomor Induk Mahasiswa"
                    value="<?php echo $data['nim']; ?>" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>No Telp :</label>
                <input type="text" name="no_telp" class="form-control" placeholder="Masukan No Telp"
                    value="<?php echo $data['no_telp']; ?>" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Email :</label>
                <input type="email" name="email" class="form-control" placeholder="Masukan Email"
                    value="<?php echo $data['email']; ?>" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Alamat :</label>
                <textarea class="form-control" name="alamat" rows="4"
                    id="alamat"><?php echo $data['alamat']; ?></textarea>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="row">
                <div class="col-sm-7">
                    <div id="msg"></div>
                    <label>Upload Foto Baru:</label>
                    <input type="file" name="foto_baru" class="file" style="visibility: hidden; position: absolute;">
                    <div class="input-group my-3">
                        <input type="text" class="form-control" disabled placeholder="Upload File" id="file">
                        <div class="input-group-append">
                            <button type="button" id="pilih_foto" class="browse btn btn-info" style="margin-top: 10px;">
                                <i class="fa fa-search"></i> Pilih Foto
                            </button>
                            <a href="javascript:void(0);" id="hapus_foto"
                                class="btn btn-danger btn-circle <?php echo empty($data['foto']) ? 'disabled' : ''; ?>"
                                style="margin-top: 10px;" title="Hapus Foto">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-5">
                    <label>Foto :</label><br>
                    <div style="width: 100%; text-align: center;">
                        <img class="img-thumbnail" src="apps/mahasiswa/foto/<?php echo (!empty($data['foto']) && file_exists("../../apps/mahasiswa/foto/" . $data['foto'])) ? $data['foto'] : 'foto_default.png'; ?>"
                            id="preview" width="90%" class="rounded" alt="Foto Mahasiswa" style="width: 130px; height: 130px; object-fit: cover;">
                        <input type="hidden" name="foto_saat_ini" id="foto_saat_ini"
                            value="<?php echo $data['foto']; ?>" class="form-control" />
                        <input type="hidden" name="hapus_foto" id="hapus_foto_input" value="0">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="edit_profil" id="Submit" class="btn btn-warning">
                    <i class="fa fa-edit"></i> Update
                </button>
            </div>
        </div>
    </div>
</form>

<script>
    $(document).on("click", ".browse", function () {
        var file = $(this).parents().find(".file");
        file.trigger("click");
    });

    // Saat memilih file baru
    $('input[type="file"]').change(function (e) {
        var fileName = e.target.files[0].name;
        $("#file").val(fileName);

        var reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById("preview").src = e.target.result;
        };
        reader.readAsDataURL(this.files[0]);

        $('#hapus_foto_input').val('0'); // Reset status hapus foto
        $('#hapus_foto').removeClass('disabled'); // Aktifkan tombol hapus foto lagi
    });

    // Saat klik tombol hapus foto
    $('#hapus_foto').on('click', function () {
        if (!$(this).hasClass('disabled')) {
            const defaultFoto = 'apps/mahasiswa/foto/foto_default.png';
            $('#preview').attr('src', defaultFoto); // Ganti preview ke default
            $('#hapus_foto_input').val('1');        // Tandai sebagai "hapus"
            $('input[type="file"]').val('');        // Kosongkan input file
            $('#file').val('');                     // Kosongkan tampilan input teks
            $(this).addClass('disabled');           // Disable tombol hapus foto setelah di klik
        }
    });
</script>