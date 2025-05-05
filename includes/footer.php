<?php
// includes/footer.php
?>
    </div><!-- end .container -->
      
    <footer class="site-footer">
        <div class="container">
            <div class="footer-logo">
                <img src="<?php echo url_base() . 'assets/images/logo.png'; ?>" alt="Logo LangkahKata.com" style="width: 70px; height: 70px;">
                <a href="<?php echo url_base(); ?>index">LangkahKata.</a>
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
                    <p>LangkahKata.com adalah portal berita dan artikel menarik yang menyajikan informasi dari berbagai kategori seperti kesehatan, pendidikan, teknologi, olahraga, dan hiburan.</p>
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
                    <p><i class="fas fa-envelope"></i> info@langkahkata.com</p>
                    <p><i class="fas fa-phone-alt"></i> +62 813 53 893 823</p>
                    <p><i class="fas fa-map-marker-alt"></i> Jl. W. J. Lalamentik No. 12 b, Kalabahi</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> LangkahKata. Hak Cipta Dilindungi.</p>
            </div>
        </div>
    </footer>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <!-- Custom script -->
    <script src="<?php echo url_base(); ?>assets/js/script.js"></script>

    <!-- AdSense Fix Script -->
    <script>
    (function() {
        // Deteksi AdBlocker
        function detectAdBlocker() {
            return new Promise(resolve => {
                const testAd = document.createElement('div');
                testAd.className = 'adsbox';
                testAd.style.cssText = 'position:absolute;opacity:0;pointer-events:none;z-index:-1;';
                document.body.appendChild(testAd);
                setTimeout(function() {
                    const terdeteksi = testAd.offsetHeight === 0;
                    testAd.remove();
                    resolve(terdeteksi);
                }, 100);
            });
        }

        // Perbaiki container iklan kosong
        function fixEmptyAdContainers() {
            document.querySelectorAll('.adsbygoogle-container').forEach(container => {
                const adElement = container.querySelector('.adsbygoogle');
                if (!adElement || adElement.clientHeight < 10) {
                    container.style.minHeight = '90px';
                    container.style.height = 'auto';
                    container.style.overflow = 'hidden';
                    container.style.marginBottom = '20px';
                    container.classList.add('ad-empty');
                }
            });
        }

        // Reload iklan jika gagal dimuat
        function reloadAdsIfNeeded() {
            document.querySelectorAll('.adsbygoogle').forEach(element => {
                if ((element.innerHTML.trim() === '' || element.clientHeight < 10) && !element.dataset.reloaded) {
                    try {
                        (adsbygoogle = window.adsbygoogle || []).push({});
                        element.dataset.reloaded = "1";
                    } catch (e) {
                        const parentContainer = element.closest('.adsbygoogle-container');
                        if (parentContainer) fixEmptyAdContainers();
                    }
                }
            });
        }

        // Perbaiki posisi footer
        function fixFooterPosition() {
            const footer = document.querySelector('.site-footer');
            const main = document.querySelector('.main-container');
            if (!footer) return;
            const bodyHeight = document.body.offsetHeight;
            const windowHeight = window.innerHeight;
            if (bodyHeight < windowHeight) {
                footer.style.position = 'fixed';
                footer.style.bottom = '0';
                footer.style.left = '0';
                footer.style.width = '100%';
                if (main) main.style.paddingBottom = footer.offsetHeight + 'px';
            } else {
                footer.style.position = 'relative';
                if (main) main.style.paddingBottom = '';
            }
        }

        // Event saat window load
        window.addEventListener('load', async function() {
            const hasAdBlocker = await detectAdBlocker();
            if (hasAdBlocker) {
                console.log('AdBlocker terdeteksi');
                fixEmptyAdContainers();
            } else {
                setTimeout(reloadAdsIfNeeded, 2000);
                setTimeout(fixEmptyAdContainers, 3000);
            }
            setTimeout(function() {
                const mainContainer = document.querySelector('.main-container');
                if (mainContainer) {
                    mainContainer.style.minHeight = '500px';
                    mainContainer.style.marginBottom = '20px';
                }
                document.querySelectorAll('.article-card, .single-article').forEach(article => {
                    article.style.overflow = 'hidden';
                });
                fixFooterPosition();
            }, 1000);
        });

        // Perbaiki posisi footer pada resize dan scroll
        window.addEventListener('resize', fixFooterPosition);
        window.addEventListener('scroll', function() {
            if (!window.footerThrottleTimeout) {
                window.footerThrottleTimeout = setTimeout(function() {
                    fixFooterPosition();
                    window.footerThrottleTimeout = null;
                }, 100);
            }
        });
    })();
    </script>
    
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
        
        // Add a class when the page has finished loading
        // setTimeout(function(){
        //     document.body.classList.add('theme-transition-complete');
        // }, 300);
    });
    </script>

</body>
</html>