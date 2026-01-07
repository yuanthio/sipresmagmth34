<?php
include '../../config/database.php';

if (isset($_POST['id_absensi'])) {
    $id_absensi = $_POST['id_absensi'];

    // Ambil data status dan kamera dari tabel absensi
    $query = mysqli_query($kon, "SELECT id_mahasiswa, kamera, status, tanggal 
                                 FROM tbl_absensi 
                                 WHERE id_absensi = '$id_absensi' LIMIT 1");
    $data = mysqli_fetch_assoc($query);

    if ($data) {
        $status = $data['status'];
        $kamera = $data['kamera'];
        $tanggal = $data['tanggal'];
        $id_mahasiswa = $data['id_mahasiswa'];

        $file_path = '../../apps/pengguna/kamera/' . $kamera;
        $file_url = 'apps/pengguna/kamera/' . $kamera;

        // ===============================
        // 1. Status Hadir (1) / Terlambat (3) -> Kamera
        // ===============================
        if (($status == '1' || $status == '3') && empty($kamera)) {
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
                    <p style="font-size: 1.4em; color: #555; margin-top: 10px;">
                        <i class="bi bi-camera-video-off-fill"></i> Presensi tidak menggunakan kamera.
                    </p>
                </div>
            </div>';
        } else if (($status == '1' || $status == '3') && !empty($kamera) && file_exists($file_path)) {
            echo '<img src="' . $file_url . '" alt="Foto Presensi" class="img-thumbnail"
                style="width: 400px; height:400px; object-fit: cover; border: 2px solid #444; border-radius: 10px; margin-bottom: 10px;"><br>';
        } else if (($status == '1' || $status == '3') && !empty($kamera) && !file_exists($file_path)) {
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
                        <p style="font-size: 1.4em; color: #555; margin-top: 10px;">
                            <i class="bi bi-x-circle-fill"></i> Foto tidak ditemukan.
                        </p>
                    </div>
                </div>';
        }

        // ===============================
        // 2. Status WFA (5) -> Bukti WFA (jpg/jpeg/png)
        // ===============================
        if ($status == '5') {
            $query_wfa = mysqli_query($kon, "SELECT bukti_wfa 
                                     FROM tbl_bukti_wfa 
                                     WHERE id_mahasiswa = '$id_mahasiswa' 
                                       AND tanggal = '$tanggal'");
            if (mysqli_num_rows($query_wfa) > 0) {
                while ($wfa = mysqli_fetch_assoc($query_wfa)) {
                    $file_wfa = $wfa['bukti_wfa'];
                    $ext = strtolower(pathinfo($file_wfa, PATHINFO_EXTENSION));

                    $file_path_wfa = '../../apps/data_absensi/file_wfa/' . $file_wfa;
                    $file_url_wfa = 'apps/data_absensi/file_wfa/' . $file_wfa;

                    // Jika file berupa gambar
                    if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                        if (file_exists($file_path_wfa)) {
                            echo '<div style="margin-top: 15px; text-align: center;">';
                            echo '<img src="' . $file_url_wfa . '" alt="Bukti WFA" class="img-thumbnail"
                        style="width: 400px; height:400px; object-fit: cover; border: 2px solid #444; border-radius: 10px;">';
                            echo '</div>';
                        }
                    }
                    // Jika file berupa dokumen (pdf, doc, docx)
                    else if (in_array($ext, ['pdf', 'doc', 'docx'])) {
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
                                    <p style="font-size: 1.4em; color: #555; margin-top: 10px;">
                                        <i class="bi bi-x-circle-fill"></i> Foto tidak ditemukan.
                                    </p>
                                </div>
                            </div>';
                    }
                }
            } else {
                echo '
                    <div style="text-align: center; padding: 20px;">
                        <p style="font-size: 1.2em; color: #777;">
                            <i class="bi bi-exclamation-circle"></i> Bukti WFA tidak ditemukan.
                        </p>
                    </div>';
            }
        }

        // ===============================
        // 3. Status Izin (2) -> Foto tidak ditemukan
        // ===============================
        if ($status == '2') {
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
                        <p style="font-size: 1.4em; color: #555; margin-top: 10px;">
                            <i class="bi bi-x-circle-fill"></i> Foto tidak ditemukan.
                        </p>
                    </div>
                </div>';
        }

    } else {
        echo '<div class="alert alert-danger">Data absensi tidak ditemukan.</div>';
    }
} else {
    echo '<div class="alert alert-danger">ID absensi tidak ditemukan.</div>';
}
?>
