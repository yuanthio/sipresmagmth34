<?php
	include('config.php');
	include('fungsi.php');

	include('header.php');
?>
<section class="content" style="background-color: rgb(13, 10, 44)">
	<h2 class="ui header text-white">Perbandingan Kriteria</h2>
	<?php showTabelPerbandingan('kriteria','kriteria'); ?>
</section>

<?php include('footer.php'); ?>