<?php 
include('includes/header.php');

// Only collectors can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'collector') {
    header("Location: login.php");
    exit();
}

$driver_id = $_SESSION['user_id'];
$driver_name = $_SESSION['user_name'];

// --- Stats Queries ---
$total_assigned_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM fleet_assignments WHERE collector_id = $driver_id");
$total_assigned = ($total_assigned_res) ? mysqli_fetch_assoc($total_assigned_res)['c'] : 0;

$active_tasks_res = mysqli_query($conn, 
    "SELECT COUNT(*) as c FROM fleet_assignments f 
     LEFT JOIN disposal_requests r ON f.request_id = r.request_id 
     LEFT JOIN illegal_dumps d ON f.dump_id = d.dump_id
     WHERE f.collector_id = $driver_id 
     AND (r.status = 'Dispatched' OR d.status = 'Cleanup Dispatched')");
$active_tasks = ($active_tasks_res) ? mysqli_fetch_assoc($active_tasks_res)['c'] : 0;

$completed_tasks_res = mysqli_query($conn, 
    "SELECT COUNT(*) as c FROM fleet_assignments f 
     LEFT JOIN disposal_requests r ON f.request_id = r.request_id 
     LEFT JOIN illegal_dumps d ON f.dump_id = d.dump_id
     WHERE f.collector_id = $driver_id 
     AND (r.status = 'Resolved' OR d.status = 'Resolved')");
$completed_tasks = ($completed_tasks_res) ? mysqli_fetch_assoc($completed_tasks_res)['c'] : 0;

// --- Get Last Known Location ---
$loc_res = mysqli_query($conn, "SELECT lat, lng FROM driver_locations WHERE driver_id = $driver_id");
$last_loc = mysqli_fetch_assoc($loc_res);
$startLat = $last_loc ? $last_loc['lat'] : 14.4600;
$startLng = $last_loc ? $last_loc['lng'] : 75.9150;
?>

<style>
    :root {
        --accent-amber: #f59e0b;
        --accent-blue: #3b82f6;
        --bg-glass: rgba(255, 255, 255, 0.75);
        --primary: #4ade80;
        --dark: #1e293b;
    }
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        margin: 0;
        padding: 0;
        min-height: 100vh;
    }

    /* Floating Background Orbs */
    .orb {
        position: fixed;
        border-radius: 50%;
        filter: blur(80px);
        z-index: -1;
        opacity: 0.35;
    }

    .driver-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    .driver-header {
        margin-bottom: 40px;
    }
    .driver-header h1 {
        font-weight: 800;
        font-size: 2.5rem;
        letter-spacing: -1px;
    }
    .driver-header p {
        color: #64748b;
        font-size: 1rem;
        margin-top: 5px;
    }

    /* Stats Row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 35px;
    }
    .stat-card {
        background: var(--bg-glass);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.5);
        border-radius: 24px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 15px 40px rgba(0,0,0,0.04);
        transition: transform 0.3s ease;
    }
    .stat-card:hover { transform: translateY(-5px); }
    .stat-card .stat-icon {
        font-size: 2rem;
        margin-bottom: 10px;
    }
    .stat-card .stat-value {
        font-size: 2.2rem;
        font-weight: 800;
        color: var(--dark);
    }
    .stat-card .stat-label {
        font-size: 0.8rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 5px;
    }

    /* Glass Panel */
    .glass-panel {
        background: var(--bg-glass);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.5);
        border-radius: 30px;
        padding: 35px;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }
    .glass-panel h2 {
        font-weight: 800;
        letter-spacing: -0.5px;
        margin-bottom: 25px;
        font-size: 1.5rem;
    }

    /* Task Cards */
    .task-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        flex-wrap: wrap;
        gap: 15px;
    }
    .task-card:hover {
        transform: scale(1.01);
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
    }
    .task-info {
        flex: 1;
        min-width: 200px;
    }
    .task-info .task-id {
        font-weight: 800;
        font-size: 1.1rem;
        color: var(--dark);
    }
    .task-info .task-meta {
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 4px;
    }
    .task-info .task-comment {
        font-size: 0.85rem;
        color: #94a3b8;
        margin-top: 6px;
        font-style: italic;
    }

    /* Badges */
    .badge {
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-high { background: #fee2e2; color: #991b1b; }
    .badge-medium { background: #fef3c7; color: #92400e; }
    .badge-low { background: #f0fdf4; color: #166534; }
    .badge-dispatched { background: #dbeafe; color: #1e40af; }
    .badge-resolved { background: #dcfce7; color: #166534; }

    /* Resolve Button */
    .btn-resolve {
        background: linear-gradient(135deg, #4ade80, #22c55e);
        color: white;
        padding: 12px 24px;
        border-radius: 14px;
        border: none;
        cursor: pointer;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 15px rgba(74, 222, 128, 0.3);
    }
    .btn-resolve:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(74, 222, 128, 0.4);
    }

    /* Vehicle Badge */
    .vehicle-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f1f5f9;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--dark);
    }

    /* Completed Table */
    .completed-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    .completed-table th {
        text-align: left;
        padding: 12px 15px;
        color: #64748b;
        font-weight: 500;
        font-size: 0.85rem;
    }
    .completed-table td {
        padding: 15px;
        background: white;
        font-size: 0.9rem;
    }
    .completed-table td:first-child { border-radius: 12px 0 0 12px; }
    .completed-table td:last-child { border-radius: 0 12px 12px 0; }

    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: #94a3b8;
    }
    .empty-state .emoji {
        font-size: 3rem;
        margin-bottom: 15px;
    }

    @media (max-width: 768px) {
        .stats-row { grid-template-columns: 1fr; }
        .task-card { flex-direction: column; align-items: flex-start; }
        .driver-header h1 { font-size: 1.8rem; }
    }
</style>

<!-- Antigravity Background Orbs -->
<div class="orb" style="width: 400px; height: 400px; top: -100px; right: -100px; background: #f59e0b;"></div>
<div class="orb" style="width: 300px; height: 300px; bottom: 50px; left: -80px; background: #4ade80;"></div>
<div class="orb" style="width: 250px; height: 250px; top: 40%; right: 10%; background: #3b82f6;"></div>

<div class="driver-container">
    <!-- Header -->
    <div class="driver-header" style="display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h1>Driver <span style="color: var(--accent-amber);">Portal</span></h1>
            <p>Welcome back, <strong><?php echo htmlspecialchars($driver_name); ?></strong> — here are your assigned pickups.</p>
        </div>
        <button onclick="location.reload()" style="background: white; border: 1px solid #e2e8f0; padding: 10px 15px; border-radius: 12px; cursor: pointer; font-weight: 700; color: #64748b;">🔄 Refresh</button>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card" data-aos="zoom-in" data-aos-delay="100">
            <div class="stat-icon">📦</div>
            <div class="stat-value" id="count-total"><?php echo $total_assigned; ?></div>
            <div class="stat-label">Total Assigned</div>
        </div>
        <div class="stat-card" data-aos="zoom-in" data-aos-delay="200">
            <div class="stat-icon">🚛</div>
            <div class="stat-value" id="count-active" style="color: var(--accent-blue);"><?php echo $active_tasks; ?></div>
            <div class="stat-label">Active Pickups</div>
        </div>
        <div class="stat-card" data-aos="zoom-in" data-aos-delay="300">
            <div class="stat-icon">✅</div>
            <div class="stat-value" id="count-completed" style="color: var(--primary);"><?php echo $completed_tasks; ?></div>
            <div class="stat-label">Completed</div>
        </div>
    </div>

    <!-- Next Priority Pickup Card (Report Format) -->
    <?php
    // Get the most urgent next task
    $priority_query = "SELECT f.*, 
                              COALESCE(r.category, 'Illegal Dump Cleanup') as category, 
                              COALESCE(r.comment, d.description) as comment, 
                              COALESCE(r.urgency_level, d.severity) as urgency_level, 
                              z.zone_name, z.lat as dest_lat, z.lng as dest_lng, u.full_name as citizen_name,
                              d.image_path,
                              CASE WHEN f.dump_id IS NOT NULL THEN 'dump' ELSE 'request' END as type
                      FROM fleet_assignments f
                      LEFT JOIN disposal_requests r ON f.request_id = r.request_id
                      LEFT JOIN illegal_dumps d ON f.dump_id = d.dump_id
                      JOIN zones z ON (r.zone_id = z.zone_id OR d.zone_id = z.zone_id)
                      JOIN users u ON (r.citizen_id = u.user_id OR d.citizen_id = u.user_id)
                      WHERE f.collector_id = $driver_id 
                      AND (r.status = 'Dispatched' OR d.status = 'Cleanup Dispatched')
                      ORDER BY CASE WHEN COALESCE(r.urgency_level, d.severity) = 'Critical' THEN 1 
                                    WHEN COALESCE(r.urgency_level, d.severity) = 'High' THEN 2 
                                    WHEN COALESCE(r.urgency_level, d.severity) = 'Medium' THEN 3 
                                    ELSE 4 END, f.assignment_id ASC
                      LIMIT 1";
    $priority_res = mysqli_query($conn, $priority_query);
    $priority_task = mysqli_fetch_assoc($priority_res);
    ?>

    <?php if ($priority_task): ?>
    <div class="glass-panel" style="border-left: 10px solid <?php echo $priority_task['type'] == 'dump' ? 'var(--danger)' : 'var(--accent-amber)'; ?>;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
            <div style="flex: 1;">
                <h2 style="margin: 0; color: <?php echo $priority_task['type'] == 'dump' ? 'var(--danger)' : 'var(--accent-amber)'; ?>;">
                    📍 Next Priority <?php echo $priority_task['type'] == 'dump' ? 'Cleanup' : 'Pickup'; ?> Report
                </h2>
                <div style="margin-top: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <small style="color: #64748b; text-transform: uppercase; font-weight: 700;">Location Details</small>
                        <div style="font-size: 1.1rem; font-weight: 800;"><?php echo $priority_task['zone_name']; ?></div>
                        <div style="font-size: 0.85rem; color: #94a3b8;"><?php echo $priority_task['type'] == 'dump' ? 'Reported by ' : 'Citizen: '; ?><strong><?php echo $priority_task['citizen_name']; ?></strong></div>
                    </div>
                    <div>
                        <small style="color: #64748b; text-transform: uppercase; font-weight: 700;">Optimal Route</small>
                        <div style="font-size: 1.1rem; font-weight: 800; color: var(--accent-blue);" id="route-meta">Calculating...</div>
                        <div style="font-size: 0.85rem; color: #94a3b8;">via Smart Traffic AI</div>
                    </div>
                </div>
                <?php if($priority_task['image_path']): ?>
                <div style="margin-top: 15px;">
                    <small style="color: #64748b; text-transform: uppercase; font-weight: 700;">Dump Photo</small><br>
                    <img src="<?php echo $priority_task['image_path']; ?>" style="width: 120px; height: 120px; object-fit: cover; border-radius: 15px; border: 2px solid white; margin-top: 5px; cursor: pointer;" onclick="window.open(this.src)">
                </div>
                <?php endif; ?>
                <div style="margin-top: 20px; padding: 15px; background: white; border-radius: 15px; border: 1px dashed #cbd5e1;">
                    <strong style="font-size: 0.8rem; color: #64748b;">🧭 NAVIGATION INSTRUCTIONS:</strong>
                    <p style="margin: 5px 0 0; font-size: 0.9rem; line-height: 1.4;" id="nav-instructions">
                        Initializing GPS and fetching optimal path from OSRM...
                    </p>
                </div>
                <div style="margin-top: 15px; padding: 15px; background: #fffbeb; border-radius: 15px; border: 1px solid #fde68a;">
                    <strong style="font-size: 0.8rem; color: #92400e;">📜 DUTY PROTOCOL & REPORT:</strong>
                    <ul style="margin: 5px 0 0; font-size: 0.8rem; color: #92400e; padding-left: 20px;">
                        <li>Verify waste category matches the request.</li>
                        <li>Ensure proper containment before loading.</li>
                        <li>Update status immediately upon pickup.</li>
                        <li>Do NOT mix hazardous waste with organics.</li>
                    </ul>
                </div>
            </div>
            <div style="text-align: right;">
                <div class="badge badge-high" style="margin-bottom: 10px;">Priority: <?php echo $priority_task['urgency_level']; ?></div>
                <br>
                <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo $priority_task['dest_lat']; ?>,<?php echo $priority_task['dest_lng']; ?>" target="_blank" class="btn-resolve" style="background: var(--accent-blue); text-decoration: none; display: inline-flex;">
                    🗺️ Open in Maps
                </a>
            </div>
        </div>
        
        <!-- Real-time GPS Sync (Simulation for Demo) -->
        <script>
            let currentLat = <?php echo $startLat; ?>; 
            let currentLng = <?php echo $startLng; ?>;
            const destLat = <?php echo $priority_task['dest_lat']; ?>;
            const destLng = <?php echo $priority_task['dest_lng']; ?>;
            
            function syncGPS() {
                // Fetch route info for the "Report"
                fetch(`https://router.project-osrm.org/route/v1/driving/${currentLng},${currentLat};${destLng},${destLat}?overview=false&steps=true`)
                    .then(r => r.json())
                    .then(data => {
                        if(data.routes && data.routes[0]) {
                            const route = data.routes[0];
                            document.getElementById('route-meta').innerHTML = `⏱️ ${Math.ceil(route.duration/60)} min &bull; ${(route.distance/1000).toFixed(1)} km`;
                            
                            let instructions = "Follow the route and continue towards " + "<?php echo addslashes($priority_task['zone_name']); ?>.";
                            if (route.legs && route.legs[0].steps && route.legs[0].steps.length > 0) {
                                const step = route.legs[0].steps[0];
                                if (step.maneuver && step.maneuver.instruction) {
                                    instructions = step.maneuver.instruction + " and continue towards " + "<?php echo addslashes($priority_task['zone_name']); ?>.";
                                }
                            }
                            document.getElementById('nav-instructions').innerHTML = instructions;
                        } else {
                            document.getElementById('nav-instructions').innerHTML = "Unable to fetch routing data. Please use manual navigation.";
                        }
                    });

                // Simulate movement
                currentLat += (destLat - currentLat) * 0.05;
                currentLng += (destLng - currentLng) * 0.05;

                // Update database
                fetch(`update_driver_location.php?lat=${currentLat}&lng=${currentLng}`)
                    .catch(err => console.error("GPS Sync failed"));
            }

            setInterval(syncGPS, 5000);
            syncGPS();
        </script>
    </div>
    <?php endif; ?>

    <!-- Active Assignments -->
    <div class="glass-panel">
        <h2>🚀 Dispatch Queue Overview</h2>
        <?php
        $active_query = "SELECT f.*, 
                                COALESCE(r.category, '🚨 Illegal Dump') as category, 
                                COALESCE(r.comment, d.description) as comment, 
                                COALESCE(r.urgency_level, d.severity) as urgency_level, 
                                COALESCE(r.status, d.status) as status, 
                                z.zone_name, u.full_name as citizen_name,
                                CASE WHEN f.dump_id IS NOT NULL THEN 'dump' ELSE 'request' END as type
                         FROM fleet_assignments f
                         LEFT JOIN disposal_requests r ON f.request_id = r.request_id
                         LEFT JOIN illegal_dumps d ON f.dump_id = d.dump_id
                         JOIN zones z ON (r.zone_id = z.zone_id OR d.zone_id = z.zone_id)
                         JOIN users u ON (r.citizen_id = u.user_id OR d.citizen_id = u.user_id)
                         WHERE f.collector_id = $driver_id 
                         AND (r.status = 'Dispatched' OR d.status = 'Cleanup Dispatched')
                         ORDER BY f.assignment_id DESC";
        $active_result = mysqli_query($conn, $active_query);

        if ($active_result && mysqli_num_rows($active_result) > 0):
            while($task = mysqli_fetch_assoc($active_result)):
                $urgencyClass = 'badge-' . strtolower($task['urgency_level']);
        ?>
            <div class="task-card" style="<?php echo $task['type'] == 'dump' ? 'border-left: 5px solid var(--danger);' : ''; ?>">
                <div class="task-info" style="display: flex; gap: 15px; align-items: center;">
                    <?php if($task['type'] == 'dump' && !empty($task['dump_id'])): 
                        // Fetch the image path specifically for this dump
                        $d_id = $task['dump_id'];
                        $img_res = mysqli_query($conn, "SELECT image_path FROM illegal_dumps WHERE dump_id = $d_id");
                        $img_data = mysqli_fetch_assoc($img_res);
                        if($img_data && $img_data['image_path']): ?>
                            <img src="<?php echo $img_data['image_path']; ?>" style="width: 60px; height: 60px; border-radius: 12px; object-fit: cover; border: 1px solid #e2e8f0;">
                        <?php endif; ?>
                    <?php endif; ?>
                    <div>
                        <div class="task-id">
                            <?php echo $task['type'] == 'dump' ? '🚨 DUMP CLEANUP' : '📦 PICKUP'; ?> #<?php echo $task['type'] == 'dump' ? $task['dump_id'] : $task['request_id']; ?>
                        </div>
                        <div class="task-meta">
                            <strong><?php echo htmlspecialchars($task['citizen_name']); ?></strong> 
                            &bull; <?php echo htmlspecialchars($task['zone_name']); ?>
                        </div>
                        <?php if(!empty($task['comment'])): ?>
                            <div class="task-comment">"<?php echo htmlspecialchars($task['comment']); ?>"</div>
                        <?php endif; ?>
                        <div style="margin-top: 8px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <span class="badge <?php echo $urgencyClass; ?>"><?php echo $task['urgency_level']; ?></span>
                            <span class="vehicle-tag">🚐 <?php echo htmlspecialchars($task['vehicle_no']); ?></span>
                        </div>
                    </div>
                </div>
                <form action="resolve_task.php" method="POST">
                    <?php if($task['type'] == 'dump'): ?>
                        <input type="hidden" name="dump_id" value="<?php echo $task['dump_id']; ?>">
                    <?php else: ?>
                        <input type="hidden" name="request_id" value="<?php echo $task['request_id']; ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn-resolve">✓ Mark Resolved</button>
                </form>
            </div>
        <?php endwhile;
        else: ?>
            <div class="empty-state">
                <div class="emoji">🎉</div>
                <p>No active assignments. You're all caught up!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Completed Pickups -->
    <div class="glass-panel">
        <h2>📋 Completed Pickups</h2>
        <?php
        $done_query = "SELECT f.*, 
                              COALESCE(r.category, 'Illegal Dump') as category, 
                              COALESCE(r.status, d.status) as status, 
                              z.zone_name, u.full_name as citizen_name,
                              CASE WHEN f.dump_id IS NOT NULL THEN 'dump' ELSE 'request' END as type
                       FROM fleet_assignments f
                       LEFT JOIN disposal_requests r ON f.request_id = r.request_id
                       LEFT JOIN illegal_dumps d ON f.dump_id = d.dump_id
                       JOIN zones z ON (r.zone_id = z.zone_id OR d.zone_id = z.zone_id)
                       JOIN users u ON (r.citizen_id = u.user_id OR d.citizen_id = u.user_id)
                       WHERE f.collector_id = $driver_id 
                       AND (r.status = 'Resolved' OR d.status = 'Resolved')
                       ORDER BY f.assignment_id DESC
                       LIMIT 10";
        $done_result = mysqli_query($conn, $done_query);

        if ($done_result && mysqli_num_rows($done_result) > 0):
        ?>
        <table class="completed-table">
            <thead>
                <tr>
                    <th>Request</th>
                    <th>Citizen</th>
                    <th>Zone</th>
                    <th>Category</th>
                    <th>Vehicle</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($done_result)): ?>
                <tr>
                    <td><strong>#<?php echo $row['type'] == 'dump' ? $row['dump_id'] : $row['request_id']; ?></strong><br>
                        <small style="color:<?php echo $row['type'] == 'dump' ? 'var(--danger)' : 'var(--accent-amber)'; ?>; font-weight:700;">
                            <?php echo strtoupper($row['type']); ?>
                        </small>
                    </td>
                    <td><?php echo htmlspecialchars($row['citizen_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['zone_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><span class="vehicle-tag">🚐 <?php echo htmlspecialchars($row['vehicle_no']); ?></span></td>
                    <td><span class="badge badge-resolved">Resolved</span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="emoji">📭</div>
                <p>No completed pickups yet. Start by resolving your active tasks!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Smooth Antigravity Motion for Orbs
    gsap.to(".orb", {
        y: "random(-60, 60)",
        x: "random(-30, 30)",
        duration: 10,
        repeat: -1,
        yoyo: true,
        ease: "sine.inOut"
    });
</script>

<?php if(isset($_GET['resolved'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Task Resolved!',
            text: 'The pickup has been marked as completed.',
            icon: 'success',
            confirmButtonColor: '#4ade80',
            background: 'rgba(255,255,255,0.95)',
            backdrop: 'rgba(0,0,0,0.1)'
        });
    });
</script>
<?php endif; ?>

<?php include('includes/footer.php'); ?>
