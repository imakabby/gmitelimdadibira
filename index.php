<?php
// index.php - Homepage
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/date_helper.php';

// Check if the featured_image column exists in the articles table
$column_exists = false;
try {
    $check_column = $pdo->query("SHOW COLUMNS FROM articles LIKE 'image'");
    $column_exists = ($check_column->rowCount() > 0);
} catch (PDOException $e) {
    // Column doesn't exist
}

// Get page number for pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 5; // Number of articles per page
$offset = ($page - 1) * $per_page;

// Get total number of published articles
$sql = "SELECT COUNT(*) as total FROM articles WHERE status = 'published'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$total_articles = $stmt->fetch()['total'];

// Calculate total pages
$total_pages = ceil($total_articles / $per_page);

// Get latest articles for running text
$sql_latest = "SELECT id, title, slug, created_at FROM articles 
               WHERE status = 'published' 
               ORDER BY created_at DESC 
               LIMIT 5";
$stmt_latest = $pdo->prepare($sql_latest);
$stmt_latest->execute();
$latest_articles = $stmt_latest->fetchAll();

// Get published articles for current page
if ($column_exists) {
    $sql = "SELECT a.*, c.name as category_name, u.name as author 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE a.status = 'published' 
            ORDER BY a.created_at DESC 
            LIMIT :limit OFFSET :offset";
} else {
    $sql = "SELECT a.id, a.title, a.slug, a.excerpt, a.category_id, a.user_id, a.status, a.created_at, 
            c.name as category_name, u.name as author 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE a.status = 'published' 
            ORDER BY a.created_at DESC 
            LIMIT :limit OFFSET :offset";
}

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll();

// If featured_image column doesn't exist, set it to null for all articles
if (!$column_exists) {
    foreach ($articles as &$article) {
        $article['image'] = null;
    }
}

// Get all categories for the sidebar with article count
$sql = "SELECT c.*, COUNT(a.id) as article_count 
        FROM categories c 
        LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published' 
        GROUP BY c.id 
        ORDER BY c.name";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<?php

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
    <meta itemprop="name" content="<?php echo isset($page_title) ? htmlspecialchars($page_title) : 'GMITElimDadibira.org'; ?>">
    <meta itemprop="description" content="<?php echo isset($page_description) ? htmlspecialchars($page_description) : 'GMIT Elim Dadibira adalah website yang menyediakan berita dan artikel terkini seputar kalabahi dan sekitarnya.'; ?>">
    <?php if (isset($article) && !empty($article['image'])): ?>
    <meta itemprop="image" content="<?php echo htmlspecialchars($article['image']); ?>">
    <?php endif; ?>
    
    <?php if (isset($canonical_url)): ?>
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url); ?>" />
    <?php endif; ?>

    <!-- Google AdSense dengan setup yang lebih baik -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7436399062257055" crossorigin="anonymous"></script>
    <!-- <link rel="stylesheet" href="assets/css/search.css"> -->
    <!-- Custom JS sudah ada di footer -->

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
                        <span>Elim Dadibra</span>
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
    $banner_title = 'GMITElimDadibira.org';
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
    ?>
    
<div class="container-fluid">
    <div class="hero-image parallax-effect" data-speed="0.5">
        <div class="title-welcome">
            <h1>Selamat Datang</h1>
            <h3>di Website Resmi <span>GMIT Elim Dadibra<span></h3>
        </div>
    </div>
</div>

<?php if ($show_banner): ?>
    <div class="banner-container">
        <?php if (!empty($banner_image) && file_exists($banner_image)): ?>
        <img src="<?php echo $banner_image; ?>" alt="<?php echo htmlspecialchars($banner_title); ?>">
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

<!-- Running Text -->
<div class="running-text">
    <div class="running-text-label">
        <span>Berita Terbaru:</span>
    </div>
    <div class="running-text-content">
        <p>
            <?php if (!empty($latest_articles)): ?>
                <?php foreach ($latest_articles as $latest): ?>
                    <a href="<?php echo url_base(); ?>artikel/<?php echo $latest['slug']; ?>">
                        <?php echo htmlspecialchars($latest['title']); ?>
                        <small>(<?php echo waktuYangLalu($latest['created_at']); ?>)</small>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <a href="#">Belum ada artikel terbaru</a>
            <?php endif; ?>
        </p>
    </div>
</div>

<!-- Headline News Section -->
<?php if ($page < 2 && !empty($articles)): ?>
<div class="headline-section">
    <div class="headline-grid">
        <div class="headline-main">
            <a href="artikel/<?php echo $articles[0]['slug']; ?>">
                <img src="<?php echo $articles[0]['image']; ?>" alt="<?php echo htmlspecialchars_decode($articles[0]['title']); ?>" class="headline-image">
                <div class="headline-content">
                    <span class="headline-category"><?php echo htmlspecialchars($articles[0]['category_name']); ?></span>
                    <h2 class="headline-title"><?php echo htmlspecialchars_decode($articles[0]['title']); ?></h2>
                    <div class="headline-meta">
                        <span><i class="far fa-clock"></i> <?php echo waktuYangLalu($articles[0]['created_at']); ?></span>
                        <span><i class="far fa-user"></i> <?php echo htmlspecialchars($articles[0]['author']); ?></span>
                    </div>
                </div>
            </a>
        </div>
        <div class="headline-secondary">
            <?php for($i = 1; $i < 3 && $i < count($articles); $i++): ?>
                <div class="headline-item">
                    <a href="artikel/<?php echo $articles[$i]['slug']; ?>">
                        <img src="<?php echo $articles[$i]['image']; ?>" alt="<?php echo htmlspecialchars_decode($articles[$i]['title']); ?>" class="headline-image">
                        <div class="headline-content">
                            <span class="headline-category"><?php echo htmlspecialchars($articles[$i]['category_name']); ?></span>
                            <h3 class="headline-title"><?php echo htmlspecialchars_decode($articles[$i]['title']); ?></h3>
                            <div class="headline-meta">
                                <span><i class="far fa-clock"></i> <?php echo waktuYangLalu($articles[$i]['created_at']); ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>
<?php endif ?>

<!-- Regular News Section -->
<div class="container">

<?php if ($page < 2 && !empty($articles)): ?>
<div class="col-md-12">
    <div style="margin: 0 -15px 20px -15px; padding: 0 !important; box-shadow: none !important;">
            <div class="pilihan-container" style="padding: 0 !important; box-shadow: none !important;">
            <h2 style="padding-left: 50px; z-index: 10; font-size: 22px;">Pilihan Editor</h2>
                <div class="pilihan-scrollable">
                    <?php 
                    // Periksa apakah kolom is_editors_pick ada di tabel articles
                    $column_editors_pick_exists = false;
                    try {
                        $check_column = $pdo->query("SHOW COLUMNS FROM articles LIKE 'is_editors_pick'");
                        $column_editors_pick_exists = ($check_column->rowCount() > 0);
                    } catch (PDOException $e) {
                        // Kolom tidak ada, buat kolom baru
                        try {
                            $pdo->exec("ALTER TABLE articles ADD COLUMN is_editors_pick TINYINT(1) NOT NULL DEFAULT 0");
                            $column_editors_pick_exists = true;
                        } catch (PDOException $e) {
                            // Gagal membuat kolom
                        }
                    }
                    
                    // Ambil artikel pilihan editor jika kolom ada, atau gunakan artikel acak jika tidak
                    if ($column_editors_pick_exists) {
                        $sql_pilihan = "SELECT a.*, c.name as category_name 
                                    FROM articles a 
                                    LEFT JOIN categories c ON a.category_id = c.id 
                                    WHERE a.status = 'published' AND a.is_editors_pick = 1
                                    ORDER BY a.created_at DESC 
                                    LIMIT 15";
                    } else {
                        // Fallback ke query lama jika kolom belum ada
                        $sql_pilihan = "SELECT a.*, c.name as category_name 
                                    FROM articles a 
                                    LEFT JOIN categories c ON a.category_id = c.id 
                                    WHERE a.status = 'published' 
                                    ORDER BY RAND() 
                                    LIMIT 15";
                    }
                    
                    $stmt_pilihan = $pdo->prepare($sql_pilihan);
                    $stmt_pilihan->execute();
                    $articles_pilihan = $stmt_pilihan->fetchAll();
                    
                    // Jika tidak ada artikel pilihan editor, gunakan artikel acak
                    if (empty($articles_pilihan) && $column_editors_pick_exists) {
                        $sql_pilihan = "SELECT a.*, c.name as category_name 
                                    FROM articles a 
                                    LEFT JOIN categories c ON a.category_id = c.id 
                                    WHERE a.status = 'published' 
                                    ORDER BY RAND() 
                                    LIMIT 15";
                        $stmt_pilihan = $pdo->prepare($sql_pilihan);
                        $stmt_pilihan->execute();
                        $articles_pilihan = $stmt_pilihan->fetchAll();
                    }
                    
                    // Array untuk melacak slug artikel yang sudah ditampilkan
                    $displayed_slugs = array();
                    ?>
                    
                    <?php foreach ($articles_pilihan as $article_pilihan): ?>
                    <?php
                    // Periksa apakah artikel dengan slug ini sudah ditampilkan
                    if (in_array($article_pilihan['slug'], $displayed_slugs)) {
                        continue; // Lewati artikel ini karena sudah ditampilkan
                    }
                    
                    // Tambahkan slug ke array artikel yang sudah ditampilkan
                    $displayed_slugs[] = $article_pilihan['slug'];
                    ?>
                    <div class="pilihan-item" data-slug="<?php echo $article_pilihan['slug']; ?>">
                        <a href="artikel/<?php echo $article_pilihan['slug']; ?>">
                            <div class="article-card-pilihan">
                                <img src="<?php echo !empty($article_pilihan['image']) ? $article_pilihan['image'] : 'assets/images/no-image.jpg'; ?>" alt="<?php echo htmlspecialchars_decode($article_pilihan['title']); ?>">
                                <div class="pilihan-content">
                                    <div class="pilihan-category"><?php echo htmlspecialchars($article_pilihan['category_name']); ?></div>
                                    <h4 class="pilihan-title"><?php echo htmlspecialchars_decode($article_pilihan['title']); ?></h4>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="pilihan-nav pilihan-prev"><i class="fas fa-chevron-left"></i></button>
                <button class="pilihan-nav pilihan-next"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
        </div>
<?php endif; ?>

    <div class="row">

        <div class="col-md-8">
            <div class="card reveal from-left delay-200">
                <div class="card-header">
                    <h2 style="padding-left: 50px;">Terbaru</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($articles)): ?>
                        <div class="alert alert-info">Tidak ada berita dan artikel terbaru.</div>
                    <?php else: ?>
                        <?php foreach ($articles as $article): ?>
                            <div class="article-card reveal from-bottom" data-delay="100">
                                <?php if (!empty($article['image'])): ?>
                                    <div class="article-image">
                                        <span class="article-category" style="position: absolute; top: 10px; left: 10px;">
                                            <a href="kategori/<?php echo $article['category_id']; ?>">
                                                <?php echo htmlspecialchars($article['category_name']); ?>
                                            </a>
                                        </span>
                                        <a href="artikel/<?php echo $article['slug']; ?>">
                                            <img src="<?php echo $article['image']; ?>" alt="<?php echo htmlspecialchars_decode($article['title']); ?>" class="parallax-element">
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="article-content">
                                    <div class="article-main">
                                        <h2>
                                            <a href="artikel/<?php echo $article['slug']; ?>">
                                                <?php echo htmlspecialchars_decode($article['title']); ?>
                                            </a>
                                        </h2>
                                        <div class="article-meta">
                                            <span class="article-date">
                                                <i class="far fa-calendar-alt"></i>
                                                <?php echo waktuYangLalu($article['created_at']); ?>
                                            </span>
                                            <span class="article-author">
                                                <i class="far fa-user"></i>
                                                <?php echo htmlspecialchars($article['author']); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="article-excerpt">
                                            <?php 
                                            $excerpt = strip_tags($article['content']);
                                            $excerpt = substr($excerpt, 0, 200);
                                            $excerpt = rtrim($excerpt, "!,.-");
                                            $excerpt = substr($excerpt, 0, strrpos($excerpt, ' '));
                                            echo $excerpt . '...';
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <a href="artikel/<?php echo $article['slug']; ?>" class="read-more">
                                        Baca selengkapnya
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="index?page=<?php echo ($page - 1); ?>" class="prev">&laquo; Sebelum</a>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <span class="current"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="index?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="index?page=<?php echo ($page + 1); ?>" class="next">Berikut &raquo;</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Iklan AdSense di bawah daftar artikel -->
                <div class="adsbygoogle-container" id="ads-container-1" style="margin: 30px 0;">
                    <script>
                        window.addEventListener('load', function() {
                            (adsbygoogle = window.adsbygoogle || []).push({});
                        });
                    </script>
                    <ins class="adsbygoogle"
                        style="display:block"
                        data-ad-format="autorelaxed"
                        data-ad-client="ca-pub-7436399062257055"
                        data-ad-slot="2433247719"></ins>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    function hideEmptyAds(containerId) {
                        var container = document.getElementById(containerId);
                        if (!container) return;
                        var ad = container.querySelector('.adsbygoogle');
                        if (!ad) return;
                        setTimeout(function() {
                            if (ad.offsetHeight < 10 || ad.innerHTML.trim() === '') {
                                container.style.display = 'none';
                            }
                        }, 2000);
                    }
                    hideEmptyAds('ads-container-1');
                });
                </script>
            </div>
        </div>
       
        <!-- Sidebar -->
        <div class="col-md-4">
            <?php include 'includes/sidebar.php'; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7436399062257055"
     crossorigin="anonymous"></script>

<!-- Script untuk dark mode -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Memastikan script dark mode parallax dijalankan
        var scriptElement = document.createElement('script');
        scriptElement.src = 'assets/js/script.js?v=' + new Date().getTime();
        document.body.appendChild(scriptElement);
    });
</script>
</body>
</html>