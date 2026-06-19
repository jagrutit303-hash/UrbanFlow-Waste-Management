<?php 
require_once(__DIR__ . '/includes/auth_check.php');
$isLoggedIn = isset($_SESSION['user_id']);
$dashboardLink = 'dashboard.php';
if ($isLoggedIn) {
    if ($_SESSION['role'] == 'admin') $dashboardLink = 'admin.php';
    elseif ($_SESSION['role'] == 'collector') $dashboardLink = 'driver.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UrbanFlow | Future of Waste Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4ade80;
            --secondary: #6366f1;
            --accent: #3b82f6;
            --text-main: #1e293b;
            --bg-gradient: linear-gradient(135deg, #ffffff 0%, #eff6ff 100%);
        }

        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-gradient);
            color: var(--text-main);
            overflow-x: hidden;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem 5%;
            position: relative;
            z-index: 10;
        }

        .hero {
            height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
        }

        .action-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            padding: 60px;
            border-radius: 40px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05);
            max-width: 600px;
        }

        .action-card h1 {
            font-size: 4rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -2px;
        }

        .action-card p {
            color: #64748b;
            font-size: 1.2rem;
            margin: 20px 0 40px;
        }

        .btn-premium {
            background: var(--text-main);
            color: white;
            padding: 18px 36px;
            border-radius: 20px;
            border: none;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-decoration: none;
            display: inline-block;
        }

        .btn-premium:hover {
            transform: scale(1.05) translateY(-5px);
            background: var(--primary);
            box-shadow: 0 20px 40px rgba(74, 222, 128, 0.3);
        }

        .floating-orb {
            position: absolute;
            border-radius: 50%;
            background: rgba(74, 222, 128, 0.15);
            filter: blur(100px);
            z-index: 0;
        }

        /* Glassmorphism Navigation Styling */
        .navbar a {
            text-decoration: none;
            color: var(--text-main);
            font-weight: 600;
            padding: 10px 25px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            transition: 0.3s;
        }
        .navbar a:hover {
            background: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<div id="page-loader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 9999; display: flex; align-items: center; justify-content: center;">
    <h1 style="font-weight: 800; opacity: 0; font-family: 'Plus Jakarta Sans', sans-serif;" id="loader-text">
        URBAN<span style="color:#4ade80">FLOW</span>
    </h1>
</div>

<div class="antigravity-layer">
    <div class="floating-orb" style="width: 500px; height: 500px; top: -100px; left: -100px;"></div>
    <div class="floating-orb" style="width: 600px; height: 600px; bottom: -200px; right: -200px; background: rgba(59, 130, 246, 0.1);"></div>
</div>

<nav class="navbar">
    <div style="font-weight: 800; font-size: 1.8rem; letter-spacing: -1px;">URBAN<span style="color: var(--primary);">FLOW</span></div>
    <div>
        <?php if($isLoggedIn): ?>
            <a href="<?php echo $dashboardLink; ?>">Dashboard →</a>
        <?php else: ?>
            <a href="login.php">Sign In</a>
        <?php endif; ?>
    </div>
</nav>

<section class="hero">
    <div class="action-card" data-aos="zoom-in-up">
        <h1>Clean <span style="color: var(--primary);">Living.</span></h1>
        <p>Experience the next generation of smart city waste management. Track, manage, and resolve collection in real-time.</p>
        <div style="display: flex; gap: 15px; justify-content: center;">
            <?php if($isLoggedIn): ?>
                <a href="<?php echo $dashboardLink; ?>" class="btn-premium">Go to Dashboard</a>
                <a href="logout.php" class="btn-premium" style="background: rgba(255,255,255,0.5); color: #1e293b; border: 1px solid rgba(0,0,0,0.1);">Logout</a>
            <?php else: ?>
                <a href="register.php" class="btn-premium">Register</a>
                <a href="login.php" class="btn-premium" style="background: rgba(255,255,255,0.5); color: #1e293b; border: 1px solid rgba(0,0,0,0.1);">Sign In</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script>
    AOS.init({ duration: 1000 });
    
    // Antigravity Animation Logic
    gsap.to(".floating-orb", {
        y: "random(-100, 100)",
        x: "random(-50, 50)",
        duration: 8,
        repeat: -1,
        yoyo: true,
        ease: "sine.inOut"
    });

    // Parallax effect for the hero card
    document.addEventListener("mousemove", (e) => {
        const x = (e.clientX / window.innerWidth - 0.5) * 20;
        const y = (e.clientY / window.innerHeight - 0.5) * 20;
        gsap.to(".action-card", {
            rotationY: x,
            rotationX: -y,
            duration: 1,
            ease: "power2.out"
        });
    });

    // Robust Page Loader Script
    function hideLoader() {
        const loader = document.getElementById("page-loader");
        if (loader && loader.style.opacity !== "0") {
            const tl = gsap.timeline();
            tl.to("#loader-text", { opacity: 1, y: -20, duration: 0.5 })
              .to("#page-loader", { opacity: 0, pointerEvents: "none", duration: 0.8, delay: 0.2 });
        }
    }

    window.addEventListener('load', hideLoader);
    document.addEventListener('DOMContentLoaded', hideLoader);
    setTimeout(hideLoader, 3000);
</script>
<?php include('includes/footer.php'); ?>