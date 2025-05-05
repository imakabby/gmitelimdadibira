<?php
// includes/header.php
if (!function_exists('start_session')) {
    require_once 'includes/functions.php';
}
start_session();
?><!DOCTYPE html>
<html lang="id" <?php echo (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'class="dark-mode"' : ''; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-adsense-account" content="ca-pub-7436399062257055">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    
    <!-- Style untuk mencegah transisi pada awal load -->
    <style>
        .no-transition, .no-transition * {
            transition: none !important;
        }
        
        /* Style tambahan untuk memastikan dark mode body */
        body.dark-mode {
            background-color: #26272e !important;
            color: #e0e0e0 !important;
        }
        
        html.dark-mode body {
            background-color: #26272e !important;
            color: #e0e0e0 !important;
        }
        
        /* Class dengan kekuatan tertinggi untuk dark mode */
        .force-dark-bg {
            background-color: #26272e !important;
            color: #e0e0e0 !important;
        }
    </style>
    
    <!-- Script untuk menerapkan dark mode sebelum halaman dimuat -->
    <script>
        // Cek dark mode dari localStorage dan cookie, terapkan segera
        const savedTheme = localStorage.getItem('theme');
        
        // Fungsi untuk mengatur cookie
        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }
        
        if (savedTheme === 'dark') {
            // Pre-apply dark mode style untuk mencegah flicker
            document.documentElement.classList.add('dark-mode');
            document.write('<style>html.dark-mode{background-color:#26272e;}body{background-color:#26272e!important;color:#e0e0e0!important;transition:none!important;} body.dark-mode,html.dark-mode body{background-color:#26272e!important;color:#e0e0e0!important;} body.dark-mode .card,html.dark-mode .card{background-color:#1e1e21!important;} body.dark-mode header,html.dark-mode header{background:linear-gradient(195deg,#342c47 10%,#241937 45%,#150f21 75%,#0d0811 100%)!important;}</style>');
            
            // Simpan juga di cookie untuk PHP
            setCookie('theme', 'dark', 30);
        } else if (savedTheme === 'light') {
            // Pastikan tidak ada dark mode styles
            setCookie('theme', 'light', 30);
        }
    </script>
    
    <?php if (isset($page_description)): ?>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <?php else: ?>
    <meta name="description" content="GMIT Elim Dadibira adalah website yang menyediakan berita dan artikel terkini seputar kalabahi dan sekitarnya.">
    <?php endif; ?>
    
    <?php if (isset($page_title)): ?>
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <?php else: ?>
    <title>GMIT Elim Dadibira</title>
    <?php endif; ?>
    
    <?php if (isset($favicon)): ?>
    <link rel="icon" href="<?php echo htmlspecialchars($favicon); ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo htmlspecialchars($favicon); ?>" type="image/x-icon">
    <?php else: ?>
    <link rel="icon" href="<?php echo url_base() . 'assets/images/logo.png'; ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo url_base() . 'assets/images/logo.png'; ?>" type="image/x-icon">
    <?php endif; ?>
    
    <!-- Open Graph meta tags untuk media sosial -->
    <?php if (isset($page_title) && isset($page_description)): ?>
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?php echo current_url(); ?>">
    <?php if (isset($article) && !empty($article['image'])): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($article['image']); ?>">
    <?php endif; ?>
    <?php endif; ?>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="<?php echo url_base() .'assets/css/style.css?v='.time(); ?>">
    <link rel="stylesheet" href="<?php echo url_base() .'assets/css/banner.css?v='.time(); ?>">
    <link rel="stylesheet" href="<?php echo url_base() .'assets/css/adsfix.css?v='.time(); ?>">
    <link rel="stylesheet" href="<?php echo url_base() .'assets/css/darkmode.css?v='.time(); ?>">
    <link rel="stylesheet" href="<?php echo url_base() .'styles.css?v='.time(); ?>">
    
    <!-- Twitter card -->
    <?php if (isset($page_title) && isset($page_description)): ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <?php if (isset($article) && !empty($article['image'])): ?>
    <meta name="twitter:image" content="<?php echo htmlspecialchars($article['image']); ?>">
    <?php endif; ?>
    <?php endif; ?>
    
    <!-- Google / Search Engine Tags -->
    <meta itemprop="name" content="<?php echo isset($page_title) ? htmlspecialchars($page_title) : 'GMIT Elim Dadibira'; ?>">
    <meta itemprop="description" content="<?php echo isset($page_description) ? htmlspecialchars($page_description) : 'GMIT Elim Dadibira adalah website yang menyediakan berita dan artikel terkini seputar kalabahi dan sekitarnya.'; ?>">
    <?php if (isset($article) && !empty($article['image'])): ?>
    <meta itemprop="image" content="<?php echo htmlspecialchars($article['image']); ?>">
    <?php endif; ?>
    
    <?php if (isset($canonical_url)): ?>
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url); ?>" />
    <?php endif; ?>

    <script>
        // Toggle Menu untuk Mobile
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            const mainNav = document.getElementById('main-nav');
            
            if (mobileMenu && mainNav) {
                mobileMenu.addEventListener('click', function() {
                    this.classList.toggle('active');
                    mainNav.classList.toggle('active');
                    document.body.classList.toggle('menu-open');
                });
                
                // Menutup menu saat link di klik
                const navLinks = mainNav.querySelectorAll('a');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        mobileMenu.classList.remove('active');
                        mainNav.classList.remove('active');
                        document.body.classList.remove('menu-open');
                    });
                });
            }
            
            // Toggle Dark Mode
            const toggleSwitch = document.querySelector('.theme-switch input[type="checkbox"]');
            const toggleSwitchDua = document.querySelector('.theme-switch-wrapper.dua .theme-switch input[type="checkbox"]');
            const currentTheme = localStorage.getItem('theme');
            
            if (currentTheme) {
                document.documentElement.setAttribute('data-theme', currentTheme);
                if (currentTheme === 'dark') {
                    document.body.classList.add('dark-mode');
                    if (toggleSwitch) toggleSwitch.checked = true;
                    if (toggleSwitchDua) toggleSwitchDua.checked = true;
                    
                    // Tampilkan/sembunyikan ikon yang sesuai
                    const sunIcons = document.querySelectorAll('.slider-icon.sun');
                    const moonIcons = document.querySelectorAll('.slider-icon.moon');
                    sunIcons.forEach(icon => icon.style.display = 'none');
                    moonIcons.forEach(icon => icon.style.display = 'inline-block');
                }
            }
            
            // Dipanggil pada onclick dalam checkbox
            window.toggleDarkModeDirect = function(shouldBeDark) {
                // Manipulasi visual langsung
                if (shouldBeDark) {
                    // Force perubahan langsung dengan prioritas tinggi
                    document.documentElement.classList.add('dark-mode');
                    document.body.classList.add('dark-mode');
                    document.body.classList.add('force-dark-bg');
                    
                    // Force background color dengan inline style (prioritas tertinggi)
                    document.body.style.cssText = "background-color: #26272e !important; color: #e0e0e0 !important";
                    
                    // Injeksi style langsung ke head dengan ID unik
                    var forceStyle = document.getElementById('force-dark-now');
                    if (!forceStyle) {
                        forceStyle = document.createElement('style');
                        forceStyle.id = 'force-dark-now';
                        forceStyle.innerHTML = 'html body, body.dark-mode, html.dark-mode body { background-color: #26272e !important; color: #e0e0e0 !important; }';
                        document.head.appendChild(forceStyle);
                    }
                    
                    // Simpan pengaturan
                    localStorage.setItem('theme', 'dark');
                    
                    // Set cookie untuk server-side
                    document.cookie = "theme=dark; path=/; max-age=31536000";
                    
                    // Force paint
                    void document.body.offsetHeight;
                    
                    // Panggil fungsi lengkap untuk melakukan sisa perubahan
                    setTimeout(function() {
                        setDarkModeImmediately(true);
                    }, 5);
                } else {
                    // Force perubahan langsung untuk light mode
                    document.documentElement.classList.remove('dark-mode');
                    document.body.classList.remove('dark-mode');
                    document.body.classList.remove('force-dark-bg');
                    
                    // Hapus style inline
                    document.body.style.cssText = "";
                    
                    // Hapus style yang diinjeksi
                    var forceStyle = document.getElementById('force-dark-now');
                    if (forceStyle) {
                        forceStyle.remove();
                    }
                    
                    // Simpan pengaturan
                    localStorage.setItem('theme', 'light');
                    
                    // Set cookie untuk server-side
                    document.cookie = "theme=light; path=/; max-age=31536000";
                    
                    // Force paint
                    void document.body.offsetHeight;
                    
                    // Panggil fungsi lengkap untuk melakukan sisa perubahan
                    setTimeout(function() {
                        setDarkModeImmediately(false);
                    }, 5);
                }
                
                return false;
            };
            
            // Script sebelum DOMContentLoaded - fungsi tersedia secara global
            window.setDarkModeImmediately = function(isDark) {
                // Log untuk debugging
                console.log("Setting dark mode immediately:", isDark);
                
                // 1. Simpan ke localStorage dulu
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
                
                // 2. Set cookie untuk server-side
                document.cookie = isDark ? 
                    "theme=dark; path=/; max-age=31536000" : 
                    "theme=light; path=/; max-age=31536000";
                
                // 3. Langsung terapkan style untuk perubahan visual instan
                if (isDark) {
                    // Gunakan cssText untuk prioritas tertinggi
                    document.body.style.cssText = "background-color: #26272e !important; color: #e0e0e0 !important";
                    
                    // Injeksi style dengan ID unik
                    var forceStyle = document.getElementById('force-dark-now');
                    if (!forceStyle) {
                        forceStyle = document.createElement('style');
                        forceStyle.id = 'force-dark-now';
                        forceStyle.innerHTML = 'html body, body.dark-mode, html.dark-mode body, body#body { background-color: #26272e !important; color: #e0e0e0 !important; }';
                        document.head.appendChild(forceStyle);
                    }
                    
                    // Set class segera
                    document.documentElement.classList.add('dark-mode');
                    document.body.classList.add('dark-mode');
                    document.body.classList.add('force-dark-bg');
                    
                    // Force repaint untuk perubahan visual instan
                    void document.body.offsetHeight;
                } else {
                    // Hapus style inline
                    document.body.style.cssText = "";
                    
                    // Hapus style yang diinjeksi
                    var forceStyle = document.getElementById('force-dark-now');
                    if (forceStyle) {
                        forceStyle.remove();
                    }
                    
                    // Hapus class segera
                    document.documentElement.classList.remove('dark-mode');
                    document.body.classList.remove('dark-mode');
                    document.body.classList.remove('force-dark-bg');
                    
                    // Force repaint untuk perubahan visual instan
                    void document.body.offsetHeight;
                }
                
                // 4. Update checkbox
                var checkboxes = document.querySelectorAll('.theme-switch input[type="checkbox"]');
                if (checkboxes.length > 0) {
                    for (var i = 0; i < checkboxes.length; i++) {
                        checkboxes[i].checked = isDark;
                    }
                }
                
                // 5. Update icons
                var sunIcons = document.querySelectorAll('.slider-icon.sun');
                var moonIcons = document.querySelectorAll('.slider-icon.moon');
                
                if (sunIcons.length > 0 && moonIcons.length > 0) {
                    for (var i = 0; i < sunIcons.length; i++) {
                        sunIcons[i].style.display = isDark ? 'none' : 'inline-block';
                    }
                    
                    for (var i = 0; i < moonIcons.length; i++) {
                        moonIcons[i].style.display = isDark ? 'inline-block' : 'none';
                    }
                }
                
                return isDark;
            };
            
            window.toggleDarkMode = function(shouldBeDark) {
                var isDarkMode = shouldBeDark === true;
                return setDarkModeImmediately(isDarkMode);
            };
            
            // Skrip pertama yang dijalankan - ini akan berjalan lebih dulu daripada DOMContentLoaded
            (function() {
                console.log("Immediate script execution - checking theme");
                var currentTheme = localStorage.getItem('theme');
                console.log("Current theme:", currentTheme);
                
                if (currentTheme === 'dark') {
                    setDarkModeImmediately(true);
                }
            })();
        }); 
    </script>

    <!-- Brute force dark mode styles -->
    <style id="dark-mode-force-styles">
        /* Super specificity selectors */
        body.dark-mode[id],
        body.dark-mode[class],
        html body.dark-mode,
        body.dark-mode, 
        .dark-mode body,
        body.force-dark-bg,
        body[class*='dark-mode'],
        body#body.dark-mode,
        html.dark-mode body#body {
            background-color: #26272e !important;
            color: #e0e0e0 !important;
            transition: color 0.3s ease !important;
        }
    </style>
    
    <!-- Preload script - Dijalankan bahkan sebelum DOM mulai diproses -->
    <script>
        // Deteksi tema dari localStorage atau cookie
        (function() {
            var isDarkMode = false;
            
            // Cek localStorage (prioritas tinggi)
            if (localStorage.getItem('theme') === 'dark') {
                isDarkMode = true;
            } 
            // Cek cookies jika localStorage kosong
            else if (!localStorage.getItem('theme')) {
                var cookies = document.cookie.split(';');
                for(var i = 0; i < cookies.length; i++) {
                    var cookie = cookies[i].trim();
                    if (cookie.indexOf('theme=dark') === 0) {
                        isDarkMode = true;
                        break;
                    }
                }
            }
            
            // Terapkan tema langsung jika dark mode
            if (isDarkMode) {
                document.documentElement.classList.add('dark-mode');
                document.write('<style>body{background-color:#26272e !important;color:#e0e0e0 !important;}</style>');
            }
        })();
    </script>
</head>
<body id="body" <?php 
    $isDarkMode = isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark';
    echo $isDarkMode ? 
        'class="dark-mode no-transition force-dark-bg" style="background-color: #26272e !important; color: #e0e0e0 !important;"' : 
        'class="no-transition"'; 
?>>
    <script>
        // Skrip pertama yang dijalankan - ini akan berjalan lebih dulu daripada DOMContentLoaded
        (function() {
            console.log("Immediate script execution - checking theme");
            var currentTheme = localStorage.getItem('theme');
            console.log("Current theme:", currentTheme);
            
            if (currentTheme === 'dark') {
                console.log("Applying dark theme immediately");
                document.documentElement.classList.add('dark-mode');
                document.body.classList.add('dark-mode');
                document.body.style.backgroundColor = "#26272e";
                document.body.style.color = "#e0e0e0";
                
                // Update ikon - opsional, tapi bisa membantu
                try {
                    document.querySelectorAll('.slider-icon.sun').forEach(function(el) {
                        el.style.display = 'none';
                    });
                    document.querySelectorAll('.slider-icon.moon').forEach(function(el) {
                        el.style.display = 'inline-block';
                    });
                } catch(e) {
                    console.log("Icon elements might not be available yet");
                }
            }
        })();
        
        // Hapus kelas no-transition setelah load dan aktifkan transisi bertahap
        window.addEventListener('load', function() {
            // Memberikan sedikit delay sebelum mengaktifkan transisi
            setTimeout(function() {
                document.body.classList.remove('no-transition');
                document.body.classList.add('theme-transition-complete');
            }, 100);
        });
    </script>
    <header>
        <div class="container">
            <div class="site-header">
                <div class="site-logo">
                    <a href="<?php echo url_base() . 'beranda'; ?>">
                        <span>GMIT Elim Dadibira</span>
                    </a>
                </div>
                <div style="display: flex; align-items: center; gap: 40px;">
                    <div class="theme-switch-wrapper satu">
                            <label class="theme-switch" for="checkbox">
                                <input type="checkbox" id="checkbox" onclick="toggleDarkModeDirect(this.checked); return false;">
                                <span class="slider-icon sun" onclick="toggleDarkModeDirect(true); return false;"><i class="bx bxs-sun"></i></span>
                                <span class="slider-icon moon" onclick="toggleDarkModeDirect(false); return false;"><i class="bx bxs-moon"></i></span>
                            </label>
                        </div>
                    
                    <div class="menu-toggle" id="mobile-menu">
                        <span class="bar"></span>
                        <span class="bar"></span>
                        <span class="bar"></span>
                    </div>
                </div>

                <nav class="main-nav" id="main-nav">
                    <div class="theme-switch-wrapper dua">
                        <label class="theme-switch" for="checkbox2">
                            <input type="checkbox" id="checkbox2" onclick="toggleDarkModeDirect(this.checked); return false;">
                            <span class="slider-icon sun" onclick="toggleDarkModeDirect(true); return false;"><i class="bx bxs-sun"></i></span>
                            <span class="slider-icon moon" onclick="toggleDarkModeDirect(false); return false;"><i class="bx bxs-moon"></i></span>
                        </label>
                    </div>
                    <div class="search-widget">
                        <form action="<?php echo url_base() . 'cari'; ?>" method="get" class="search-form">
                            <div class="search-input-group">
                                <input type="text" name="q" class="search-input" placeholder="Cari artikel..." required>
                                <button type="submit" class="search-button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <ul>
                        <li>
                            <a class="btn-primary" href="<?php echo url_base() . 'login'; ?>">Masuk</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <?php
    // Mengambil data banner dari database
    $banner_image = '';
    $banner_title = 'GMIT Elim Dadibira';
    $banner_description = 'Tempat Berbagi Informasi dan Pengalaman';
    $show_banner = false;
    $show_title = true;
    $show_description = true;
    
    // Cek apakah user admin untuk keperluan debugging
    $is_logged_in = isset($_SESSION['user_id']);
    $is_admin = false;
    
    if ($is_logged_in && function_exists('get_user')) {
        $current_user = get_user($_SESSION['user_id']);
        $is_admin = $current_user && isset($current_user['is_admin']) && $current_user['is_admin'] == 1;
    }
    
    // Debug: Periksa apakah function ada
    $function_exists = function_exists('get_banner');
    $debug_message = "Function get_banner exists: " . ($function_exists ? 'Yes' : 'No');
    
    // Coba dapatkan data banner dari database
    if ($function_exists) {
        try {
            // Dapatkan semua banner termasuk yang tidak aktif
            $banner = get_banner(false);
            $debug_message .= "<br>Banner data: " . ($banner ? 'Found' : 'Not found');
            
            if ($banner) {
                $debug_message .= "<br>Banner ID: " . ($banner['id'] ?? 'Not set');
                $debug_message .= "<br>Banner active: " . ($banner['is_active'] == 1 ? 'Yes' : 'No');
                $debug_message .= "<br>Banner image: " . (empty($banner['image_url']) ? 'Not set' : $banner['image_url']);
                $debug_message .= "<br>Banner title: " . ($banner['title'] ?? 'Not set');
                $debug_message .= "<br>Show title: " . (isset($banner['show_title']) && $banner['show_title'] == 1 ? 'Yes' : 'No');
                $debug_message .= "<br>Show description: " . (isset($banner['show_description']) && $banner['show_description'] == 1 ? 'Yes' : 'No');
                
                $banner_image = $banner['image_url'];
                if (!empty($banner['title'])) {
                    $banner_title = $banner['title'];
                }
                if (!empty($banner['description'])) {
                    $banner_description = $banner['description'];
                }
                $show_banner = $banner['is_active'] == 1;
                $show_title = isset($banner['show_title']) ? $banner['show_title'] == 1 : true;
                $show_description = isset($banner['show_description']) ? $banner['show_description'] == 1 : true;
            }
        } catch (Exception $e) {
            $debug_message .= "<br>Error: " . $e->getMessage();
        }
    }
    
    // Tampilkan debug message hanya jika user admin dan ada permintaan debug
    if ($is_admin && isset($_GET['debug'])) {
        echo '<div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin: 10px auto; max-width: 1200px; font-size: 13px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <details>
                <summary style="cursor: pointer; font-weight: bold; color: #495057;">Debug Banner (Klik untuk menampilkan)</summary>
                <div style="margin-top: 10px; background: #fff; padding: 10px; border-radius: 5px; border: 1px solid #e9ecef;">' . $debug_message . '</div>
            </details>
        </div>';
    }
    
    if ($show_banner):
    ?>
    <div class="banner-container">
        <?php if (!empty($banner_image) && file_exists($banner_image)): ?>
        <img src="<?php echo url_base() . $banner_image; ?>" alt="<?php echo htmlspecialchars($banner_title); ?>">
        <?php else: ?>
        <!-- Default banner style -->
        <div class="default-banner">
            <div class="default-banner-text">
                <i class="fas fa-newspaper" style="font-size: 48px; margin-bottom: 20px;"></i>
                <h2 style="font-size: 28px; margin-bottom: 10px;">Berita Terkini</h2>
            </div>
        </div>
        <?php endif; ?>
        <div class="banner-content">
            <?php if ($show_title): ?>
            <h1><?php echo htmlspecialchars($banner_title); ?></h1>
            <?php endif; ?>
            <?php if ($show_description): ?>
            <p><?php echo htmlspecialchars($banner_description); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="container main-container">


 