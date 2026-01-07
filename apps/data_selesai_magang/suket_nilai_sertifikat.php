<style>
    @import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css");

    .table-responsive {
        overflow-y: auto;
        max-height: 500px;
    }

    .table thead {
        position: sticky;
        top: 0;
        background-color: #f1f1f1;
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
        <li class="active">Data Akhir Magang</li>
    </ol>
</div>

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Data Akhir Magang
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama</th>
                                <th>Universitas / Sekolah</th>
                                <th>Jenis Data</th>
                                <th>Hari</th>
                                <th>Di Unggah</th>
                                <th>Ukuran File</th>
                                <th>Nama File</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include "config/database.php";

                            // Mengasumsikan Anda memiliki variabel sesi yang menyimpan id mahasiswa yang sedang login
                            $id_mahasiswa = $_SESSION['id_mahasiswa'];

                            // Ambil data dari tabel tbl_suket untuk mahasiswa yang sedang login
                            $query = "SELECT * FROM tbl_suket WHERE id_mahasiswa = $id_mahasiswa";
                            $result = mysqli_query($kon, $query);

                            // Cek apakah data kosong
                            if (mysqli_num_rows($result) == 0) {
                                echo '<tr><td colspan="9" class="text-center">Data masih kosong</td></tr>';
                                $data_kosong = true;
                            } else {
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Ambil ekstensi file_suket
                                    $file_ext = pathinfo($row['file_suket'], PATHINFO_EXTENSION);

                                    // Tentukan ikon berdasarkan ekstensi file
                                    if ($file_ext == 'pdf') {
                                        $icon = 'pdf.png';
                                    } elseif ($file_ext == 'doc') {
                                        $icon = 'doc.png';
                                    } elseif ($file_ext == 'docx') {
                                        $icon = 'docx.png';
                                    } else {
                                        $icon = 'default.png'; // jika ada ekstensi yang tidak diketahui
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo $no; ?></td>
                                        <td><?php echo $row['nama']; ?></td>
                                        <td><?php echo $row['universitas']; ?></td>
                                        <td><?php echo $row['jenis_data']; ?></td>
                                        <td><?php echo $row['hari']; ?></td>
                                        <td><?php echo $row['tanggal']; ?></td>
                                        <td><?php echo $row['ukuran_file']; ?></td>
                                        <td>
                                            <img width="30" src="apps/data_selesai_magang/format_file/<?php echo $icon; ?>"
                                                alt="icon">
                                            <?php echo $row['file_suket']; ?>
                                        </td>
                                        <td>
                                            <a href="apps/data_selesai_magang/download.php?id_suket=<?php echo $row['id_suket']; ?>"
                                                class="btn btn-primary"><i class="bi bi-download"></i></a>
                                        </td>
                                    </tr>
                                    <?php
                                    $no++;
                                }
                                $data_kosong = false;
                            }

                            // Tutup koneksi database
                            mysqli_close($kon);
                            ?>
                        </tbody>
                    </table>
                </div>
                <p style="color: #fff;">
                    <?php
                    if ($data_kosong) {
                        echo "Data akhir magang belum di unggah oleh administrator <span class='label label-danger'>X</span>";
                    } else {
                        echo "Data akhir magang sudah bisa di download <span class='label label-success'>✓</span>";
                    }
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

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