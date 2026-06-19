<?php 
require_once(__DIR__ . '/includes/auth_check.php');
require_login();
include('includes/header.php');

$uid = $_SESSION['user_id'];

// Get counts for dashboard
$req_count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM disposal_requests WHERE citizen_id = $uid");
$req_count = mysqli_fetch_assoc($req_count_res)['total'];

$dump_count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM illegal_dumps WHERE citizen_id = $uid");
$dump_count = mysqli_fetch_assoc($dump_count_res)['total'];
?>

<style>
    .dashboard-container { padding: 40px 20px; max-width: 1300px; margin: 0 auto; }
    .welcome-section { margin-bottom: 40px; }
    .welcome-section h1 { font-size: 2.5rem; font-weight: 800; letter-spacing: -1px; }
    
    .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { 
        background: rgba(255, 255, 255, 0.7); 
        backdrop-filter: blur(20px); 
        padding: 30px; 
        border-radius: 24px; 
        border: 1px solid rgba(255,255,255,0.5);
        box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        text-align: center;
    }
    .stat-card h2 { font-size: 2.2rem; margin: 10px 0; color: var(--primary); font-weight: 800; }
    .stat-card p { color: #64748b; font-size: 0.9rem; font-weight: 600; text-transform: uppercase; }

    .main-grid { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 30px; }
    
    .action-card { 
        background: rgba(255, 255, 255, 0.85); 
        backdrop-filter: blur(20px); 
        border-radius: 32px; 
        border: 1px solid white;
        box-shadow: 0 25px 50px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .card-header { padding: 30px; border-bottom: 1px solid #f1f5f9; }
    .card-header h2 { font-size: 1.5rem; font-weight: 800; display: flex; align-items: center; gap: 12px; }
    
    .form-body { padding: 30px; }
    .input-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }

    /* Map Styling */
    .map-wrapper { 
        position: relative; 
        height: 450px; 
        border-radius: 24px; 
        overflow: hidden; 
        border: 1px solid #e2e8f0; 
        margin-bottom: 25px;
    }
    .floating-search {
        position: absolute; top: 20px; left: 20px; right: 20px; z-index: 1000;
        display: flex; gap: 12px;
    }
    .floating-status {
        position: absolute; bottom: 20px; left: 20px; right: 20px; z-index: 1000;
        background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);
        padding: 15px 25px; border-radius: 16px; border: 1px solid #e2e8f0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        font-weight: 600; font-size: 0.9rem;
    }

    .btn-submit-premium {
        width: 100%; padding: 20px; border-radius: 20px; border: none;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white; font-weight: 800; font-size: 1.1rem;
        cursor: pointer; transition: 0.3s;
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
    }
    .btn-submit-premium:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(16, 185, 129, 0.4); }

    /* Quick Links */
    .quick-link-card { 
        background: linear-gradient(135deg, rgba(239,68,68,0.1), rgba(249,115,22,0.1));
        padding: 30px; border-radius: 24px; border: 1px solid rgba(239,68,68,0.2);
        margin-top: 30px; display: flex; justify-content: space-between; align-items: center;
    }
</style>

<div class="dashboard-container">
    <div class="welcome-section" data-aos="fade-down">
        <h1>Urban<span style="color:var(--primary)">Flow</span> Explorer</h1>
        <p>Your centralized hub for a cleaner, smarter city experience.</p>
    </div>

    <!-- Stats Bar -->
    <div class="stats-row" data-aos="fade-up">
        <div class="stat-card">
            <p>Collection Requests</p>
            <h2 id="count-req"><?php echo $req_count; ?></h2>
        </div>
        <div class="stat-card">
            <p>Illegal Dumps Reported</p>
            <h2 id="count-dump"><?php echo $dump_count; ?></h2>
        </div>
        <div class="stat-card">
            <p>CO2 Offset Contribution</p>
            <h2>42.5kg</h2>
        </div>
    </div>

    <div class="main-grid">
        <!-- Main Form Column -->
        <div class="action-card" data-aos="fade-right">
            <div class="card-header">
                <h2>🗑️ Request Waste Collection</h2>
            </div>
            <div class="form-body">
                <form action="process_request.php" method="POST">
                    <div class="input-grid" style="grid-template-columns: repeat(3, 1fr);">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" required>
                                <option value="Organic">Organic Waste</option>
                                <option value="Recyclable">Recyclable Items</option>
                                <option value="Hazardous">Hazardous Materials</option>
                                <option value="Bulk">Bulk / Large Furniture</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Service Zone</label>
                            <select name="zone_id" required>
                                <?php
                                $zones = mysqli_query($conn, "SELECT * FROM zones");
                                while($z = mysqli_fetch_assoc($zones)) echo "<option value='{$z['zone_id']}'>{$z['zone_name']}</option>";
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Urgency</label>
                            <select name="urgency_level" required>
                                <option value="Low">Low (Next 2-3 Days)</option>
                                <option value="Medium" selected>Medium (Within 24h)</option>
                                <option value="High">High (Immediate)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 25px;">
                        <label>Pickup Instructions (Optional)</label>
                        <textarea name="comment" placeholder="Help the driver find you..."></textarea>
                    </div>

                    <div class="map-wrapper">
                        <div id="disposalMap" style="height: 100%; width: 100%;"></div>
                        <div class="floating-search">
                            <div id="geocoder-container" style="flex: 1;"></div>
                            <button type="button" class="btn-locate-me" onclick="findMe()" style="height: 46px; background: white; color: var(--primary); border: 1px solid #e2e8f0; padding: 0 20px; border-radius: 12px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                                🛰️ Locate Me
                            </button>
                        </div>
                        <div class="floating-status" id="locStatus">
                            📍 Tap the map to select your pickup location
                        </div>
                    </div>

                    <input type="hidden" name="lat" id="disposalLat">
                    <input type="hidden" name="lng" id="disposalLng">

                    <button type="submit" class="btn-submit-premium">🚀 Confirm Location & Log Request</button>
                </form>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="sidebar">
            <!-- Recent Activity -->
            <div class="action-card" data-aos="fade-left" style="margin-bottom: 30px;">
                <div class="card-header">
                    <h2>🕒 Recent Status</h2>
                </div>
                <div style="padding: 20px;">
                    <?php
                    $latest = mysqli_query($conn, "SELECT * FROM disposal_requests WHERE citizen_id = $uid ORDER BY created_at DESC LIMIT 1");
                    if($r = mysqli_fetch_assoc($latest)): ?>
                        <div style="background: #f8fafc; padding: 20px; border-radius: 20px; border: 1px solid #e2e8f0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <span style="font-weight: 800; color: var(--dark);">Request #<?php echo $r['request_id']; ?></span>
                                <span style="font-size: 0.75rem; color: #64748b;"><?php echo date('H:i', strtotime($r['created_at'])); ?></span>
                            </div>
                            <div style="font-size: 0.9rem; color: #64748b; margin-bottom: 15px;">Status: <strong style="color: var(--primary);"><?php echo $r['status']; ?></strong></div>
                            <a href="track_request.php?id=<?php echo $r['request_id']; ?>" class="btn-premium" style="display: block; text-align: center; text-decoration: none; padding: 12px; font-size: 0.85rem;">🛰️ Track Driver</a>
                        </div>
                    <?php else: ?>
                        <p style="text-align: center; color: #94a3b8; padding: 20px;">No active requests.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Illegal Dump CTA -->
            <div class="quick-link-card" data-aos="fade-up">
                <div>
                    <h3 style="margin: 0; font-weight: 800;">🚨 Report Illegal Dump</h3>
                    <p style="margin: 5px 0 0; font-size: 0.8rem; color: #64748b;">Spot unauthorized waste? Act now.</p>
                </div>
                <a href="report_dump.php" style="background: #ef4444; color: white; padding: 12px 20px; border-radius: 14px; text-decoration: none; font-weight: 700; font-size: 0.85rem;">Report →</a>
            </div>

            <!-- Impact Section -->
            <div class="action-card" data-aos="fade-up" style="margin-top: 30px;">
                <div class="card-header">
                    <h2>🌱 Green Impact</h2>
                </div>
                <div style="padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div style="font-size: 2rem;">🏆</div>
                        <div>
                            <div style="font-weight: 800;">Eco-Warrior Tier</div>
                            <div style="font-size: 0.8rem; color: #64748b;">Top 15% of your neighborhood</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // --- MAP LOGIC ---
    var disposalMap = L.map('disposalMap', { zoomControl: false }).setView([14.4644, 75.9218], 13);
    
    L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: '© Google Maps'
    }).addTo(disposalMap);

    var pickerMarker;

    var geocoder = L.Control.geocoder({
        defaultMarkGeocode: false,
        collapsed: false,
        placeholder: "🔍 Search address...",
    }).on('markgeocode', function(e) {
        var latlng = e.geocode.center;
        updatePicker(latlng.lat, latlng.lng, e.geocode.name);
        disposalMap.flyTo(latlng, 18);
    }).addTo(disposalMap);

    document.getElementById('geocoder-container').appendChild(geocoder.getContainer());

    function updatePicker(lat, lng, name = "") {
        if (pickerMarker) {
            pickerMarker.setLatLng([lat, lng]);
        } else {
            pickerMarker = L.marker([lat, lng], { draggable: true }).addTo(disposalMap);
            pickerMarker.on('dragend', function(e) {
                let newPos = e.target.getLatLng();
                updatePicker(newPos.lat, newPos.lng);
            });
        }
        document.getElementById('disposalLat').value = lat;
        document.getElementById('disposalLng').value = lng;
        document.getElementById('locStatus').innerHTML = name ? `✅ <strong>Location:</strong> ${name}` : `✅ <strong>Pinned:</strong> ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    }

    disposalMap.on('click', function(e) { updatePicker(e.latlng.lat, e.latlng.lng); });

    function findMe() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                disposalMap.flyTo([lat, lng], 18);
                updatePicker(lat, lng, "Detected GPS Position");
            });
        }
    }

    // --- ANIMATIONS ---
    AOS.init({ duration: 1000, once: true });
    
    gsap.from(".stat-card", {
        y: 30, opacity: 0, duration: 1, stagger: 0.2, ease: "power3.out"
    });

    gsap.to(".orb", {
        y: "random(-40, 40)", x: "random(-20, 20)",
        duration: 10, repeat: -1, yoyo: true, ease: "sine.inOut"
    });
</script>

<?php include('includes/footer.php'); ?>
