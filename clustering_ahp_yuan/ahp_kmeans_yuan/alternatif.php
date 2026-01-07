<?php
include('config.php');
include('fungsi.php');

// menjalankan perintah edit
if (isset($_POST['edit'])) {
	$id = $_POST['id'];

	header('Location: edit.php?jenis=alternatif&id=' . $id);
	exit();
}

// menjalankan perintah delete
if (isset($_POST['delete'])) {
	$id = $_POST['id'];
	if (deleteAlternatif($id)) {
		$deleteSuccessMessage = 'Data berhasil dihapus';
	} else {
		$deleteErrorMessage = 'Data berhasil dihapus';
	}
}

// menjalankan perintah tambah
if (isset($_POST['tambah'])) {
	$nama = $_POST['nama'];
	tambahData('alternatif', $nama);
}

include('header.php');

?>


<section class="content" style="background-color: rgb(13, 10, 44)">

	<h2 class="ui header text-white">Alternatif</h2>
	<?php
	// Tampilkan pesan gagal delete jika ada
	if (isset($deleteErrorMessage)) {
		echo '<div class="alert alert-success" role="alert">';
		echo htmlspecialchars($deleteErrorMessage);
		echo '</div>';
	}
	?>

	<?php
	if (isset($_GET['status']) && $_GET['status'] == 'success') {
		echo '<div class="alert alert-success" role="alert">';
		echo 'Data berhasil ditambah';
		echo '</div>';
	}

	if (isset($_GET['success'])) {
		$successMessage = $_GET['success'];
		echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($successMessage) . '</div>';
	}
	?>
	<table class="ui celled table">
		<thead>
			<tr>
				<th class="collapsing">No</th>
				<th colspan="2">Nama Alternatif</th>
			</tr>
		</thead>
		<tbody>

			<?php
			// Menampilkan list alternatif
			$query = "SELECT id,nama FROM alternatif ORDER BY id";
			$result = mysqli_query($koneksi, $query);

			$i = 0;
			while ($row = mysqli_fetch_array($result)) {
				$i++;
				?>
				<tr>
					<td>
						<?php echo $i ?>
					</td>
					<td>
						<?php echo $row['nama'] ?>
					</td>
					<td class="right aligned collapsing">
						<form method="post" action="alternatif.php">
							<input type="hidden" name="id" value="<?php echo $row['id'] ?>">
							<button type="submit" name="edit" class="ui mini teal left labeled icon button"><i
									class="right edit icon"></i>EDIT</button>
							<button type="submit" name="delete" class="ui mini red left labeled icon button"
								onclick="return confirm('Apakah Anda yakin ingin menghapus kriteria ini?');">
								<i class="right remove icon"></i>DELETE
							</button>
						</form>
					</td>
				</tr>

			<?php } ?>

		</tbody>
		<tfoot class="full-width">
			<tr>
				<th colspan="3">
					<a href="tambah.php?jenis=alternatif">
						<div class="ui right floated small primary labeled icon button">
							<i class="plus icon"></i>Tambah
						</div>
					</a>
				</th>
			</tr>
		</tfoot>
	</table>

	<br>


	<form action="bobot_kriteria.php">
		<button class="ui right labeled icon button" style="float: right;">
			<i class="right arrow icon"></i>
			Lanjut
		</button>
	</form>
</section>

<?php include('footer.php'); ?>