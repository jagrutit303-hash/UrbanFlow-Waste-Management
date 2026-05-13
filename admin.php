<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
include('includes/header.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Helper function to safe-query counts
function getCount($conn, $query) {
    $res = mysqli_query($conn, $query);
    if (!$res) return 0;
    $row = mysqli_fetch_assoc($res);
    return $row['c'] ?? 0;
}
?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --accent-blue: #3b82f6;
            --secondary: #6366f1;
            --bg-glass: rgba(255, 255, 255, 0.8);
            --primary: #4ade80;
            --danger: #ef4444;
            --dark: #1e293b;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 40px;
        }

        .admin-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .glass-panel {
            background: var(--bg-glass);
            backdrop-filter: blur(15px);
            border-radius: 30px;
            padding: 35px;
            border: 1px solid rgba(255,255,255,0.5);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.05);
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .data-table th {
            text-align: left;
            padding: 15px;
            color: #64748b;
            font-weight: 400;
        }

        .data-table tr {
            background: white;
            transition: transform 0.3s ease;
        }

        .data-table tr:hover {
            transform: scale(1.01);
            box-shadow: 0 10px 20px rgba(0,0,0,0.02);
        }

        .data-table td {
            padding: 20px 15px;
        }

        .data-table td:first-child { border-radius: 15px 0 0 15px; }
        .data-table td:last-child { border-radius: 0 15px 15px 0; }

        .btn-dispatch {
            background: var(--accent-blue);
            color: white;
            padding: 10px 20px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-urgent { background: #fee2e2; color: #991b1b; }
        .badge-low { background: #f0fdf4; color: #166534; }

        /* Tabs */
        .admin-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }
        .admin-tab {
            padding: 12px 24px;
            border-radius: 14px;
            border: 2px solid #e2e8f0;
            background: white;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.9rem;
            transition: all 0.3s;
            color: #64748b;
        }
        .admin-tab.active {
            border-color: var(--accent-blue);
            background: #eff6ff;
            color: var(--accent-blue);
        }
        .admin-tab:hover { transform: scale(1.03); }

        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* Dump report specifics */
        .dump-severity-critical { background: #fef2f2; color: #991b1b; }
        .dump-severity-high { background: #fff7ed; color: #9a3412; }
        .dump-severity-medium { background: #fefce8; color: #854d0e; }
        .dump-severity-low { background: #f0fdf4; color: #166534; }

        .dump-status-reported { background: #fef2f2; color: #991b1b; }
        .dump-status-under-review { background: #fefce8; color: #854d0e; }
        .dump-status-cleanup-dispatched { background: #dbeafe; color: #1e40af; }
        .dump-status-resolved { background: #dcfce7; color: #166534; }

        .img-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.3s;
            border: 2px solid #e2e8f0;
        }
        .img-thumb:hover { transform: scale(1.3); }

        .status-select {
            padding: 8px 12px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            font-size: 0.8rem;
            font-weight: 600;
            background: white;
            cursor: pointer;
        }

        .btn-update {
            background: var(--primary);
            color: #1e293b;
            padding: 8px 16px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.8rem;
            transition: 0.3s;
        }
        .btn-update:hover { transform: scale(1.05); }

        /* Analytics Specifics */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.03);
            border: 1px solid #f1f5f9;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 35px rgba(0,0,0,0.07);
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(74, 222, 128, 0.1) 0%, rgba(59, 130, 246, 0.1) 100%);
            border-radius: 0 0 0 100%;
            z-index: 0;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
        }
    </style>

<div class="admin-grid">
    <header data-aos="fade-down" style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-weight: 800; font-size: 2.5rem; margin: 0;">System <span style="color:var(--accent-blue)">Overview</span></h1>
            <p style="margin: 5px 0 0;">Logistics Management & Fleet Dispatch</p>
        </div>
        <div class="glass-panel" style="padding: 10px 20px; border-radius: 15px; display: flex; align-items: center; gap: 15px;">
            <div style="text-align: right;">
                <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">System Status</div>
                <div style="font-weight: 800; color: var(--primary);">🟢 Operational</div>
            </div>
            <div style="height: 30px; width: 2px; background: #e2e8f0;"></div>
            <div>
                <div style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">AI Core</div>
                <div style="font-weight: 800; color: var(--accent-blue);">🛰️ Synchronized</div>
            </div>
            <div style="height: 30px; width: 2px; background: #e2e8f0;"></div>
            <button onclick="downloadReport()" class="btn-update" style="background:var(--dark); color:white; padding: 10px 15px;">📥 Full Report</button>
        </div>
    </header>

    <!-- Tabs -->
    <div class="admin-tabs" data-aos="fade-up">
        <div class="admin-tab active" onclick="switchTab('dispatch', event)">🚛 Dispatch Queue</div>
        <div class="admin-tab" onclick="switchTab('dumps', event)">🚨 Dump Reports</div>
        <div class="admin-tab" onclick="switchTab('feedback', event)">💬 Citizen Feedback</div>
        <div class="admin-tab" onclick="switchTab('analytics', event)">📊 System Analytics</div>
        <div class="admin-tab" onclick="switchTab('users', event)">👥 User Management</div>
    </div>

    <!-- TAB 1: Dispatch Queue -->
    <div class="tab-content active" id="tab-dispatch">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            <div class="glass-panel">
                <div style="margin-bottom: 20px; display: flex; gap: 10px;">
                    <input type="text" id="searchInput" placeholder="Search by Citizen Name or Zone..." 
                           style="flex: 1; padding: 12px; border-radius: 12px; border: 1px solid #e2e8f0;">
                </div>
                <h2>Live Dispatch Queue</h2>
                <table class="data-table" id="dispatchTable">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Citizen</th>
                            <th>Category</th>
                            <th>Zone</th>
                            <th>Urgency</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "(SELECT r.request_id as id, u.full_name, r.category, z.zone_name, r.urgency_level, r.status, 'request' as type, r.created_at 
                                   FROM disposal_requests r 
                                   JOIN users u ON r.citizen_id = u.user_id 
                                   JOIN zones z ON r.zone_id = z.zone_id 
                                   WHERE r.status = 'Logged')
                                  UNION ALL
                                  (SELECT d.dump_id as id, u.full_name, 'Illegal Dump' as category, z.zone_name, d.severity as urgency_level, d.status, 'dump' as type, d.created_at 
                                   FROM illegal_dumps d 
                                   JOIN users u ON d.citizen_id = u.user_id 
                                   JOIN zones z ON d.zone_id = z.zone_id 
                                   WHERE d.status = 'Reported' OR d.status = 'Under Review')
                                  ORDER BY created_at DESC";
                        
                        $result = mysqli_query($conn, $query);

                        if($result && mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                $isDump = ($row['type'] === 'dump');
                                $badgeClass = ($row['urgency_level'] == 'High' || $row['urgency_level'] == 'Critical') ? 'badge-urgent' : 'badge-low';
                                $typeLabel = $isDump ? "<span style='color:var(--danger); font-size:0.65rem; font-weight:800;'>🚨 CLEANUP</span>" : "<span style='color:var(--accent-blue); font-size:0.65rem; font-weight:800;'>📦 PICKUP</span>";
                                
                                echo "<tr>
                                        <td>#{$row['id']}</td>
                                        <td><strong>{$row['full_name']}</strong><br>{$typeLabel}</td>
                                        <td>{$row['category']}</td>
                                        <td>{$row['zone_name']}</td>
                                        <td><span class='badge {$badgeClass}'>{$row['urgency_level']}</span></td>
                                        <td>
                                            <form action='dispatch.php' method='POST'>
                                                <input type='hidden' name='" . ($isDump ? 'dump_id' : 'request_id') . "' value='{$row['id']}'>
                                                <button type='submit' class='btn-dispatch'>Dispatch Fleet</button>
                                            </form>
                                        </td>
                                      </tr>";
                            }
                        } else { ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 50px; color: #94a3b8;">
                                    <div style="font-size: 2.5rem; margin-bottom: 10px;">✅</div>
                                    <p>All requests processed. Dispatch queue is empty!</p>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="glass-panel" style="text-align: center;">
                <h3>Waste Distribution</h3>
                <div style="position: relative; height: 250px; width: 100%;">
                    <canvas id="wasteChart"></canvas>
                </div>
                <div style="margin-top: 20px; text-align: left;">
                    <h4>Recent Activity</h4>
                    <div style="font-size: 0.8rem; color: #64748b; max-height: 150px; overflow-y: auto;">
                        <?php
                        $activity_query = "(SELECT request_id as id, status, created_at, 'request' as type FROM disposal_requests)
                                            UNION ALL
                                            (SELECT dump_id as id, status, created_at, 'dump' as type FROM illegal_dumps)
                                            ORDER BY created_at DESC LIMIT 5";
                        $activity = mysqli_query($conn, $activity_query);
                        if($activity && mysqli_num_rows($activity) > 0) {
                            while($a = mysqli_fetch_assoc($activity)) {
                                $isDump = ($a['type'] === 'dump');
                                $color = $isDump ? '#ef4444' : '#4ade80';
                                $label = $isDump ? 'DUMP' : 'REQ';
                                echo "<div style='margin-bottom:8px; border-left:3px solid {$color}; padding-left:10px;'>
                                        <strong>#{$a['id']} ({$label})</strong> was {$a['status']}<br>
                                        <small>".date('H:i', strtotime($a['created_at']))."</small>
                                      </div>";
                            }
                        } else {
                            echo "<p style='text-align:center; padding:10px;'>No recent activity.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: Illegal Dump Reports -->
    <div class="tab-content" id="tab-dumps">
        <div class="glass-panel" data-aos="zoom-in">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                <h2 style="margin: 0;">🚨 Illegal Dump Reports</h2>
                <input type="text" id="searchDumps" placeholder="Search dump reports..." 
                       style="padding: 10px 16px; border-radius: 12px; border: 1px solid #e2e8f0; width: 260px;">
            </div>
            <table class="data-table" id="dumpsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Photo/Voice</th>
                        <th>Citizen</th>
                        <th>Zone</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $dump_query = "SELECT d.*, u.full_name, z.zone_name 
                                  FROM illegal_dumps d 
                                  JOIN users u ON d.citizen_id = u.user_id 
                                  JOIN zones z ON d.zone_id = z.zone_id 
                                  ORDER BY d.created_at DESC";
                    $dump_result = mysqli_query($conn, $dump_query);

                    if($dump_result && mysqli_num_rows($dump_result) > 0) {
                        while($d = mysqli_fetch_assoc($dump_result)) {
                            $sevClass = 'dump-severity-' . strtolower($d['severity']);
                            $statClass = 'dump-status-' . strtolower(str_replace(' ', '-', $d['status']));
                            
                            $mediaHtml = $d['image_path'] 
                                ? "<img src='{$d['image_path']}' class='img-thumb' onclick=\"window.open('{$d['image_path']}', '_blank')\">"
                                : '';
                            
                            if (isset($d['voice_note_path']) && $d['voice_note_path']) {
                                $mediaHtml .= "<br><audio controls style='height:20px; width:100px; margin-top:5px;'><source src='{$d['voice_note_path']}' type='audio/webm'></audio>";
                            }

                            if (!$mediaHtml) $mediaHtml = '<span style="color:#94a3b8;">No media</span>';

                            echo "<tr>
                                <td><strong>#{$d['dump_id']}</strong></td>
                                <td>{$mediaHtml}</td>
                                <td><strong>{$d['full_name']}</strong></td>
                                <td>{$d['zone_name']}</td>
                                <td><span class='badge {$sevClass}'>{$d['severity']}</span></td>
                                <td><span class='badge {$statClass}'>{$d['status']}</span></td>
                                <td>
                                    <div style='display: flex; gap: 6px; align-items: center;'>
                                        <form action='update_dump_status.php' method='POST' style='display:flex; gap:6px;'>
                                            <input type='hidden' name='dump_id' value='{$d['dump_id']}'>
                                            <select name='new_status' class='status-select'>
                                                <option value='Reported'".($d['status']=='Reported'?' selected':'').">Reported</option>
                                                <option value='Under Review'".($d['status']=='Under Review'?' selected':'').">Review</option>
                                                <option value='Cleanup Dispatched'".($d['status']=='Cleanup Dispatched'?' selected':'').">Cleanup</option>
                                                <option value='Resolved'".($d['status']=='Resolved'?' selected':'').">Resolved</option>
                                            </select>
                                            <button type='submit' class='btn-update'>✓</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>";
                        }
                    } else { ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 50px; color: #94a3b8;">No illegal dump reports found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: Citizen Feedback -->
    <div class="tab-content" id="tab-feedback">
        <div class="glass-panel" data-aos="zoom-in">
            <h2>💬 Citizen Feedback & Ratings</h2>
            <table class="data-table" id="feedbackTable">
                <thead>
                    <tr>
                        <th>Req ID</th>
                        <th>Citizen</th>
                        <th>Rating</th>
                        <th>Comments</th>
                        <th>Media</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $f_query = "SELECT f.*, u.full_name, r.category 
                                FROM feedback f 
                                JOIN disposal_requests r ON f.request_id = r.request_id 
                                JOIN users u ON r.citizen_id = u.user_id 
                                ORDER BY f.created_at DESC";
                    $f_result = mysqli_query($conn, $f_query);
                    if ($f_result && mysqli_num_rows($f_result) > 0) {
                        while($f = mysqli_fetch_assoc($f_result)) {
                            $stars = str_repeat('⭐', (int)$f['rating']);
                            $name = htmlspecialchars($f['full_name'] ?? 'Citizen');
                            echo "<tr>
                                <td>#{$f['request_id']}</td>
                                <td><strong>{$name}</strong><br><small>{$f['category']}</small></td>
                                <td style='font-size:1.2rem;'>{$stars}</td>
                                <td>{$f['comments']}</td>
                                <td>".(isset($f['voice_feedback_path']) && $f['voice_feedback_path'] ? "🎤 <audio controls style='height:20px; width:80px;'><source src='{$f['voice_feedback_path']}' type='audio/webm'></audio>" : "-")."</td>
                                <td>".date('d M', strtotime($f['created_at']))."</td>
                            </tr>";
                        }
                    } else { ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 50px; color: #94a3b8;">No feedback received yet.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 4: Analytics -->
    <div class="tab-content" id="tab-analytics">
        <!-- KPI Cards -->
        <div class="stats-grid">
            <?php
            $stat_users = getCount($conn, "SELECT COUNT(*) as c FROM users WHERE role='citizen'");
            $stat_reqs = getCount($conn, "SELECT COUNT(*) as c FROM disposal_requests WHERE status='Logged'");
            $stat_dumps = getCount($conn, "SELECT COUNT(*) as c FROM illegal_dumps WHERE status!='Resolved'");
            $stat_rating_res = mysqli_query($conn, "SELECT AVG(rating) as r FROM feedback");
            $stat_rating_data = ($stat_rating_res) ? mysqli_fetch_assoc($stat_rating_res) : null;
            $stat_rating = $stat_rating_data['r'] ?? 0;
            ?>
            <div class="stat-card" data-aos="fade-up">
                <div class="stat-value"><?php echo $stat_users; ?></div>
                <div class="stat-label">Total Citizens</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-value" style="color: var(--accent-blue);"><?php echo $stat_reqs; ?></div>
                <div class="stat-label">Pending Requests</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-value" style="color: var(--danger);"><?php echo $stat_dumps; ?></div>
                <div class="stat-label">Active Dumps</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-value" style="color: #fbbf24;"><?php echo number_format($stat_rating, 1); ?> ⭐</div>
                <div class="stat-label">Avg Citizen Rating</div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="400">
                <?php $total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c']; ?>
                <div class="stat-value" style="color: #6366f1;"><?php echo $total_users; ?></div>
                <div class="stat-label">System Users</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 30px;">
            <div class="glass-panel" data-aos="fade-up">
                <h3>📊 Request Status Breakdown</h3>
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
            <div class="glass-panel" data-aos="fade-up" data-aos-delay="100">
                <h3>🚨 Dump Reports by Severity</h3>
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="dumpChart"></canvas>
                </div>
            </div>
            <div class="glass-panel" data-aos="fade-up">
                <h3>📍 Zone Activity Level</h3>
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="zoneChart"></canvas>
                </div>
            </div>
            <div class="glass-panel" data-aos="fade-up" data-aos-delay="100">
                <h3>👥 User Role Distribution</h3>
                <div style="position: relative; height: 300px; width: 100%;">
                    <canvas id="roleChart"></canvas>
                </div>
            </div>
        </div>

        <div style="margin-top: 30px; display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            <div class="glass-panel" data-aos="fade-up">
                <h3>📈 Weekly Collection Trends</h3>
                <div style="position: relative; height: 350px; width: 100%;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
            <div class="glass-panel" data-aos="fade-up">
                <h3>🏆 Top Collectors</h3>
                <div style="max-height: 350px; overflow-y: auto;">
                    <?php
                    $top_collectors = mysqli_query($conn, "SELECT u.full_name, COUNT(f.assignment_id) as tasks 
                                                          FROM users u 
                                                          JOIN fleet_assignments f ON u.user_id = f.collector_id 
                                                          GROUP BY u.user_id 
                                                          ORDER BY tasks DESC LIMIT 5");
                    if ($top_collectors && mysqli_num_rows($top_collectors) > 0) {
                        while($tc = mysqli_fetch_assoc($top_collectors)) {
                            echo "<div style='display:flex; justify-content:space-between; align-items:center; padding:15px; border-bottom:1px solid #f1f5f9;'>
                                    <div>
                                        <div style='font-weight:700;'>{$tc['full_name']}</div>
                                        <div style='font-size:0.75rem; color:#64748b;'>Fleet Driver</div>
                                    </div>
                                    <div style='background:#eff6ff; color:#3b82f6; padding:5px 12px; border-radius:10px; font-weight:800;'>{$tc['tasks']} Tasks</div>
                                  </div>";
                        }
                    } else {
                        echo "<p style='color:#94a3b8; text-align:center; padding:20px;'>No assignments yet.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 5: User Management -->
    <div class="tab-content" id="tab-users">
        <div class="glass-panel" data-aos="zoom-in">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                <h2 style="margin: 0;">👥 User Management</h2>
                <input type="text" id="searchUsers" placeholder="Search users by name, email or role..." 
                       style="padding: 10px 16px; border-radius: 12px; border: 1px solid #e2e8f0; width: 300px;">
            </div>
            <table class="data-table" id="usersTable">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $users_query = "SELECT * FROM users ORDER BY created_at DESC";
                    $users_result = mysqli_query($conn, $users_query);

                    if($users_result && mysqli_num_rows($users_result) > 0) {
                        while($u = mysqli_fetch_assoc($users_result)) {
                            $is_self = ($u['user_id'] == $_SESSION['user_id']);
                            $role_color = ($u['role'] == 'admin' ? '#3b82f6' : ($u['role'] == 'collector' ? '#f59e0b' : '#10b981'));
                            
                            echo "<tr>
                                <td><strong>#{$u['user_id']}</strong></td>
                                <td><strong>{$u['full_name']}</strong>".($is_self ? " <span class='badge' style='background:#e2e8f0; color:#475569;'>You</span>" : "")."</td>
                                <td>{$u['email']}</td>
                                <td><span class='badge' style='background:".str_replace(')', ', 0.1)', str_replace('rgb', 'rgba', $role_color))."; color:{$role_color};'>".ucfirst($u['role'])."</span></td>
                                <td>".date('d M Y', strtotime($u['created_at']))."</td>
                                <td>";
                            
                            if (!$is_self) {
                                echo "<button onclick='removeUser({$u['user_id']}, \"".addslashes($u['full_name'])."\")' class='btn-update' style='background:#fee2e2; color:#ef4444;'>Remove</button>";
                            } else {
                                echo "<span style='color:#94a3b8; font-size:0.8rem;'>Protected</span>";
                            }
                            
                            echo "</td>
                            </tr>";
                        }
                    } else { ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 50px; color: #94a3b8;">No users found.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    let charts = {};

    function switchTab(tab, event) {
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
        const target = document.getElementById('tab-' + tab);
        if (target) target.classList.add('active');
        if (event) event.currentTarget.classList.add('active');
        
        if (tab === 'analytics' || tab === 'dispatch') {
            setTimeout(() => {
                if (typeof initCharts === 'function') initCharts();
            }, 150);
        }
    }

    function initCharts() {
        // Destroy existing to avoid overlap
        Object.keys(charts).forEach(key => {
            if (charts[key]) charts[key].destroy();
        });
        charts = {};

        const wasteCtx = document.getElementById('wasteChart');
        if (wasteCtx) {
            charts.waste = new Chart(wasteCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Organic', 'Recyclable', 'Hazardous', 'Bulk'],
                    datasets: [{
                        data: [<?php 
                            $types = ['Organic', 'Recyclable', 'Hazardous', 'Bulk'];
                            foreach($types as $t) {
                                echo getCount($conn, "SELECT COUNT(*) as c FROM disposal_requests WHERE category='$t'") . ",";
                            }
                        ?>],
                        backgroundColor: ['#4ade80', '#60a5fa', '#f87171', '#fbbf24'],
                        borderWidth: 0
                    }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
        }

        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            charts.status = new Chart(statusCtx, {
                type: 'bar',
                data: {
                    labels: ['Logged', 'Dispatched', 'Resolved'],
                    datasets: [{
                        label: 'Requests',
                        data: [<?php 
                            foreach(['Logged', 'Dispatched', 'Resolved'] as $s) {
                                echo getCount($conn, "SELECT COUNT(*) as c FROM disposal_requests WHERE status='$s'") . ",";
                            }
                        ?>],
                        backgroundColor: ['#fbbf24', '#60a5fa', '#4ade80'],
                        borderRadius: 12
                    }]
                },
                options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
            });
        }

        const dumpCtx = document.getElementById('dumpChart');
        if (dumpCtx) {
            charts.dump = new Chart(dumpCtx, {
                type: 'polarArea',
                data: {
                    labels: ['Low', 'Medium', 'High', 'Critical'],
                    datasets: [{
                        data: [<?php 
                            foreach(['Low', 'Medium', 'High', 'Critical'] as $s) {
                                echo getCount($conn, "SELECT COUNT(*) as c FROM illegal_dumps WHERE severity='$s'") . ",";
                            }
                        ?>],
                        backgroundColor: ['rgba(74,222,128,0.6)', 'rgba(251,191,36,0.6)', 'rgba(249,115,22,0.6)', 'rgba(239,68,68,0.6)']
                    }]
                },
                options: { maintainAspectRatio: false }
            });
        }

        const zoneCtx = document.getElementById('zoneChart');
        if (zoneCtx) {
            charts.zone = new Chart(zoneCtx, {
                type: 'bar',
                data: {
                    labels: [<?php 
                        $zones = mysqli_query($conn, "SELECT zone_name FROM zones");
                        while($z = mysqli_fetch_assoc($zones)) echo "'".$z['zone_name']."',";
                    ?>],
                    datasets: [{
                        label: 'Total Reports',
                        data: [<?php 
                            $zones = mysqli_query($conn, "SELECT zone_id FROM zones");
                            if($zones) {
                                while($z = mysqli_fetch_assoc($zones)) {
                                    $c = getCount($conn, "SELECT (SELECT COUNT(*) FROM disposal_requests WHERE zone_id=".$z['zone_id'].") + (SELECT COUNT(*) FROM illegal_dumps WHERE zone_id=".$z['zone_id'].") as c");
                                    echo (int)$c . ",";
                                }
                            }
                        ?>],
                        backgroundColor: '#60a5fa',
                        borderRadius: 8
                    }]
                },
                options: { 
                    maintainAspectRatio: false, 
                    indexAxis: 'y',
                    scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } 
                }
            });
        }

        const roleCtx = document.getElementById('roleChart');
        if (roleCtx) {
            charts.role = new Chart(roleCtx, {
                type: 'pie',
                data: {
                    labels: ['Citizen', 'Driver', 'Admin'],
                    datasets: [{
                        data: [<?php 
                            foreach(['citizen', 'collector', 'admin'] as $r) {
                                echo getCount($conn, "SELECT COUNT(*) as c FROM users WHERE role='$r'") . ",";
                            }
                        ?>],
                        backgroundColor: ['#4ade80', '#fbbf24', '#3b82f6'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
        }

        const trendCtx = document.getElementById('trendChart');
        if (trendCtx) {
            charts.trend = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: [<?php 
                        for($i=6; $i>=0; $i--) {
                            echo "'" . date('d M', strtotime("-$i days")) . "',";
                        }
                    ?>],
                    datasets: [{
                        label: 'Service Requests',
                        data: [<?php 
                            for($i=6; $i>=0; $i--) {
                                $date = date('Y-m-d', strtotime("-$i days"));
                                echo getCount($conn, "SELECT COUNT(*) as c FROM disposal_requests WHERE DATE(created_at)='$date'") . ",";
                            }
                        ?>],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'Dump Reports',
                        data: [<?php 
                            for($i=6; $i>=0; $i--) {
                                $date = date('Y-m-d', strtotime("-$i days"));
                                echo getCount($conn, "SELECT COUNT(*) as c FROM illegal_dumps WHERE DATE(created_at)='$date'") . ",";
                            }
                        ?>],
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: { 
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });
        }

        // Enhanced GSAP Animations for Analytics
        gsap.from(".stat-card", {
            duration: 1,
            y: 50,
            opacity: 0,
            stagger: 0.15,
            ease: "power4.out"
        });
    }

    // Initialization & Search Filtering
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init();
        initCharts();

        function initSearch(inputId, tableId) {
            const input = document.getElementById(inputId);
            const table = document.getElementById(tableId);
            if (!input || !table) return;

            input.addEventListener('input', function() {
                const filter = this.value.toLowerCase().trim();
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });
        }

        initSearch('searchInput', 'dispatchTable');
        initSearch('searchDumps', 'dumpsTable');
        initSearch('searchUsers', 'usersTable');
        initSearch('searchFeedback', 'feedbackTable');
    });

    function removeUser(userId, userName) {
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to remove user "${userName}". This action cannot be undone and will delete all their associated data!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, remove them!',
            background: 'rgba(255, 255, 255, 0.95)',
            backdrop: `rgba(15, 23, 42, 0.4) blur(4px)`
        }).then((result) => {
            if (result.isConfirmed) {
                // Send removal request
                fetch('remove_user.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Removed!',
                            text: 'User has been successfully removed.',
                            icon: 'success',
                            confirmButtonColor: '#4ade80'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Failed to remove user.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'An unexpected error occurred.', 'error');
                });
            }
        });
    }

    function downloadReport() {
        const { jspdf } = window;
        let PDF;
        
        if (jspdf && jspdf.jsPDF) {
            PDF = jspdf.jsPDF;
        } else if (window.jsPDF) {
            PDF = window.jsPDF;
        }

        if (!PDF) {
            Swal.fire('Library Not Found', 'The PDF generation library is still loading. Please wait a second and try again.', 'info');
            return;
        }

        const doc = new PDF();
        doc.setFontSize(22);
        doc.text("UrbanFlow - Smart City System Report", 20, 20);
        doc.setFontSize(12);
        doc.text("Generated on: " + new Date().toLocaleString(), 20, 30);
        
        doc.text("--------------------------------------------------", 20, 35);
        
        doc.text("System Statistics:", 20, 45);
        <?php 
            $q1 = mysqli_query($conn, "SELECT COUNT(*) as c FROM disposal_requests");
            $c1 = mysqli_fetch_assoc($q1)['c'] ?? 0;
            $q2 = mysqli_query($conn, "SELECT COUNT(*) as c FROM illegal_dumps WHERE status != 'Resolved'");
            $c2 = mysqli_fetch_assoc($q2)['c'] ?? 0;
            $q3 = mysqli_query($conn, "SELECT COUNT(*) as c FROM fleet_assignments");
            $c3 = mysqli_fetch_assoc($q3)['c'] ?? 0;
        ?>
        doc.text("- Total Disposal Requests: <?php echo $c1; ?>", 25, 52);
        doc.text("- Active Illegal Dumps: <?php echo $c2; ?>", 25, 59);
        doc.text("- Fleet Assignments: <?php echo $c3; ?>", 25, 66);
        doc.text("- Citizen Satisfaction: <?php echo number_format($stat_rating, 1); ?>/5.0 ⭐", 25, 73);
        
        doc.text("Area Activity Breakdown:", 20, 87);
        <?php 
            $areas = mysqli_query($conn, "SELECT z.zone_name, 
                        (SELECT COUNT(*) FROM disposal_requests WHERE zone_id=z.zone_id) + 
                        (SELECT COUNT(*) FROM illegal_dumps WHERE zone_id=z.zone_id) as c 
                        FROM zones z");
            $y = 94;
            while($row = mysqli_fetch_assoc($areas)) {
                echo "doc.text('- " . addslashes($row['zone_name']) . ": " . $row['c'] . " total reports', 25, $y);\n";
                $y += 7;
            }
        ?>

        doc.save("UrbanFlow_System_Report.pdf");
    }
</script>

<!-- Add jsPDF for report generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<?php if(isset($_GET['dispatched'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Dispatched!',
            text: 'Fleet has been assigned to the request.',
            icon: 'success',
            confirmButtonColor: '#3b82f6',
            background: 'rgba(255,255,255,0.95)',
            backdrop: 'rgba(0,0,0,0.1)'
        });
    });
</script>
<?php endif; ?>

<?php if(isset($_GET['dump_updated'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Updated!',
            text: 'Dump report status has been updated.',
            icon: 'success',
            confirmButtonColor: '#4ade80',
            background: 'rgba(255,255,255,0.95)',
            backdrop: 'rgba(0,0,0,0.1)'
        });
    });
</script>
<?php endif; ?>

<?php include('includes/footer.php'); ?>