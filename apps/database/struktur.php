<?php
// Menghubungkan ke database
include '../../config/database.php';

if (isset($_POST['table'])) {
    $table = $_POST['table'];

    // Query untuk mendapatkan struktur tabel
    $query = "SHOW COLUMNS FROM $table";
    $result = mysqli_query($kon, $query);

    if (mysqli_num_rows($result) > 0) {
        ?>
        <div class="table-responsive" id="draggable-table"
            style="max-height: 500px; overflow-y: auto; overflow-x: auto; position: relative; cursor: grab;">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Null</th>
                        <th>Key</th>
                        <th>Default</th>
                        <th>Extra</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Loop untuk menampilkan setiap kolom dalam tabel
                    while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <td><?php echo $row['Field']; ?></td>
                            <td><?php echo $row['Type']; ?></td>
                            <td><?php echo $row['Null']; ?></td>
                            <td><?php echo $row['Key']; ?></td>
                            <td><?php echo $row['Default']; ?></td>
                            <td><?php echo $row['Extra']; ?></td>
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
        echo "Tabel tidak ditemukan atau tidak memiliki kolom.";
    }
}
?>