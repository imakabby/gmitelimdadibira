<?php
// admin/users/create.php
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

$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $username = clean_input($_POST['username']);
    $email = clean_input($_POST['email']);
    $name = clean_input($_POST['name']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
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
        $errors[] = "Nama lengkap wajib diisi";
    }
    
    if (empty($password)) {
        $errors[] = "Password wajib diisi";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Password tidak cocok";
    }
    
    // If no errors, create user
    if (empty($errors)) {
        $result = create_user($username, $password, $email, $name, $is_admin);
        
        if ($result['success']) {
            // Redirect to users list
            header("Location: index.php?success=created");
            exit;
        } else {
            $errors[] = $result['message'];
        }
    }
}

include '../../includes/admin-header.php';
?>

<div class="admin-content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h1><i class="fas fa-user-plus"></i> Tambah Pengguna Baru</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo url_base2(); ?>admin/dashboard">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo url_base2(); ?>admin/user/utama">Pengguna</a></li>
                            <li class="breadcrumb-item active">Tambah Baru</li>
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
                    <div class="alert-text">Pengguna berhasil dibuat!</div>
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

        <div class="row">
            <div class="col-lg-12">
                <div class="content-card">
                    <div class="card-header" style="padding: 28px;">
                        <h4>Informasi Pengguna</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" action="" class="user-form">
                            <div class="form-group">
                                <label for="username" class="form-label required">
                                    <i class="fas fa-user"></i> Username
                                </label>
                                <input type="text" class="form-control custom-input" id="username" name="username" required 
                                       placeholder="Masukkan username"
                                       value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="name" class="form-label required">
                                    <i class="fas fa-id-card"></i> Nama Lengkap
                                </label>
                                <input type="text" class="form-control custom-input" id="name" name="name" required 
                                       placeholder="Masukkan nama lengkap"
                                       value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label required">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <input type="email" class="form-control custom-input" id="email" name="email" required
                                       placeholder="Masukkan alamat email"
                                       value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="password" class="form-label required">
                                    <i class="fas fa-lock"></i> Password
                                </label>
                                <input type="password" class="form-control custom-input" id="password" name="password" required
                                       placeholder="Masukkan password">
                                <div class="form-text">Password minimal 6 karakter</div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password" class="form-label required">
                                    <i class="fas fa-lock"></i> Konfirmasi Password
                                </label>
                                <input type="password" class="form-control custom-input" id="confirm_password" name="confirm_password" required
                                       placeholder="Konfirmasi password">
                            </div>
                            
                            <div class="form-group custom-checkbox">
                                <div class="custom-control">
                                    <input type="checkbox" id="is_admin" name="is_admin" class="form-check-input" 
                                           <?php echo isset($_POST['is_admin']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_admin">
                                        <i class="fas fa-shield-alt"></i> Hak akses admin
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Simpan Pengguna
                                </button>
                                <a href="index.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/admin-footer.php'; ?>