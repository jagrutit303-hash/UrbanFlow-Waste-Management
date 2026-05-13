<?php include('chatbot.php'); ?>
<footer style="text-align: center; padding: 50px; color: #94a3b8; font-size: 0.9rem;">
        &copy; 2026 UrbanFlow Digital Twin Project | Built for DBMS Mini-Project
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1000 });
        
        // More robust Page Loader Script
        function hideLoader() {
            const loader = document.getElementById("page-loader");
            if (loader) {
                if (window.gsap) {
                    const tl = gsap.timeline();
                    tl.to("#loader-text", { opacity: 1, y: -20, duration: 0.5 })
                      .to("#page-loader", { opacity: 0, pointerEvents: "none", duration: 0.8, delay: 0.2, onComplete: () => loader.style.display = 'none' });
                } else {
                    loader.style.display = 'none';
                }
            }
        }

        window.addEventListener('load', hideLoader);
        document.addEventListener('DOMContentLoaded', hideLoader);
        // Safety timeout in case load events are blocked
        setTimeout(hideLoader, 3000);

        // Floating animation for truck icons in Map
        if (document.querySelector(".truck-icon")) {
            gsap.to(".truck-icon", {
                y: "random(-20, 20)",
                duration: "random(2, 4)",
                repeat: -1,
                yoyo: true,
                ease: "sine.inOut"
            });
        }

        // GSAP Parallax Scrolling
        window.addEventListener('scroll', () => {
            let value = window.scrollY;
            // Background orbs move slower than scroll to create depth
            gsap.to(".orb", {
                y: value * 0.3,
                duration: 0.5,
                ease: "none"
            });
        });

        // Add a cursor glow effect
        const glow = document.createElement('div');
        glow.style.cssText = "position:fixed; width:400px; height:400px; background:radial-gradient(circle, rgba(74,222,128,0.08) 0%, transparent 70%); border-radius:50%; pointer-events:none; z-index:0; transform:translate(-50%, -50%); transition: 0.1s;";
        document.body.appendChild(glow);

        document.addEventListener('mousemove', (e) => {
            glow.style.left = e.clientX + 'px';
            glow.style.top = e.clientY + 'px';
        });
    </script>
</body>
</html>