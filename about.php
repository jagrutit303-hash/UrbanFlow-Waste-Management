<?php include('includes/header.php'); ?>

<style>
/* Modern Card Styling */
.about-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 25px;
    padding: 35px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    cursor: pointer;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

/* Hover Effect: Turns Green and Pops Up */
.about-card:hover {
    transform: translateY(-15px);
    background: #4ade80; /* Changes to UrbanFlow Green */
    color: white !important;
    box-shadow: 0 25px 50px rgba(74, 222, 128, 0.3);
}

.about-card:hover h3, .about-card:hover p {
    color: white !important;
}
</style>

<!-- Section 1: Our Mission -->
<div class="container" style="padding: 100px 10%; text-align: center;">
    <h1 data-aos="fade-down" style="font-size: 3.5rem; font-weight: 800;">Our <span style="color:var(--primary)">Mission</span></h1>
    <p data-aos="fade-up" style="max-width: 700px; margin: 20px auto; color: #64748b; font-size: 1.2rem;">
        UrbanFlow is more than just a waste tracker. It's a digital twin for modern cities, designed to bridge the gap between citizens and municipal efficiency using real-time data.
    </p>
</div>

<!-- Section 2: Impact Grid -->
<div style="background: rgba(241, 245, 249, 0.5); padding: 80px 10%;">
    <div style="display: flex; gap: 30px; flex-wrap: wrap; justify-content: center;">
        <div class="about-card" style="width: 320px;" data-aos="zoom-in">
            <h3 style="margin-top: 0;">Our Mission 🎯</h3>
            <p style="font-size: 0.9rem; color: #64748b;">To digitize waste management and create a cleaner, smarter urban environment for everyone.</p>
        </div>
        
        <div class="about-card" style="width: 320px;" data-aos="zoom-in" data-aos-delay="200">
            <h3 style="margin-top: 0;">BIET Innovation 🎓</h3>
            <p style="font-size: 0.9rem; color: #64748b;">Developed by 2nd-year CSE students, bridging the gap between DBMS theory and real-world impact.</p>
        </div>
    </div>
</div>

<!-- Section 3: Contact Form -->
<div class="container" style="padding: 100px 10%; display: grid; grid-template-columns: 1fr 1fr; gap: 50px;">
    <div data-aos="fade-right">
        <h2 style="font-size: 2.5rem;">Have a <span style="color:var(--primary)">Question?</span></h2>
        <p style="line-height: 1.6; color: #64748b;">Our team at Bapuji Institute of Engineering and Technology is always ready to help optimize your ward's collection schedule.</p>
        <div style="margin-top: 30px;">
            <p><strong>📍 Location:</strong> Davangere, Karnataka</p>
            <p><strong>📧 Email:</strong> support@urbanflow.com</p>
        </div>
    </div>

    <div class="glass-card" data-aos="fade-left" style="padding: 40px;">
        <!-- Ensure the 'action' points to your processing file -->
        <form action="contact_process.php" method="POST">
            <div style="margin-bottom: 15px;">
                <input type="text" name="name" placeholder="Your Name" required style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd;">
            </div>
            <div style="margin-bottom: 15px;">
                <textarea name="message" placeholder="Ask UrbanBot anything..." required style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd; height: 100px;"></textarea>
            </div>
            <button type="submit" style="background: #4ade80; color: white; padding: 12px 30px; border: none; border-radius: 10px; cursor: pointer; font-weight: bold; width: 100%;">
                Send Message
            </button>
        </form>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<!-- SweetAlert for Form Success -->
<?php if (isset($_GET['success'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        title: 'Message Sent! 🚀',
        text: 'Thanks, <?php echo htmlspecialchars($_GET['name']); ?>! UrbanBot will get back to you soon.',
        icon: 'success',
        confirmButtonColor: '#4ade80',
        background: '#ffffff',
        borderRadius: '20px'
    });
    // Clean up the URL
    window.history.replaceState({}, document.title, "about.php");
</script>
<?php endif; ?>
