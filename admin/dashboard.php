<?php
// admin/dashboard.php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/date_helper.php';

// Require login
require_login();

// Get current user
$current_user = get_user($_SESSION['user_id']);

// Get statistics
$stats = [];

// Count published articles
$sql = "SELECT COUNT(*) as count FROM articles WHERE status = 'published'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$stats['published_articles'] = $stmt->fetch()['count'];

// Count draft articles
$sql = "SELECT COUNT(*) as count FROM articles WHERE status = 'draft'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$stats['draft_articles'] = $stmt->fetch()['count'];

// Count categories
$sql = "SELECT COUNT(*) as count FROM categories";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$stats['categories'] = $stmt->fetch()['count'];

// Count users (admin only)
if ($current_user && $current_user['is_admin']) {
    $sql = "SELECT COUNT(*) as count FROM users";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $stats['users'] = $stmt->fetch()['count'];
}

// Get recent articles
$sql = "SELECT a.*, c.name as category_name, u.name as author 
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.id 
        LEFT JOIN users u ON a.user_id = u.id 
        ORDER BY a.created_at DESC LIMIT 5";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$recent_articles = $stmt->fetchAll();

include '../includes/admin-header.php';
?>

<div class="admin-wrapper">
    <div class="admin-content">
        <div class="page-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <p class="welcome-message" style="font-size: 1.5rem;">Selamat datang kembali, <strong><?php echo $current_user['name']; ?></strong>!</p>
        </div>


        <div class="container-fluid">
            <div class="dashboard-stats">
                <div class="row">
                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="stat-card published">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="stat-content">
                                    <h3>Dipublikasikan</h3>
                                    <p class="stat-value"><?php echo $stats['published_articles']; ?></p>
                                </div>
                            </div>
                            <div class="stat-footer">
                                <a href="<?php echo url_base2(); ?>admin/artikel/utama?status=published">Lihat Semua <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="stat-card draft">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-file"></i>
                                </div>
                                <div class="stat-content">
                                    <h3>Draft</h3>
                                    <p class="stat-value"><?php echo $stats['draft_articles']; ?></p>
                                </div>
                            </div>
                            <div class="stat-footer">
                                <a href="<?php echo url_base2(); ?>admin/artikel/utama?status=draft">Lihat Semua <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
                        <div class="stat-card category">
                            <div class="stat-card-body">
                                <div class="stat-icon">
                                    <i class="fas fa-folder"></i>
                                </div>
                                <div class="stat-content">
                                    <h3>Kategori</h3>
                                    <p class="stat-value"><?php echo $stats['categories']; ?></p>
                                </div>
                            </div>
                            <div class="stat-footer">
                                <a href="<?php echo url_base2(); ?>admin/kategori/utama">Lihat Semua <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($current_user && $current_user['is_admin']): ?>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-12">
                            <div class="stat-card user">
                                <div class="stat-card-body">
                                    <div class="stat-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h3>Pengguna</h3>
                                        <p class="stat-value"><?php echo $stats['users']; ?></p>
                                    </div>
                                </div>
                                <div class="stat-footer">
                                    <a href="<?php echo url_base2(); ?>admin/user/utama">Lihat Semua <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-lg-12">
                    <div class="content-card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                            <h4 class="mb-2 mb-md-0">Artikel Terbaru</h4>
                            <a href="<?php echo url_base2(); ?>admin/artikel/buat" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Buat Artikel
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_articles)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-newspaper empty-icon"></i>
                                    <p>Belum ada artikel yang dibuat.</p>
                                    <a href="<?php echo url_base2(); ?>admin/artikel/buat" class="btn btn-primary">Buat Artikel Pertama</a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="admin-table" style="min-width: 100%; width: max-content;">
                                        <thead>
                                            <tr>
                                                <th style="width: 25%;">Judul</th>
                                                <th style="width: 15%;" class="d-none d-md-table-cell">Kategori</th>
                                                <th style="width: 12%;">Status</th>
                                                <th style="width: 10%;" class="d-none d-md-table-cell">Jumlah Akses</th>
                                                <th style="width: 15%;" class="d-none d-lg-table-cell">Penulis</th>
                                                <th style="width: 13%;" class="d-none d-md-table-cell">Tanggal</th>
                                                <th style="width: 10%;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_articles as $article): ?>
                                                <tr>
                                                    <td class="title-cell" style="text-align: left; white-space: normal; word-wrap: break-word; max-width: 400px;"><?php echo $article['title']; ?></td>
                                                    <td class="d-none d-md-table-cell">
                                                        <span class="category-badge">
                                                            <i class="fas fa-folder"></i> <?php echo $article['category_name']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($article['status'] == 'published'): ?>
                                                            <span class="status-badge published">
                                                                <i class="fas fa-check-circle"></i> Dipublikasikan
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="status-badge draft">
                                                                <i class="fas fa-save"></i> Draft
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="d-none d-md-table-cell">
                                                        <span class="view-count">
                                                            <i class="fas fa-eye"></i> <?php echo number_format($article['view_count']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="d-none d-lg-table-cell"><span class="author"><i class="fas fa-user"></i> <?php echo $article['author']; ?></span></td>
                                                    <td class="d-none d-md-table-cell"><span class="date"><i class="far fa-calendar-alt"></i> <?php echo waktuYangLalu($article['created_at']); ?></span></td>
                                                    <td>
                                                        <button type="button" onclick="window.location.href='<?php echo url_base(); ?>admin/artikel/buat?id=<?php echo $article['id']; ?>'" class="btn btn-sm btn-edit" title="Edit Artikel">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" onclick="window.open('/article.php?slug=<?php echo $article['slug']; ?>', '_blank')" class="btn btn-sm btn-view" title="Lihat Artikel">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="view-all-link text-center text-md-end" style="margin-top: 30px;">
                                    <a href="<?php echo url_base2(); ?>admin/artikel/utama" class="btn btn-outline-primary">
                                        Lihat Semua Artikel <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>