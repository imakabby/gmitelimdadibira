<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Pastikan user sudah login
require_login();

// Ambil filter untuk jenis media
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$items_per_page = 12;
$offset = ($page - 1) * $items_per_page;

// Hapus gambar jika ada request
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $image_id = (int)$_GET['delete'];
    
    // Ambil informasi gambar
    $stmt = $pdo->prepare("SELECT * FROM image_metadata WHERE id = ?");
    $stmt->execute([$image_id]);
    $image = $stmt->fetch();
    
    if ($image) {
        // Path lengkap ke file gambar
        $image_path = realpath($_SERVER['DOCUMENT_ROOT'] . $image['path']);
        
        // Hapus file fisik jika ada
        if (file_exists($image_path)) {
            unlink($image_path);
        }
        
        // Hapus metadata dari database
        $delete_stmt = $pdo->prepare("DELETE FROM image_metadata WHERE id = ?");
        $delete_stmt->execute([$image_id]);
        
        // Redirect ke halaman yang sama dengan pesan sukses
        header("Location: media-manager.php?filter=$filter&page=$page&success=1");
        exit;
    }
}

// Query dasar
$base_query = "SELECT * FROM image_metadata";
$count_query = "SELECT COUNT(*) FROM image_metadata";
$where_conditions = [];
$params = [];

// Filter berdasarkan sumber
if ($filter === 'ckeditor') {
    $where_conditions[] = "source = 'ckeditor'";
} elseif ($filter === 'form') {
    $where_conditions[] = "source = 'form'";
}

// Filter pencarian
if (!empty($search)) {
    $where_conditions[] = "(original_filename LIKE ? OR filename LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Gabungkan kondisi WHERE jika ada
if (!empty($where_conditions)) {
    $where_clause = " WHERE " . implode(" AND ", $where_conditions);
    $base_query .= $where_clause;
    $count_query .= $where_clause;
}

// Query final dengan pagination
$base_query .= " ORDER BY upload_date DESC LIMIT $offset, $items_per_page";

// Jalankan query utama
$stmt = $pdo->prepare($base_query);
if (!empty($params)) {
    foreach ($params as $key => $param) {
        $stmt->bindValue($key + 1, $param);
    }
}
$stmt->execute();
$images = $stmt->fetchAll();

// Ambil total jumlah gambar untuk pagination
$count_stmt = $pdo->prepare($count_query);
if (!empty($params)) {
    foreach ($params as $key => $param) {
        $count_stmt->bindValue($key + 1, $param);
    }
}
$count_stmt->execute();
$total_images = $count_stmt->fetchColumn();
$total_pages = ceil($total_images / $items_per_page);

include '../includes/admin-header.php';
?>

<div class="admin-wrapper">
    <div class="admin-content">
        <div class="page-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <h1><i class="fas fa-images"></i> Manajemen Media</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid" style="margin-bottom: 2rem;">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Manajemen Media</li>
                </ol>
            </nav>
        </div>

        <div class="container-fluid">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <div class="alert-content">
                        <div class="alert-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="alert-text">Media berhasil dihapus!</div>
                    </div>
                    <button type="button" class="alert-dismiss">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-8 col-sm-12 mb-3 mb-md-0">
                    <div class="media-actions" role="group">
                        <a href="media-manager.php" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <i class="fas fa-images"></i> Semua
                        </a>
                        <a href="media-manager.php?filter=ckeditor" class="btn <?php echo $filter === 'ckeditor' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <i class="fas fa-edit"></i> CKEditor
                        </a>
                        <a href="media-manager.php?filter=form" class="btn <?php echo $filter === 'form' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <i class="fas fa-upload"></i> Upload Form
                        </a>
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <form action="media-manager.php" method="get" class="d-flex">
                        <?php if ($filter !== 'all'): ?>
                            <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                        <?php endif; ?>
                        <input type="text" name="search" style="border-radius: 20px; padding: 8px 16px;" class="form-control" placeholder="Cari gambar..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary ms-2"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>

            <?php if (empty($images)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Tidak ada gambar yang ditemukan.
                </div>
            <?php else: ?>
                <div class="row media-gallery">
                    <?php foreach ($images as $image): ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                            <div class="card h-100">
                                <div class="card-img-top position-relative" style="height: 200px; background-color: #f8f9fa; overflow: hidden;">
                                    <img src="<?php echo $image['source'] === 'ckeditor' ? url_base() . htmlspecialchars($image['path']) : htmlspecialchars($image['path']); ?>" class="img-fluid" style="object-fit: cover; width: 100%; height: 100%;" alt="<?php echo htmlspecialchars($image['original_filename'] ?? $image['filename']); ?>">
                                    <span class="badge bg-<?php echo $image['source'] === 'ckeditor' ? 'primary' : 'success'; ?> position-absolute top-0 end-0 m-2">
                                        <?php echo $image['source'] === 'ckeditor' ? 'Editor' : 'Form'; ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <h6 class="card-title text-truncate" title="<?php echo htmlspecialchars($image['original_filename'] ?? $image['filename']); ?>">
                                        <?php echo htmlspecialchars($image['original_filename'] ?? $image['filename']); ?>
                                    </h6>
                                    <p class="card-text small">
                                        <span class="text-muted"><?php echo date('d M Y, H:i', strtotime($image['upload_date'])); ?></span><br>
                                        <?php echo $image['width']; ?> x <?php echo $image['height']; ?> px<br>
                                        <?php echo formatFileSize($image['file_size']); ?>
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between flex-wrap media-card-footer">
                                        <a href="<?php echo htmlspecialchars($image['path']); ?>" class="btn btn-sm btn-outline-primary mb-1 mb-md-0" target="_blank">
                                            <i class="fas fa-eye"></i><span class="d-none d-md-inline"> Lihat</span>
                                        </a>
                                        <button class="btn btn-sm btn-outline-info copy-url-btn mb-1 mb-md-0" data-url="<?php echo htmlspecialchars($image['path']); ?>">
                                            <i class="fas fa-copy"></i><span class="d-none d-md-inline"> Copy URL</span>
                                        </button>
                                        <a href="media-manager.php?delete=<?php echo $image['id']; ?>&filter=<?php echo $filter; ?>&page=<?php echo $page; ?>" 
                                           class="btn btn-sm btn-outline-danger mb-1 mb-md-0" 
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus gambar ini?');">
                                            <i class="fas fa-trash"></i><span class="d-none d-md-inline"> Hapus</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="media-manager.php?filter=<?php echo $filter; ?>&page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                        <i class="fas fa-chevron-left"></i> Sebelumnya
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            if ($start_page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="media-manager.php?filter=' . $filter . '&page=1' . (!empty($search) ? '&search=' . urlencode($search) : '') . '">1</a></li>';
                                if ($start_page > 2) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                            }
                            
                            for ($i = $start_page; $i <= $end_page; $i++) {
                                echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="media-manager.php?filter=' . $filter . '&page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . '">' . $i . '</a></li>';
                            }
                            
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="media-manager.php?filter=' . $filter . '&page=' . $total_pages . (!empty($search) ? '&search=' . urlencode($search) : '') . '">' . $total_pages . '</a></li>';
                            }
                            ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="media-manager.php?filter=<?php echo $filter; ?>&page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                        Selanjutnya <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fungsi untuk copy URL gambar
    const copyButtons = document.querySelectorAll('.copy-url-btn');
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const url = this.dataset.url;
            navigator.clipboard.writeText(window.location.origin + url)
                .then(() => {
                    // Visual feedback
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-check"></i> URL Copied!';
                    this.classList.remove('btn-outline-info');
                    this.classList.add('btn-success');
                    
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.classList.remove('btn-success');
                        this.classList.add('btn-outline-info');
                    }, 2000);
                })
                .catch(err => {
                    alert('Gagal menyalin URL: ' + err);
                });
        });
    });
});
</script>

<?php
// Fungsi untuk format ukuran file
function formatFileSize($size) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($size >= 1024 && $i < count($units) - 1) {
        $size /= 1024;
        $i++;
    }
    return round($size, 2) . ' ' . $units[$i];
}

include '../includes/admin-footer.php';
?> 