<?php include('includes/header.php'); ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />

<style>
    :root {
        --primary: #4ade80;
        --blue: #3b82f6;
        --danger: #ef4444;
        --glass: rgba(255,255,255,0.8);
    }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f1f5f9; margin: 0; }

    .map-container {
        max-width: 1300px;
        margin: 0 auto;
        padding: 30px 20px;
    }
    .map-header {
        margin-bottom: 25px;
    }
    .map-header h1 {
        font-weight: 800;
        font-size: 2.2rem;
        letter-spacing: -1px;
    }

    .map-grid {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 20px;
    }

    .map-panel {
        background: var(--glass);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255,255,255,0.5);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(0,0,0,0.06);
    }

    #map {
        height: 600px;
        width: 100%;
        border-radius: 24px;
    }

    /* Sidebar */
    .sidebar {
        background: var(--glass);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255,255,255,0.5);
        border-radius: 24px;
        padding: 20px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.06);
        max-height: 600px;
        overflow-y: auto;
    }
    .sidebar h3 { font-weight: 800; margin-bottom: 15px; font-size: 1.1rem; }

    .dispatch-card {
        background: white;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 12px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.03);
        transition: transform 0.2s;
        cursor: pointer;
    }
    .dispatch-card:hover { transform: scale(1.02); }
    .dispatch-card .dc-title { font-weight: 700; font-size: 0.95rem; }
    .dispatch-card .dc-meta { font-size: 0.8rem; color: #64748b; margin-top: 4px; }
    .dispatch-card .dc-eta {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #dbeafe;
        color: #1e40af;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
        margin-top: 8px;
    }

    .dump-card {
        background: #fef2f2;
        border-radius: 16px;
        padding: 14px;
        margin-bottom: 10px;
        border-left: 4px solid #ef4444;
    }
    .dump-card .dump-title { font-weight: 700; font-size: 0.85rem; color: #991b1b; }
    .dump-card .dump-meta { font-size: 0.75rem; color: #64748b; margin-top: 3px; }

    .legend {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        padding: 12px 0;
        border-top: 1px solid #e2e8f0;
        margin-top: 15px;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.75rem;
        color: #64748b;
    }
    .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .refresh-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.8rem;
        color: #64748b;
        background: #f1f5f9;
        padding: 6px 14px;
        border-radius: 10px;
    }
    .refresh-badge .pulse {
        width: 8px;
        height: 8px;
        background: #4ade80;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.3); }
    }

    /* Custom Leaflet popup */
    .leaflet-popup-content-wrapper {
        border-radius: 16px !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12) !important;
    }

    /* Hide OSRM branding */
    .leaflet-routing-container { display: none !important; }

    @media (max-width: 900px) {
        .map-grid { grid-template-columns: 1fr; }
        .sidebar { max-height: 350px; }
        #map { height: 400px; }
    }
</style>

<div class="map-container">
    <div class="map-header" data-aos="fade-down">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <h1>🗺️ Live <span style="color: var(--blue);">Fleet Tracking</span></h1>
                <p style="color: #64748b; margin-top: 5px;">Real-time truck locations, optimized routes & ETA</p>
            </div>
            <div class="refresh-badge"><div class="pulse"></div> Auto-refreshing every 5s</div>
        </div>
    </div>

    <div class="map-grid">
        <!-- Map Panel -->
        <div class="map-panel" data-aos="zoom-in">
            <div id="map"></div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar" data-aos="fade-left" data-aos-delay="200">
            <h3>🚛 Active Dispatches</h3>
            <div id="dispatchList">Loading...</div>

            <h3 style="margin-top: 20px;">🚨 Dump Reports</h3>
            <div id="dumpList">Loading...</div>

            <div class="legend">
                <div class="legend-item"><div class="legend-dot" style="background: #4ade80;"></div> Truck</div>
                <div class="legend-item"><div class="legend-dot" style="background: #3b82f6;"></div> Pickup Zone</div>
                <div class="legend-item"><div class="legend-dot" style="background: #ef4444;"></div> Dump Report</div>
                <div class="legend-item"><div class="legend-dot" style="background: #8b5cf6; border: 2px dashed #8b5cf6;"></div> Route</div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });

    // Initialize map centered on Davanagere
    var map = L.map('map', { zoomControl: false }).setView([14.4644, 75.9218], 14);
    L.control.zoom({ position: 'topright' }).addTo(map);

    // Premium tile layer
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; CARTO'
    }).addTo(map);

    // Custom icons
    function createIcon(emoji, bgColor, shadowColor) {
        return L.divIcon({
            className: 'custom-icon',
            html: `<div style="background:${bgColor}; padding:8px; border-radius:50%; box-shadow: 0 0 18px ${shadowColor}; text-align:center; font-size:1.3rem; line-height:1; border: 2px solid white;">${emoji}</div>`,
            iconSize: [42, 42],
            iconAnchor: [21, 21],
            popupAnchor: [0, -24]
        });
    }

    var truckIcon = createIcon('🚚', '#4ade80', 'rgba(74,222,128,0.5)');
    var zoneIcon = createIcon('📍', '#3b82f6', 'rgba(59,130,246,0.4)');
    var dumpIcon = createIcon('🚨', '#ef4444', 'rgba(239,68,68,0.4)');

    var markers = {}; // Store markers by unique ID
    var routeControls = {}; // Store routes by unique ID

    function loadTrackingData() {
        fetch('get_tracking_data.php')
            .then(r => r.json())
            .then(data => {
                let dispatchHTML = '';
                let dumpHTML = '';
                let activeIds = new Set();

                // Draw dispatches
                data.dispatches.forEach((d) => {
                    let zoneLat = parseFloat(d.req_lat) || parseFloat(d.zone_lat);
                    let zoneLng = parseFloat(d.req_lng) || parseFloat(d.zone_lng);
                    
                    let driverLat = parseFloat(d.driver_lat) || (zoneLat + 0.003);
                    let driverLng = parseFloat(d.driver_lng) || (zoneLng - 0.004);
                    
                    let truckId = 'truck-' + d.collector_id;
                    let zoneId = 'zone-' + d.request_id;
                    let routeId = 'route-' + d.request_id;
                    activeIds.add(truckId);
                    activeIds.add(zoneId);

                    // Truck marker
                    if (markers[truckId]) {
                        markers[truckId].setLatLng([driverLat, driverLng]);
                    } else {
                        markers[truckId] = L.marker([driverLat, driverLng], { icon: truckIcon }).addTo(map);
                    }
                    
                    // Zone marker
                    if (!markers[zoneId]) {
                        markers[zoneId] = L.marker([zoneLat, zoneLng], { icon: zoneIcon }).addTo(map);
                        markers[zoneId].bindPopup(`<strong>📍 ${d.zone_name}</strong><br>Pickup for Request #${d.request_id}`);
                    }

                    // Optimized route via OSRM
                    if (!routeControls[routeId]) {
                        routeControls[routeId] = L.Routing.control({
                            waypoints: [L.latLng(driverLat, driverLng), L.latLng(zoneLat, zoneLng)],
                            router: L.Routing.osrmv1({ serviceUrl: 'https://router.project-osrm.org/route/v1' }),
                            lineOptions: { styles: [{ color: '#8b5cf6', weight: 5, opacity: 0.75, dashArray: '10 8' }] },
                            show: false, addWaypoints: false, fitSelectedRoutes: false,
                            createMarker: function() { return null; }
                        }).addTo(map);

                        routeControls[routeId].on('routesfound', function(e) {
                            let route = e.routes[0];
                            let distKm = (route.summary.totalDistance / 1000).toFixed(1);
                            let timeMin = Math.ceil(route.summary.totalTime / 60);
                            
                            let etaEl = document.getElementById('eta-' + d.request_id);
                            if (etaEl) etaEl.innerHTML = `⏱️ ${timeMin} min &bull; ${distKm} km`;

                            markers[truckId].setPopupContent(`
                                <div style="min-width:200px;">
                                    <strong style="font-size:1rem;">🚚 ${d.vehicle_no}</strong><br>
                                    <small style="color:#64748b;">Request #${d.request_id}</small>
                                    <div style="margin-top:8px; background:#f0fdf4; padding:8px 12px; border-radius:10px;">
                                        <strong style="color:#166534;">⏱️ ETA: ${timeMin} min</strong><br>
                                        <small style="color:#64748b;">Distance: ${distKm} km</small>
                                    </div>
                                    <div style="margin-top:6px;">
                                        <strong>Citizen:</strong> ${d.citizen_name}<br>
                                        <strong>Category:</strong> ${d.category}<br>
                                        <strong>Zone:</strong> ${d.zone_name}
                                    </div>
                                </div>
                            `);
                        });
                    } else {
                        // Update waypoints if needed
                        routeControls[routeId].setWaypoints([L.latLng(driverLat, driverLng), L.latLng(zoneLat, zoneLng)]);
                    }

                    dispatchHTML += `
                        <div class="dispatch-card" onclick="map.flyTo([${driverLat}, ${driverLng}], 15)">
                            <div class="dc-title">Request #${d.request_id} — ${d.category}</div>
                            <div class="dc-meta">${d.citizen_name} &bull; ${d.zone_name}</div>
                            <div class="dc-meta">🚐 ${d.vehicle_no}</div>
                            <div class="dc-eta" id="eta-${d.request_id}">⏳ Calculating...</div>
                        </div>
                    `;
                });

                // Draw dump reports
                data.dumps.forEach(d => {
                    let lat = parseFloat(d.citizen_lat) || parseFloat(d.zone_lat);
                    let lng = parseFloat(d.citizen_lng) || parseFloat(d.zone_lng);
                    let dumpId = 'dump-' + d.dump_id;
                    activeIds.add(dumpId);

                    if (!markers[dumpId]) {
                        markers[dumpId] = L.marker([lat, lng], { icon: dumpIcon }).addTo(map);
                        markers[dumpId].bindPopup(`
                            <div style="min-width:180px;">
                                <strong style="color:#991b1b;">🚨 Illegal Dump</strong><br>
                                <strong>Zone:</strong> ${d.zone_name}<br>
                                <strong>Severity:</strong> ${d.severity}<br>
                                <strong>Status:</strong> ${d.status}<br>
                                <small style="color:#64748b;">${d.description ? d.description.substring(0, 80) + '...' : ''}</small>
                            </div>
                        `);
                    }

                    dumpHTML += `
                        <div class="dump-card" onclick="map.flyTo([${lat}, ${lng}], 16)">
                            <div class="dump-title">${d.severity} — ${d.zone_name}</div>
                            <div class="dump-meta">${d.status} &bull; ${d.citizen_name}</div>
                        </div>
                    `;
                });

                // Cleanup inactive markers
                Object.keys(markers).forEach(id => {
                    if (!activeIds.has(id)) {
                        map.removeLayer(markers[id]);
                        delete markers[id];
                        
                        let rid = 'route-' + id.split('-')[1];
                        if (routeControls[rid]) {
                            map.removeControl(routeControls[rid]);
                            delete routeControls[rid];
                        }
                    }
                });

                document.getElementById('dispatchList').innerHTML = dispatchHTML || '<p style="color:#94a3b8; text-align:center; padding:20px;">No active dispatches</p>';
                document.getElementById('dumpList').innerHTML = dumpHTML || '<p style="color:#94a3b8; text-align:center; padding:20px;">No active reports</p>';
            })
            .catch(err => console.error('Tracking fetch error:', err));
    }

    // Initial load + auto-refresh
    loadTrackingData();
    setInterval(loadTrackingData, 5000);
</script>

<?php include('includes/footer.php'); ?>