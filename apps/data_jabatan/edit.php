<?php
session_start();
include '../../config/database.php';

function input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Proses pengambilan data dari database berdasarkan id_jabatan
if (isset($_POST['id_jabatan'])) {
    $id = $_POST['id_jabatan'];
    $result = mysqli_query($kon, "SELECT * FROM tbl_jabatan WHERE id_jabatan='$id'");
    $data = mysqli_fetch_array($result);
    $nama_jabatan_array = array_map('trim', explode(',', $data['nama']));

    // Ambil semua unit kerja
    $unitKerjaList = mysqli_query($kon, "SELECT * FROM tbl_unit_kerja ORDER BY nama ASC");
}

// Proses simpan data
if (isset($_POST['simpan'])) {
    $id = $_POST['id_jabatan'];
    $unit_kerja = input($_POST['unit_kerja']);
    $nama_jabatan_array = array_map('trim', $_POST['nama_jabatan']);

    // Lakukan pengecekan duplikat
    foreach ($nama_jabatan_array as $jabatan) {
        // Pengecekan nama jabatan di unit kerja yang sama
        $resultCheck = mysqli_query($kon, "SELECT * FROM tbl_jabatan WHERE nama = '$jabatan' AND unit_kerja = '$unit_kerja' AND id_jabatan != '$id'");
        if (mysqli_num_rows($resultCheck) > 0) {
            // Jika ada duplikat, tampilkan pesan error
            header("Location: ../../index.php?page=data_jabatan&edit=nama_sudah_ada");
            exit;
        }
    }

    // Jika tidak ada duplikat, lanjutkan ke proses update
    $nama_jabatan = implode(', ', $nama_jabatan_array);
    $kode_pengguna = $_SESSION['kode_pengguna'];
    $resultUser = mysqli_query($kon, "SELECT level FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
    $user = mysqli_fetch_assoc($resultUser);
    $level = $user['level'];
    $resultAdmin = mysqli_query($kon, "SELECT nama FROM tbl_admin WHERE kode_admin = '$kode_pengguna'");
    $admin = mysqli_fetch_assoc($resultAdmin);
    $nama_admin = $admin['nama'];
    date_default_timezone_set('Asia/Jakarta');
    $tanggal = date("Y-m-d H:i:s");
    $aktivitas = "Edit data jabatan $nama_jabatan pada unit kerja ($unit_kerja)";

    mysqli_query($kon, "START TRANSACTION");
    $update = mysqli_query($kon, "UPDATE tbl_jabatan SET nama='$nama_jabatan', unit_kerja='$unit_kerja' WHERE id_jabatan='$id'");

    if ($update) {
        mysqli_query($kon, "COMMIT");
        mysqli_query($kon, "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                            VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', 'berhasil')");
        header("Location: ../../index.php?page=data_jabatan&edit=berhasil");
        exit;
    } else {
        mysqli_query($kon, "ROLLBACK");
        mysqli_query($kon, "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status)
                            VALUES ('$tanggal', '$nama_admin', '$level', '$kode_pengguna', '$aktivitas', 'gagal')");
        header("Location: ../../index.php?page=data_jabatan&edit=gagal");
        exit;
    }
}
?>

<form action="apps/data_jabatan/edit.php" method="POST" id="form-edit-jabatan"
    style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;" enctype="multipart/form-data">
    <input type="hidden" name="id_jabatan" value="<?php echo $data['id_jabatan']; ?>">

    <div class="form-group">
        <label for="unit_kerja">Unit Kerja</label>
        <select name="unit_kerja_display" id="unit_kerja_display" class="form-control" disabled>
            <option value="">-- Pilih Unit Kerja --</option>
            <?php while ($row = mysqli_fetch_array($unitKerjaList)): ?>
                <option value="<?php echo htmlspecialchars($row['nama']); ?>" <?php if ($row['nama'] == $data['unit_kerja'])
                       echo 'selected'; ?>>
                    <?php echo htmlspecialchars($row['nama']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <!-- Hidden input agar nilainya tetap terkirim ke server -->
        <input type="hidden" name="unit_kerja" value="<?php echo htmlspecialchars($data['unit_kerja']); ?>">
    </div>

    <label>Jabatan</label>
    <div id="jabatan-container">
        <?php foreach ($nama_jabatan_array as $jabatan): ?>
            <div class="form-group" style="display: flex; align-items: center;">
                <input type="text" class="form-control" name="nama_jabatan[]"
                    value="<?php echo htmlspecialchars($jabatan); ?>" required>
                <button type="button" class="btn btn-danger btn-sm ml-2 remove-jabatan" style="margin-left: 8px;">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="form-group">
        <button type="submit" name="simpan" class="btn btn-info"><i class="fa fa-save"></i> Simpan</button>
        <button type="button" class="btn btn-success mb-3" id="tambah-jabatan"><i class="fa fa-plus"></i> Tambah
            Jabatan</button>
    </div>
</form>

<script>
    // Fungsi untuk memeriksa duplikat nama jabatan di form
    function checkDuplicateJabatan() {
        const jabatanInputs = document.querySelectorAll('input[name="nama_jabatan[]"]');
        const jabatanValues = [];
        let isDuplicate = false;

        jabatanInputs.forEach(input => {
            const jabatanValue = input.value.trim().toLowerCase();
            if (jabatanValues.includes(jabatanValue)) {
                isDuplicate = true;
            } else {
                jabatanValues.push(jabatanValue);
            }
        });

        return isDuplicate;
    }

    // Tambah input jabatan baru
    document.getElementById('tambah-jabatan').addEventListener('click', function () {
        const container = document.getElementById('jabatan-container');
        const div = document.createElement('div');
        div.className = 'form-group';
        div.style.display = 'flex';
        div.style.alignItems = 'center';
        div.innerHTML = `
        <input type="text" class="form-control" name="nama_jabatan[]" required>
        <button type="button" class="btn btn-danger btn-sm remove-jabatan" style="margin-left: 8px;">
            <i class="fa fa-trash"></i>
        </button>
    `;
        container.appendChild(div);
        updateRemoveButtonVisibility();  // Update visibility of remove buttons after adding a new input
    });

    // Validasi form sebelum submit
    document.getElementById('form-edit-jabatan').addEventListener('submit', function (e) {
        if (checkDuplicateJabatan()) {
            e.preventDefault();
            alert("Nama jabatan tidak boleh duplikat dalam unit kerja yang sama!");
        }
    });

    // Hapus input jabatan
    document.addEventListener('click', function (e) {
        if (e.target && e.target.closest('.remove-jabatan')) {
            e.target.closest('.form-group').remove();
            updateRemoveButtonVisibility();  // Update visibility of remove buttons after removing an input
        }
    });

    // Fungsi untuk mengupdate tombol hapus (hanya tampil jika lebih dari satu inputan)
    function updateRemoveButtonVisibility() {
        const inputGroups = document.querySelectorAll('#jabatan-container .form-group');
        inputGroups.forEach((group, index) => {
            const removeButton = group.querySelector('.remove-jabatan');
            if (inputGroups.length > 1) {
                removeButton.style.display = 'inline-block';  // Tampilkan tombol hapus jika lebih dari satu input
            } else {
                removeButton.style.display = 'none';  // Sembunyikan tombol hapus jika hanya satu input
            }
        });
    }

    // Pastikan tombol hapus tersembunyi pada awalnya jika hanya ada satu input
    updateRemoveButtonVisibility();
</script>