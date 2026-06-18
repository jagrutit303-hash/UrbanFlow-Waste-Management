<?php
/**
 * login.php
 * session_start() must happen before any HTML output.
 * We handle the redirect check BEFORE including header.php
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect already-logged-in users before any HTML is sent
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin')          header("Location: admin.php");
    elseif ($_SESSION['role'] === 'collector')  header("Location: driver.php");
    else                                         header("Location: dashboard.php");
    exit();
}

include('includes/header.php');
?>

<div class="hero-container" style="display:flex;justify-content:center;align-items:center;min-height:80vh;">
    <div class="glass-card" style="width:400px;padding:50px;" data-aos="zoom-in">
        <h2 style="text-align:center;margin-bottom:10px;">Portal <span style="color:var(--primary)">Login</span></h2>
        <p style="text-align:center;color:#64748b;font-size:0.9rem;margin-bottom:30px;">Access your smart city dashboard</p>

        <?php if (isset($_GET['error'])): ?>
            <div style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:10px;text-align:center;margin-bottom:20px;font-size:0.85rem;">
                ❌ Invalid email or password. Please try again.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['logout'])): ?>
            <div style="background:#f0fdf4;color:#166534;padding:12px;border-radius:10px;text-align:center;margin-bottom:20px;font-size:0.85rem;">
                ✅ Successfully logged out.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['registered'])): ?>
            <div style="background:#f0fdf4;color:#166534;padding:12px;border-radius:10px;text-align:center;margin-bottom:20px;font-size:0.85rem;">
                ✅ Account created! Please login.
            </div>
        <?php endif; ?>

        <form action="auth_action.php" method="POST">
            <input type="email"    name="email"    placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password"      required>
            <button type="submit" name="login" class="btn-premium" style="width:100%;margin-top:20px;">
                Sign In →
            </button>
        </form>

        <p style="text-align:center;margin-top:25px;font-size:0.9rem;">
            New to the city? <a href="register.php" style="color:var(--secondary);text-decoration:none;">Create Account</a>
        </p>
    </div>
</div>

<?php include('includes/footer.php'); ?>