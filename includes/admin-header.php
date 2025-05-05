<?php
// includes/admin-header.php
// Memastikan file functions.php diinclude
if (!function_exists('url_base2')) {
    require_once __DIR__ . '/functions.php';
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit;
}

// Get current user if not already defined
if (!isset($current_user) && isset($_SESSION['user_id'])) {
    $current_user = get_user($_SESSION['user_id']);
}

// Fungsi url_base2() sudah dideklarasikan di includes/functions.php
// jadi tidak perlu dideklarasikan lagi di sini

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Portal Berita</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- <link rel="stylesheet" href="/assets/css/style.css"> -->
    <link rel="stylesheet" href="<?php echo url_base2(); ?>assets/css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo url_base2(); ?>assets/css/admin-responsive.css?v=<?php echo time(); ?>">
    <link rel="icon" href="<?php echo url_base() . 'assets/images/logo.png'; ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo url_base2(); ?>assets/images/logo.png" type="image/x-icon">
</head>
<body>
    <!-- Overlay untuk sidebar mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar" id="sidebar" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="sidebar-header" style="display: flex; align-items: center; justify-content: center;">
                    <img src="<?php echo url_base2(); ?>assets/images/logo.png" alt="Logo Langkahkata.com" style="width: 50px; height: 50px; padding: 0;">
                    <a href="<?php echo url_base2(); ?>admin/dashboard"><span>LangkahKata.</span></a>
                </div>
                
                <div class="sidebar-menu">
                    <div class="sidebar-title">Menu Utama</div>
                    <ul>
                        <li><a href="<?php echo url_base2(); ?>admin/dashboard" <?php echo strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false ? 'class="active"' : ''; ?>><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="<?php echo url_base2(); ?>admin/artikel/utama" <?php echo strpos($_SERVER['REQUEST_URI'], '/artikel/') !== false ? 'class="active"' : ''; ?>><i class="fas fa-newspaper"></i> Artikel</a></li>
                        <li><a href="<?php echo url_base2(); ?>admin/kategori/utama" <?php echo strpos($_SERVER['REQUEST_URI'], '/kategori/') !== false ? 'class="active"' : ''; ?>><i class="fas fa-folder"></i> Kategori</a></li>
                        <li><a href="<?php echo url_base2(); ?>admin/komentar/utama" <?php echo strpos($_SERVER['REQUEST_URI'], '/komentar/') !== false ? 'class="active"' : ''; ?>><i class="fas fa-comments"></i> Komentar</a></li>
                        <li><a href="<?php echo url_base2(); ?>admin/media" <?php echo basename($_SERVER['PHP_SELF']) == 'media-manager' ? 'class="active"' : ''; ?>><i class="fas fa-images"></i> Media</a></li>
                        <li><a href="<?php echo url_base2(); ?>admin/banner" <?php echo basename($_SERVER['PHP_SELF']) == 'banner-manager' ? 'class="active"' : ''; ?>><i class="fas fa-image"></i> Kelola Banner</a></li>
                    </ul>
                
                    <div class="sidebar-title">Pengguna</div>
                    <ul>
                        <?php if (isset($current_user) && $current_user['is_admin']): ?>
                            <li><a href="<?php echo url_base2(); ?>admin/user/utama" <?php echo strpos($_SERVER['REQUEST_URI'], '/user/') !== false ? 'class="active"' : ''; ?>><i class="fas fa-users"></i> Kelola Pengguna</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo url_base2(); ?>admin/profil" <?php echo strpos($_SERVER['REQUEST_URI'], 'profile') !== false ? 'class="active"' : ''; ?>><i class="fas fa-user-circle"></i> Profil Saya</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="sidebar-footer">
                <a href="<?php echo url_base2(); ?>logout"><i class="fas fa-sign-out-alt"></i> Keluar</a>
                <div class="version">LangkahKata v1.0</div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="admin-main">
            <!-- Top Navbar -->
            <div class="admin-navbar">
                <div class="navbar-start">
                    <button class="menu-toggle" id="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="navbar-title d-none d-md-flex">Halaman CMS</div>
                </div>
                
                <div class="navbar-end">
                    <a href="<?php echo url_base2(); ?>beranda" class="nav-link" target="_blank" title="Lihat Website">
                        <i class="fas fa-globe"></i>
                        <span class="d-none d-lg-inline">Lihat Website</span>
                    </a>
                    <div class="navbar-dropdown">
                        <button class="navbar-dropdown-toggle" id="userDropdown">
                            <div class="user-avatar">
                                <?php echo substr($current_user['name'], 0, 1); ?>
                            </div>
                            <span class="d-none d-md-inline"><?php echo $current_user['name']; ?></span>
                            <i class="fas fa-chevron-down d-none d-md-inline" style="font-size: 0.8rem; margin-left: 5px;"></i>
                        </button>
                        <div class="navbar-dropdown-menu" id="userDropdownMenu">
                            <ul>
                                <li>
                                    <a href="<?php echo url_base2(); ?>admin/profil">
                                        <i class="fas fa-user-circle"></i> Profil Saya
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo url_base2(); ?>admin/artikel/buat">
                                        <i class="fas fa-plus-circle"></i> Buat Artikel
                                    </a>
                                </li>
                                <div class="dropdown-divider"></div>
                                <li>
                                    <a href="<?php echo url_base2(); ?>logout">
                                        <i class="fas fa-sign-out-alt"></i> Keluar
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="admin-content">
            
            <script>
                // Toggle sidebar on mobile
                document.addEventListener('DOMContentLoaded', function() {
                    const menuToggle = document.getElementById('menu-toggle');
                    const sidebar = document.getElementById('sidebar');
                    const sidebarOverlay = document.getElementById('sidebarOverlay');
                    
                    // Toggle sidebar on menu button click
                    if (menuToggle && sidebar) {
                        menuToggle.addEventListener('click', function() {
                            sidebar.classList.toggle('active');
                            sidebarOverlay.classList.toggle('active');
                            document.body.classList.toggle('sidebar-open');
                        });
                    }
                    
                    // Close sidebar when clicking on overlay
                    if (sidebarOverlay) {
                        sidebarOverlay.addEventListener('click', function() {
                            sidebar.classList.remove('active');
                            sidebarOverlay.classList.remove('active');
                            document.body.classList.remove('sidebar-open');
                        });
                    }
                    
                    // Close sidebar on window resize (if in mobile view)
                    window.addEventListener('resize', function() {
                        if (window.innerWidth >= 768) {
                            sidebar.classList.remove('active');
                            sidebarOverlay.classList.remove('active');
                            document.body.classList.remove('sidebar-open');
                        }
                    });
                    
                    // Close sidebar when clicking on a menu item (in mobile view)
                    const sidebarLinks = sidebar.querySelectorAll('a');
                    sidebarLinks.forEach(function(link) {
                        link.addEventListener('click', function() {
                            if (window.innerWidth < 768) {
                                sidebar.classList.remove('active');
                                sidebarOverlay.classList.remove('active');
                                document.body.classList.remove('sidebar-open');
                            }
                        });
                    });
                    
                    // User dropdown toggle
                    const userDropdown = document.getElementById('userDropdown');
                    const userDropdownMenu = document.getElementById('userDropdownMenu');
                    
                    if (userDropdown && userDropdownMenu) {
                        userDropdown.addEventListener('click', function(e) {
                            e.stopPropagation();
                            userDropdownMenu.classList.toggle('show');
                        });
                        
                        // Close dropdown when clicking outside
                        document.addEventListener('click', function(e) {
                            if (!userDropdown.contains(e.target)) {
                                userDropdownMenu.classList.remove('show');
                            }
                        });
                    }
                    
                    // Alert dismiss functionality
                    const alertDismissButtons = document.querySelectorAll('.alert-dismiss');
                    alertDismissButtons.forEach(button => {
                        button.addEventListener('click', function() {
                            const alert = this.closest('.alert');
                            alert.style.opacity = '0';
                            alert.style.transform = 'translateY(-20px)';
                            setTimeout(() => {
                                alert.style.display = 'none';
                            }, 300);
                        });
                    });
                });
            </script>
</body>
</html>