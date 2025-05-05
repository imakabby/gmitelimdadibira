<?php
// admin/users/edit.php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Require login
require_login();

// Get current user
$current_user = get_user($_SESSION['user_id']);

// Check if user is admin
if (!$current_user || !$current_user['is_admin']) {
    header("Location: ../dashboard.php");
    exit;
}

// Get user ID from URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    header("Location: index.php");
    exit;
}

// Get user details
$user = get_user($user_id);

if (!$user) {
    header("Location: index.php");
    exit;
}

$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $username = clean_input($_POST['username']);
    $email = clean_input($_POST['email']);
    $name = clean_input($_POST['name']);
    $is_admin = isset($_POST['is_admin']) ? true : false;
    
    // Validate form data
    if (empty($username)) {
        $errors[] = "Username wajib diisi";
    }
    
    if (empty($email)) {
        $errors[] = "Email wajib diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (empty($name)) {
        $errors[] = "Nama wajib diisi";
    }
    
    // If no errors, update user
    if (empty($errors)) {
        $result = update_user($user_id, $username, $email, $name, $is_admin);
        
        if ($result['success']) {
            $success = true;
            
            // Reload user data
            $user = get_user($user_id);
        } else {
            $errors[] = $result['message'];
        }
    }
    
    // Process password change if provided
    if (!empty($_POST['new_password'])) {
        if (strlen($_POST['new_password']) < 6) {
            $errors[] = "Password minimal 6 karakter";
        } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
            $errors[] = "Password tidak cocok";
        } else {
            // Admin can change password without current password
            $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            
            $sql = "UPDATE users SET password = :password WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':password', $hashed_password);
            $stmt->bindValue(':id', $user_id);
            
            if ($stmt->execute()) {
                $success = true;
            } else {
                $errors[] = "Gagal memperbarui password";
            }
        }
    }
    
    if ($success && empty($errors)) {
        header("Location: index.php?success=updated");
        exit;
    }
}

include '../../includes/admin-header.php';
?>

<div class="admin-wrapper">
    <div class="admin-content">
        <div class="page-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <h1><i class="fas fa-user-edit"></i> Edit Pengguna</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo url_base2(); ?>admin/dashboard">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo url_base2(); ?>admin/user/utama">Pengguna</a></li>
                                <li class="breadcrumb-item active">Edit Pengguna</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <div class="alert-content">
                        <div class="alert-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="alert-text">Pengguna berhasil diperbarui!</div>
                    </div>
                    <button type="button" class="alert-dismiss">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <div class="alert-content">
                        <div class="alert-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="alert-text">
                            <h5>Terjadi Kesalahan:</h5>
                            <ul class="error-list">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="alert-dismiss">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>

            <div class="row mt-2">
                <div class="col-lg-12">
                    <div class="content-card">
                        <div class="card-header" style="padding: 28px;">
                            <h4>Informasi Pengguna</h4>
                        </div>
                        <div class="card-body">
                            <form method="post" action="" class="user-form">
                                <div class="form-group row">
                                    <label for="username" class="col-sm-2 col-form-label"><i class="fas fa-user"></i> Username</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="username" name="username" required value="<?php echo $user['username']; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label for="name" class="col-sm-2 col-form-label"><i class="fas fa-id-card"></i> Nama Lengkap</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="name" name="name" required value="<?php echo $user['name']; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label for="email" class="col-sm-2 col-form-label"><i class="fas fa-envelope"></i> Email</label>
                                    <div class="col-sm-10">
                                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo $user['email']; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label for="new_password" class="col-sm-2 col-form-label"><i class="fas fa-lock"></i> Password Baru</label>
                                    <div class="col-sm-10">
                                        <input type="password" class="form-control" id="new_password" name="new_password">
                                        <small class="form-text text-muted">Biarkan kosong untuk mempertahankan password saat ini</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label for="confirm_password" class="col-sm-2 col-form-label"><i class="fas fa-lock"></i> Konfirmasi Password</label>
                                    <div class="col-sm-10">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <div class="col-sm-2"><i class="fas fa-user-shield"></i> Hak Akses</div>
                                    <div class="col-sm-10">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin" <?php echo $user['is_admin'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_admin">Administrator</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-actions text-right">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>