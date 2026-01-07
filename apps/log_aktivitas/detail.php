<?php
include '../../config/database.php'; // Pastikan path ke file konfigurasi database benar

if (isset($_POST['kode'])) {
    $kode_pengguna = $_POST['kode'];

    // Ambil semua data aktivitas berdasarkan kode pengguna dan urutkan dari yang terbaru
    $query = "SELECT tanggal, aktivitas, status, nama, level FROM tbl_log_aktivitas WHERE kode_pengguna = ? ORDER BY tanggal DESC";
    $stmt = $kon->prepare($query);
    $stmt->bind_param("s", $kode_pengguna); // Ganti "s" dengan "i" jika kode adalah integer
    $stmt->execute();
    $result = $stmt->get_result();

    // Mulai membangun HTML
    if ($result->num_rows > 0) {
        ?>
        <div class="table-responsive" id="draggable-table"
            style="max-height: 500px; overflow-y: auto; overflow-x: auto; position: relative; cursor: grab;">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Level</th>
                        <th>Aktivitas</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama']); ?></td> <!-- Tampilkan Nama -->
                            <td><?php echo htmlspecialchars($row['level']); ?></td> <!-- Tampilkan Level -->
                            <td>
                                <?php
                                $aktivitas = htmlspecialchars($row['aktivitas']);
                                $levelAktif = $row['level'];

                                if ($levelAktif === 'Admin' || $levelAktif === 'Mentor') {
                                    // Cari posisi tanda kurung tutup pertama
                                    $pos = strpos($aktivitas, ')');
                                    if ($pos !== false && $pos < strlen($aktivitas) - 1) {
                                        // Pecah jadi dua bagian: sebelum dan sesudah ')'
                                        $sebelum = substr($aktivitas, 0, $pos + 1);
                                        $setelah = substr($aktivitas, $pos + 1);
                                        echo $sebelum . '<span style="color:red;">' . htmlspecialchars($setelah) . '</span>';
                                    } else {
                                        // Jika tidak ditemukan, tampilkan biasa
                                        echo $aktivitas;
                                    }
                                } else {
                                    // Warna merah untuk teks dalam kurung
                                    $aktivitas = preg_replace('/\((.*?)\)/', '(<span style="color:red;">$1</span>)', $aktivitas);
                                    echo $aktivitas;
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($row['status'] == 'berhasil'): ?>
                                    <span class="label label-success">Berhasil</span>
                                <?php else: ?>
                                    <span class="label label-danger">Gagal</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <script>
            // JavaScript untuk drag-and-drop
            const tableContainer = document.getElementById('draggable-table');
            let isDragging = false;
            let startX, startY, scrollLeft, scrollTop;

            tableContainer.addEventListener('mousedown', (e) => {
                isDragging = true;
                tableContainer.style.cursor = 'grabbing'; // Ubah kursor saat dragging
                startX = e.pageX - tableContainer.offsetLeft;
                startY = e.pageY - tableContainer.offsetTop;
                scrollLeft = tableContainer.scrollLeft;
                scrollTop = tableContainer.scrollTop;
            });

            tableContainer.addEventListener('mouseleave', () => {
                isDragging = false;
                tableContainer.style.cursor = 'grab'; // Kembalikan kursor saat keluar
            });

            tableContainer.addEventListener('mouseup', () => {
                isDragging = false;
                tableContainer.style.cursor = 'grab'; // Kembalikan kursor saat mouse up
            });

            tableContainer.addEventListener('mousemove', (e) => {
                if (!isDragging) return; // stop the fn from running
                e.preventDefault();
                const x = e.pageX - tableContainer.offsetLeft;
                const y = e.pageY - tableContainer.offsetTop;
                const walkX = (x - startX) * 1; // the multiplier for speed
                const walkY = (y - startY) * 1;
                tableContainer.scrollLeft = scrollLeft - walkX;
                tableContainer.scrollTop = scrollTop - walkY;
            });
        </script>
        <?php
    } else {
        echo 'Tidak ada aktivitas ditemukan.';
    }
} else {
    echo 'Kode pengguna tidak valid.';
}
?>