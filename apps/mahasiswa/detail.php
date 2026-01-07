<style>
    .btn {
        transition: .4s all;
    }

    .btn:hover {
        transform: translateY(-3px);
    }
</style>

<?php
session_start();

if ($_SESSION["level"] != 'Admin' && $_SESSION["level"] != 'Mentor' && $_SESSION["level"] != 'admin') {
    echo "<br><div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
}
?>

<?php
// Sertakan file konfigurasi database dan file fungsi
include '../../config/database.php';
include '../../config/function.php';

// Ambil nilai id_mahasiswa yang dikirimkan melalui metode POST
$id_mahasiswa = $_POST["id_mahasiswa"];
$sql = "SELECT * FROM tbl_mahasiswa WHERE id_mahasiswa = $id_mahasiswa LIMIT 1";
$hasil = mysqli_query($kon, $sql);
$data = mysqli_fetch_array($hasil);

// Ambil waktu terakhir login dari tbl_log_aktivitas
$kode_mahasiswa = $data['kode_mahasiswa'];

// Ambil waktu terakhir login mahasiswa
$terakhir_login = "-"; // default jika belum pernah login

$query_login = "SELECT tanggal FROM tbl_log_aktivitas 
                WHERE level = 'Mahasiswa' 
                AND aktivitas = 'login' 
                AND kode_pengguna = '$kode_mahasiswa' 
                ORDER BY tanggal DESC LIMIT 1";
$hasil_login = mysqli_query($kon, $query_login);
if ($row = mysqli_fetch_assoc($hasil_login)) {
    $terakhir_login = date("d-m-Y H:i:s", strtotime($row['tanggal']));
}
?>

<div class="row">
    <div class="col-lg-4 text-center" style="margin-bottom: 40px;">
        <div style="width: 100%; height: auto; text-align: center;">
            <img src="apps/mahasiswa/foto/<?php echo $data['foto']; ?>" alt="<?php echo $data['nama']; ?>"
                style="width: 240px; height: 240px; object-fit: cover; border-radius: 50%; border: 4px solid rgb(13, 10, 44);">
        </div>
    </div>
    <div class="col-lg-8">
        <div class="table-responsive" style="margin-bottom: 10px;">
            <table class="table" style="background-color: rgb(24, 18, 92); color: #fff; padding: 10px;">
                <tbody>
                    <tr>
                        <td>Nama Lengkap</td>
                        <td width="70%">:
                            <?php echo $data['nama']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>NIM / NIS</td>
                        <td width="70%">:
                            <?php echo $data['nim']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Universitas / Sekolah</td>
                        <td width="70%">:
                            <?php echo $data['universitas']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Jurusan</td>
                        <td width="70%">:
                            <?php echo $data['jurusan']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Mulai Magang</td>
                        <td width="70%">:
                            <?php
                            $tgl = date("d", strtotime($data['mulai_magang']));
                            $bulan = date("m", strtotime($data['mulai_magang']));
                            $tahun = date("Y", strtotime($data['mulai_magang']));
                            echo $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun;
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Akhir Magang</td>
                        <td width="70%">:
                            <?php
                            $tgl = date("d", strtotime($data['akhir_magang']));
                            $bulan = date("m", strtotime($data['akhir_magang']));
                            $tahun = date("Y", strtotime($data['akhir_magang']));
                            echo $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun;
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Unit Kerja</td>
                        <td width="70%">:
                            <?php echo $data['unit_kerja']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Mentor</td>
                        <td width="70%">:
                            <?php echo $data['mentor']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>No Telp</td>
                        <td width="70%">:
                            <?php echo $data['no_telp']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td width="70%">:
                            <?php echo $data['email']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td width="70%">:
                            <?php echo $data['alamat']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Status Magang</td>
                        <td width="70%">
                            : <span
                                class="label <?php echo $data['status_magang'] === 'Aktif' ? 'label-success' : 'label-danger'; ?>">
                                <?php echo $data['status_magang']; ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>Terakhir Login</td>
                        <td width="70%">: <?php echo $terakhir_login; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($_SESSION["level"] == 'Admin'): ?>
    <button style="margin-bottom: 5px;" data-id-mahasiswa='<?php echo $data['id_mahasiswa']; ?>'
        class="cetak_nilai btn btn-info" id="cetak_nilai"><i class="fa fa-print"></i> Template Nilai PKL</button>
    <button style="margin-bottom: 5px;" data-id-mahasiswa='<?php echo $data['id_mahasiswa']; ?>'
        class="cetak_suket btn btn-info" id="cetak_suket"><i class="fa fa-print"></i> Template Surat Keterangan</button>
    <button style="margin-bottom: 5px;" data-id-mahasiswa='<?php echo $data['id_mahasiswa']; ?>'
        class="cetak_sertifikat btn btn-info" id="cetak_sertifikat"><i class="fa fa-print"></i> Template Sertifikat</button>
<?php endif; ?>

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
    // cetak suket
    $(document).ready(function () {
        $('.cetak_suket').on('click', function () {
            var id_mahasiswa = $(this).data("id-mahasiswa");

            $.ajax({
                url: 'apps/pengguna/cetak_suket.php',
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
    // cetak suket
    $(document).ready(function () {
        $('.cetak_sertifikat').on('click', function () {
            var id_mahasiswa = $(this).data("id-mahasiswa");

            $.ajax({
                url: 'apps/pengguna/cetak_sertifikat.php',
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