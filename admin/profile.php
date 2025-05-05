<?php
// admin/profile.php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require login
require_login();

// Get current user
$user = get_user($_SESSION['user_id']);

if (!$user) {
    header("Location: login");
    exit;
}

$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $email = clean_input($_POST['email']);
    $name = clean_input($_POST['name']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate form data
    if (empty($email)) {
        $errors[] = "Email wajib diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }
    
    if (empty($name)) {
        $errors[] = "Nama lengkap wajib diisi";
    }
    
    // Update profile if changed
    if ($email != $user['email'] || $name != $user['name']) {
        // Check if email already exists
        $sql = "SELECT id FROM users WHERE email = :email AND id != :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':id', $user['id']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email sudah digunakan";
        } else {
            $sql = "UPDATE users SET email = :email, name = :name WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':id', $user['id']);
            
            if ($stmt->execute()) {
                $success = true;
                $user['email'] = $email;
                $user['name'] = $name;
            } else {
                $errors[] = "Gagal memperbarui profil";
            }
        }
    }
    
    // Process password change if provided
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors[] = "Password saat ini diperlukan untuk mengubah password";
        }
        
        if (empty($new_password)) {
            $errors[] = "Password baru wajib diisi";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "Password baru minimal 6 karakter";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "Password baru tidak cocok";
        }
        
        if (empty($errors)) {
            $result = change_password($user['id'], $current_password, $new_password);
            
            if ($result['success']) {
                $success = true;
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

include '../includes/admin-header.php';
?>

<div class="admin-wrapper">
    <div class="admin-content">
        <div class="page-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <h1><i class="fas fa-user-circle"></i> Profil Saya</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Profil Saya</li>
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
                        <i class="fas fa-check-circle alert-icon"></i>
                        <div class="alert-text">Profil berhasil diperbarui!</div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <div class="alert-content">
                        <i class="fas fa-exclamation-triangle alert-icon"></i>
                        <div class="alert-text">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="row mt-2">
                <div class="col-lg-12">
                    <div class="content-card">
                        <div class="card-header" style="padding: 28px;">
                            <h4>Informasi Profil</h4>
                        </div>
                        <div class="card-body">
                            <form method="post" action="" class="user-form">
                                <div class="form-group row mb-3">
                                    <label for="username" class="col-sm-12 col-md-3 col-form-label"><i class="fas fa-user"></i> Nama Pengguna</label>
                                    <div class="col-sm-12 col-md-9">
                                        <input type="text" class="form-control" id="username" value="<?php echo $user['username']; ?>" disabled>
                                        <small class="form-text text-muted">Nama Pengguna tidak dapat diubah</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label for="name" class="col-sm-12 col-md-3 col-form-label"><i class="fas fa-id-card"></i> Nama Lengkap</label>
                                    <div class="col-sm-12 col-md-9">
                                        <input type="text" class="form-control" id="name" name="name" required value="<?php echo $user['name']; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label for="email" class="col-sm-12 col-md-3 col-form-label"><i class="fas fa-envelope"></i> Email</label>
                                    <div class="col-sm-12 col-md-9">
                                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo $user['email']; ?>">
                                    </div>
                                </div>
                                                                
                                <div class="form-group row mb-3">
                                    <label for="current_password" class="col-sm-12 col-md-3 col-form-label"><i class="fas fa-key"></i> Password Saat Ini</label>
                                    <div class="col-sm-12 col-md-9">
                                        <input type="password" class="form-control" id="current_password" name="current_password">
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-3">
                                    <label for="new_password" class="col-sm-12 col-md-3 col-form-label"><i class="fas fa-lock"></i> Password Baru</label>
                                    <div class="col-sm-12 col-md-9">
                                        <input type="password" class="form-control" id="new_password" name="new_password">
                                        <small class="form-text text-muted">Password minimal 6 karakter</small>
                                    </div>
                                </div>
                                
                                <div class="form-group row mb-4">
                                    <label for="confirm_password" class="col-sm-12 col-md-3 col-form-label"><i class="fas fa-lock"></i> Konfirmasi Password</label>
                                    <div class="col-sm-12 col-md-9">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                    </div>
                                </div>
                                
                                <div class="form-actions text-end">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/admin-footer.php'; ?>