<?php
// includes/footer.php
?>
    </div><!-- end .container -->
 
    <svg id="wave" style="margin-bottom: -10px" viewBox="0 0 1440 490" version="1.1" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="sw-gradient-0" x1="0" x2="0" y1="1" y2="0"><stop stop-color="rgba(35, 35, 35, 1)" offset="0%"></stop><stop stop-color="rgba(35, 35, 35, 1)" offset="100%"></stop></linearGradient></defs><path style="transform:translate(0, 0px); opacity:1" fill="url(#sw-gradient-0)" d="M0,392L80,367.5C160,343,320,294,480,302.2C640,310,800,376,960,359.3C1120,343,1280,245,1440,212.3C1600,180,1760,212,1920,245C2080,278,2240,310,2400,269.5C2560,229,2720,114,2880,122.5C3040,131,3200,261,3360,261.3C3520,261,3680,131,3840,114.3C4000,98,4160,196,4320,212.3C4480,229,4640,163,4800,179.7C4960,196,5120,294,5280,285.8C5440,278,5600,163,5760,98C5920,33,6080,16,6240,8.2C6400,0,6560,0,6720,0C6880,0,7040,0,7200,24.5C7360,49,7520,98,7680,171.5C7840,245,8000,343,8160,367.5C8320,392,8480,343,8640,318.5C8800,294,8960,294,9120,302.2C9280,310,9440,327,9600,318.5C9760,310,9920,278,10080,253.2C10240,229,10400,212,10560,196C10720,180,10880,163,11040,204.2C11200,245,11360,343,11440,392L11520,441L11520,490L11440,490C11360,490,11200,490,11040,490C10880,490,10720,490,10560,490C10400,490,10240,490,10080,490C9920,490,9760,490,9600,490C9440,490,9280,490,9120,490C8960,490,8800,490,8640,490C8480,490,8320,490,8160,490C8000,490,7840,490,7680,490C7520,490,7360,490,7200,490C7040,490,6880,490,6720,490C6560,490,6400,490,6240,490C6080,490,5920,490,5760,490C5600,490,5440,490,5280,490C5120,490,4960,490,4800,490C4640,490,4480,490,4320,490C4160,490,4000,490,3840,490C3680,490,3520,490,3360,490C3200,490,3040,490,2880,490C2720,490,2560,490,2400,490C2240,490,2080,490,1920,490C1760,490,1600,490,1440,490C1280,490,1120,490,960,490C800,490,640,490,480,490C320,490,160,490,80,490L0,490Z"></path></svg>
    <footer class="site-footer" style="margin-top: 0px !important;">
        <div class="container">
            <div class="footer-logo">
                <a href="<?php echo url_base(); ?>index">GMIT Elim Dadibira</a>
            </div>
            
            <div class="footer-newsletter">
                <h4>Berlangganan Artikel</h4>
                <form class="newsletter-form">
                    <input type="email" class="newsletter-input" placeholder="Masukkan email Anda" required>
                    <button type="submit" class="newsletter-button">Langganan</button>
                </form>
            </div>
            
            <div class="footer-social">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
                <a href="#"><i class="fab fa-telegram"></i></a>
            </div>
            
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Tentang Kami</h3>
                    <p>GMIT Elim Dadibira adalah gereja yang berada di Klasis Alor Barat Laut (ABAL), tepatnya di Desa Pura Utara, Kecamatan Pulau Pura, Kabupaten Alor, Nusa Tenggara Timur.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Link Cepat</h3>
                    <ul>
                        <li><a href="<?php echo url_base() . 'beranda'; ?>">Beranda</a></li>
                        <li><a href="#">Tentang Kami</a></li>
                        <li><a href="#">Kebijakan Privasi</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Kontak Kami</h3>
                    <p><i class="fas fa-envelope"></i> info@gmitelimdadibira.org</p>
                    <p><i class="fas fa-phone-alt"></i> +62 813 53 893 823</p>
                    <p><i class="fas fa-map-marker-alt"></i> Jl. W. J. Lalamentik No. 12 b, Kalabahi</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> GMIT Elim Dadibira. Hak Cipta Dilindungi.<br>Developed by <a href="https://www.instagram.com/imm.kbb_y01" target="_blank" style="color: #FFA1BE; text-decoration: none;">Ima Kabby</a></p>
            </div>
        </div>
    </footer>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <!-- Custom script -->
    <script src="<?php echo url_base(); ?>assets/js/script.js"></script>  
    <!-- Main JavaScript -->
    <script src="<?php echo url_base() . 'assets/js/main.js?v='.time(); ?>"></script>
 
    <!-- Perbaikan Footer dan AdSense -->
    <style>
    .site-footer {
        bottom: 0;
        width: 100%;
        z-index: 10;
        /* margin-top: 40px; */
    }
    
    .adsbygoogle-container {
        min-height: 90px;
        overflow: visible;
        margin-bottom: 20px;
        position: relative;
        z-index: 1;
    }
    
    .ad-empty {
        display: none;
        height: auto;
        min-height: 90px;
    }
    
    .main-container {
        position: relative;
        z-index: 5;
        min-height: 500px;
    }
    </style>

    <!-- Dark Mode Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Pastikan tema dari localStorage diterapkan dengan benar
        const currentTheme = localStorage.getItem('theme');
        if (currentTheme === 'dark') {
            document.body.classList.add('dark-mode');
            
            // Update toggle switch
            const toggleSwitches = document.querySelectorAll('.theme-switch input[type="checkbox"]');
            toggleSwitches.forEach(function(toggleSwitch) {
                toggleSwitch.checked = true;
            });
            
            // Update ikon sun/moon
            document.querySelectorAll('.slider-icon.sun').forEach(function(sunIcon) {
                sunIcon.style.display = 'none';
            });
            document.querySelectorAll('.slider-icon.moon').forEach(function(moonIcon) {
                moonIcon.style.display = 'inline-block';
            });
        }
        
    });
    </script>

</body>
</html>