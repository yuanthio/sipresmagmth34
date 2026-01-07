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
        <li class="active">Backup Database</li>
    </ol>
</div>

<div class="row" style="margin-top: 20px; margin-bottom: 50px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Backup Database
                <span class="pull-right clickable panel-toggle panel-button-tab-left">
                    <em class="fa fa-toggle-up"></em>
                </span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">
                <div class="form-group">
                    <button type="button" class="btn btn-primary" id="download-all-database">
                        <em class='fa fa-download'></em> Unduh Keseluruhan
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama Tabel</th>
                                <th width="320">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Menghubungkan ke database
                            include 'config/database.php';

                            // Query untuk mengambil daftar tabel dari database
                            $query = "SHOW TABLES";
                            $result = mysqli_query($kon, $query);

                            $no = 1; // Untuk nomor urut
                            // Looping melalui setiap tabel
                            while ($row = mysqli_fetch_row($result)) {
                                $table_name = $row[0]; // Nama tabel
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><i class="bi bi-database-fill" style="color: rgb(204, 46, 72);"></i> <?php echo $table_name; ?></td>
                                    <td>
                                        <button class="btn btn-warning tombol_struktur"
                                            data-table="<?php echo $table_name; ?>">
                                            <em class="fa fa-table"></em> Struktur
                                        </button>
                                        <button class="btn btn-info tombol_detail" data-table="<?php echo $table_name; ?>">
                                            <em class="fa fa-eye"></em> Detail Data
                                        </button>
                                        <a href="apps/database/backup_table.php?table=<?php echo $table_name; ?>"
                                            class="btn btn-primary tombol_unduh_tabel">
                                            <em class="fa fa-download"></em> Unduh
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title text-center" id="judul"></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body" style="background-color: rgb(24, 18, 92); padding: 20px;">
                <div id="tampil_data">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="fa fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // Konfirmasi untuk mengunduh keseluruhan database
    document.getElementById("download-all-database").addEventListener("click", function () {
        Swal.fire({
            title: '<span style="font-size: 1.2em;">Konfirmasi</span>',
            html: '<span style="font-size: 1.5em;">Apakah Anda yakin ingin mengunduh keseluruhan database?</span>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<span style="font-size: 1.5em;">Ya, Unduh!</span>',
            cancelButtonText: '<span style="font-size: 1.5em;">Batal</span>'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'apps/database/backup_all_database.php';
            }
        });
    });

    // Konfirmasi untuk mengunduh tabel individual
    $(document).ready(function () {
        $('.tombol_unduh_tabel').on('click', function (e) {
            e.preventDefault(); // Mencegah aksi default link
            var link = $(this).attr('href'); // Ambil link unduh

            Swal.fire({
                title: '<span style="font-size: 1.2em;">Konfirmasi</span>',
                html: '<span style="font-size: 1.5em;">Apakah Anda yakin ingin mengunduh tabel ini?</span>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '<span style="font-size: 1.5em;">Ya, Unduh!</span>',
                cancelButtonText: '<span style="font-size: 1.5em;">Batal</span>'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = link; // Lanjutkan ke link unduh
                }
            });
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('.tombol_detail').on('click', function () {
            var tableName = $(this).data('table'); // Ambil nama tabel dari data-table

            $.ajax({
                url: 'apps/database/detail.php',
                method: 'POST',
                data: { table: tableName }, // Kirim nama tabel ke detail.php
                success: function (data) {
                    $('#tampil_data').html(data);
                    $('#judul').html(tableName);
                    $('#modal').modal('show');
                }
            });
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('.tombol_struktur').on('click', function () {
            var tableName = $(this).data('table'); // Ambil nama tabel dari atribut data-table

            $.ajax({
                url: 'apps/database/struktur.php',
                method: 'POST',
                data: { table: tableName }, // Kirim nama tabel ke struktur.php
                success: function (data) {
                    $('#tampil_data').html(data); // Tampilkan data di dalam modal
                    $('#judul').html(tableName); // Judul modal
                    $('#modal').modal('show'); // Tampilkan modal
                }
            });
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