<?php
include('config.php');
include('fungsi.php');

// header
include('header.php');

$queryKriteria = "SELECT COUNT(*) as total_kriteria FROM kriteria";
$resultKriteria = mysqli_query($koneksi, $queryKriteria);
$dataKriteria = mysqli_fetch_assoc($resultKriteria);
$totalKriteria = $dataKriteria['total_kriteria'];

$queryAlternatif = "SELECT COUNT(*) as total_alternatif FROM alternatif";
$resultAlternatif = mysqli_query($koneksi, $queryAlternatif);
$dataAlternatif = mysqli_fetch_assoc($resultAlternatif);
$totalAlternatif = $dataAlternatif['total_alternatif'];
?>

<section class="content" style="background-color: rgb(13, 10, 44)">
	<div class="row">
		<div class="col-lg-3">
			<div class="card">
				<div class="card-header">
					<div class="jumlah-data-kriteria text-center">
						<h1>
							<?php echo $totalKriteria; ?>
						</h1>
					</div>
				</div>
				<div class="card-body text-center">
					<img src="img/clipboard.png" class="mb-4" width="100" alt="Kriteria">
					<h3>Kriteria</h3>
				</div>
			</div>
		</div>
		<div class="col-lg-3">
			<div class="card">
				<div class="card-header">
					<div class="jumlah-data-alternatif text-center">
						<h1>
							<?php echo $totalAlternatif; ?>
						</h1>
					</div>
				</div>
				<div class="card-body text-center">
					<img src="img/arrows.png" class="mb-4" width="100" alt="Kriteria">
					<h3>Alternatif</h3>
				</div>
			</div>
		</div>
	</div>
</section>

<?php include('footer.php'); ?>