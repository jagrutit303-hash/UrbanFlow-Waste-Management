<?php 
include('includes/header.php'); 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$uid = $_SESSION['user_id'];

// Fetch request details
$query = "SELECT r.*, z.zone_name, z.ward_no, f.vehicle_no, f.collector_id, u.full_name as driver_name
          FROM disposal_requests r
          JOIN zones z ON r.zone_id = z.zone_id
          LEFT JOIN fleet_assignments f ON r.request_id = f.request_id
          LEFT JOIN users u ON f.collector_id = u.user_id
          WHERE r.request_id = $request_id AND r.citizen_id = $uid";

if ($request_id == 0) {
    // Fallback to latest request if no ID provided
    $query = "SELECT r.*, z.zone_name, z.ward_no, f.vehicle_no, f.collector_id, u.full_name as driver_name
              FROM disposal_requests r
              JOIN zones z ON r.zone_id = z.zone_id
              LEFT JOIN fleet_assignments f ON r.request_id = f.request_id
              LEFT JOIN users u ON f.collector_id = u.user_id
              WHERE r.citizen_id = $uid
              ORDER BY r.created_at DESC LIMIT 1";
}

$res = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($res);

if (!$data) {
    echo "<div class='container' style='padding: 100px; text-align: center;'><h2>No active request found to track.</h2><a href='dashboard.php' class='btn-premium'>Back to Dashboard</a></div>";
    include('includes/footer.php');
    exit();
}

// Map status to visual steps
$status_map = ['Logged' => 1, 'Dispatched' => 3, 'Resolved' => 4];
$current_step = $status_map[$data['status']] ?? 1;
?>

<style>
    /* Tracker Page Styles */
    #tracker-container {
        position: relative;
        height: 350px;
        background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 50%, #ecfdf5 100%);
        border-radius: 30px;
        overflow: hidden;
        border: 2px solid rgba(16, 185, 129, 0.15);
        margin: 20px 0;
        box-shadow: 0 10px 40px rgba(0,0,0,0.06);
    }

    /* Animated road dashes */
    .road-path {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 60px;
        background: repeating-linear-gradient(
            90deg,
            #cbd5e1 0px, #cbd5e1 30px,
            transparent 30px, transparent 50px
        );
        opacity: 0.25;
    }

    /* Waypoint dots */
    .waypoint {
        position: absolute;
        width: 14px;
        height: 14px;
        background: var(--primary, #10b981);
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
    }

    .waypoint::after {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: 50%;
        border: 2px solid rgba(16, 185, 129, 0.2);
        animation: wpPulse 2s ease-out infinite;
    }

    @keyframes wpPulse {
        0% { transform: scale(1); opacity: 0.6; }
        100% { transform: scale(2); opacity: 0; }
    }

    /* Status cards */
    .tracker-card {
        background: rgba(255,255,255,0.8);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255,255,255,0.5);
        border-radius: 20px;
        padding: 25px 30px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.06);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .tracker-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    }

    .tracker-step {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 14px 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .tracker-step:last-child { border-bottom: none; }

    .step-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .step-icon.done {
        background: linear-gradient(135deg, #10b981, #059669);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
    }

    .step-icon.pending { background: #f1f5f9; }

    .eta-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        padding: 10px 24px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1rem;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35);
        animation: etaGlow 2s ease-in-out infinite;
    }

    @keyframes etaGlow {
        0%, 100% { box-shadow: 0 6px 20px rgba(16, 185, 129, 0.35); }
        50% { box-shadow: 0 6px 35px rgba(16, 185, 129, 0.6); }
    }
</style>

<!-- Page Title -->
<div class="container" style="padding: 80px 10% 30px; text-align: center;">
    <h1 data-aos="fade-down" style="font-size: 3rem; font-weight: 800;">
        Track Request <span style="color:var(--primary)">#<?php echo $data['request_id']; ?></span> 🚛
    </h1>
    <p data-aos="fade-up" style="max-width: 600px; margin: 15px auto 0; color: #64748b; font-size: 1.1rem;">
        Assigned for <strong><?php echo $data['category']; ?></strong> waste collection in <strong><?php echo $data['zone_name']; ?></strong>.
    </p>
</div><!-- Real-time Tracking Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div style="padding: 0 10% 40px;">
    <div id="tracking-map" style="height: 450px; border-radius: 30px; border: 2px solid rgba(16, 185, 129, 0.2); box-shadow: 0 15px 40px rgba(0,0,0,0.08); overflow: hidden; position: relative;" data-aos="zoom-in">
        <!-- Floating Info Overlay -->
        <div style="position: absolute; top: 20px; left: 20px; z-index: 1000; background: rgba(255,255,255,0.9); backdrop-filter: blur(10px); padding: 15px 20px; border-radius: 20px; border: 1px solid white; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="font-size: 1.5rem;">🚚</div>
                <div>
                    <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Current Task</div>
                    <div style="font-weight: 800; color: #1e293b;"><?php echo $data['category']; ?> Pickup</div>
                </div>
            </div>
            <div id="live-eta-box" style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 0.85rem; font-weight: 600; color: var(--primary, #10b981);">
                🛰️ Syncing with driver GPS...
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize Tracking Map
    const map = L.map('tracking-map', { zoomControl: false }).setView([14.4644, 75.9218], 15);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; CARTO'
    }).addTo(map);

    const truckIcon = L.divIcon({
        className: 'custom-div-icon',
        html: "<div style='background:#4ade80; padding:10px; border-radius:50%; border:3px solid white; box-shadow:0 0 20px rgba(74,222,128,0.5); font-size:1.5rem; text-align:center;'>🚚</div>",
        iconSize: [45, 45],
        iconAnchor: [22, 22]
    });

    const citizenIcon = L.divIcon({
        className: 'custom-div-icon',
        html: "<div style='background:#3b82f6; padding:10px; border-radius:50%; border:3px solid white; box-shadow:0 0 20px rgba(59,130,246,0.4); font-size:1.5rem; text-align:center;'>🏠</div>",
        iconSize: [45, 45],
        iconAnchor: [22, 22]
    });

    let truckMarker, citizenMarker, routeLine;
    const destLat = <?php echo ($data['lat'] ? $data['lat'] : $z['lat']); ?>;
    const destLng = <?php echo ($data['lng'] ? $data['lng'] : $z['lng']); ?>;

    // Place Citizen marker
    citizenMarker = L.marker([destLat, destLng], { icon: citizenIcon }).addTo(map);
    citizenMarker.bindPopup("<strong>Your Location</strong>").openPopup();

    function updateTracking() {
        fetch('get_tracking_data.php')
            .then(r => r.json())
            .then(data => {
                const driver = data.dispatches.find(d => d.request_id == <?php echo $data['request_id']; ?>);
                if (driver) {
                    const lat = parseFloat(driver.driver_lat);
                    const lng = parseFloat(driver.driver_lng);

                    if (!truckMarker) {
                        truckMarker = L.marker([lat, lng], { icon: truckIcon }).addTo(map);
                    } else {
                        truckMarker.setLatLng([lat, lng]);
                    }

                    // Update Route and ETA
                    fetch(`https://router.project-osrm.org/route/v1/driving/${lng},${lat};${destLng},${destLat}?overview=full&geometries=geojson`)
                        .then(r => r.json())
                        .then(rData => {
                            if (rData.routes && rData.routes[0]) {
                                const route = rData.routes[0];
                                const dist = (route.distance / 1000).toFixed(1);
                                const time = Math.ceil(route.duration / 60);

                                document.getElementById('live-eta-box').innerHTML = `⏱️ ETA: ${time} min &bull; ${dist} km away`;
                                document.getElementById('eta-countdown').innerText = time + " min";

                                if (routeLine) map.removeLayer(routeLine);
                                routeLine = L.geoJSON(route.geometry, {
                                    style: { color: '#3b82f6', weight: 5, opacity: 0.6, dashArray: '10, 10' }
                                }).addTo(map);

                                // Ensure both markers are in view
                                const group = new L.featureGroup([truckMarker, citizenMarker]);
                                map.fitBounds(group.getBounds(), { padding: [50, 50] });
                            }
                        });
                }
            });
    }

    setInterval(updateTracking, 5000);
    updateTracking();
</script>

<!-- Status Timeline + Details -->
<div style="padding: 0 10% 80px; display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">

    <!-- Left: Live Status -->
    <div class="tracker-card" data-aos="fade-right">
        <h3 style="margin: 0 0 20px; font-size: 1.1rem; font-weight: 700;">📋 Pickup Status</h3>

        <div class="tracker-step">
            <div class="step-icon <?php echo $current_step >= 1 ? 'done' : 'pending'; ?>">
                <?php echo $current_step >= 1 ? '✅' : '⏳'; ?>
            </div>
            <div>
                <div style="font-weight: 600; font-size: 0.9rem;">Request Logged</div>
                <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 2px;"><?php echo date('d M, H:i', strtotime($data['created_at'])); ?></div>
            </div>
        </div>

        <div class="tracker-step">
            <div class="step-icon <?php echo $current_step >= 2 || $data['collector_id'] ? 'done' : 'pending'; ?>">
                <?php echo ($current_step >= 2 || $data['collector_id']) ? '✅' : '⏳'; ?>
            </div>
            <div>
                <div style="font-weight: 600; font-size: 0.9rem;">Driver Assigned</div>
                <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 2px;">
                    <?php echo $data['driver_name'] ? $data['driver_name'] . " — Vehicle #" . $data['vehicle_no'] : "Searching for nearby collectors..."; ?>
                </div>
            </div>
        </div>

        <div class="tracker-step">
            <div class="step-icon <?php echo $current_step >= 3 ? 'done' : 'pending'; ?>">
                <?php echo $current_step >= 3 ? '🚛' : '⏳'; ?>
            </div>
            <div>
                <div style="font-weight: 600; font-size: 0.9rem; <?php echo $current_step == 3 ? 'color: var(--primary, #10b981);' : ''; ?>">En Route to You</div>
                <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 2px;">
                    <?php echo $current_step >= 3 ? "Currently moving through Ward #" . $data['ward_no'] . " (" . $data['zone_name'] . ")" : "Awaiting dispatch"; ?>
                </div>
            </div>
        </div>

        <div class="tracker-step">
            <div class="step-icon <?php echo $current_step >= 4 ? 'done' : 'pending'; ?>">
                <?php echo $current_step >= 4 ? '✅' : '⏳'; ?>
            </div>
            <div>
                <div style="font-weight: 600; font-size: 0.9rem; color: <?php echo $current_step >= 4 ? '#10b981' : '#94a3b8'; ?>;">Collection Complete</div>
                <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 2px;">
                    <?php echo $current_step >= 4 ? "Finished at " . date('H:i') : "Pending completion"; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: ETA + Vehicle -->
    <div data-aos="fade-left" style="display: flex; flex-direction: column; gap: 20px;">
        <div class="tracker-card" style="text-align: center;">
            <p style="margin: 0 0 12px; color: #64748b; font-size: 0.85rem; font-weight: 500;">Estimated Arrival</p>
            <div class="eta-badge">
                <span style="font-size: 1.4rem;">⏱️</span>
                <span id="eta-countdown"><?php echo ($current_step >= 3) ? "8 min" : ($current_step >= 2 ? "15 min" : "TBD"); ?></span>
            </div>
        </div>

        <div class="tracker-card">
            <h4 style="margin: 0 0 12px; font-weight: 700;">🚛 Vehicle Details</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.85rem;">
                <div><span style="color: #94a3b8;">Driver</span><br><strong><?php echo $data['driver_name'] ?: 'Not Assigned'; ?></strong></div>
                <div><span style="color: #94a3b8;">Vehicle</span><br><strong><?php echo $data['vehicle_no'] ?: 'UF-TBD'; ?></strong></div>
                <div><span style="color: #94a3b8;">Type</span><br><strong><?php echo $data['category']; ?></strong></div>
                <div><span style="color: #94a3b8;">Zone</span><br><strong><?php echo $data['zone_name']; ?></strong></div>
            </div>
        </div>

        <div class="tracker-card" style="background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(5,150,105,0.05)); border: 1px solid rgba(16,185,129,0.15);">
            <p style="margin: 0; font-size: 0.85rem; color: #64748b;">
                💡 <strong>Eco Tip:</strong> Separate your wet and dry waste before the truck arrives for faster collection!
            </p>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>