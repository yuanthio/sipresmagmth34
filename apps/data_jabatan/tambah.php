<?php
session_start();
if (isset($_POST['tambah_jabatan'])) {
    include '../../config/database.php';

    function bersihkan_input_array($data_array)
    {
        return array_map(function ($item) {
            $item = trim($item);
            $item = stripslashes($item);
            $item = htmlspecialchars($item);
            return $item;
        }, $data_array);
    }

    function input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nama_array = bersihkan_input_array($_POST["nama"]);
        $unit_kerja = input($_POST["unit_kerja"]);

        // Hapus input kosong
        $nama_array = array_filter($nama_array);

        // Cek duplikat antar input
        if (count($nama_array) !== count(array_unique($nama_array))) {
            header("Location: ../../index.php?page=data_jabatan&tambah=nama_sama");
            exit;
        }

        $input_jabatan_baru = implode(", ", $nama_array);

        $cekUnitKerja = mysqli_query($kon, "SELECT * FROM tbl_jabatan WHERE unit_kerja='$unit_kerja'");

        if (mysqli_num_rows($cekUnitKerja) > 0) {
            $existingData = mysqli_fetch_array($cekUnitKerja);
            $existingJabatan = $existingData['nama'];
            $existingArray = array_map('trim', explode(",", $existingJabatan));

            $duplikat = array_intersect($existingArray, $nama_array);

            if (!empty($duplikat)) {
                header("Location: ../../index.php?page=data_jabatan&tambah=nama_sudah_ada");
                exit;
            }

            $gabungan = $existingJabatan . ", " . $input_jabatan_baru;
            $sql = "UPDATE tbl_jabatan SET nama='$gabungan' WHERE unit_kerja='$unit_kerja'";
            $simpan = mysqli_query($kon, $sql);
        } else {
            $sql = "INSERT INTO tbl_jabatan (nama, unit_kerja) VALUES ('$input_jabatan_baru', '$unit_kerja')";
            $simpan = mysqli_query($kon, $sql);
        }

        // Logging aktivitas
        $kode_pengguna = $_SESSION['kode_pengguna'];
        $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna='$kode_pengguna'");
        $user = mysqli_fetch_assoc($resultUser);
        $level = $user['level'];

        $resultAdmin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin='$kode_pengguna'");
        $admin = mysqli_fetch_assoc($resultAdmin);
        $nama_admin = $admin['nama'];

        date_default_timezone_set('Asia/Jakarta');
        $tanggal = date("Y-m-d H:i:s");

        $aktivitas = "Tambah jabatan $input_jabatan_baru pada unit kerja ($unit_kerja)";
        $status = $simpan ? "berhasil" : "gagal";

        $sqlLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                   VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', '$status')";
        mysqli_query($kon, $sqlLog);

        header("Location: ../../index.php?page=data_jabatan&tambah=$status");
    }
}
?>

<form action="apps/data_jabatan/tambah.php" method="post"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;" enctype="multipart/form-data"
    id="formJabatan">

    <label>Jabatan</label>
    <div id="jabatan-container">
        <div class="form-group" style="display: flex; align-items: center;">
            <input type="text" name="nama[]" class="form-control" placeholder="Masukkan Nama Jabatan" required>
            <button type="button" class="btn btn-danger btn-sm ml-2 remove-jabatan" style="margin-left: 8px;">
                <i class="fa fa-trash"></i>
            </button>
        </div>
    </div>

    <div class="form-group mt-3">
        <label>Unit Kerja:</label>
        <select name="unit_kerja" class="form-control" required>
            <option value="">-- Pilih Unit Kerja --</option>
            <?php
            include '../../config/database.php';

            // Ambil semua nama unit kerja
            $queryAll = mysqli_query($kon, "SELECT nama FROM tbl_unit_kerja ORDER BY nama ASC");
            while ($data = mysqli_fetch_array($queryAll)) {
                $nama = htmlspecialchars($data['nama']);
                echo "<option value='$nama'>$nama</option>";
            }
            ?>
        </select>
    </div>

    <button type="submit" name="tambah_jabatan" class="btn btn-info"><i class="fa fa-save"></i> Simpan</button>
    <button type="button" onclick="tambahInputJabatan()" class="btn btn-success"><i class="fa fa-plus"></i> Tambah
        Jabatan</button>
    <button type="reset" class="btn btn-warning"><i class="fa fa-trash"></i> Reset</button>
</form>

<script>
    function tambahInputJabatan() {
        const container = document.getElementById('jabatan-container');
        const inputGroup = document.createElement('div');
        inputGroup.className = 'form-group';
        inputGroup.style.display = 'flex';
        inputGroup.style.alignItems = 'center';
        inputGroup.innerHTML = `
        <input type="text" name="nama[]" class="form-control" placeholder="Masukkan Nama Jabatan" required>
        <button type="button" class="btn btn-danger btn-sm ml-2 remove-jabatan" style="margin-left: 8px;">
            <i class="fa fa-trash"></i>
        </button>
    `;
        container.appendChild(inputGroup);
        updateRemoveButtonVisibility();
    }

    // Fungsi untuk menghapus input jabatan
    document.addEventListener('click', function (e) {
        if (e.target && e.target.closest('.remove-jabatan')) {
            e.target.closest('.form-group').remove();
            updateRemoveButtonVisibility();
        }
    });

    // Fungsi untuk mengupdate tombol hapus (hanya tampil jika lebih dari satu inputan)
    function updateRemoveButtonVisibility() {
        const inputGroups = document.querySelectorAll('#jabatan-container .form-group');
        inputGroups.forEach((group, index) => {
            const removeButton = group.querySelector('.remove-jabatan');
            if (inputGroups.length > 1) {
                removeButton.style.display = 'inline-block'; // Tampilkan tombol hapus jika lebih dari satu input
            } else {
                removeButton.style.display = 'none'; // Sembunyikan tombol hapus jika hanya satu input
            }
        });
    }

    // Pastikan tombol hapus tersembunyi pada awalnya jika hanya ada satu input
    updateRemoveButtonVisibility();
</script>