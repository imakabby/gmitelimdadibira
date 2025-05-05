<?php
// admin/categories/edit.php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Require login
require_login();

// Get current user
$current_user = get_user($_SESSION['user_id']);

// Get category ID from URL
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($category_id <= 0) {
    header("Location: ../utama");
    exit;
}

// Get category details
$sql = "SELECT * FROM categories WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $category_id);
$stmt->execute();
$category = $stmt->fetch();

if (!$category) {
    header("Location: ../utama");
    exit;
}

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
    
    // Check if category already exists (excluding current category)
    $sql = "SELECT id FROM categories WHERE name = :name AND id != :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':id', $category_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $errors[] = "Kategori dengan nama ini sudah ada";
    }
    
    // Check if slug already exists (excluding current category)
    $sql = "SELECT id FROM categories WHERE slug = :slug AND id != :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':slug', $slug);
    $stmt->bindValue(':id', $category_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $errors[] = "Kategori dengan nama serupa sudah ada";
    }
    
    // If no errors, update category
    if (empty($errors)) {
        $sql = "UPDATE categories SET name = :name, slug = :slug, description = :description WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':slug', $slug);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':id', $category_id);
        
        if ($stmt->execute()) {
            $success = true;
            
            // Reload category data
            $sql = "SELECT * FROM categories WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $category_id);
            $stmt->execute();
            $category = $stmt->fetch();
            
            // Redirect to categories list
            header("Location: ../utama?success=updated");
            exit;
        } else {
            $errors[] = "Gagal memperbarui kategori";
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
                        <h1><i class="fas fa-edit"></i> Edit Kategori</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid" style="margin-bottom: 2rem;">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo url_base2(); ?>admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo url_base2(); ?>admin/kategori/utama">Kategori</a></li>
                    <li class="breadcrumb-item active">Edit Kategori</li>
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
                        <div class="alert-text">Kategori berhasil diperbarui!</div>
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
                                        value="<?php echo $category['name']; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-align-left"></i> Deskripsi
                                    </label>
                                    <textarea class="form-control custom-input" id="description" name="description" 
                                            rows="8" placeholder="Masukkan deskripsi kategori (opsional)"><?php echo $category['description']; ?></textarea>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Perbarui Kategori
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