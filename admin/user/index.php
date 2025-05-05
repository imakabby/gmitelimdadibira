<?php
// admin/users/index.php
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/date_helper.php';

// Require login
require_login();

// Get current user
$current_user = get_user($_SESSION['user_id']);

// Check if user is admin
if (!$current_user || !$current_user['is_admin']) {
    header("Location: ../dashboard.php");
    exit;
}

// Get all users
$users = get_users();

include '../../includes/admin-header.php';
?>

<div class="admin-wrapper">
    <div class="admin-content">
        <div class="page-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <h1><i class="fas fa-users"></i> Kelola Pengguna</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo url_base2(); ?>admin/dashb  oard">Dashboard</a></li>
                                <li class="breadcrumb-item active">Pengguna</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="container-fluid">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    if ($_GET['success'] == 'created') {
                        echo 'Pengguna berhasil dibuat.';
                    } elseif ($_GET['success'] == 'updated') {
                        echo 'Pengguna berhasil diperbarui.';
                    } elseif ($_GET['success'] == 'deleted') {
                        echo 'Pengguna berhasil dihapus.';
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_GET['error']; ?>
                </div>
            <?php endif; ?>
            
            <div class="row mt-2">
                <div class="col-lg-12">
                    <div class="content-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4>Daftar Pengguna</h4>
                            <a href="create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Baru</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($users)): ?>
                                <div class="empty-state">
                                    <i class="fas fa-users empty-icon"></i>
                                    <p>Belum ada pengguna yang terdaftar.</p>
                                    <a href="create.php" class="btn btn-primary">Tambah Pengguna Pertama</a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="admin-table" style="min-width: 100%; width: max-content;">
                                        <thead>
                                            <tr>
                                                <th>Username</th>
                                                <th>Nama Lengkap</th>
                                                <th>Email</th>
                                                <th>Peran</th>
                                                <th>Tanggal Dibuat</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td><i class="fas fa-user"></i> <?php echo $user['username']; ?></td>
                                                    <td><i class="fas fa-id-card"></i> <?php echo $user['name']; ?></td>
                                                    <td><i class="fas fa-envelope"></i> <?php echo $user['email']; ?></td>
                                                    <td>
                                                        <?php if ($user['is_admin']): ?>
                                                            <span class="status-badge published">
                                                                <i class="fas fa-user-shield"></i> Admin
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="status-badge draft">
                                                                <i class="fas fa-user"></i> Pengguna
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><span class="date"><i class="far fa-calendar-alt"></i> <?php echo waktuYangLalu($user['created_at']); ?></span></td>
                                                    <td class="actions">
                                                        <button type="button" onclick="window.location.href='edit.php?id=<?php echo $user['id']; ?>'" class="btn btn-sm btn-edit" title="Edit Pengguna">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                            <button type="button" onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo addslashes($user['name']); ?>')" class="btn btn-sm btn-delete" title="Hapus Pengguna">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
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
            text: 'Apakah Anda yakin ingin menghapus pengguna "' + name + '"?',
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
                window.location.href = 'delete.php?id=' + id;
            }
        });
    }
</script>