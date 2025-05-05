<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/date_helper.php';

// Require login
require_login();

// Get statistics
$stats = [];

// Count all articles
$sql = "SELECT COUNT(*) as count FROM articles";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$stats['all_articles'] = $stmt->fetch()['count'];

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

// Get status filter
$status = isset($_GET['status']) ? clean_input($_GET['status']) : 'all';

// Build query based on status filter
$sql = "SELECT a.*, c.name as category_name 
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.id";

$params = [];

if ($status == 'published' || $status == 'draft') {
    $sql .= " WHERE a.status = :status";
    $params[':status'] = $status;
}

$sql .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$articles = $stmt->fetchAll();

include '../../includes/admin-header.php';
?>

<div class="admin-wrapper">
    <div class="admin-content">
        <div class="page-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <h1><i class="fas fa-newspaper"></i> Kelola Artikel</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid" style="margin-bottom: 2rem;">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo url_base2(); ?>admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Kelola Artikel</li>
                </ol>
            </nav>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <div class="alert-content">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="alert-text">
                        <?php 
                        if ($_GET['success'] == 'created') {
                            echo 'Artikel berhasil dibuat.';
                        } elseif ($_GET['success'] == 'updated') {
                            echo 'Artikel berhasil diperbarui.';
                        } elseif ($_GET['success'] == 'deleted') {
                            echo 'Artikel berhasil dihapus.';
                        }
                        ?>
                    </div>
                </div>
                <button type="button" class="alert-dismiss">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <div class="alert-content">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-text">
                        <?php echo $_GET['error']; ?>
                    </div>
                </div>
                <button type="button" class="alert-dismiss">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="content-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>Daftar Artikel</h4>
                            <a href="buat" class="btn btn-primary" style="font-size: 1rem;">
                                <i class="fas fa-plus"></i> Buat Artikel
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="filter-tabs">
                                <a href="<?php echo url_base2(); ?>admin/artikel/utama" class="filter-tab <?php echo $status == 'all' ? 'active' : ''; ?>">
                                    <i class="fas fa-list"></i> Semua
                                    <span class="counter"><?php echo $stats['all_articles']; ?></span>
                                </a>
                                <a href="<?php echo url_base2(); ?>admin/artikel/utama?status=published" class="filter-tab <?php echo $status == 'published' ? 'active' : ''; ?>">
                                    <i class="fas fa-check-circle"></i> Dipublikasikan
                                    <span class="counter"><?php echo $stats['published_articles']; ?></span>
                                </a>
                                <a href="<?php echo url_base2(); ?>admin/artikel/utama?status=draft" class="filter-tab <?php echo $status == 'draft' ? 'active' : ''; ?>">
                                    <i class="fas fa-save"></i> Draft
                                    <span class="counter"><?php echo $stats['draft_articles']; ?></span>
                                </a>
                            </div>
                            
                            <?php if (empty($articles)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-newspaper empty-icon"></i>
                                    <p>Belum ada artikel yang ditemukan.</p>
                                    <a href="artikel/buat" class="btn btn-primary">Buat Artikel Baru</a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="admin-table" style="min-width: 100%; width: max-content !important;">
                                        <thead>
                                            <tr>
                                                <th style="width: 36%;">Judul</th>
                                                <th style="width: 15%;">Kategori</th>
                                                <th style="width: 12%;">Status</th>
                                                <th style="width: 10%;">Jumlah Akses</th>
                                                <th style="width: 15%;">Tanggal</th>
                                                <th style="width: 14%;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($articles as $article): ?>
                                                <tr>
                                                    <td class="title-cell" style="text-align: left; white-space: normal; word-wrap: break-word; max-width: 400px;"><?php echo $article['title']; ?></td>
                                                    <td>
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
                                                    <td>
                                                        <span class="view-count">
                                                            <i class="fas fa-eye"></i> <?php echo number_format($article['view_count']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="date">
                                                            <i class="far fa-calendar-alt"></i> <?php echo waktuYangLalu($article['created_at']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center" style="white-space: nowrap;">
                                                        <button type="button" onclick="window.location.href='<?php echo url_base2(); ?>/admin/artikel/edit/<?php echo $article['id']; ?>'" class="btn btn-sm btn-edit" title="Edit Artikel">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" onclick="window.open('../../artikel/<?php echo $article['slug']; ?>', '_blank')" class="btn btn-sm btn-view" title="Lihat Artikel">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button type="button" onclick="confirmDelete(<?php echo $article['id']; ?>, '<?php echo htmlspecialchars(addslashes($article['title']), ENT_QUOTES, 'UTF-8'); ?>')" class="btn btn-sm btn-delete" title="Hapus Artikel">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>

<script>
    function confirmDelete(id, title) {
        title = title.replace(/\\'/g, "'");
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus artikel "' + title + '"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            backdrop: `rgba(0,0,0,0.4)`,
            customClass: {
                popup: 'swal-popup',
                title: 'swal-title',
                confirmButton: 'swal-button-confirm',
                cancelButton: 'swal-button-cancel'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'delete/' + id;
            }
        });
    }
</script>