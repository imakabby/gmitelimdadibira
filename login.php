<?php
// admin/login.php
require_once 'config/database.php';
require_once 'includes/functions.php';

start_session();

// Cek apakah sudah login
if (is_logged_in()) {
    header("Location: admin/dashboard");
    exit;
}

$error = '';

// Proses form login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    
    $result = login_user($username, $password);
    
    if ($result['success']) {
        header("Location: admin/dashboard");
        exit;
    } else {
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - GMIT Elim Dadibira</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/admin-responsive.css?v=<?php echo time(); ?>">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<style>
    #login-form input {
        font-size: 1rem;
        font-weight: 600;
        padding: 12px;
        border-radius: 20px;
        width: 100%;
    }
    
    @media (max-width: 575.98px) {
        .login-container {
            max-width: 95%;
            padding: 40px 20px;
        }
        
        #login-form input, 
        #login-form button {
            font-size: 0.95rem;
            padding: 10px;
        }
        
        .login-container h1 {
            font-size: 1.8rem;
            margin-bottom: 20px;
        }
    }
    
    @media (max-width: 767.98px) {
        .login-container {
            width: 90%;
        }
        
        .admin-login::before,
        .admin-login::after {
            width: 250px;
            height: 250px;
        }
    }
</style>
<body class="admin-login">
    <div class="login-container">
        <h1>Masuk</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <div class="alert-content">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-text"><?php echo $error; ?></div>
                </div>
                <button type="button" class="alert-dismiss">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>
        
        <form method="post" action="" id="login-form">
            <div class="form-group">
                <label for="username">Nama Pengguna</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="font-size: 1rem; font-weight: 600; padding: 15px; width: 100%; margin-top: 15px;">Masuk</button>
        </form>
        
        <p style="text-align: center; margin-top: 30px;"><a href="beranda" style="font-size: 1rem; font-weight: 600; padding: 15px; width: 100%; margin-top: 20px; display: flex; align-items: center; justify-content: center; gap: 10px;"><i class="fas fa-arrow-left"></i> Kembali ke Website</a></p>
    </div>
    <script>
        // Alert dismiss functionality
        document.addEventListener('DOMContentLoaded', function() {
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