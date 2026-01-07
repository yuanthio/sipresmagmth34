<?php
include '../../config/database.php';

$id_absensi = $_POST['id_absensi'];
$query = mysqli_query($kon, "SELECT id_mahasiswa, kamera, status, waktu, tanggal, input_admin 
                              FROM tbl_absensi WHERE id_absensi = '$id_absensi'");
$data = mysqli_fetch_assoc($query);

if ($data) {
    $id_mahasiswa = $data['id_mahasiswa'];
    $status = $data['status'];
    $filename = $data['kamera'];
    $tanggal = $data['tanggal'];
    $input_admin = $data['input_admin'];

    $status_text = "Tidak Diketahui";
    $label_class = "default";

    if ($status == '1') {
        $status_text = "Hadir";
        $label_class = "success";
    } else if ($status == '2') {
        $status_text = "Izin";
        $label_class = "info";
    } else if ($status == '3') {
        $status_text = "Terlambat";
        $label_class = "warning";
    } else if ($status == '4') {
        $status_text = "Tidak Hadir";
        $label_class = "danger";
    } else if ($status == '5') {
        $status_text = "WFA";
        $label_class = "primary";
    }

    // Cek apakah ada foto kamera
    $ada_foto_kamera = !empty($filename) && file_exists("../../apps/pengguna/kamera/" . $filename);

    // Jika status izin, cek juga foto alasan
    $ada_foto_izin = false;
    $izin = null;

    if ($status == '2') {
        $q_izin = mysqli_query($kon, "SELECT foto FROM tbl_alasan WHERE id_mahasiswa='$id_mahasiswa' AND tanggal='$tanggal' LIMIT 1");
        $izin = mysqli_fetch_assoc($q_izin);

        if ($izin && !empty($izin['foto']) && file_exists("../../apps/pengguna/bukti_alasan/" . $izin['foto'])) {
            $ada_foto_izin = true;
        }
    }

    // Jika status WFA, cek juga bukti WFA berupa jpg/png/jpeg
    $ada_foto_wfa = false;
    $wfa = null;

    if ($status == '5') {
        $q_wfa = mysqli_query($kon, "SELECT bukti_wfa FROM tbl_bukti_wfa WHERE id_mahasiswa='$id_mahasiswa' AND tanggal='$tanggal' LIMIT 1");
        $wfa = mysqli_fetch_assoc($q_wfa);

        if ($wfa && !empty($wfa['bukti_wfa'])) {
            $ext = strtolower(pathinfo($wfa['bukti_wfa'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png'];
            if (in_array($ext, $allowed_ext) && file_exists("../../apps/data_absensi/file_wfa/" . $wfa['bukti_wfa'])) {
                $ada_foto_wfa = true;
            }
        }
    }

    // Jika ada salah satu foto, tampilkan semuanya
    if ($ada_foto_kamera || $ada_foto_izin || $ada_foto_wfa) {
        echo '<div class="text-center">';

        // Foto kamera jika ada
        if ($ada_foto_kamera) {
            $path_url = "apps/pengguna/kamera/" . $filename;
            echo '<img src="' . $path_url . '" alt="Foto Presensi" class="img-thumbnail" 
                  style="width: 400px; height:400px; object-fit: cover; border: 2px solid #444; 
                  border-radius: 10px; margin-bottom: 10px;"><br>';
        }

        // Foto izin jika ada
        if ($ada_foto_izin) {
            $izin_foto_url = "apps/pengguna/bukti_alasan/" . $izin['foto'];
            echo '<img src="' . $izin_foto_url . '" alt="Bukti Izin" class="img-thumbnail" 
                  style="width: 400px; height:400px; object-fit: cover; border: 2px solid #444; 
                  border-radius: 10px; margin-bottom: 10px;"><br>';
        }

        // Foto WFA jika ada
        if ($ada_foto_wfa) {
            $wfa_foto_url = "apps/data_absensi/file_wfa/" . $wfa['bukti_wfa'];
            echo '<img src="' . $wfa_foto_url . '" alt="Bukti WFA" class="img-thumbnail" 
                  style="width: 400px; height:400px; object-fit: cover; border: 2px solid #444; 
                  border-radius: 10px; margin-bottom: 10px;"><br>';
        }

        echo '<p><strong>Status:</strong> <span class="label label-' . $label_class . '">' . $status_text . '</span></p>';
        echo '<p><strong>Waktu:</strong> ' . $data['waktu'] . '</p>';
        echo '<p><strong>Tanggal:</strong> ' . date('d-m-Y', strtotime($data['tanggal'])) . '</p>';
        echo '</div>';
    } else {
        // Jika status Hadir, kamera kosong, dan input_admin = "input_admin"
        // Tampilkan animasi sesuai kondisi
        if (empty($filename)) {
            if ($input_admin == 'input_admin' || $input_admin == 'input_mahasiswa') {
                tampilkanAnimasiTidakMenggunakanFoto();
            } else {
                tampilkanAnimasiKosong(); // fallback untuk kondisi lainnya
            }
        } else {
            tampilkanAnimasiKosong();
        }
    }
} else {
    tampilkanAnimasiKosong();
}

function tampilkanAnimasiKosong()
{
    echo '
    <div style="text-align: center; padding: 20px; display: flex; justify-content: center;">
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <dotlottie-player 
                src="https://lottie.host/ae6d9d71-02d6-4c7d-b236-c62156bf9027/PxFiXBjMRd.lottie" 
                background="transparent" 
                speed="1" 
                style="width: 200px; height: 200px;" 
                loop 
                autoplay>
            </dotlottie-player>
            <p style="font-size: 1.4em; color: #555; margin-top: 10px;"><i class="bi bi-x-circle-fill"></i> 
                Foto tidak ditemukan.
            </p>
        </div>
    </div>';
}

function tampilkanAnimasiTidakMenggunakanFoto()
{
    echo '
    <div style="text-align: center; padding: 20px; display: flex; justify-content: center;">
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
            <dotlottie-player 
                src="https://lottie.host/ae6d9d71-02d6-4c7d-b236-c62156bf9027/PxFiXBjMRd.lottie" 
                background="transparent" 
                speed="1" 
                style="width: 200px; height: 200px;" 
                loop 
                autoplay>
            </dotlottie-player>
            <p style="font-size: 1.4em; color: #555; margin-top: 10px;"><i class="bi bi-camera-video-off-fill"></i>  
                Presensi tidak menggunakan kamera.
            </p>
        </div>
    </div>';
}
?>
