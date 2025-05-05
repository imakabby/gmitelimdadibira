<?php
// admin/comments/index.php - Mengelola komentar artikel
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/date_helper.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: /login");
    exit;
}

// Inisialisasi variabel
$message = '';
$status = '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Cek tabel komentar
$check_table = $pdo->query("SHOW TABLES LIKE 'comments'");
if ($check_table->rowCount() == 0) {
    $pdo->exec("CREATE TABLE comments (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        article_id INT(11) UNSIGNED NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        comment TEXT NOT NULL,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (article_id, status)
    )");
}

// Proses aksi moderasi komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['comment_id'])) {
    $comment_id = (int)$_POST['comment_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $sql = "UPDATE comments SET status = 'approved' WHERE id = :id";
        $status = 'success';
        $message = 'Komentar berhasil disetujui.';
    } elseif ($action === 'reject') {
        $sql = "UPDATE comments SET status = 'rejected' WHERE id = :id";
        $status = 'success';
        $message = 'Komentar berhasil ditolak.';
    } elseif ($action === 'delete') {
        $sql = "DELETE FROM comments WHERE id = :id";
        $status = 'success';
        $message = 'Komentar berhasil dihapus.';
    } else {
        $status = 'error';
        $message = 'Aksi tidak valid.';
    }
    
    if (!empty($sql)) {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $comment_id);
            $stmt->execute();
        } catch (PDOException $e) {
            $status = 'error';
            $message = 'Gagal: ' . $e->getMessage();
        }
    }
}

// Query untuk filter komentar
$where_clause = '';
if ($filter === 'pending') {
    $where_clause = "WHERE c.status = 'pending'";
} elseif ($filter === 'approved') {
    $where_clause = "WHERE c.status = 'approved'";
} elseif ($filter === 'rejected') {
    $where_clause = "WHERE c.status = 'rejected'";
}

// Hitung total komentar untuk pagination
$count_sql = "SELECT COUNT(*) FROM comments c $where_clause";
$count_stmt = $pdo->query($count_sql);
$total_comments = $count_stmt->fetchColumn();
$total_pages = ceil($total_comments / $per_page);

// Query untuk mendapatkan komentar dengan info artikel
$comments_sql = "SELECT c.*, a.title as article_title, a.slug as article_slug 
               FROM comments c 
               LEFT JOIN articles a ON c.article_id = a.id 
               $where_clause 
               ORDER BY c.created_at DESC 
               LIMIT :offset, :per_page";
$comments_stmt = $pdo->prepare($comments_sql);
$comments_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$comments_stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$comments_stmt->execute();
$comments = $comments_stmt->fetchAll();

// Include header
include '../../includes/admin-header.php';
?>

<div class="admin-wrapper">
    <div class="admin-content">
        <div class="page-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <h1><i class="fas fa-comments"></i> Kelola Komentar</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid" style="margin-bottom: 2rem;">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo url_base2(); ?>admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Kelola Komentar</li>
                </ol>
            </nav>
        </div>

        <div class="container-fluid">
            <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
                <?php $status = in_array($_GET['status'], ['success', 'danger', 'warning', 'info']) ? $_GET['status'] : 'info'; ?>
                <div class="alert alert-<?php echo $status; ?>">
                    <div class="alert-content">
                        <div class="alert-icon">
                            <i class="fas fa-<?php echo $status === 'success' ? 'check-circle' : ($status === 'danger' ? 'exclamation-triangle' : ($status === 'warning' ? 'exclamation-circle' : 'info-circle')); ?>"></i>
                        </div>
                        <div class="alert-text"><?php echo htmlspecialchars($_GET['message']); ?></div>
                    </div>
                    <button type="button" class="alert-dismiss">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <div class="content-card">
            <div class="card-header d-flex justify-content-between align-items-center" style="padding: 28px;">
                <h4>Daftar Komentar</h4>
            </div>
            <div class="card-body">
            <!-- Filter Tabs -->
            <div class="filter-tabs mb-4">
                <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i> Semua Komentar
                    <span class="counter"><?php echo $filter === 'all' ? $total_comments : ''; ?></span>
                </a>
                <a href="?filter=pending" class="filter-tab pending <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                    <i class="fas fa-clock"></i> Pending
                </a>
                <a href="?filter=approved" class="filter-tab approved <?php echo $filter === 'approved' ? 'active' : ''; ?>">
                    <i class="fas fa-check-circle"></i> Disetujui
                </a>
                <a href="?filter=rejected" class="filter-tab rejected <?php echo $filter === 'rejected' ? 'active' : ''; ?>">
                    <i class="fas fa-ban"></i> Ditolak
                </a>
            </div>

            <div class="content-card shadow-sm rounded-4 overflow-hidden">
                <?php if (count($comments) > 0): ?>
                    <div class="table-responsive">
                        <table class="table admin-table table-striped table-hover mb-0 align-middle" style="min-width: 100%; width: max-content;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Komentar</th>
                                    <th>Artikel</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($comments as $comment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($comment['name']); ?></td>
                                        <td><?php echo htmlspecialchars($comment['email']); ?></td>
                                        <td class="title-cell">
                                            <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                                <?php echo htmlspecialchars(substr($comment['comment'], 0, 100)) . (strlen($comment['comment']) > 100 ? '...' : ''); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="../../article.php?slug=<?php echo $comment['article_slug']; ?>" target="_blank" class="article-link">
                                                <?php echo htmlspecialchars(substr($comment['article_title'], 0, 30)) . (strlen($comment['article_title']) > 30 ? '...' : ''); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar-alt"></i> <?php echo waktuYangLalu($comment['created_at']); ?>
                                        </td>
                                        <td>
                                            <?php if ($comment['status'] === 'pending'): ?>
                                                <span class="status-badge pending">
                                                    <i class="fas fa-clock"></i> Pending
                                                </span>
                                            <?php elseif ($comment['status'] === 'approved'): ?>
                                                <span class="status-badge approved">
                                                    <i class="fas fa-check-circle"></i> Disetujui
                                                </span>
                                            <?php elseif ($comment['status'] === 'rejected'): ?>
                                                <span class="status-badge rejected">
                                                    <i class="fas fa-ban"></i> Ditolak
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <?php if ($comment['status'] !== 'approved'): ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                        <input type="hidden" name="action" value="approve">
                                                        <button type="submit" class="btn btn-sm btn-success" title="Setujui">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <?php if ($comment['status'] !== 'rejected'): ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                        <input type="hidden" name="action" value="reject">
                                                        <button type="submit" class="btn btn-sm btn-warning" title="Tolak">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteComment(<?php echo $comment['id']; ?>, '<?php echo addslashes(htmlspecialchars(substr($comment['comment'], 0, 50))) . (strlen($comment['comment']) > 50 ? '...' : ''); ?>')" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination-container p-3 bg-light border-top">
                            <nav aria-label="Page navigation">
                                <ul class="pagination mb-0 justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?filter=<?php echo $filter; ?>&page=<?php echo ($page - 1); ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?filter=<?php echo $filter; ?>&page=<?php echo $i; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?filter=<?php echo $filter; ?>&page=<?php echo ($page + 1); ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center p-5">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <h4>Tidak Ada Komentar</h4>
                        <p class="text-muted">Belum ada komentar <?php echo ($filter !== 'all') ? "dengan status $filter" : ''; ?>.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        </div>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>

<script>
    function confirmDeleteComment(id, commentPreview) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus komentar "' + commentPreview + '"?',
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
                // Buat form secara dinamis dan kirim
                var form = document.createElement('form');
                form.method = 'post';
                form.style.display = 'none';
                
                var commentIdInput = document.createElement('input');
                commentIdInput.name = 'comment_id';
                commentIdInput.value = id;
                
                var actionInput = document.createElement('input');
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                form.appendChild(commentIdInput);
                form.appendChild(actionInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script> 