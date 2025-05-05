<?php
// admin/categories/create.php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Require login
require_login();

// Get current user
$current_user = get_user($_SESSION['user_id']);

$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = clean_input($_POST['name']);
    $description = clean_input($_POST['description']);
    
    // Generate slug
    $slug = generate_slug($name);
    
    // Validate form data
    if (empty($name)) {
        $errors[] = "Nama kategori wajib diisi";
    }
    
    // Check if category already exists
    $sql = "SELECT id FROM categories WHERE name = :name";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $name);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $errors[] = "Kategori dengan nama ini sudah ada";
    }
    
    // Check if slug already exists
    $sql = "SELECT id FROM categories WHERE slug = :slug";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':slug', $slug);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $errors[] = "Kategori dengan nama serupa sudah ada";
    }
    
    // If no errors, create category
    if (empty($errors)) {
        $sql = "INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :description)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':slug', $slug);
        $stmt->bindValue(':description', $description);
        
        if ($stmt->execute()) {
            // Redirect to categories list
            header("Location: utama?success=created");
            exit;
        } else {
            $errors[] = "Gagal membuat kategori";
        }
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
                        <h1><i class="fas fa-folder-plus"></i> Tambah Kategori Baru</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid" style="margin-bottom: 2rem;">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo url_base2(); ?>admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo url_base2(); ?>admin/kategori/utama">Kategori</a></li>
                    <li class="breadcrumb-item active">Tambah Baru</li>
                </ol>
            </nav>
        </div>

        <div class="container-fluid">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <div class="alert-content">
                        <div class="alert-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="alert-text">Kategori berhasil dibuat!</div>
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
                        <div class="card-header d-flex justify-content-between align-items-center" style="padding: 28px;">
                            <h4>Informasi Kategori</h4>
                        </div>
                        <div class="card-body">
                            <form method="post" action="" class="category-form">
                                <div class="form-group">
                                    <label for="name" class="form-label required">
                                        <i class="fas fa-folder"></i> Nama Kategori
                                    </label>
                                    <input type="text" class="form-control custom-input" id="name" name="name" required 
                                        placeholder="Masukkan nama kategori"
                                        value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-align-left"></i> Deskripsi
                                    </label>
                                    <textarea class="form-control custom-input" id="description" name="description" 
                                            rows="8" placeholder="Masukkan deskripsi kategori (opsional)"><?php echo isset($_POST['description']) ? $_POST['description'] : ''; ?></textarea>
                                </div>
                                
                                <div class="form-actions" style="display: flex; justify-content: flex-start; gap: 10px;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Kategori
                                    </button>
                                    <a href="<?php echo url_base2(); ?>admin/kategori/utama" class="btn btn-outline-secondary">
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
</div>

<?php include '../../includes/admin-footer.php'; ?>