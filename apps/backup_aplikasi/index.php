<script src="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js
"></script>
<link href="
https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css
" rel="stylesheet">

<style>
    .table-responsive {
        overflow-y: auto;
        max-height: 500px;
    }

    .table thead {
        position: sticky;
        top: 0;
        background-color: #f1f1f1;
        z-index: 2;
    }

    /* Overlay */
    #loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(24, 18, 92, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;

        opacity: 1;
        visibility: visible;
        transition: opacity 0.5s ease, visibility 0.5s ease;
    }

    /* Hidden state */
    #loader-overlay.fade-out {
        opacity: 0;
        visibility: hidden;
    }

    .loader {
        width: 50px;
        aspect-ratio: 1;
        display: grid;
    }

    .loader::before,
    .loader::after {
        content: "";
        grid-area: 1/1;
        --c: no-repeat radial-gradient(farthest-side, #25b09b 92%, #0000);
        background:
            var(--c) 50% 0,
            var(--c) 50% 100%,
            var(--c) 100% 50%,
            var(--c) 0 50%;
        background-size: 12px 12px;
        animation: l12 1s infinite;
    }

    .loader::before {
        margin: 4px;
        filter: hue-rotate(45deg);
        background-size: 8px 8px;
        animation-timing-function: linear
    }

    @keyframes l12 {
        100% {
            transform: rotate(.5turn)
        }
    }
</style>

<div id="loader-overlay">
    <div class="loader"></div>
</div>

<div class="row">
    <ol class="breadcrumb" style="background-color: #eaeaea">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Backup Aplikasi</li>
    </ol>
</div>

<div class="row" style="margin-top: 20px; margin-bottom: 50px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Backup Aplikasi
                <span class="pull-right clickable panel-toggle panel-button-tab-left">
                    <em class="fa fa-toggle-up"></em>
                </span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">
                <div class="form-group">
                    <button type="button" class="btn btn-primary" id="download-all-folder" data-toggle="modal"
                        data-target="#modalUnduhAll">
                        <em class='fa fa-download'></em> Unduh Keseluruhan
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Folder / File</th>
                                <th width="100">Ukuran</th>
                                <th width="100">Tipe</th>
                                <th width="200">Tanggal Diubah</th> <!-- Tambahkan kolom Tanggal Diubah -->
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include 'config/function.php'; // Pastikan function.php sudah di-include
                            
                            // Path folder 'absensi_magang_coba'
                            $folder_path = '../absensi_magang_coba/'; // Ubah ke path yang sesuai
                            
                            // Ambil semua item dari directory 'absensi_magang_coba'
                            $items = scandir($folder_path);

                            // Filter item yang bukan '.' dan '..'
                            $items = array_diff($items, array('.', '..'));

                            // Pisahkan folder dan file
                            $folders = [];
                            $files = [];

                            foreach ($items as $item) {
                                $item_path = $folder_path . $item;
                                if (is_dir($item_path)) {
                                    $folders[] = $item; // Simpan folder
                                } else {
                                    $files[] = $item; // Simpan file
                                }
                            }

                            // Gabungkan folder dan file
                            $sorted_items = array_merge($folders, $files);

                            if (!empty($sorted_items)) {
                                $no = 1;
                                foreach ($sorted_items as $item) {
                                    $item_path = $folder_path . $item;
                                    // Dapatkan waktu modifikasi file/folder
                                    $modified_time = filemtime($item_path);

                                    // Tanggal dan jam
                                    $tanggal = date('d', $modified_time); // Hari
                                    $bulan = date('n', $modified_time);   // Bulan dalam bentuk angka
                                    $tahun = date('Y', $modified_time);   // Tahun
                                    $jam = date('H:i', $modified_time);   // Jam dan menit
                            
                                    ?>
                                    <tr>
                                        <td><?php echo $no; ?></td>
                                        <td>
                                            <?php if (is_dir($item_path)): ?>
                                                <i class="fa fa-folder" style="color: orange;"></i> <?php echo $item; ?>
                                            <?php else: ?>
                                                <i class="fa fa-file" style="color: blue;"></i> <?php echo $item; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            if (is_dir($item_path)) {
                                                // Hitung ukuran folder
                                                $size = 0;
                                                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($item_path));
                                                foreach ($iterator as $file) {
                                                    $size += $file->getSize();
                                                }
                                                echo formatSize($size); // Format ukuran
                                            } else {
                                                // Ukuran file
                                                echo formatSize(filesize($item_path)); // Format ukuran
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            if (is_dir($item_path)) {
                                                echo 'File Folder';
                                            } else {
                                                // Tentukan tipe berdasarkan ekstensi
                                                $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));
                                                switch ($ext) {
                                                    case 'php':
                                                        echo 'PHP Source';
                                                        break;
                                                    case 'sql':
                                                        echo 'SQL File';
                                                        break;
                                                    case 'txt':
                                                        echo 'Text File';
                                                        break;
                                                    case 'jpg':
                                                    case 'jpeg':
                                                    case 'png':
                                                    case 'gif':
                                                        echo 'Image File';
                                                        break;
                                                    case 'pdf':
                                                        echo 'PDF Document';
                                                        break;
                                                    case 'doc':
                                                    case 'docx':
                                                        echo 'Word Document';
                                                        break;
                                                    case 'xls':
                                                    case 'xlsx':
                                                        echo 'Excel Spreadsheet';
                                                        break;
                                                    case 'ppt':
                                                    case 'pptx':
                                                        echo 'PowerPoint Presentation';
                                                        break;
                                                    case 'zip':
                                                    case 'rar':
                                                        echo 'Compressed File';
                                                        break;
                                                    case 'mp4':
                                                        echo 'MP4 Video';
                                                        break;
                                                    case 'mp3':
                                                        echo 'MP3 Audio';
                                                        break;
                                                    case 'csv':
                                                        echo 'CSV File';
                                                        break;
                                                    case 'html':
                                                    case 'htm':
                                                        echo 'HTML Document';
                                                        break;
                                                    case 'js':
                                                        echo 'JavaScript File';
                                                        break;
                                                    case 'css':
                                                        echo 'CSS File';
                                                        break;
                                                    case 'json':
                                                        echo 'JSON File';
                                                        break;
                                                    case 'xml':
                                                        echo 'XML File';
                                                        break;
                                                    default:
                                                        echo 'Other';
                                                        break;
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            // Tampilkan tanggal dan jam modifikasi dengan bulan dalam bahasa Indonesia
                                            echo $tanggal . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun . ', ' . $jam;
                                            ?>
                                        </td>
                                        <td>
                                            <?php if (is_dir($item_path)): ?>
                                                <button type="button" class="btn btn-primary tombol_unduh_folder"
                                                    data-folder="<?php echo $item; ?>" data-toggle="modal"
                                                    data-target="#modalUnduh">
                                                    <em class="fa fa-download"></em> Unduh
                                                </button>
                                            <?php else: ?>
                                                <a href="apps/backup_aplikasi/backup_file.php?file=<?php echo urlencode($item); ?>"
                                                    class="btn btn-primary">
                                                    <em class="fa fa-download"></em> Unduh
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $no++;
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="6">Tidak ada item ditemukan.</td>
                                </tr>
                                <?php
                            }

                            // Fungsi untuk format ukuran
                            function formatSize($size)
                            {
                                if ($size >= 1073741824) {
                                    return number_format($size / 1073741824, 2) . ' GB';
                                } elseif ($size >= 1048576) {
                                    return number_format($size / 1048576, 2) . ' MB';
                                } elseif ($size >= 1024) {
                                    return number_format($size / 1024, 2) . ' KB';
                                } else {
                                    return $size . ' bytes';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk memilih format arsip keseluruhan -->
<div class="modal fade" id="modalUnduhAll" tabindex="-1" role="dialog" aria-labelledby="modalUnduhAllLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalUnduhAllLabel">Pilih Format Arsip untuk Keseluruhan Folder</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Pilih format arsip untuk mengunduh keseluruhan folder aplikasi, dibutuhkan waktu yang
                    cukup lama dalam mengunduhnya.</p>
                <div class="btn-group" style="display: flex; justify-content: center; flex-wrap: wrap;">
                    <button type="button" class="btn btn-primary unduh-all" data-format="zip">ZIP</button>
                    <button type="button" class="btn btn-secondary unduh-all" data-format="tar">TAR</button>
                    <button type="button" class="btn btn-info unduh-all" data-format="tar.gz">TAR.GZ</button>
                    <button type="button" class="btn btn-warning unduh-all" data-format="tar.bz2">TAR.BZ2</button>
                    <button type="button" class="btn btn-success unduh-all" data-format="tar.xz">TAR.XZ</button>
                    <button type="button" class="btn btn-danger unduh-all" data-format="tar.lz4">TAR.LZ4</button>
                    <button type="button" class="btn btn-dark unduh-all" style="background-color: grey; color: white;" data-format="tar.sz">TAR.SZ</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal untuk memilih format arsip -->
<div class="modal fade" id="modalUnduh" tabindex="-1" role="dialog" aria-labelledby="modalUnduhLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalUnduhLabel">Pilih Format Arsip</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Pilih format arsip untuk folder: <strong id="folderName"></strong></p>
                <div class="btn-group" style="display: flex; justify-content: center; flex-wrap: wrap;">
                    <button type="button" class="btn btn-primary unduh" data-format="zip">ZIP</button>
                    <button type="button" class="btn btn-secondary unduh" data-format="tar">TAR</button>
                    <button type="button" class="btn btn-info unduh" data-format="tar.gz">TAR.GZ</button>
                    <button type="button" class="btn btn-warning unduh" data-format="tar.bz2">TAR.BZ2</button>
                    <button type="button" class="btn btn-success unduh" data-format="tar.xz">TAR.XZ</button>
                    <button type="button" class="btn btn-danger unduh" data-format="tar.lz4">TAR.LZ4</button>
                    <button type="button" class="btn btn-dark unduh" style="background-color: grey; color: white;" data-format="tar.sz">TAR.SZ</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        var folderName = '';

        // Ketika tombol "Unduh" diklik, simpan nama folder
        $('.tombol_unduh_folder').on('click', function () {
            folderName = $(this).data('folder');
            $('#folderName').text(folderName);  // Tampilkan nama folder di modal
        });

        // Ketika tombol format arsip diklik
        $('.unduh').on('click', function () {
            var format = $(this).data('format');
            var url = 'apps/backup_aplikasi/backup_folder.php?folder=' + folderName + '&format=' + format;

            // Redirect untuk mengunduh file
            window.location.href = url;
        });
    });
</script>

<script>
    $(document).ready(function () {
        var fileName = '';

        // Ketika tombol "Unduh File" diklik, simpan nama file
        $('.tombol_unduh_file').on('click', function () {
            fileName = $(this).data('file');
            $('#fileName').text(fileName);  // Tampilkan nama file di modal
        });

        // Ketika tombol unduh file diklik
        $('.unduh-file').on('click', function () {
            var url = 'apps/backup_aplikasi/backup_file.php?file=' + encodeURIComponent(fileName);
            // Redirect untuk mengunduh file
            window.location.href = url;
        });
    });
</script>

<script>
    $(document).ready(function () {
        // Ketika tombol format arsip untuk keseluruhan diklik
        $('.unduh-all').on('click', function () {
            var format = $(this).data('format');
            var url = 'apps/backup_aplikasi/backup_all.php?format=' + format;

            // Redirect untuk mengunduh file
            window.location.href = url;
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const loader = document.getElementById("loader-overlay");

        if (sessionStorage.getItem("sudah_load")) {
            // Kalau sudah pernah load, langsung sembunyikan
            loader.classList.add("fade-out");
        } else {
            // Kalau pertama kali load, kasih delay sebelum fade out
            setTimeout(function () {
                loader.classList.add("fade-out");
                sessionStorage.setItem("sudah_load", "true");
            }, 1000); // spinner muncul 1 detik lalu fade out
        }
    });
</script>