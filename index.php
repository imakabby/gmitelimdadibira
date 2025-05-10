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

<!DOCTYPE html>
<html lang="id" <?php echo (isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark') ? 'class="dark-mode"' : ''; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
        
    <?php if (isset($page_description)): ?>
        <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <?php else: ?>
        <meta name="description" content="Selamat Datang di Website Resmi GMIT Elim Dadibira.">
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
        <link rel="icon" href="<?php echo url_base() . 'assets/background.jpg'; ?>" type="image/x-icon">
        <link rel="shortcut icon" href="<?php echo url_base() . 'assets/background.jpg'; ?>" type="image/x-icon">
    <?php endif; ?>
    
    <!-- Open Graph meta tags untuk media sosial -->
    <?php if (isset($page_title) && isset($page_description)): ?>
        <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
        <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
        <meta property="og:type" content="article">
        <meta property="og:url" content="<?php echo current_url(); ?>">
        <?php if (isset($article) && !empty($article['image'])): ?>
            <meta property="og:image" content="<?php echo url_base() . 'assets/background.jpg'; ?>">
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
    
    <!-- Tambahkan script slider -->
    <script src="<?php echo url_base() .'assets/js/slider.js?v='.time(); ?>"></script>
    
    <!-- Twitter card -->
    <?php if (isset($page_title) && isset($page_description)): ?>
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
        <meta name="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>">
        <?php if (isset($article) && !empty($article['image'])): ?>
            <meta name="twitter:image" content="<?php echo url_base() . 'assets/background.jpg'; ?>">
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Google / Search Engine Tags -->
    <meta itemprop="name" content="<?php echo isset($page_title) ? htmlspecialchars($page_title) : 'GMITElimDadibira.org'; ?>">
    <meta itemprop="description" content="<?php echo isset($page_description) ? htmlspecialchars($page_description) : 'Selamat Datang di Website Resmi GMIT Elim Dadibira.'; ?>">
    <?php if (isset($article) && !empty($article['image'])): ?>
        <meta itemprop="image" content="<?php echo url_base() . 'assets/background.jpeg'; ?>">
    <?php endif; ?>
    
    <?php if (isset($canonical_url)): ?>
        <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url); ?>" />
    <?php endif; ?>

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
    
</head>
<body id="body" <?php 
    $isDarkMode = isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark';
    echo $isDarkMode ? 'class="dark-mode"' : ''; ?>>
 
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
                        <label class="theme-switch" for="checkbox">
                            <input type="checkbox" id="checkbox" onclick="toggleDarkModeDirect(this.checked); return false;">
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
        <div class="slider">
            <div id="slide1" class="slide"></div>
            <div id="slide2" class="slide"></div>
            <div id="slide3" class="slide"></div>
        </div>
        <div class="title-welcome">
            <h1>Selamat Datang</h1>
            <h3>di Website Resmi <span>GMIT Elim Dadibra<span></h3>
        </div>
        <div class="thema">
            <h1>Lakukanlah Keadilan, Cintai Kesetiaan, dan Hidup Rendah Hati di Hadapan Allah.</h1>
            <h3>Bdk. Mikhael 6:8</h3>
        </div>
    </div>
 
<svg class="background-svg" width="100%" height="120%" id="svg" viewBox="0 0 1440 590" xmlns="http://www.w3.org/2000/svg" class="transition duration-300 ease-in-out delay-150"><style>
          .path-0{
            animation:pathAnim-0 4s;
            animation-timing-function: linear;
            animation-iteration-count: infinite;
          }
          @keyframes pathAnim-0{
            0%{
              d: path("M 0,600 L 0,112 C 102.48461538461538,122.53076923076924 204.96923076923076,133.06153846153848 280,121 C 355.03076923076924,108.93846153846154 402.60769230769233,74.28461538461539 486,72 C 569.3923076923077,69.71538461538461 688.6000000000001,99.8 758,94 C 827.3999999999999,88.2 846.9923076923076,46.515384615384605 916,57 C 985.0076923076924,67.4846153846154 1103.4307692307693,130.13846153846157 1199,148 C 1294.5692307692307,165.86153846153843 1367.2846153846153,138.93076923076922 1440,112 L 1440,600 L 0,600 Z");
            }
            25%{
              d: path("M 0,600 L 0,112 C 70.11025641025643,120.93589743589745 140.22051282051285,129.8717948717949 223,135 C 305.77948717948715,140.1282051282051 401.228205128205,141.44871794871793 490,148 C 578.771794871795,154.55128205128207 660.8666666666668,166.33333333333334 729,153 C 797.1333333333332,139.66666666666666 851.305128205128,101.2179487179487 921,79 C 990.694871794872,56.78205128205129 1075.9128205128206,50.794871794871796 1165,59 C 1254.0871794871794,67.2051282051282 1347.0435897435896,89.6025641025641 1440,112 L 1440,600 L 0,600 Z");
            }
            50%{
              d: path("M 0,600 L 0,112 C 65.1307692307692,119.13589743589743 130.2615384615384,126.27179487179487 214,132 C 297.7384615384616,137.72820512820513 400.0846153846154,142.04871794871795 477,140 C 553.9153846153846,137.95128205128205 605.4,129.53333333333336 680,114 C 754.6,98.46666666666665 852.3153846153845,75.81794871794871 934,88 C 1015.6846153846155,100.18205128205129 1081.3384615384616,147.1948717948718 1163,157 C 1244.6615384615384,166.8051282051282 1342.3307692307692,139.4025641025641 1440,112 L 1440,600 L 0,600 Z");
            }
            75%{
              d: path("M 0,600 L 0,112 C 69.37692307692308,115.23589743589744 138.75384615384615,118.47179487179487 223,102 C 307.24615384615385,85.52820512820513 406.36153846153843,49.34871794871795 496,66 C 585.6384615384616,82.65128205128205 665.8,152.13333333333335 736,165 C 806.2,177.86666666666665 866.4384615384616,134.1179487179487 934,112 C 1001.5615384615384,89.88205128205128 1076.4461538461537,89.39487179487179 1162,93 C 1247.5538461538463,96.60512820512821 1343.7769230769231,104.3025641025641 1440,112 L 1440,600 L 0,600 Z");
            }
            100%{
              d: path("M 0,600 L 0,112 C 102.48461538461538,122.53076923076924 204.96923076923076,133.06153846153848 280,121 C 355.03076923076924,108.93846153846154 402.60769230769233,74.28461538461539 486,72 C 569.3923076923077,69.71538461538461 688.6000000000001,99.8 758,94 C 827.3999999999999,88.2 846.9923076923076,46.515384615384605 916,57 C 985.0076923076924,67.4846153846154 1103.4307692307693,130.13846153846157 1199,148 C 1294.5692307692307,165.86153846153843 1367.2846153846153,138.93076923076922 1440,112 L 1440,600 L 0,600 Z");
            }
          }</style><defs><linearGradient id="gradient" x1="0%" y1="51%" x2="100%" y2="49%"><stop offset="5%" stop-color="#9900ef"></stop><stop offset="95%" stop-color="#f78da7"></stop></linearGradient></defs><path d="M 0,600 L 0,112 C 102.48461538461538,122.53076923076924 204.96923076923076,133.06153846153848 280,121 C 355.03076923076924,108.93846153846154 402.60769230769233,74.28461538461539 486,72 C 569.3923076923077,69.71538461538461 688.6000000000001,99.8 758,94 C 827.3999999999999,88.2 846.9923076923076,46.515384615384605 916,57 C 985.0076923076924,67.4846153846154 1103.4307692307693,130.13846153846157 1199,148 C 1294.5692307692307,165.86153846153843 1367.2846153846153,138.93076923076922 1440,112 L 1440,600 L 0,600 Z" stroke="none" stroke-width="0" fill="url(#gradient)" fill-opacity="0.4" class="transition-all duration-300 ease-in-out delay-150 path-0" transform="rotate(-180 720 300)"></path><style>
          .path-1{
            animation:pathAnim-1 4s;
            animation-timing-function: linear;
            animation-iteration-count: infinite;
          }
          @keyframes pathAnim-1{
            0%{
              d: path("M 0,600 L 0,262 C 65.63846153846151,252.28205128205127 131.27692307692303,242.56410256410257 209,234 C 286.723076923077,225.43589743589743 376.53076923076935,218.02564102564102 459,213 C 541.4692307692306,207.97435897435898 616.5999999999999,205.33333333333334 702,212 C 787.4000000000001,218.66666666666666 883.0692307692309,234.64102564102564 964,235 C 1044.930769230769,235.35897435897436 1111.1230769230767,220.1025641025641 1188,222 C 1264.8769230769233,223.8974358974359 1352.4384615384615,242.94871794871796 1440,262 L 1440,600 L 0,600 Z");
            }
            25%{
              d: path("M 0,600 L 0,262 C 53.69487179487177,268.7974358974359 107.38974358974355,275.5948717948718 196,275 C 284.61025641025645,274.4051282051282 408.1358974358975,266.4179487179487 504,279 C 599.8641025641025,291.5820512820513 668.0666666666667,324.73333333333335 746,316 C 823.9333333333333,307.26666666666665 911.5974358974358,256.64871794871794 982,247 C 1052.4025641025642,237.35128205128203 1105.5435897435898,268.6717948717949 1179,278 C 1252.4564102564102,287.3282051282051 1346.228205128205,274.66410256410256 1440,262 L 1440,600 L 0,600 Z");
            }
            50%{
              d: path("M 0,600 L 0,262 C 91.4128205128205,246.5974358974359 182.825641025641,231.1948717948718 250,249 C 317.174358974359,266.8051282051282 360.11025641025645,317.81794871794875 435,317 C 509.88974358974355,316.18205128205125 616.7333333333332,263.5333333333333 702,238 C 787.2666666666668,212.46666666666667 850.9564102564102,214.04871794871792 933,214 C 1015.0435897435898,213.95128205128208 1115.4410256410256,212.2717948717949 1203,220 C 1290.5589743589744,227.7282051282051 1365.2794871794872,244.86410256410255 1440,262 L 1440,600 L 0,600 Z");
            }
            75%{
              d: path("M 0,600 L 0,262 C 79.17948717948721,235.31538461538463 158.35897435897442,208.63076923076923 229,208 C 299.6410256410256,207.36923076923077 361.7435897435896,232.79230769230765 445,260 C 528.2564102564104,287.20769230769235 632.6666666666669,316.20000000000005 710,316 C 787.3333333333331,315.79999999999995 837.5897435897433,286.4076923076923 923,284 C 1008.4102564102567,281.5923076923077 1128.9743589743591,306.16923076923075 1221,307 C 1313.0256410256409,307.83076923076925 1376.5128205128203,284.9153846153846 1440,262 L 1440,600 L 0,600 Z");
            }
            100%{
              d: path("M 0,600 L 0,262 C 65.63846153846151,252.28205128205127 131.27692307692303,242.56410256410257 209,234 C 286.723076923077,225.43589743589743 376.53076923076935,218.02564102564102 459,213 C 541.4692307692306,207.97435897435898 616.5999999999999,205.33333333333334 702,212 C 787.4000000000001,218.66666666666666 883.0692307692309,234.64102564102564 964,235 C 1044.930769230769,235.35897435897436 1111.1230769230767,220.1025641025641 1188,222 C 1264.8769230769233,223.8974358974359 1352.4384615384615,242.94871794871796 1440,262 L 1440,600 L 0,600 Z");
            }
          }</style><defs><linearGradient id="gradient" x1="0%" y1="51%" x2="100%" y2="49%"><stop offset="5%" stop-color="#9900ef"></stop><stop offset="95%" stop-color="#f78da7"></stop></linearGradient></defs><path d="M 0,600 L 0,262 C 65.63846153846151,252.28205128205127 131.27692307692303,242.56410256410257 209,234 C 286.723076923077,225.43589743589743 376.53076923076935,218.02564102564102 459,213 C 541.4692307692306,207.97435897435898 616.5999999999999,205.33333333333334 702,212 C 787.4000000000001,218.66666666666666 883.0692307692309,234.64102564102564 964,235 C 1044.930769230769,235.35897435897436 1111.1230769230767,220.1025641025641 1188,222 C 1264.8769230769233,223.8974358974359 1352.4384615384615,242.94871794871796 1440,262 L 1440,600 L 0,600 Z" stroke="none" stroke-width="0" fill="url(#gradient)" fill-opacity="0.53" class="transition-all duration-300 ease-in-out delay-150 path-1" transform="rotate(-180 720 300)"></path><style>
          .path-2{
            animation:pathAnim-2 4s;
            animation-timing-function: linear;
            animation-iteration-count: infinite;
          }
          @keyframes pathAnim-2{
            0%{
              d: path("M 0,600 L 0,412 C 85.15641025641025,425.95641025641027 170.3128205128205,439.91282051282053 237,452 C 303.6871794871795,464.08717948717947 351.9051282051282,474.30512820512814 441,459 C 530.0948717948718,443.69487179487186 660.0666666666667,402.8666666666667 752,398 C 843.9333333333333,393.1333333333333 897.8282051282051,424.2282051282051 976,421 C 1054.1717948717949,417.7717948717949 1156.6205128205129,380.2205128205128 1238,373 C 1319.3794871794871,365.7794871794872 1379.6897435897436,388.8897435897436 1440,412 L 1440,600 L 0,600 Z");
            }
            25%{
              d: path("M 0,600 L 0,412 C 97.5871794871795,421.8538461538461 195.174358974359,431.7076923076923 282,441 C 368.825641025641,450.2923076923077 444.8897435897436,459.0230769230769 523,457 C 601.1102564102564,454.9769230769231 681.2666666666667,442.2 750,436 C 818.7333333333333,429.8 876.0435897435898,430.17692307692306 955,416 C 1033.9564102564102,401.82307692307694 1134.5589743589742,373.0923076923077 1219,370 C 1303.4410256410258,366.9076923076923 1371.7205128205128,389.45384615384614 1440,412 L 1440,600 L 0,600 Z");
            }
            50%{
              d: path("M 0,600 L 0,412 C 94.37948717948717,424.9666666666667 188.75897435897434,437.93333333333334 261,439 C 333.24102564102566,440.06666666666666 383.34358974358975,429.2333333333334 463,434 C 542.6564102564103,438.7666666666666 651.8666666666666,459.1333333333333 744,465 C 836.1333333333334,470.8666666666667 911.1897435897436,462.23333333333335 981,458 C 1050.8102564102564,453.76666666666665 1115.374358974359,453.9333333333333 1191,447 C 1266.625641025641,440.0666666666667 1353.3128205128205,426.03333333333336 1440,412 L 1440,600 L 0,600 Z");
            }
            75%{
              d: path("M 0,600 L 0,412 C 91.43333333333334,385.37179487179486 182.86666666666667,358.7435897435897 264,377 C 345.1333333333333,395.2564102564103 415.9666666666666,458.39743589743597 493,458 C 570.0333333333334,457.60256410256403 653.2666666666668,393.66666666666663 738,394 C 822.7333333333332,394.33333333333337 908.9666666666667,458.9358974358974 978,467 C 1047.0333333333333,475.0641025641026 1098.8666666666666,426.5897435897436 1173,408 C 1247.1333333333334,389.4102564102564 1343.5666666666666,400.7051282051282 1440,412 L 1440,600 L 0,600 Z");
            }
            100%{
              d: path("M 0,600 L 0,412 C 85.15641025641025,425.95641025641027 170.3128205128205,439.91282051282053 237,452 C 303.6871794871795,464.08717948717947 351.9051282051282,474.30512820512814 441,459 C 530.0948717948718,443.69487179487186 660.0666666666667,402.8666666666667 752,398 C 843.9333333333333,393.1333333333333 897.8282051282051,424.2282051282051 976,421 C 1054.1717948717949,417.7717948717949 1156.6205128205129,380.2205128205128 1238,373 C 1319.3794871794871,365.7794871794872 1379.6897435897436,388.8897435897436 1440,412 L 1440,600 L 0,600 Z");
            }
          }</style><defs><linearGradient id="gradient" x1="0%" y1="51%" x2="100%" y2="49%"><stop offset="5%" stop-color="#9900ef"></stop><stop offset="95%" stop-color="#f78da7"></stop></linearGradient></defs><path d="M 0,600 L 0,412 C 85.15641025641025,425.95641025641027 170.3128205128205,439.91282051282053 237,452 C 303.6871794871795,464.08717948717947 351.9051282051282,474.30512820512814 441,459 C 530.0948717948718,443.69487179487186 660.0666666666667,402.8666666666667 752,398 C 843.9333333333333,393.1333333333333 897.8282051282051,424.2282051282051 976,421 C 1054.1717948717949,417.7717948717949 1156.6205128205129,380.2205128205128 1238,373 C 1319.3794871794871,365.7794871794872 1379.6897435897436,388.8897435897436 1440,412 L 1440,600 L 0,600 Z" stroke="none" stroke-width="0" fill="url(#gradient)" fill-opacity="1" class="transition-all duration-300 ease-in-out delay-150 path-2" transform="rotate(-180 720 300)"></path></svg>


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

<!-- Headline News Sect
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
ion -->


<!-- Regular News Section -->
<div class="container">
<!-- 
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
<?php endif; ?> -->

<div class="container">
    <div class="pendeta-section">
        <h2 >PROFIL PENDETA</h2>
        <div class="card-pendeta-wrapper">
            <div class="card-pendeta">
                <div class="card-body-pendeta">
                    <img src="assets/pendeta.png" alt="Profil Pendeta GMIT Elim Dadibira">
                </div>
            </div>
        </div>
        <div class="card-body-pendeta-text">
            <h3>Pdt. Selvy Putri Fabiola, M.Si</h3>
            <p>Ketua Majelis Jemaat GMIT Elim Dadibira</p>
        </div>
    </div>
</div>

<div class="button-container container" style="margin-bottom: 60px ">
    <button class="button-struktur btn">
        <a href="#">Struktur Organisasi <i class="fas fa-arrow-right"></i></a>
    </button>
    <button class="button-data-jemaat btn">
        <a href="#">Data Jemaat Elim Dadibira <i class="fas fa-arrow-right"></i></a>
    </button>
</div>

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

            </div>
        </div>
       
        <!-- Sidebar -->
        <div class="col-md-4">
            <?php include 'includes/sidebar.php'; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

    
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


           // Inisialisasi saat DOM telah dimuat
    document.addEventListener('DOMContentLoaded', function() {
        const titleWelcome = document.querySelector('.title-welcome');
        const thema = document.querySelector('.thema');
        let isShowingWelcome = true;

        // Hapus style display: none dari thema
        thema.removeAttribute('style');
        
        // Set kondisi awal
        titleWelcome.classList.add('active');
        thema.classList.add('hidden');

        function switchContent() {
            if (isShowingWelcome) {
                // Sembunyikan welcome
                titleWelcome.classList.remove('active');
                titleWelcome.classList.add('inactive');
                setTimeout(() => {
                    titleWelcome.classList.add('hidden');
                    titleWelcome.classList.remove('inactive');
                }, 500); // beri waktu animasi keluar

                // Tampilkan thema
                thema.classList.remove('hidden');
                thema.classList.remove('inactive');
                setTimeout(() => {
                    thema.classList.add('active');
                }, 10);
            } else {
                // Sembunyikan thema
                thema.classList.remove('active');
                thema.classList.add('inactive');
                setTimeout(() => {
                    thema.classList.add('hidden');
                    thema.classList.remove('inactive');
                }, 500);

                // Tampilkan welcome
                titleWelcome.classList.remove('hidden');
                titleWelcome.classList.remove('inactive');
                setTimeout(() => {
                    titleWelcome.classList.add('active');
                }, 10);
            }
            isShowingWelcome = !isShowingWelcome;
        }

        // Jalankan pergantian setiap 3 detik
        setInterval(switchContent, 3000);
    });
    </script>
</body>
</html>