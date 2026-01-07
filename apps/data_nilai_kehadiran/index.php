<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.all.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css" rel="stylesheet">

<?php
if ($_SESSION["level"] != 'Admin' && $_SESSION["level"] != 'Mentor' && $_SESSION["level"] != 'admin') {
    echo "<br><div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
}
?>

<style>
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
        <li class="active">Data Penilaian Kinerja</li>
    </ol>
</div>

<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading" style="background-color: rgb(24, 18, 92); color: #fff;">
                Data Penilaian Kinerja
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body" style="background-color: rgb(24, 18, 92); color: #fff;">
                <div class="row">
                    <form action="#" method="GET">
                        <input type="hidden" name="page" value="data_nilai_kehadiran" />
                        <div class="col-sm-3">
                            <div class="form-group">
                                <input type="text" name="search" id="search" class="form-control"
                                    value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>"
                                    placeholder="Pencarian">
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Cari</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" style="margin-bottom: 50px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body" style="background-color: rgb(24, 18, 92);">
                <div id="successMessage" class="alert alert-success" style="display:none;">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Mohon tunggu...</span>
                    </div>
                    <strong>Simpan nilai sedang diproses</strong>
                </div>
                <div id="successDeleteMessage" class="alert alert-success" style="display:none;">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Mohon tunggu...</span>
                    </div>
                    <strong>Hapus nilai sedang di proses</strong>
                </div>
                <h5 style="color: white; font-size: 1.2em; margin-bottom: 10px;">Catatan : Untuk data penilaian karyawan magang yang
                    ditandai dengan background orange merupakan data penilaian karyawan magang yang sudah tidak aktif
                </h5>
                <div class="table-responsive" style="margin-bottom: 20px;">
                    <table class="table table-bordered nilai" id="dataTable" cellspacing="0">
                        <thead>
                            <tr>
                                <th style="text-align: center;">No</th>
                                <th style="text-align: center;">Nama</th>
                                <th style="text-align: center;">Kehadiran dan Kedisplinan</th>
                                <th style="text-align: center;">Keaktifan dan Tanggung Jawab</th>
                                <th style="text-align: center;">Kreatifitas dan Inisiatif</th>
                                <th style="text-align: center;">Kepatuhan dan Loyalitas</th>
                                <th style="text-align: center;">Kepribadian dan Tingkah Laku</th>
                                <th style="text-align: center;">Keahlian</th>
                                <th style="text-align: center;">Jumlah</th>
                                <th style="text-align: center;" width="80">Rata-rata</th>
                                <th style="text-align: center;" width="200">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Sertakan file database untuk koneksi ke database
                            include 'config/database.php';

                            // Tangkap nilai pencarian
                            $search = isset($_GET['search']) ? $_GET['search'] : '';

                            // Inisialisasi filter untuk mentor yang sedang login
                            $mentorFilter = "";

                            // Cek apakah pengguna adalah seorang mentor
                            if ($_SESSION["level"] == 'Mentor') {
                                $mentor_name = $_SESSION["nama_mentor"];
                                $mentorFilter = " AND mentor = '$mentor_name'";
                            }

                            // Query untuk mengambil data dari database hanya untuk mahasiswa dengan status magang aktif atau yang telah berakhir dalam 7 hari
                            if ($_SESSION["level"] == 'Mentor') {
                                // Jika login sebagai mentor, tampilkan semua mahasiswa yang dibimbingnya tanpa batasan status
                                $query = "SELECT * FROM tbl_mahasiswa 
                                          WHERE mentor = '$mentor_name' 
                                          AND (nama LIKE '%$search%' OR universitas LIKE '%$search%' OR nim LIKE '%$search%') 
                                          ORDER BY (status_magang = 'aktif') DESC, nama ASC";
                            } elseif ($_SESSION["level"] == 'Admin') {
                                // Jika login sebagai admin, tampilkan semua mahasiswa tanpa filter status
                                $query = "SELECT * FROM tbl_mahasiswa 
                                          WHERE nama LIKE '%$search%' OR universitas LIKE '%$search%' OR nim LIKE '%$search%' 
                                          ORDER BY (status_magang = 'aktif') DESC, nama ASC";
                            } else {
                                // Jika bukan mentor maupun admin, tampilkan berdasarkan status magang aktif atau tidak aktif dalam 7 hari
                                $query = "SELECT * FROM tbl_mahasiswa 
                                          WHERE (status_magang = 'aktif' OR (status_magang = 'tidak aktif' AND DATEDIFF(CURDATE(), akhir_magang) <= 7)) 
                                          AND (nama LIKE '%$search%' OR universitas LIKE '%$search%' OR nim LIKE '%$search%') 
                                          ORDER BY (status_magang = 'aktif') DESC, nama ASC";
                            }
                            $result = mysqli_query($kon, $query);

                            // Counter untuk penomoran baris
                            $counter = 1;

                            if (mysqli_num_rows($result) == 0) {
                                echo "<tr><td colspan='11' class='text-center'>Data belum ada yang ditampilkan</td></tr>";
                            } else {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $idMahasiswa = $row['id_mahasiswa'];

                                    // Cek apakah status magang tidak aktif
                                    $statusMagang = $row['status_magang'];
                                    $rowStyle = ($statusMagang == 'Tidak Aktif') ? 'style="background-color: #ffc107; font-weight: bold;"' : '';

                                    // Query untuk mendapatkan data nilai dari tbl_nilai
                                    $queryNilai = "SELECT * FROM tbl_nilai WHERE id_mahasiswa = $idMahasiswa";
                                    $resultNilai = mysqli_query($kon, $queryNilai);

                                    // Cek apakah ada data nilai untuk mahasiswa ini
                                    if ($rowNilai = mysqli_fetch_assoc($resultNilai)) {
                                        // Output data ke dalam tabel
                                        echo "<tr $rowStyle>";
                                        echo "<td>$counter</td>";
                                        echo "<td>{$row['nama']}</td>";
                                        echo "<td style='text-align:center;'>{$row['nilai_kehadiran']}</td>";
                                        echo "<td style='text-align:center;'>{$rowNilai['keaktifan']}</td>";
                                        echo "<td style='text-align:center;'>{$rowNilai['kreatifitas']}</td>";
                                        echo "<td style='text-align:center;'>{$rowNilai['kepatuhan']}</td>";
                                        echo "<td style='text-align:center;'>{$rowNilai['tingkah_laku']}</td>";
                                        echo "<td style='text-align:center;'>{$rowNilai['keahlian']}</td>";
                                        echo "<td style='text-align:center;'>{$rowNilai['jumlah']}</td>";
                                        echo "<td style='text-align:center;'>{$rowNilai['rata_rata']}</td>";
                                        echo "<td style='text-align:left;'>
                                                <button class='tombol_tambah btn btn-success btn-circle' title='Input Nilai Kinerja' id_mahasiswa='" . $row['id_mahasiswa'] . "'><i class='bi bi-pencil-fill'></i></button>
                                                <button class='tombol_detail btn btn-primary btn-circle' title='Riwayat Presensi'  id_mahasiswa='" . $row['id_mahasiswa'] . "'><i class='fa fa-history'></i></button>
                                                <button data-id-mahasiswa='" . $row['id_mahasiswa'] . "' class='cetak_nilai btn btn-info' title='Cetak Nilai Kinerja'  id='cetak_nilai'><i class='fa fa-print'></i></button>
                                                <button class='tombol_hapus btn btn-danger btn-circle' title='Hapus Nilai Kinerja'  id_mahasiswa='" . $row['id_mahasiswa'] . "'><i class='fa fa-trash'></i></button>
                                            </td>";

                                        echo "</tr>";
                                    } else {
                                        // Jika tidak ada data nilai, tampilkan kolom kosong
                                        echo "<tr $rowStyle>";
                                        echo "<td>$counter</td>";
                                        echo "<td>{$row['nama']}</td>";
                                        echo "<td style='text-align:center;'>{$row['nilai_kehadiran']}</td>";
                                        echo "<td style='text-align:center;'>0</td>";
                                        echo "<td style='text-align:center;'>0</td>";
                                        echo "<td style='text-align:center;'>0</td>";
                                        echo "<td style='text-align:center;'>0</td>";
                                        echo "<td style='text-align:center;'>0</td>";
                                        echo "<td style='text-align:center;'>0</td>";
                                        echo "<td style='text-align:center;'>0</td>";
                                        echo "<td style='text-align:left;'>
                                                <button class='tombol_tambah btn btn-success btn-circle' title='Input Nilai Kinerja' id_mahasiswa='" . $row['id_mahasiswa'] . "'><i class='bi bi-pencil-fill'></i></button>
                                                <button class='tombol_detail btn btn-primary btn-circle' title='Riwayat Presensi' id_mahasiswa='" . $row['id_mahasiswa'] . "'><i class='fa fa-history'></i></button>
                                                <button data-id-mahasiswa='" . $row['id_mahasiswa'] . "' class='cetak_nilai btn btn-info' id='cetak_nilai' title='Cetak Nilai Kinerja'  ><i class='fa fa-print'></i></button>
                                            </td>";
                                        echo "</tr>";
                                    }

                                    $counter++;
                                }
                            }

                            // Tutup koneksi database
                            mysqli_close($kon);
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: rgb(24, 18, 92);">
                <h3 style="color:#fff;" class="modal-title" id="judul"></h3>
                <button style="background-color: #fff; padding: 0 5px;" type="button" class="close"
                    data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body" id="tampil_data" style="background-color: rgb(13, 10, 44); ">
                <div style="background-color: red;">
                    <!-- Konten modal di sini -->
                </div>
            </div>

            <div class="modal-footer" style="background-color: rgb(24, 18, 92);">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i>
                    Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Input form -->
<div class="modal fade" id="inputForm">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background-color: rgb(24, 18, 92);">
                <h3 class="modal-title" style="color: white;">Input Nilai</h3>
                <button style="background-color: #fff; padding: 0 5px;" type="button" class="close"
                    data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="warningMessage" class="alert alert-danger" style="display:none;">
                    Nilai tidak boleh melebihi 100
                </div>
                <form id="nilaiForm">
                    <input type="hidden" id="form_mahasiswa_id" name="id_mahasiswa">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="kehadiran">Kehadiran dan Kedisplinan</label>
                                <input type="text" class="form-control" id="form_kehadiran" name="kehadiran" <?php echo ($_SESSION["level"] == 'Mentor') ? 'readonly' : ''; ?>>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="keaktifan">Keaktifan dan Tanggung Jawab</label>
                                <input type="number" class="form-control" id="form_keaktifan" name="keaktifan">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="kreatifitas">Kreatifitas dan Inisiatif</label>
                                <input type="number" class="form-control" id="form_kreatifitas" name="kreatifitas">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="kepatuhan">Kepatuhan dan Loyalitas</label>
                                <input type="number" class="form-control" id="form_kepatuhan" name="kepatuhan">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="tingkah_laku">Kepribadian dan Tingkah Laku</label>
                                <input type="number" class="form-control" id="form_tingkah_laku" name="tingkah_laku">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="keahlian">Keahlian</label>
                                <input type="number" class="form-control" id="form_keahlian" name="keahlian">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="jumlah">Jumlah</label>
                                <span id="jumlah"></span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="rata_rata">Rata-Rata</label>
                                <span id="rata_rata"></span>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="bi bi-floppy-fill"></i>
                        Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('[title]').tooltip();
    });
</script>

<script>
    $('.tombol_detail').on('click', function () {
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        $.ajax({
            url: 'apps/data_nilai_kehadiran/detail_presensi.php',
            method: 'post',
            data: {
                id_mahasiswa: id_mahasiswa // Make sure id_mahasiswa is set here
            },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Riwayat Presensi';
                $('#modal').modal('show');
            }
        });
    });
</script>

<script>
    // cetak nilai
    $(document).ready(function () {
        $('.cetak_nilai').on('click', function () {
            var id_mahasiswa = $(this).data("id-mahasiswa");

            $.ajax({
                url: 'apps/pengguna/cetak.php',
                method: 'post',
                data: {
                    id_mahasiswa: id_mahasiswa
                },
                success: function (data) {
                    $('#tampil_data').html('<embed src="data:application/pdf;base64,' + data + '" type="application/pdf" width="100%" height="600px"/>');

                    // Ubah teks judul modal dengan id 'judul' menjadi 'Pratinjau Nilai PKL'
                    document.getElementById("judul").innerHTML = 'Pratinjau Nilai PKL';

                    // Buka modal dengan id 'modal'
                    $('#modal').modal('show');
                }
            });
        });
    });
</script>

<script>
    $(document).ready(function () {
        // Fokus pada input
        $('#form_keaktifan, #form_kreatifitas, #form_kepatuhan, #form_tingkah_laku, #form_keahlian').focus(function () {
            if ($(this).val() === '0') {
                $(this).val(''); // Hapus nilai 0 jika nilainya adalah 0
            }
        });

        // Kehilangan fokus dari input
        $('#form_keaktifan, #form_kreatifitas, #form_kepatuhan, #form_tingkah_laku, #form_keahlian').blur(function () {
            if ($(this).val() === '') {
                $(this).val('0'); // Setel kembali nilai 0 jika input kosong
            }
        });

        // Pengecekan nilai lebih dari 100 saat pengguna mengetikkan input
        $('#form_keaktifan, #form_kreatifitas, #form_kepatuhan, #form_tingkah_laku, #form_keahlian').on('input', function () {
            var nilai = parseInt($(this).val());
            if (nilai > 100) {
                $(this).val(100);
                $('#warningMessage').show();
                setTimeout(function () {
                    $('#warningMessage').hide();
                }, 3000);
            } else {
                $('#warningMessage').hide();
            }
        });
    });
</script>

<script>
    // Menghitung jumlah dan rata-rata setiap kali nilai berubah
    $('#form_kehadiran, #form_keaktifan, #form_kreatifitas, #form_kepatuhan, #form_tingkah_laku, #form_keahlian').on('input', function () {
        hitungJumlahDanRataRata();
    });

    /// Fungsi untuk menghitung jumlah dan rata-rata
    function hitungJumlahDanRataRata() {
        var kehadiran = parseInt($('#form_kehadiran').val()) || 0;
        var keaktifan = parseInt($('#form_keaktifan').val()) || 0;
        var kreatifitas = parseInt($('#form_kreatifitas').val()) || 0;
        var kepatuhan = parseInt($('#form_kepatuhan').val()) || 0;
        var tingkah_laku = parseInt($('#form_tingkah_laku').val()) || 0;
        var keahlian = parseInt($('#form_keahlian').val()) || 0;

        // Menghitung jumlah
        var jumlah = kehadiran + keaktifan + kreatifitas + kepatuhan + tingkah_laku + keahlian;

        // Menghitung rata-rata
        var rata_rata = jumlah / 6; // Jumlah kriteria

        // Menetapkan nilai ke elemen dengan ID jumlah dan rata_rata
        $('#jumlah').text($('#jumlah').text() || '0');
        $('#rata_rata').text($('#rata_rata').text() || '0.00');

        $('#jumlah').text(jumlah);
        $('#rata_rata').text(rata_rata.toFixed(2)); // Menetapkan rata-rata dengan dua desimal
    }

    // Memastikan nilai default diatur saat dokumen dimuat
    $(document).ready(function () {
        hitungJumlahDanRataRata();
    });
</script>

<!-- Input Form JS -->
<script>
    $('.tombol_tambah').on('click', function () {
        var id_mahasiswa = $(this).attr("id_mahasiswa");

        // Mengambil nilai dari tbl_nilai
        var nilai_kehadiran = $('#dataTable').find('[id_mahasiswa="' + id_mahasiswa + '"]').closest('tr').find('td:eq(2)').text();
        var nilai_keaktifan = $('#dataTable').find('[id_mahasiswa="' + id_mahasiswa + '"]').closest('tr').find('td:eq(3)').text();
        var nilai_kreatifitas = $('#dataTable').find('[id_mahasiswa="' + id_mahasiswa + '"]').closest('tr').find('td:eq(4)').text();
        var nilai_kepatuhan = $('#dataTable').find('[id_mahasiswa="' + id_mahasiswa + '"]').closest('tr').find('td:eq(5)').text();
        var nilai_tingkah_laku = $('#dataTable').find('[id_mahasiswa="' + id_mahasiswa + '"]').closest('tr').find('td:eq(6)').text();
        var nilai_keahlian = $('#dataTable').find('[id_mahasiswa="' + id_mahasiswa + '"]').closest('tr').find('td:eq(7)').text();

        // Mengisi formulir tambah dengan nilai
        $('#form_mahasiswa_id').val(id_mahasiswa);
        $('#form_kehadiran').val(nilai_kehadiran);
        $('#form_keaktifan').val(nilai_keaktifan);
        $('#form_kreatifitas').val(nilai_kreatifitas);
        $('#form_kepatuhan').val(nilai_kepatuhan);
        $('#form_tingkah_laku').val(nilai_tingkah_laku);
        $('#form_keahlian').val(nilai_keahlian);

        // Menampilkan modal formulir tambah
        $('#inputForm').modal('show');
    });
</script>

<script>
    $('#nilaiForm').submit(function (e) {
        e.preventDefault();
        var id_mahasiswa = $('#form_mahasiswa_id').val();
        var kehadiran = $('#form_kehadiran').val();
        var keaktifan = $('#form_keaktifan').val();
        var kreatifitas = $('#form_kreatifitas').val();
        var kepatuhan = $('#form_kepatuhan').val();
        var tingkah_laku = $('#form_tingkah_laku').val();
        var keahlian = $('#form_keahlian').val();

        // Hitung Jumlah dan Rata-rata
        var jumlah = parseInt(kehadiran) + parseInt(keaktifan) + parseInt(kreatifitas) + parseInt(kepatuhan) + parseInt(tingkah_laku) + parseInt(keahlian);
        var rata_rata = jumlah / 6; // Anggap ada 5 kriteria

        // Kirim data ke server
        $.ajax({
            url: 'apps/data_nilai_kehadiran/tambah.php',
            method: 'post',
            data: {
                id_mahasiswa: id_mahasiswa,
                kehadiran: kehadiran,
                keaktifan: keaktifan,
                kreatifitas: kreatifitas,
                kepatuhan: kepatuhan,
                tingkah_laku: tingkah_laku,
                keahlian: keahlian,
                jumlah: jumlah,
                rata_rata: rata_rata
            },
            success: function (data) {
                $('#inputForm').modal('hide');
                Swal.fire({
                    title: `<span style="font-size: 1.5em;">Sukses</span>`,
                    html: `<span style="font-size: 1.5em;">Nilai berhasil disimpan.</span>`,
                    icon: 'success',
                    showConfirmButton: false
                });
                setTimeout(function () {
                    location.reload(true);
                }, 2000);
            },
        });
    });
</script>

<script>
    $('.tombol_hapus').on('click', function () {
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        Swal.fire({
            title: '<span style="font-size: 1.5em;">Konfirmasi</span>',
            html: '<span style="font-size: 1.5em;">Apakah Anda yakin ingin menghapus data nilai karyawan magang ini?</span>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<span style="font-size: 1.5em;">Ya, hapus!</span>',
            cancelButtonText: '<span style="font-size: 1.5em;">Batal</span>'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'apps/data_nilai_kehadiran/hapus.php',
                    method: 'post',
                    data: {
                        id_mahasiswa: id_mahasiswa
                    },
                    dataType: 'json',
                    success: function (data) {
                        if (data.status === 'success') {
                            $('#dataTable').find('[id_mahasiswa="' + id_mahasiswa + '"]').closest('tr').remove();
                            Swal.fire({
                                title: '<span style="font-size: 1.5em;">Berhasil!</span>',
                                html: '<span style="font-size: 1.5em;">Data nilai karyawan magang berhasil dihapus.</span>',
                                icon: 'success'
                            });
                            setTimeout(function () {
                                location.reload(true);
                            }, 2000);
                        } else {
                            Swal.fire({
                                title: '<span style="font-size: 1.5em;">Gagal!</span>',
                                html: '<span style="font-size: 1.5em;">Terjadi kesalahan saat menghapus data nilai.</span>',
                                icon: 'error'
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            title: '<span style="font-size: 1.5em;">Gagal!</span>',
                            html: '<span style="font-size: 1.5em;">Terjadi kesalahan saat menghapus data nilai.</span>',
                            icon: 'error'
                        });
                    }
                });
            }
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