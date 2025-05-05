<?php
// admin/categories/index.php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Require login
require_login();

// Get current user
$current_user = get_user($_SESSION['user_id']);

// Get all categories
$sql = "SELECT c.*, COUNT(a.id) as article_count 
        FROM categories c 
        LEFT JOIN articles a ON c.id = a.category_id 
        GROUP BY c.id 
        ORDER BY c.name";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll();

include '../../includes/admin-header.php';
?>

<div class="admin-wrapper">
    <div class="admin-content">
        <div class="page-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <h1><i class="fas fa-folder"></i> Kelola Kategori</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid" style="margin-bottom: 2rem;">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo url_base2(); ?>admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Kategori</li>
                </ol>
            </nav>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="content-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>Daftar Kategori</h4>
                            <a href="buat" class="btn btn-primary" style="font-size: 1rem;">
                                <i class="fas fa-plus"></i> Tambah Kategori
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if (isset($_GET['success'])): ?>
                                <div class="alert alert-success">
                                    <div class="alert-content">
                                        <div class="alert-icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="alert-text">
                                            <?php 
                                            if ($_GET['success'] == 'created') {
                                                echo 'Kategori berhasil dibuat.';
                                            } elseif ($_GET['success'] == 'updated') {
                                                echo 'Kategori berhasil diperbarui.';
                                            } elseif ($_GET['success'] == 'deleted') {
                                                echo 'Kategori berhasil dihapus.';
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

                            <?php if (empty($categories)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-folder-open empty-icon"></i>
                                    <p>Belum ada kategori yang ditemukan.</p>
                                    <a href="buat" class="btn btn-primary">Buat Kategori Baru</a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="admin-table" style="min-width: 100%; width: max-content;">
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th>Slug</th>
                                                <th>Deskripsi</th>
                                                <th>Jumlah Artikel</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($categories as $category): ?>
                                                <tr>
                                                    <td class="title-cell"><?php echo $category['name']; ?></td>
                                                    <td><?php echo $category['slug']; ?></td>
                                                    <td><?php echo $category['description']; ?></td>
                                                    <td>
                                                        <span class="count-badge">
                                                            <i class="fas fa-newspaper"></i> <?php echo $category['article_count']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="actions">
                                                        <a href="edit/<?php echo $category['id']; ?>" class="btn btn-sm btn-edit" title="Edit Kategori">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($category['article_count'] == 0): ?>
                                                            <button type="button" onclick="confirmDelete(<?php echo $category['id']; ?>, '<?php echo addslashes($category['name']); ?>')" class="btn btn-sm btn-delete" title="Hapus Kategori">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="btn btn-sm btn-disabled" title="Tidak dapat menghapus kategori dengan artikel">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </span>
                                                        <?php endif; ?>
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
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus kategori "' + name + '"?',
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