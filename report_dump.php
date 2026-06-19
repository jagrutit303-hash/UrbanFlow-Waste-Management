<?php 
require_once(__DIR__ . '/includes/auth_check.php');
require_login();
include('includes/header.php');
?>

<style>
    .report-container { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
    .report-header { text-align: center; margin-bottom: 40px; }
    .report-header h1 { font-size: 2.8rem; font-weight: 800; letter-spacing: -1.5px; }
    
    .main-report-card { 
        background: rgba(255, 255, 255, 0.85); 
        backdrop-filter: blur(25px); 
        border-radius: 32px; 
        border: 1px solid white;
        box-shadow: 0 30px 60px rgba(0,0,0,0.1);
        padding: 40px;
    }

    .form-section-title { 
        font-size: 0.9rem; font-weight: 800; color: var(--primary); 
        text-transform: uppercase; margin-bottom: 20px; display: block;
        letter-spacing: 1px;
    }

    .map-box { 
        height: 450px; border-radius: 24px; overflow: hidden; 
        position: relative; border: 1px solid #e2e8f0; margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    .map-floating-ui {
        position: absolute; top: 20px; left: 20px; right: 20px; z-index: 1000;
        display: flex; gap: 12px;
    }
    .map-status-floating {
        position: absolute; bottom: 20px; left: 20px; right: 20px; z-index: 1000;
        background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);
        padding: 15px 25px; border-radius: 16px; border: 1px solid #e2e8f0;
        font-weight: 600; font-size: 0.9rem; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .severity-pill-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 30px; }
    .sev-pill { position: relative; }
    .sev-pill input { position: absolute; opacity: 0; }
    .sev-pill label { 
        display: flex; flex-direction: column; align-items: center; padding: 20px 10px;
        border-radius: 20px; border: 2px solid #f1f5f9; cursor: pointer; transition: 0.3s;
    }
    .sev-pill input:checked + label { border-color: #ef4444; background: #fff5f5; transform: translateY(-5px); }

    .upload-area {
        border: 2px dashed #cbd5e1; border-radius: 24px; padding: 40px;
        text-align: center; cursor: pointer; transition: 0.3s; background: rgba(255,255,255,0.5);
    }
    .upload-area:hover { border-color: #ef4444; background: rgba(239, 68, 68, 0.02); }

    .btn-submit-report {
        width: 100%; padding: 22px; border-radius: 24px; border: none;
        background: linear-gradient(135deg, #ef4444, #b91c1c);
        color: white; font-weight: 800; font-size: 1.2rem; cursor: pointer;
        transition: 0.4s; box-shadow: 0 15px 35px rgba(239, 68, 68, 0.3);
        margin-top: 20px;
    }
    .btn-submit-report:hover { transform: scale(1.02); box-shadow: 0 20px 45px rgba(239, 68, 68, 0.4); }
</style>

<div class="report-container">
    <div class="report-header" data-aos="fade-down">
        <h1>🚨 Report <span style="color:#ef4444">Illegal Dumping</span></h1>
        <p>Your reports help us keep UrbanFlow clean. Let's act together.</p>
    </div>

    <div class="main-report-card" data-aos="zoom-in">
        <form action="submit_dump.php" method="POST" enctype="multipart/form-data">
            
            <span class="form-section-title">1. Mark the Location</span>
            <div class="map-box">
                <div id="dumpMap" style="height: 100%; width: 100%;"></div>
                <div class="map-floating-ui">
                    <div id="geocoder-container" style="flex: 1;"></div>
                    <button type="button" onclick="findMe()" style="height: 46px; background: white; border: 1px solid #e2e8f0; padding: 0 20px; border-radius: 12px; font-weight: 700; cursor: pointer; color: #ef4444;">
                        🛰️ Detect Location
                    </button>
                </div>
                <div class="map-status-floating" id="locStatus">
                    📍 Search or tap the map to pin the dump location
                </div>
            </div>
            <input type="hidden" name="citizen_lat" id="lat">
            <input type="hidden" name="citizen_lng" id="lng">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                <div class="form-group">
                    <label>Zone / Ward</label>
                    <select name="zone_id" required>
                        <option value="">Select Zone</option>
                        <?php
                        $zones = mysqli_query($conn, "SELECT * FROM zones");
                        while($z = mysqli_fetch_assoc($zones)) echo "<option value='{$z['zone_id']}'>{$z['zone_name']}</option>";
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Approx. Waste Volume</label>
                    <select name="volume" required>
                        <option value="Small Bag">Small Bag / Single Item</option>
                        <option value="Medium Pile">Medium Pile</option>
                        <option value="Large Dump">Large Dump / Truckload</option>
                    </select>
                </div>
            </div>

            <span class="form-section-title">2. Severity & Details</span>
            <div class="severity-pill-grid">
                <div class="sev-pill">
                    <input type="radio" name="severity" id="s1" value="Low">
                    <label for="s1"><span>🟢</span> Low</label>
                </div>
                <div class="sev-pill">
                    <input type="radio" name="severity" id="s2" value="Medium" checked>
                    <label for="s2"><span>🟡</span> Medium</label>
                </div>
                <div class="sev-pill">
                    <input type="radio" name="severity" id="s3" value="High">
                    <label for="s3"><span>🟠</span> High</label>
                </div>
                <div class="sev-pill">
                    <input type="radio" name="severity" id="s4" value="Critical">
                    <label for="s4"><span>🔴</span> Critical</label>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 30px;">
                <label>Description</label>
                <textarea name="description" placeholder="What type of waste is it? Any hazards like broken glass or chemicals?" required style="min-height: 120px;"></textarea>
            </div>

            <span class="form-section-title">3. Visual Evidence</span>
            <div class="upload-area" id="uploadZone" onclick="document.getElementById('dumpPhoto').click()">
                <div style="font-size: 3rem; margin-bottom: 10px;">📸</div>
                <div style="font-weight: 700; color: #1e293b;">Click to upload photo evidence</div>
                <div style="font-size: 0.8rem; color: #64748b;">JPEG, PNG, WebP — Max 10MB</div>
                <input type="file" name="dump_image" id="dumpPhoto" accept="image/*" style="display: none;">
                <img id="preview" style="display: none; max-width: 100%; height: 200px; object-fit: cover; border-radius: 16px; margin-top: 20px;">
            </div>

            <button type="submit" class="btn-submit-report">🚨 Submit Illegal Dump Report</button>
        </form>
    </div>
</div>

<script>
    // --- GOOGLE MAPS ENGINE ---
    var dumpMap = L.map('dumpMap', { zoomControl: false }).setView([14.4644, 75.9218], 13);
    
    L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: '© Google Maps'
    }).addTo(dumpMap);

    var marker;

    var geocoder = L.Control.geocoder({
        defaultMarkGeocode: false,
        collapsed: false,
        placeholder: "🔍 Search dump location...",
    }).on('markgeocode', function(e) {
        var latlng = e.geocode.center;
        updateLocation(latlng.lat, latlng.lng, e.geocode.name);
        dumpMap.flyTo(latlng, 18);
    }).addTo(dumpMap);

    document.getElementById('geocoder-container').appendChild(geocoder.getContainer());

    function updateLocation(lat, lng, name = "") {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(dumpMap);
            marker.on('dragend', function(e) {
                let p = e.target.getLatLng();
                updateLocation(p.lat, p.lng);
            });
        }
        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;
        document.getElementById('locStatus').innerHTML = name ? `✅ <strong>Found:</strong> ${name}` : `✅ <strong>Pinned:</strong> ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    }

    dumpMap.on('click', function(e) { updateLocation(e.latlng.lat, e.latlng.lng); });

    function findMe() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                dumpMap.flyTo([lat, lng], 18);
                updateLocation(lat, lng, "Current GPS Spot");
            });
        }
    }

    // --- PHOTO PREVIEW ---
    document.getElementById('dumpPhoto').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('preview');
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });

    AOS.init({ duration: 1000, once: true });
</script>

<?php include('includes/footer.php'); ?>

