<?php 
ob_start();
include('config.php'); 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Leaflet Mapping Engine -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- Leaflet Geocoder (Google Style Search) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <style>
        :root {
            --primary: #4ade80;
            --secondary: #6366f1;
            --accent: #3b82f6;
            --danger: #ef4444;
            --dark: #1e293b;
            --bg-glass: rgba(255, 255, 255, 0.75);
        }

        /* Modern Navbar Styling */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 5%;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(15px);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(255,255,255,0.3);
        }
        .nav-links a {
            margin-left: 25px;
            text-decoration: none;
            color: #1e293b;
            font-weight: 600;
            transition: 0.3s;
        }
        .nav-links a:hover { color: #4ade80; }

        /* Premium Form Controls */
        input[type="text"], input[type="email"], input[type="password"], input[type="number"], select, textarea {
            width: 100%;
            padding: 14px 18px;
            margin: 10px 0 20px 0;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.95rem;
            color: #1e293b;
            transition: all 0.3s ease;
            box-sizing: border-box;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }

        input::placeholder, textarea::placeholder { color: #94a3b8; }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #4ade80;
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 0 0 0 4px rgba(74, 222, 128, 0.2);
        }

        textarea { resize: vertical; min-height: 100px; }

        /* Responsive Grid Fix */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        @media (max-width: 768px) {
            .navbar { padding: 1rem 5%; }
            .nav-links { display: none; }
        }

        /* --- UI/UX ENHANCEMENTS --- */
        .btn-premium {
            background: linear-gradient(135deg, var(--primary), #059669);
            color: white !important;
            padding: 10px 24px;
            border-radius: 50px;
            border: none;
            font-weight: 700;
            text-decoration: none;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(74, 222, 128, 0.2);
            display: inline-block;
        }
        .btn-premium:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(74, 222, 128, 0.3); }

        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .glass-card:hover { transform: scale(1.01); box-shadow: 0 25px 50px rgba(0,0,0,0.08); }

        /* --- GOOGLE MAPS STYLE SEARCH BAR --- */
        .leaflet-top.leaflet-left {
            top: 20px !important;
            left: 20px !important;
            width: 80% !important;
            max-width: 450px !important;
        }
        .leaflet-control-geocoder {
            background: white !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important;
            border: none !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 5px !important;
            display: flex !important;
            align-items: center !important;
        }
        .leaflet-control-geocoder-form {
            width: 100% !important;
            display: flex !important;
        }
        .leaflet-control-geocoder-form input {
            width: 100% !important;
            border: none !important;
            padding: 12px 15px !important;
            font-size: 1rem !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            font-weight: 600 !important;
            color: #1e293b !important;
            background: transparent !important;
            margin: 0 !important;
            box-shadow: none !important;
        }
        .leaflet-control-geocoder-icon {
            width: 44px !important;
            height: 44px !important;
            background-color: white !important;
            border-radius: 10px !important;
            background-size: 20px 20px !important;
            order: -1 !important; /* Move icon to left */
            border: none !important;
        }
        .leaflet-control-geocoder-results {
            width: 100% !important;
            border-radius: 12px !important;
            margin-top: 10px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
            border: 1px solid #e2e8f0 !important;
            background: white !important;
            max-height: 250px !important;
            overflow-y: auto !important;
        }
        .leaflet-control-geocoder-results div {
            padding: 12px 15px !important;
            border-bottom: 1px solid #f1f5f9 !important;
            font-size: 0.9rem !important;
            cursor: pointer !important;
        }
        .leaflet-control-geocoder-results div:hover {
            background: #f8fafc !important;
            color: #10b981 !important;
        }

        /* Prevent Overlap - Keep map contained but results floating */
        .leaflet-container {
            overflow: hidden !important;
            position: relative;
            z-index: 1;
        }
        .leaflet-control-geocoder-results {
            position: absolute !important;
            z-index: 9999 !important;
            background: white !important;
        }
    </style>
</head>
<body>
    <div id="page-loader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 9999; display: flex; align-items: center; justify-content: center;">
        <h1 style="font-weight: 800; opacity: 0; font-family: 'Plus Jakarta Sans';" id="loader-text">URBAN<span style="color:#4ade80">FLOW</span></h1>
    </div>

    <nav class="navbar">
        <div style="font-weight: 800; font-size: 1.4rem;">URBAN<span style="color:#4ade80">FLOW</span></div>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="map.php">Live Map</a>
            <a href="about.php">About</a>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if($_SESSION['role'] == 'admin'): ?>
                    <a href="admin.php" style="color: #3b82f6;">Admin Panel</a>
                <?php elseif($_SESSION['role'] == 'collector'): ?>
                    <a href="driver.php" style="color: #f59e0b;">Driver Portal</a>
                <?php else: ?>
                    <a href="dashboard.php">My Portal</a>
                    <a href="report_dump.php" style="color: #ef4444;">🚨 Report Dump</a>
                    <a href="feedback.php">Feedback</a>
                <?php endif; ?>
                <a href="logout.php" style="color: #f87171;">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php" class="btn-premium" style="padding: 8px 20px; color: white;">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>