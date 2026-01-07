<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>Sistem Pendukung Keputusan metode AHP</title>
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<link rel="stylesheet" type="text/css" href="semantic/dist/semantic.min.css">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
		integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
	<style>
		/* Fonts */
		@import url('https://fonts.googleapis.com/css2?family=Poppins&display=swap');

		/* Layout */
		html {
			display: -webkit-box;
			display: -ms-flexbox;
			display: flex;
			-webkit-box-orient: vertical;
			-webkit-box-direction: normal;
			-ms-flex-direction: column;
			flex-direction: column;
		}

		/* IE fix */
		body {
			display: -webkit-box;
			display: -ms-flexbox;
			display: flex;
			-webkit-box-orient: vertical;
			-webkit-box-direction: normal;
			-ms-flex-direction: column;
			flex-direction: column;
			min-height: 100vh;
		}

		.wrapper {
			display: block;
			/* IE fix */
			-webkit-box-flex: 1;
			-ms-flex: 1 1 auto;
			flex: 1 1 auto;
			display: -webkit-box;
			display: -ms-flexbox;
			display: flex;
			-webkit-box-orient: horizontal;
			-webkit-box-direction: normal;
			-ms-flex-direction: row;
			flex-direction: row;
			margin-top: 63px;
		}

		nav {
			width: 17em;
		}

		.content {
			display: block;
			/* IE fix */
			-webkit-box-flex: 1;
			-ms-flex: 1;
			flex: 1;
		}


		/* Responsive */

		@media (max-width: 640px) {
			body {
				min-height: 0;
			}

			.wrapper {
				-webkit-box-orient: vertical;
				-webkit-box-direction: normal;
				-ms-flex-direction: column;
				flex-direction: column;
			}

			.content {
				-ms-flex-preferred-size: auto;
				flex-basis: auto;
			}

			nav {
				width: auto;
				-webkit-box-ordinal-group: 2;
				-ms-flex-order: 1;
				order: 1;
			}
		}

		/* Decoration */

		body {
			margin: 0;
			background: #fff;
			font-family: 'Poppins', sans-serif;
		}

		header,
		nav,
		section,
		footer {
			padding: 10px 30px;
			margin: 0;
			color: #fff;
		}

		header {
			background-color: rgb(13, 10, 44);
			text-align: center;
			padding: 15px 0;
			border-top: 2px solid white;
			border-bottom: 2px solid white;
		}

		#navigation {
			background-color: rgb(24, 18, 92);
		}

		nav {
			background: #666666;
			padding: 0;
		}

		nav a {
			display: block;
			font-weight: 900;
			padding: .5em 1em;
			color: #fff;
			text-decoration: none;
		}

		ul {
			list-style-type: none;
			padding: 10px 0 10px 15px;
		}

		li:hover {
			background: white;
			transition: all .3s ease-in;
		}


		li:hover a {
			color: black;
		}

		section {
			background: white;
			color: black;
			padding: 30px;
		}

		footer {
			background-color: rgb(13, 10, 44);
			text-align: center;
			border-top: 2px solid white;
			border-bottom: 2px solid white;
		}
	</style>
</head>

<body>
	<header class="fixed-top">
		<h2>Sistem Pendukung Keputusan dengan metode AHP</h2>
	</header>

	<div class="wrapper">
		<nav id="navigation" role="navigation">
			<ul>
				<li><a class="item" href="index.php">Home</a></li>
				<li>
					<a class="item" href="kriteria.php">Kriteria
						<div class="ui blue tiny label" style="float: right;">
							<?php echo getJumlahKriteria(); ?>
						</div>
					</a>
				</li>
				<li>
					<a class="item" href="alternatif.php">Alternatif
						<div class="ui blue tiny label" style="float: right;">
							<?php echo getJumlahAlternatif(); ?>
						</div>
					</a>
				</li>
				<li><a class="item" href="bobot_kriteria.php">Perbandingan Kriteria</a></li>
				<li><a class="item" href="bobot.php?c=1">Perbandingan Alternatif</a></li>
				<ul>
					<?php

					if (getJumlahKriteria() > 0) {
						for ($i = 0; $i <= (getJumlahKriteria() - 1); $i++) {
							echo "<li><a class='item' href='bobot.php?c=" . ($i + 1) . "'>" . getKriteriaNama($i) . "</a></li>";
						}
					}

					?>
				</ul>
				<li><a class="item" href="hasil.php">Hasil</a></li>
				<li><a class="item" href="../index.php">Kembali</a></li>
			</ul>
		</nav>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
			integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
			crossorigin="anonymous"></script>