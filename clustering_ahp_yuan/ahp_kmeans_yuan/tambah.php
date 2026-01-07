<?php
include('config.php');
include('fungsi.php');

// mendapatkan data edit
if (isset($_GET['jenis'])) {
	$jenis = $_GET['jenis'];
}

if (isset($_POST['tambah'])) {
	$jenis = $_POST['jenis'];
	$nama = $_POST['nama'];

	tambahData($jenis, $nama);

	header('Location: ' . $jenis . '.php?status=success');
	exit();
}

include('header.php');
?>

<section class="content" style="background-color: rgb(13, 10, 44)">
	<div class="card">
		<div class="card-header">
			<h2>Tambah
				<?php echo $jenis ?>
			</h2>
		</div>
		<div class="card-body">
			<form class="ui form" method="post" action="tambah.php">
				<div class="inline field">
					<label>Nama
						<?php echo $jenis ?>
					</label>
					<input type="text" name="nama" placeholder="<?php echo $jenis ?> baru" required>
					<input type="hidden" name="jenis" value="<?php echo $jenis ?>" required>
				</div>
				<br>
				<input class="ui green button" type="submit" name="tambah" value="SIMPAN">
			</form>
		</div>
	</div>
</section>

<?php include('footer.php'); ?>