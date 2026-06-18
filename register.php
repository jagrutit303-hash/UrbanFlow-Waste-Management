<?php 
include('includes/header.php'); 
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') header("Location: admin.php");
    else header("Location: dashboard.php");
    exit();
}
?>

<div class="registration-wrapper" style="display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 20px;">
    
    <div class="glass-card" style="width: 100%; max-width: 450px; padding: 40px; position: relative;" data-aos="zoom-in">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="font-weight: 800; margin-bottom: 10px;">Join <span style="color:#4ade80">UrbanFlow</span></h2>
            <p style="color: #64748b; font-size: 0.9rem;">Start contributing to a cleaner city today.</p>
        </div>

        <form action="auth_action.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label style="font-size: 0.8rem; font-weight: 600; color: #1e293b;">Full Name</label>
                <input type="text" name="full_name" placeholder="Enter your name" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="font-size: 0.8rem; font-weight: 600; color: #1e293b;">Email Address</label>
                <input type="email" name="email" placeholder="email@example.com" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="font-size: 0.8rem; font-weight: 600; color: #1e293b;">Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="font-size: 0.8rem; font-weight: 600; color: #1e293b;">I am a...</label>
                <select name="role">
                    <option value="citizen">Citizen (Report Waste)</option>
                    <option value="collector">Driver (Collection Team)</option>
                    <option value="admin">Admin (Manage City)</option>
                </select>
            </div>

            <button type="submit" name="register" class="btn-premium" style="width: 100%; padding: 15px; font-size: 1rem;">
                Create Account
            </button>
        </form>

        <p style="text-align: center; margin-top: 25px; font-size: 0.85rem; color: #64748b;">
            Already have an account? <a href="login.php" style="color:#3b82f6; text-decoration: none; font-weight: 600;">Sign In</a>
        </p>
    </div>
</div>

<?php include('includes/footer.php'); ?>
