<?php
// Memulai sesi
session_start();
date_default_timezone_set('Asia/Jakarta');
// Cek apakah form dengan nama 'tambah_mahasiswa' dikirim
if (isset($_POST['tambah_mahasiswa'])) {
    // Menginclude file koneksi database
    include '../../config/database.php';

    // Fungsi untuk membersihkan dan mencegah inputan karakter yang tidak diinginkan
    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    function generateUniqueCode($kon)
    {
        $huruf = "M";
        $query = mysqli_query($kon, "SELECT max(id_mahasiswa) as id_terbesar FROM tbl_mahasiswa");
        $ambil = mysqli_fetch_array($query);
        $id_mahasiswa = $ambil['id_terbesar'];
        $id_mahasiswa++;
        $kode_mahasiswa = $huruf . sprintf("%03s", $id_mahasiswa);

        // Memeriksa apakah kode_pengguna sudah digunakan sebelumnya
        $checkQuery = mysqli_query($kon, "SELECT kode_pengguna FROM tbl_user WHERE kode_pengguna = '$kode_mahasiswa'");
        if (mysqli_num_rows($checkQuery) > 0) {
            return generateUniqueCode($kon);
        } else {
            return $kode_mahasiswa;
        }
    }

    // Mengecek apakah metode pengiriman data adalah POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Memulai transaksi database
        mysqli_query($kon, "START TRANSACTION");

        // Mengambil data dari form dan membersihkannya
        $nama = input($_POST["nama"]);
        $universitas = input($_POST["universitas"]);
        $jurusan = input($_POST["jurusan"]);
        $nim = input($_POST["nim"]);
        $mulai_magang = input($_POST["mulai_magang"]);
        $akhir_magang = input($_POST["akhir_magang"]);
        $unit_kerja = input($_POST["unit_kerja"]);
        $mentor = input($_POST["mentor"]);
        $no_telp = input($_POST["no_telp"]);
        $email = input($_POST["email"]);
        $alamat = input($_POST["alamat"]);

        // Mengambil data file foto
        $foto = $_FILES['foto']['name'];
        $x = explode('.', $foto);
        $ekstensi = strtolower(end($x));
        $ukuran = $_FILES['foto']['size'];
        $file_tmp = $_FILES['foto']['tmp_name'];

        // Mendapatkan kode pengguna (admin) yang sedang login
        $kode_pengguna = $_SESSION['kode_pengguna'];
        $level = $_SESSION['level']; // Ambil level pengguna yang login, diasumsikan sudah disimpan di session
        $tanggal = date('Y-m-d H:i:s'); // Waktu sekarang untuk kolom tanggal

        // Ambil nama admin dari tabel tbl_admin
        $query_admin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin='$kode_pengguna'");
        $admin_data = mysqli_fetch_array($query_admin);
        $nama_admin = $admin_data['nama'];

        $kode_mahasiswa = generateUniqueCode($kon);
        $status_magang = 1;
        $sql = "INSERT INTO tbl_user (kode_pengguna) VALUES ('$kode_mahasiswa')";
        $simpan_pengguna = mysqli_query($kon, $sql);

        // Cek apakah ada file foto yang diunggah
        if (!empty($foto)) {
            $ekstensi_file = pathinfo($foto, PATHINFO_EXTENSION);
            if (in_array($ekstensi_file, array("jpg", "jpeg", "png", "gif"))) {
                move_uploaded_file($file_tmp, 'foto/' . $foto);
                $sql = "INSERT INTO tbl_mahasiswa (kode_mahasiswa,nama,universitas,jurusan,nim,mulai_magang,akhir_magang,alamat,no_telp,email,foto,unit_kerja,mentor,status_magang) 
                VALUES ('$kode_mahasiswa','$nama','$universitas','$jurusan','$nim','$mulai_magang','$akhir_magang','$alamat','$no_telp','$email','$foto','$unit_kerja','$mentor',$status_magang)";
            } else {
                // Ekstensi file tidak diperbolehkan
                $aktivitas = "Tambah data mahasiswa (Format file tidak didukung)";
                $status = "gagal";
                $sql_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status')";
                mysqli_query($kon, $sql_log);

                // Rollback transaksi
                mysqli_query($kon, "ROLLBACK");

                // Redirect dengan pesan kegagalan format file
                header("Location:../../index.php?page=mahasiswa&add=gagal&error=format");
                exit;
            }
        } else {
            // Jika tidak ada file foto, maka akan menggunakan gambar_default.png
            $foto = "foto_default.png";
            $sql = "INSERT INTO tbl_mahasiswa (kode_mahasiswa,nama,universitas,jurusan,nim,mulai_magang,akhir_magang,alamat,no_telp,email,foto,unit_kerja,mentor,status_magang) 
                VALUES ('$kode_mahasiswa','$nama','$universitas','$jurusan','$nim','$mulai_magang','$akhir_magang','$alamat','$no_telp','$email','$foto','$unit_kerja','$mentor',$status_magang)";
        }

        // Insert data mahasiswa
        $simpan_mahasiswa = mysqli_query($kon, $sql);

        // Cek apakah kedua operasi insert berhasil
        if ($simpan_pengguna && $simpan_mahasiswa) {
            // Log aktivitas - berhasil
            $aktivitas = "Tambah data mahasiswa ($nama)"; // Memasukkan nama mahasiswa ke dalam log
            $status = "berhasil";
            $sql_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $sql_log);

            // Commit transaksi
            mysqli_query($kon, "COMMIT");
            header("Location:../../index.php?page=mahasiswa&add=berhasil");
        } else {
            // Log aktivitas - gagal
            $aktivitas = "Tambah data mahasiswa ($nama)"; // Memasukkan nama mahasiswa ke dalam log
            $status = "gagal";
            $sql_log = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $sql_log);

            // Rollback transaksi
            mysqli_query($kon, "ROLLBACK");
            header("Location:../../index.php?page=mahasiswa&add=gagal");
        }
    }
}
?>

<form action="apps/mahasiswa/tambah.php" method="post" enctype="multipart/form-data"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Nama Lengkap :</label>
                <input type="text" name="nama" class="form-control" placeholder="Masukan Nama Mahasiswa / Siswa"
                    required autofocus oninvalid="this.setCustomValidity('Harap nama di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Universitas / Sekolah :</label>
                <input type="text" name="universitas" class="form-control"
                    placeholder="Masukan Nama Universitas / Sekolah" required autofocus
                    oninvalid="this.setCustomValidity('Harap universitas / sekolah di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Jurusan :</label>
                <input type="text" name="jurusan" class="form-control" placeholder="Masukan Nama Jurusan" required
                    autofocus oninvalid="this.setCustomValidity('Harap jurusan di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>NIM / NIS :</label>
                <input type="text" name="nim" class="form-control" placeholder="Masukan NIM / NIS" required autofocus
                    oninvalid="this.setCustomValidity('Harap NIM / NIS di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Mulai Magang :</label>
                <input type="date" name="mulai_magang" class="form-control" required autofocus
                    oninvalid="this.setCustomValidity('Harap mulai magang di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Akhir Magang :</label>
                <input type="date" name="akhir_magang" class="form-control" required autofocus
                    oninvalid="this.setCustomValidity('Harap akhir magang di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>No Telp :</label>
                <input type="text" name="no_telp" class="form-control" placeholder="Masukan No Telp" required autofocus
                    oninvalid="this.setCustomValidity('Harap no telp di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Email :</label>
                <input type="email" name="email" class="form-control" placeholder="Masukan Email" required autofocus
                    oninvalid="this.setCustomValidity('Harap email di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="unit_kerja">Unit Kerja :</label>
                <select name="unit_kerja" class="form-control" id="unit_kerja" required autofocus
                    oninvalid="this.setCustomValidity('Harap unit kerja  di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
                    <option selected disabled>Pilih Unit Kerja/Instansi</option>
                    <option value="Subbag SDM">Subbag SDM</option>
                    <option value="Subbag Umum dan TI">Subbag Umum dan TI</option>
                    <option value="Subbag Humas">Subbag Humas</option>
                    <option value="Subbag TU Kalan">Subbag TU Kalan</option>
                    <option value="Subbag Keuangan">Subbag Keuangan</option>
                    <option value="Subbag Hukum">Subbag Hukum</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="mentor">Mentor :</label>
                <select name="mentor" class="form-control" id="mentor" required autofocus
                    oninvalid="this.setCustomValidity('Harap mentor di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')">
                    <option selected disabled>Pilih Mentor</option>
                    <?php
                    include '../../config/database.php';
                    $queryMentor = "SELECT id_mentor, nama FROM tbl_mentor";
                    $resultMentor = mysqli_query($kon, $queryMentor);

                    // Periksa apakah query berhasil
                    if (!$resultMentor) {
                        die("Query gagal: " . mysqli_error($kon));
                    }

                    while ($dataMentor = mysqli_fetch_assoc($resultMentor)) {
                        echo "<option value='" . $dataMentor['nama'] . "'>" . $dataMentor['nama'] . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Alamat :</label>
                <textarea class="form-control" name="alamat" rows="4" id="alamat" required autofocus
                    oninvalid="this.setCustomValidity('Harap alamat di isi terlebih dahulu')"
                    oninput="this.setCustomValidity('')" placeholder="Masukan alamat"></textarea>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <div class="row">
                    <div class="col-sm-7">
                        <div id="msg"></div>
                        <label>Upload Foto :</label>
                        <input type="file" name="foto" class="file">
                        <div class="input-group">
                            <input type="text" class="form-control" disabled placeholder="Upload Foto" id="file">
                            <div class="input-group-append">
                                <button type="button" id="pilih_foto" class="browse btn btn-info"
                                    style="margin: 10px 0;"><i class="fa fa-search"></i> Pilih</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <label>Preview Foto</label>
                        <div style="width: 100%; text-align: center;">
                            <img src="source/img/size.png" id="preview" class="img-thumbnail"
                                style="width: 130px; height: 130px; object-fit: cover;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <button type="submit" name="tambah_mahasiswa" id="Submit" class="btn btn-success"><i class="fa fa-plus"></i>
                Daftar</button>
            <button type="reset" class="btn btn-warning"><i class="fa fa-trash"></i> Reset</button>
        </div>
    </div>
</form>

<style>
    .file {
        visibility: hidden;
        position: absolute;
    }
</style>

<script>
    $(document).on("click", "#pilih_foto", function () {
        var file = $(this).parents().find(".file");
        file.trigger("click");
    });
    $('input[type="file"]').change(function (e) {
        var fileName = e.target.files[0].name;
        $("#file").val(fileName);

        var reader = new FileReader();
        reader.onload = function (e) {
            // get loaded data and render thumbnail.
            document.getElementById("preview").src = e.target.result;
        };
        // read the image file as a data URL.
        reader.readAsDataURL(this.files[0]);
    });
</script>

<script>
    $(document).ready(function () {
        $('#unit_kerja').change(function () {
            var unitKerja = $(this).val();
            $.ajax({
                url: 'apps/mahasiswa/get_mentor_tambah.php', // Ganti dengan path menuju script PHP yang mengembalikan daftar mentor berdasarkan unit kerja
                type: 'post',
                data: { unit_kerja: unitKerja },
                dataType: 'json',
                success: function (response) {
                    var len = response.length;
                    $("#mentor").empty();
                    for (var i = 0; i < len; i++) {
                        var nama = response[i]['nama'];
                        $("#mentor").append("<option value='" + nama + "'>" + nama + "</option>");
                    }
                }
            });
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#mentor').change(function () {
            var mentor = $(this).val(); // Ambil nilai nama mentor yang dipilih
            $.ajax({
                url: 'apps/mahasiswa/get_unit_kerja_tambah.php', // Ganti dengan path ke script PHP yang mengembalikan unit kerja berdasarkan mentor
                type: 'post',
                data: { mentor: mentor },
                dataType: 'json',
                success: function (response) {
                    var unitKerja = response.unit_kerja; // Ambil unit kerja dari response JSON
                    $("#unit_kerja").val(unitKerja); // Set nilai opsi unit kerja sesuai dengan data dari mentor yang dipilih
                }
            });
        });
    });
</script>