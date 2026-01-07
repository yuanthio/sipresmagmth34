<style>
    .table thead {
        position: sticky;
        top: 0;
        background-color: #f1f1f1;
        z-index: 2;
    }
</style>

<?php
// Menghubungkan ke database
include '../../config/database.php';

if (isset($_POST['table'])) {
    $table = $_POST['table'];

    // Query untuk mengambil semua data dari tabel yang dipilih
    $query = "SELECT * FROM $table";
    $result = mysqli_query($kon, $query);

    if (mysqli_num_rows($result) > 0) {
        ?>
        <div class="table-responsive" id="draggable-table"
            style="max-height: 500px; overflow-y: auto; overflow-x: auto; position: relative; cursor: grab;">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <?php
                        // Menampilkan nama kolom
                        while ($field = mysqli_fetch_field($result)) {
                            echo "<th>{$field->name}</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Menampilkan data setiap baris
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        foreach ($row as $data) {
                            echo "<td>{$data}</td>";
                        }
                        echo "</tr>";
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
        echo "Tabel kosong atau tidak ada data.";
    }
}
?>