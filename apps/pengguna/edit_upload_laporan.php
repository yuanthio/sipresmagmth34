<script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js
"></script>
<link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css
" rel="stylesheet">

<style>
    #drop-zone {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 250px;
        border: 3px dashed #ccc;
        border-radius: 5px;
        text-align: center;
        color: #aaa;
        margin-bottom: 10px;
        cursor: pointer;
    }

    #placeholder-text {
        font-size: 1.3em;
    }

    #drop-zone.hover {
        border-color: #000;
        color: #000;
    }

    .file-icon {
        width: 100px;
    }

    #file {
        display: none;
    }

    #upload-btn {
        margin-top: 10px;
    }

    @media (max-width: 576px) {
        #placeholder-text {
            font-size: 1em;
        }
    }
</style>

<?php
session_start();
include '../../config/database.php';
include '../../config/function.php';
date_default_timezone_set('Asia/Jakarta');

// Fungsi untuk membersihkan input
function input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$kode_pengguna = input($_SESSION["kode_pengguna"]);

// Ambil data mahasiswa yang sedang login
$sql = "SELECT * FROM tbl_mahasiswa WHERE kode_mahasiswa='$kode_pengguna' LIMIT 1";
$hasil = mysqli_query($kon, $sql);
$data = mysqli_fetch_array($hasil);
$nama_mahasiswa = $data['nama'];  // Nama mahasiswa dari tbl_mahasiswa

// Ambil level pengguna dari tbl_user
$queryLevel = "SELECT level FROM tbl_user WHERE kode_pengguna='$kode_pengguna' LIMIT 1";
$resultLevel = mysqli_query($kon, $queryLevel);
$rowLevel = mysqli_fetch_assoc($resultLevel);
$level_pengguna = $rowLevel['level'];  // Level pengguna dari tbl_user

// Ambil file yang sudah ada dari database
$queryFile = "SELECT file_laporan FROM tbl_laporan WHERE kode_mahasiswa='$kode_pengguna' LIMIT 1";
$resultFile = mysqli_query($kon, $queryFile);
$dataFile = mysqli_fetch_assoc($resultFile);
$fileLaporan = $dataFile['file_laporan'];
$jalurFileLama = '../../apps/data_laporan_magang/upload/' . $fileLaporan;

$fileExist = file_exists($jalurFileLama);
$showPlaceholder = !$fileExist; // Menentukan apakah placeholder perlu ditampilkan

$fileTypeIcon = '';
if ($fileExist) {
    $fileExtension = pathinfo($fileLaporan, PATHINFO_EXTENSION);
    if ($fileExtension === 'doc') {
        $fileTypeIcon = 'doc.png';
    } elseif ($fileExtension === 'docx') {
        $fileTypeIcon = 'docx.png';
    } elseif ($fileExtension === 'pdf') {
        $fileTypeIcon = 'pdf.png';
    } else {
        $fileTypeIcon = 'format_unknown.png';
    }
}

// Periksa apakah ada unggahan file
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $namaFile = input($_FILES['file']['name']);
    $fileTempName = $_FILES['file']['tmp_name'];
    $ukuranFile = $_FILES['file']['size'];
    $ekstensiFile = pathinfo($namaFile, PATHINFO_EXTENSION);

    $namaFileAsli = pathinfo($namaFile, PATHINFO_FILENAME);
    $namaFileUnik = $namaFileAsli . '.' . $ekstensiFile;
    $jalurFile = '../../apps/data_laporan_magang/upload/' . $namaFileUnik;

    // Batas ukuran file (maks 1MB)
    $ukuranMax = 1024 * 1024;
    if ($ukuranFile > $ukuranMax) {
        $status = 'gagal';  // Status gagal
        $aktivitas = "Edit unggahan data laporan magang (ukuran file melebihi batas)"; // Menyertakan penyebab kegagalan

        // Log aktivitas jika ukuran file melebihi batas
        $tanggal_sekarang = date('Y-m-d H:i:s');
        $queryLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                     VALUES ('$tanggal_sekarang', '$nama_mahasiswa', '$level_pengguna', '$kode_pengguna', '$aktivitas', '$status')";
        mysqli_query($kon, $queryLog);

        header("Location: ../../index.php?page=laporan_magang&edit=gagal_ukuran");
        exit();
    }

    // Cek format file
    $formatDukung = array('pdf', 'doc', 'docx');
    if (!in_array(strtolower($ekstensiFile), $formatDukung)) {
        $status = 'gagal';  // Status gagal
        $aktivitas = "Edit unggahan data laporan magang (format file tidak didukung)"; // Menyertakan penyebab kegagalan

        // Log aktivitas jika format file tidak didukung
        $tanggal_sekarang = date('Y-m-d H:i:s');
        $queryLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                     VALUES ('$tanggal_sekarang', '$nama_mahasiswa', '$level_pengguna', '$kode_pengguna', '$aktivitas', '$status')";
        mysqli_query($kon, $queryLog);

        header("Location: ../../index.php?page=laporan_magang&edit=gagal_format");
        exit();
    }

    // Hapus file lama jika ada
    if ($fileExist) {
        unlink($jalurFileLama);
    }

    // Simpan file baru
    if (move_uploaded_file($fileTempName, $jalurFile)) {
        $tanggal = date('Y-m-d');
        $hari = MendapatkanHari(date('l'));

        function formatSizeUnits($bytes)
        {
            $units = array('B', 'KB', 'MB', 'GB', 'TB');
            for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $bytes /= 1024, $i++);
            return round($bytes, 2) . ' ' . $units[$i];
        }

        // Perbarui data unggahan di database
        $queryUpdate = "UPDATE tbl_laporan 
                        SET file_laporan = '$namaFileUnik', ukuran_file = '" . formatSizeUnits($ukuranFile) . "', tanggal = '$tanggal', hari = '$hari'
                        WHERE kode_mahasiswa = '$kode_pengguna'";

        if (mysqli_query($kon, $queryUpdate)) {
            $status = 'berhasil';  // Status berhasil
            // Log aktivitas jika berhasil edit laporan
            $aktivitas = "Edit unggahan data laporan magang";
            $tanggal_sekarang = date('Y-m-d H:i:s');
            $queryLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                         VALUES ('$tanggal_sekarang', '$nama_mahasiswa', '$level_pengguna', '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $queryLog);

            header("Location: ../../index.php?page=laporan_magang&edit=berhasil");
            exit();
        } else {
            $status = 'gagal';  // Status gagal
            $aktivitas = "Edit unggahan data laporan magang (gagal memperbarui data di database)";
            
            // Log aktivitas jika query gagal
            $tanggal_sekarang = date('Y-m-d H:i:s');
            $queryLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                         VALUES ('$tanggal_sekarang', '$nama_mahasiswa', '$level_pengguna', '$kode_pengguna', '$aktivitas', '$status')";
            mysqli_query($kon, $queryLog);

            header("Location: ../../index.php?page=laporan_magang&edit=gagal_query");
            exit();
        }
    } else {
        $status = 'gagal';  // Status gagal
        $aktivitas = "Edit unggahan data laporan magang (gagal menyimpan file ke server)";
        
        // Log aktivitas jika unggah file gagal
        $tanggal_sekarang = date('Y-m-d H:i:s');
        $queryLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                     VALUES ('$tanggal_sekarang', '$nama_mahasiswa', '$level_pengguna', '$kode_pengguna', '$aktivitas', '$status')";
        mysqli_query($kon, $queryLog);

        header("Location: ../../index.php?page=laporan_magang&edit=gagal_upload");
        exit();
    }
}
?>

<div class="front-enkapsulasi" style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="form-group">
        <input type="file" name="file_laporan" id="file_laporan" class="form-control">
    </div>
    <div class="enkapsulasi" style="padding: 10px; background-color: white; border-radius: 5px;">
        <div id="drop-zone">
            <div class="row" style="padding: 0 30px;">
                <?php if ($showPlaceholder): ?>
                    <span id="placeholder-text">Drag & Drop file di sini atau klik untuk memilih file<br>( Ukuran file
                        maksimal 1mb
                        )</span>
                <?php endif; ?>
                <div class="row"
                    style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%;">
                    <img id="file-icon" class="file-icon"
                        src="<?php echo $fileExist ? 'apps/data_laporan_magang/logo_drag_and_drop/' . $fileTypeIcon : ''; ?>"
                        alt="file type icon" style="<?php echo $fileExist ? 'display:block;' : 'display:none;'; ?>">
                    <div id="file-preview" class="file-preview">
                        <?php if ($fileExist): ?>
                            <span><?php echo $fileLaporan; ?></span>
                        <?php else: ?>
                            <span>No file uploaded yet.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form id="upload-form" action="apps/pengguna/edit_upload_laporan.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file" id="file" accept=".pdf, .doc, .docx" required>
        <button id="upload-btn" class="btn btn-primary" type="button"><i class="bi bi-upload"></i> Unggah</button>
    </form>
</div>

<script>
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file');
    const fileLaporanInput = document.getElementById('file_laporan');
    const uploadBtn = document.getElementById('upload-btn');
    const filePreview = document.getElementById('file-preview');
    const fileIcon = document.getElementById('file-icon');
    const uploadForm = document.getElementById('upload-form');
    const placeholderText = document.getElementById('placeholder-text');

    function updateFileIcon(fileExtension) {
        let iconPath = 'apps/data_laporan_magang/logo_drag_and_drop/format_unknown.png'; // Default icon
        if (fileExtension === 'doc') {
            iconPath = 'apps/data_laporan_magang/logo_drag_and_drop/doc.png';
        } else if (fileExtension === 'docx') {
            iconPath = 'apps/data_laporan_magang/logo_drag_and_drop/docx.png';
        } else if (fileExtension === 'pdf') {
            iconPath = 'apps/data_laporan_magang/logo_drag_and_drop/pdf.png';
        }
        fileIcon.src = iconPath;
        fileIcon.style.display = 'block';
    }

    function displayFilePreview(file) {
        const fileName = file.name;
        const fileExtension = fileName.split('.').pop().toLowerCase();

        filePreview.innerHTML = `<strong>${fileName}</strong>`;
        placeholderText.style.display = 'none'; // Always hide the placeholder on file selection

        updateFileIcon(fileExtension);
    }

    // Handle drag-and-drop events
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, e => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('hover');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, e => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('hover');
        });
    });

    dropZone.addEventListener('click', () => fileLaporanInput.click());

    dropZone.addEventListener('drop', e => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileLaporanInput.files = files; // Set the files to the input
            displayFilePreview(files[0]);
        }
    });

    fileLaporanInput.addEventListener('change', e => {
        const files = e.target.files;
        if (files.length > 0) {
            fileInput.files = files; // Update the file input for form submission
            displayFilePreview(files[0]);
        }
    });

    uploadBtn.addEventListener('click', () => {
        if (fileInput.files.length > 0) {
            Swal.fire({
                title: '<span style="font-size: 1.2em;">Konfirmasi</span>',
                html: '<span style="font-size: 1.5em;">Apakah Anda yakin ingin mengunggah file ini?</span>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '<span style="font-size: 1.5em;">Ya, Unggah!</span>',
                cancelButtonText: '<span style="font-size: 1.5em;">Batal</span>'
            }).then((result) => {
                if (result.isConfirmed) {
                    uploadForm.submit();
                }
            });
        } else {
            Swal.fire({
                title: '<span style="font-size: 1.2em;">File masih yang lama</span>',
                html: '<span style="font-size: 1.5em;">Silahkan pilih file terlebih dahulu.</span>',
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
        }
    });
</script>