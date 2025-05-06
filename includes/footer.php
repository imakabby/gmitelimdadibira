<?php
// includes/footer.php
?>
    </div><!-- end .container -->
      
    <footer class="site-footer">
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
                <p>&copy; <?php echo date('Y'); ?> GMIT Elim Dadibira. Hak Cipta Dilindungi | Developed by <a href="https://www.instagram.com/imm.kbb_y01" target="_blank" style="color: #fff; text-decoration: none; font-weight: bold;">Ima Kabby</a></p>
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
        margin-top: 40px;
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