<?php
/**
 * register.php
 * session_start() and redirect check before any HTML output
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') header("Location: admin.php");
    else header("Location: dashboard.php");
    exit();
}

include('includes/header.php');
?>

<div class="registration-wrapper" style="display:flex;justify-content:center;align-items:center;min-height:80vh;padding:20px;">
    <div class="glass-card" style="width:100%;max-width:450px;padding:40px;position:relative;" data-aos="zoom-in">

        <div style="text-align:center;margin-bottom:30px;">
            <h2 style="font-weight:800;margin-bottom:10px;">Join <span style="color:#4ade80">UrbanFlow</span></h2>
            <p style="color:#64748b;font-size:0.9rem;">Start contributing to a cleaner city today.</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:10px;text-align:center;margin-bottom:20px;font-size:0.85rem;">
                <?php
                    $err = $_GET['error'];
                    if ($err === 'email_taken')  echo '❌ This email is already registered. <a href="login.php">Login instead?</a>';
                    elseif ($err === 'db_error') echo '❌ Registration failed. Please try again.';
                    else                          echo '❌ An error occurred.';
                ?>
            </div>
        <?php endif; ?>

        <form action="auth_action.php" method="POST">
            <div style="margin-bottom:15px;">
                <label style="font-size:0.8rem;font-weight:600;color:#1e293b;">Full Name</label>
                <input type="text"     name="full_name" placeholder="Enter your name"     required>
            </div>
            <div style="margin-bottom:15px;">
                <label style="font-size:0.8rem;font-weight:600;color:#1e293b;">Email Address</label>
                <input type="email"    name="email"     placeholder="email@example.com"    required>
            </div>
            <div style="margin-bottom:15px;">
                <label style="font-size:0.8rem;font-weight:600;color:#1e293b;">Password</label>
                <input type="password" name="password"  placeholder="Min. 6 characters"    required minlength="6">
            </div>
            <div style="margin-bottom:25px;">
                <label style="font-size:0.8rem;font-weight:600;color:#1e293b;">I am a...</label>
                <select name="role">
                    <option value="citizen">Citizen (Report Waste)</option>
                    <option value="collector">Driver (Collection Team)</option>
                    <option value="admin">Admin (Manage City)</option>
                </select>
            </div>
            <button type="submit" name="register" class="btn-premium" style="width:100%;padding:15px;font-size:1rem;">
                Create Account →
            </button>
        </form>

        <p style="text-align:center;margin-top:25px;font-size:0.85rem;color:#64748b;">
            Already have an account? <a href="login.php" style="color:#3b82f6;text-decoration:none;font-weight:600;">Sign In</a>
        </p>
    </div>
</div>

<?php include('includes/footer.php'); ?>
