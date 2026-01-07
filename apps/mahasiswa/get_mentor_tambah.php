<?php
include '../../config/database.php';

if(isset($_POST['unit_kerja'])) {
    $unitKerja = $_POST['unit_kerja'];
    $queryMentor = "SELECT nama FROM tbl_mentor WHERE unit_kerja = '$unitKerja'";
    $resultMentor = mysqli_query($kon, $queryMentor);

    $mentors = array();
    while ($dataMentor = mysqli_fetch_assoc($resultMentor)) {
        $mentors[] = $dataMentor;
    }

    echo json_encode($mentors);
}
?>
