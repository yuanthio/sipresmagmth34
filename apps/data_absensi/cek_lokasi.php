<?php
include '../../config/database.php';

if (isset($_POST['id_absensi'])) {
    $id_absensi = $_POST['id_absensi'];

    // Query untuk mengambil data lokasi dan nama mahasiswa berdasarkan id_absensi
    $query = "
        SELECT 
            tbl_absensi.latitude, 
            tbl_absensi.longitude, 
            tbl_mahasiswa.nama
        FROM tbl_absensi
        JOIN tbl_mahasiswa ON tbl_absensi.id_mahasiswa = tbl_mahasiswa.id_mahasiswa
        WHERE tbl_absensi.id_absensi = '$id_absensi'
        LIMIT 1
    ";
    $result = mysqli_query($kon, $query);

    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $latitude = $data['latitude'];
        $longitude = $data['longitude'];
        $nama_mahasiswa = $data['nama'];

        // Jika latitude atau longitude kosong, tampilkan pesan lokasi tidak ditemukan
        if (empty($latitude) || empty($longitude)) {
            echo '
                <div style="text-align: center; padding: 20px; display: flex; justify-content: center;">
                    <div>
                        <dotlottie-player
                            src="https://lottie.host/055d8991-5e4c-40d9-b0ec-37188f267bd1/UOIC3latF6.lottie"
                            background="transparent"
                            speed="1"
                            style="width: 300px; height: 300px;"
                            loop
                            autoplay>
                        </dotlottie-player>
                        <p style="font-size: 1.4em; color: #555;">Lokasi tidak ditemukan.</p>
                    </div>
                </div>
            ';
        } else {
            // Hanya tampilkan Nama Mahasiswa di atas peta
            echo "<h4>$nama_mahasiswa</h4>";

            // Peta
            echo '<div id="map" style="height: 400px; border: 5px solid rgb(24, 18, 92);"></div>';

            // Query untuk mengambil data dari tbl_lokasi_presensi
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

            // Script untuk inisialisasi peta
            echo '
                <script>
                    var map = L.map("map", {
                        center: [' . $latitude . ', ' . $longitude . '],
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

                    var locations = [{
                        "latitude": ' . $latitude . ',
                        "longitude": ' . $longitude . ',
                        "nama": "' . $nama_mahasiswa . '"
                    }];

                    locations.forEach(function (loc) {
                        if (loc.latitude && loc.longitude) {
                            L.marker([loc.latitude, loc.longitude]).addTo(map)
                                .bindPopup(`<b>Latitude:</b> ${loc.latitude}<br><b>Longitude:</b> ${loc.longitude}`);
                        }
                    });

                    var lokasiPresensi = ' . json_encode($lokasi_presensi) . ';

                    lokasiPresensi.forEach(function(loc) {
                        if (loc.latitude && loc.longitude && loc.radius) {
                            L.circle([loc.latitude, loc.longitude], {
                                color: "blue",
                                fillColor: "blue",
                                fillOpacity: 0.3,
                                radius: loc.radius
                            }).addTo(map)
                            .bindPopup(`<b>Radius:</b> ${loc.radius} meters<br><b>Status Aktif:</b> ${loc.status_aktif ? "Aktif" : "Tidak Aktif"}`);
                        }
                    });

                    if (locations.length > 0) {
                        var firstLoc = locations[0];
                        map.setView([firstLoc.latitude, firstLoc.longitude], 15);
                    }

                    // RESPONSIF: Tampilkan/hilangkan kontrol Zoom Info
                    var zoomInfoControl = null;

                    function updateZoomInfoControl() {
                        if (window.innerWidth > 768) {
                            if (!zoomInfoControl) {
                                zoomInfoControl = L.control({ position: "topright" });
                                zoomInfoControl.onAdd = function () {
                                    var div = L.DomUtil.create("div", "zoom-info");
                                    div.innerHTML = "<div style=\'background: white; padding: 5px; border-radius: 5px; font-size: 12px; box-shadow: 0 0 5px rgba(0,0,0,0.3);\'>Klik <b>CTRL</b> + Scroll untuk zoom</div>";
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
                    window.addEventListener("resize", updateZoomInfoControl);

                    const mapContainer = document.getElementById("map");

                    // Cegah zoom browser saat di atas peta dan CTRL + scroll
                    mapContainer.addEventListener("wheel", function (e) {
                        if (e.ctrlKey) {
                            e.preventDefault(); // Cegah browser zoom
                            map.scrollWheelZoom.enable();
                        } else {
                            map.scrollWheelZoom.disable();
                        }
                    }, { passive: false });

                    // Nonaktifkan zoom setelah scroll selesai (opsional untuk UX)
                    map.on("zoomend", function () {
                        map.scrollWheelZoom.disable();
                    });
                </script>';
        }
    } else {
        echo '
            <div style="text-align: center; padding: 20px; display: flex; justify-content: center;">
                <div>
                    <dotlottie-player
                        src="https://lottie.host/055d8991-5e4c-40d9-b0ec-37188f267bd1/UOIC3latF6.lottie"
                        background="transparent"
                        speed="1"
                        style="width: 300px; height: 300px;"
                        loop
                        autoplay>
                    </dotlottie-player>
                    <p style="font-size: 1.4em; color: #555;"><i class="bi bi-x-circle-fill"></i> Lokasi tidak ditemukan.</p>
                </div>
            </div>
        ';
    }
}
?>