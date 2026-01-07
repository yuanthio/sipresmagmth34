<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<style>
    #map {
        touch-action: none;
        /* untuk mencegah zoom/sentuhan multitouch pada mobile */
    }
</style>

<?php
include '../../config/database.php';

$id_absensi = $_POST['id_absensi'];

// Ambil data lokasi dari absensi
$query = "SELECT a.latitude, a.longitude, a.tanggal, a.waktu, a.status, a.konfirmasi_status, m.nama
          FROM tbl_absensi a
          JOIN tbl_mahasiswa m ON a.id_mahasiswa = m.id_mahasiswa
          WHERE a.id_absensi = '$id_absensi'";

$result = mysqli_query($kon, $query);
$data = mysqli_fetch_assoc($result);

$latitude = $data['latitude'];
$longitude = $data['longitude'];
$nama_mahasiswa = $data['nama'];

// Ambil data radius lokasi presensi dari tabel
$query_lokasi_presensi = "
    SELECT latitude, longitude, radius, status_aktif 
    FROM tbl_lokasi_presensi 
    WHERE status_aktif = 1
";
$result_lokasi_presensi = mysqli_query($kon, $query_lokasi_presensi);

$lokasi_presensi = [];
while ($row = mysqli_fetch_assoc($result_lokasi_presensi)) {
    $lokasi_presensi[] = $row;
}
?>

<div id="map" style="height: 400px; width: 100%; display: <?= ($latitude && $longitude) ? 'block' : 'none'; ?>"></div>

<div id="no-location-message"
    style="text-align: center; display: <?= ($latitude && $longitude) ? 'none' : 'flex'; ?>; justify-content: center; align-items: center; height: 400px;">
    <div>
        <dotlottie-player src="https://lottie.host/055d8991-5e4c-40d9-b0ec-37188f267bd1/UOIC3latF6.lottie"
            background="transparent" speed="1" style="width: 300px; height: 300px;" loop autoplay>
        </dotlottie-player>
        <p style="font-size: 1.4em; color: #555;"><i class="bi bi-x-circle-fill"></i> Lokasi tidak ditemukan.</p>
    </div>
</div>

<script>
    if (<?= json_encode($latitude !== null && $longitude !== null) ?>) {
        var map = L.map("map", {
            center: [<?= $latitude ?>, <?= $longitude ?>],
            zoom: 15,
            scrollWheelZoom: false
        });

        var osm = L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
            attribution: "© OpenStreetMap contributors"
        });

        var googleSat = L.tileLayer("https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}", {
            attribution: "© Google Satellite"
        });

        var googleRoadmap = L.tileLayer("https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}", {
            attribution: "© Google Roadmap"
        });

        var googleHybrid = L.tileLayer("https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}", {
            attribution: "© Google Hybrid"
        });

        var googleTerrain = L.tileLayer("https://mt1.google.com/vt/lyrs=p&x={x}&y={y}&z={z}", {
            attribution: "© Google Terrain"
        });

        osm.addTo(map);

        var baseMaps = {
            "OpenStreetMap": osm,
            "Google Earth (Satellite)": googleSat,
            "Google Roadmap": googleRoadmap,
            "Google Hybrid": googleHybrid,
            "Google Terrain": googleTerrain
        };

        L.control.layers(baseMaps).addTo(map);

        // Marker mahasiswa
        L.marker([<?= $latitude ?>, <?= $longitude ?>]).addTo(map)
            .bindPopup("<b>Latitude:</b> <?= $latitude ?><br><b>Longitude:</b> <?= $longitude ?>");

        // Circle lokasi presensi
        var lokasiPresensi = <?= json_encode($lokasi_presensi) ?>;
        lokasiPresensi.forEach(function (loc) {
            if (loc.latitude && loc.longitude && loc.radius) {
                L.circle([loc.latitude, loc.longitude], {
                    color: "blue",
                    fillColor: "blue",
                    fillOpacity: 0.3,
                    radius: loc.radius
                }).addTo(map)
                    .bindPopup(`<b>Radius:</b> ${loc.radius} meter<br><b>Status Aktif:</b> ${loc.status_aktif == 1 ? "Aktif" : "Tidak Aktif"}`);
            }
        });

        // Notifikasi zoom info
        var zoomInfoControl = null;
        function updateZoomInfoControl() {
            if (window.innerWidth > 768) {
                if (!zoomInfoControl) {
                    zoomInfoControl = L.control({ position: "topright" });
                    zoomInfoControl.onAdd = function () {
                        var div = L.DomUtil.create("div", "zoom-info");
                        div.innerHTML = "<div style='background: white; padding: 5px; border-radius: 5px; font-size: 12px; box-shadow: 0 0 5px rgba(0,0,0,0.3);'>Klik <b>CTRL</b> + Scroll untuk zoom</div>";
                        return div;
                    };
                    zoomInfoControl.addTo(map);
                }
            } else {
                if (zoomInfoControl) {
                    map.removeControl(zoomInfoControl);
                    zoomInfoControl = null;
                }
            }
        }

        updateZoomInfoControl();
        window.addEventListener('resize', updateZoomInfoControl);

        const mapContainer = document.getElementById("map");

        // Cegah zoom browser saat di atas peta dan CTRL + scroll
        mapContainer.addEventListener("wheel", function (e) {
            if (e.ctrlKey) {
                e.preventDefault(); // Cegah browser zoom
                map.scrollWheelZoom.enable();
            } else {
                map.scrollWheelZoom.disable();
            }
        }, { passive: false }); // passive: false agar e.preventDefault() bisa bekerja

        // Nonaktifkan zoom setelah scroll selesai (opsional untuk UX)
        map.on("zoomend", function () {
            map.scrollWheelZoom.disable();
        });
    }
</script>