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
// Menghubungkan database
include '../../config/database.php';
include '../../config/function.php';
date_default_timezone_set('Asia/Jakarta');

// Fungsi untuk membersihkan data input
function input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$kode_pengguna = input($_SESSION["kode_pengguna"]);

// Ambil data mahasiswa
$sql = "SELECT * FROM tbl_mahasiswa WHERE kode_mahasiswa='$kode_pengguna' LIMIT 1";
$hasil = mysqli_query($kon, $sql);
$data_mahasiswa = mysqli_fetch_array($hasil);

// Ambil data user untuk level
$sql_user = "SELECT * FROM tbl_user WHERE kode_pengguna='$kode_pengguna' LIMIT 1";
$hasil_user = mysqli_query($kon, $sql_user);
$data_user = mysqli_fetch_array($hasil_user);

// Periksa apakah formulir dikirimkan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    // Dapatkan informasi file
    $namaFile = input($_FILES['file']['name']);
    $fileTempName = $_FILES['file']['tmp_name'];
    $ukuranFile = $_FILES['file']['size'];
    $tipeFile = $_FILES['file']['type'];

    // Bersihkan nama file
    $namaFileAsli = pathinfo($namaFile, PATHINFO_FILENAME);
    $direktoriUpload = '../../apps/data_laporan_magang/upload/';
    $ekstensiFile = pathinfo($namaFile, PATHINFO_EXTENSION);
    $namaFileUnik = $namaFileAsli . '.' . $ekstensiFile;
    $jalurFile = $direktoriUpload . $namaFileUnik;

    // Periksa ukuran file (1 MB = 1024 KB)
    $ukuranMax = 1024 * 1024; // 1MB in bytes
    if ($ukuranFile > $ukuranMax) {
        header("Location: ../../index.php?page=laporan_magang&unggah=gagal_ukuran");
        simpanLogAktivitas($kon, $data_mahasiswa['nama'], 'Mahasiswa', $kode_pengguna, 'Unggah data laporan magang (Ukuran file terlalu besar)', 'gagal');
        exit();
    } else {
        // Periksa format file
        $formatDukung = array('pdf', 'doc', 'docx');
        if (!in_array(strtolower($ekstensiFile), $formatDukung)) {
            header("Location: ../../index.php?page=laporan_magang&unggah=gagal_format");
            simpanLogAktivitas($kon, $data_mahasiswa['nama'], 'Mahasiswa', $kode_pengguna, 'Unggah data laporan magang (Format file tidak didukung)', 'gagal');
            exit();
        } else {
            // Periksa apakah pengguna sudah mengunggah file sebelumnya
            $queryCekFile = "SELECT COUNT(*) as total FROM tbl_laporan WHERE kode_mahasiswa = '$kode_pengguna'";
            $resultCekFile = mysqli_query($kon, $queryCekFile);
            $rowCekFile = mysqli_fetch_assoc($resultCekFile);

            if ($rowCekFile['total'] > 0) {
                header("Location: ../../index.php?page=laporan_magang&unggah=sudah_unggah");
                simpanLogAktivitas($kon, $data_mahasiswa['nama'], 'Mahasiswa', $kode_pengguna, 'Unggah data laporan magang (Sudah unggah laporan sebelumnya)', 'gagal');
                exit();
            } else {
                // Pindahkan file yang diunggah ke direktori yang diinginkan
                if (move_uploaded_file($fileTempName, $jalurFile)) {

                    // Dapatkan informasi lain yang diperlukan
                    $tanggal = date('Y-m-d');
                    $hari = MendapatkanHari(date('l')); // Mendapatkan nama hari dalam bahasa Indonesia

                    function formatSizeUnits($bytes)
                    {
                        $units = array('B', 'KB', 'MB', 'GB', 'TB');
                        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $bytes /= 1024, $i++);
                        return round($bytes, 2) . ' ' . $units[$i];
                    }

                    // Masukkan data ke dalam tbl_laporan
                    $queryInsert = "INSERT INTO tbl_laporan (nama, kode_mahasiswa, universitas, tanggal, hari, file_laporan, ukuran_file) 
                    SELECT nama, kode_mahasiswa, universitas, '$tanggal', '$hari', '$namaFileUnik', '" . formatSizeUnits($ukuranFile) . "' 
                    FROM tbl_mahasiswa 
                    WHERE kode_mahasiswa = '$kode_pengguna'";

                    if (mysqli_query($kon, $queryInsert)) {
                        simpanLogAktivitas($kon, $data_mahasiswa['nama'], 'Mahasiswa', $kode_pengguna, 'Unggah data laporan magang', 'berhasil');
                        header("Location: ../../index.php?page=laporan_magang&unggah=berhasil");
                        exit();
                    } else {
                        simpanLogAktivitas($kon, $data_mahasiswa['nama'], 'Mahasiswa', $kode_pengguna, 'Unggah data laporan magang (Gagal menyimpan ke database)', 'gagal');
                        header("Location: ../../index.php?page=laporan_magang&unggah=gagal_query");
                        exit();
                    }
                } else {
                    simpanLogAktivitas($kon, $data_mahasiswa['nama'], 'Mahasiswa', $kode_pengguna, 'Unggah data laporan magang (Gagal memindahkan file)', 'gagal');
                    header("Location: ../../index.php?page=laporan_magang&unggah=gagal_upload");
                    exit();
                }
            }
        }
    }
}

// Fungsi untuk menyimpan log aktivitas
function simpanLogAktivitas($kon, $nama, $level, $kode_pengguna, $aktivitas, $status)
{
    $tanggal_waktu = date('Y-m-d H:i:s');
    $queryLog = "INSERT INTO tbl_log_aktivitas (tanggal, nama, level, kode_pengguna, aktivitas, status) 
                 VALUES ('$tanggal_waktu', '$nama', '$level', '$kode_pengguna', '$aktivitas', '$status')";
    mysqli_query($kon, $queryLog);
}
?>

<div class="front-enkapsulasi" style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
    <div class="form-group">
        <input type="file" name="file_laporan" id="file_laporan" class="form-control">
    </div>
    <div class="enkapsulasi" style="padding: 10px; background-color: white; border-radius: 5px;">
        <div id="drop-zone">
            <div class="row" style="padding: 0 30px;">
                <span id="placeholder-text">Drag & Drop file di sini atau klik untuk memilih file<br>( Ukuran file
                    maksimal 1mb )</span>
                <div class="row"
                    style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%;">
                    <img id="file-icon" class="file-icon" src="" alt="file type icon" style="display:none;">
                    <div id="file-preview" class="file-preview"></div>
                </div>
            </div>
        </div>
    </div>
    <form id="upload-form" action="apps/pengguna/mulai_upload_laporan.php" method="post" enctype="multipart/form-data">
        <input type="file" name="file" id="file" accept=".pdf, .doc, .docx" required>
        <button id="upload-btn" class="btn btn-primary" type="button"><i class="bi bi-upload"></i> Unggah</button>
    </form>
</div>

<script>
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file');
    const fileInputLaporan = document.getElementById('file_laporan');
    const uploadBtn = document.getElementById('upload-btn');
    const filePreview = document.getElementById('file-preview');
    const fileIcon = document.getElementById('file-icon');
    const uploadForm = document.getElementById('upload-form');
    const placeholderText = document.getElementById('placeholder-text');

    function displayFilePreview(file) {
        const fileName = file.name;
        const fileExtension = fileName.split('.').pop().toLowerCase();

        filePreview.innerHTML = `<strong>${fileName}</strong>`;

        placeholderText.style.display = 'none';

        if (fileExtension === 'doc') {
            fileIcon.src = 'apps/data_laporan_magang/logo_drag_and_drop/doc.png';
        } else if (fileExtension === 'docx') {
            fileIcon.src = 'apps/data_laporan_magang/logo_drag_and_drop/docx.png';
        } else if (fileExtension === 'pdf') {
            fileIcon.src = 'apps/data_laporan_magang/logo_drag_and_drop/pdf.png';
        } else {
            fileIcon.src = 'apps/data_laporan_magang/logo_drag_and_drop/format_unknown.png';
        }

        fileIcon.style.display = 'block';
    }

    function handleFileInputChange(input) {
        if (input.files.length > 0) {
            displayFilePreview(input.files[0]);
            fileInput.files = input.files; // Ensure the file is set for the other input
        }
    }

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

    dropZone.addEventListener('drop', e => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileInputChange({ files: files });
        }
    });

    dropZone.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', () => {
        handleFileInputChange(fileInput);
    });

    fileInputLaporan.addEventListener('change', () => {
        handleFileInputChange(fileInputLaporan);
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
                title: '<span style="font-size: 1.2em;">Tidak ada file yang dipilih</span>',
                html: '<span style="font-size: 1.5em;">Silahkan pilih file terlebih dahulu.</span>',
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: '<span style="font-size: 1.5em;">OK</span>'
            });
        }
    });
</script>