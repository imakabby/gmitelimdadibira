<?php
// admin/banner-manager.php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/date_helper.php';

// Require login
require_login();

// Get current user
$current_user = get_user($_SESSION['user_id']);

// Hanya admin yang bisa mengelola banner
if (!$current_user['is_admin']) {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini.";
    header("Location: dashboard.php");
    exit;
}

// Inisialisasi pesan error dan success
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';

// Clear session messages
unset($_SESSION['error']);
unset($_SESSION['success']);

// Dapatkan data banner saat ini
$banner = get_banner();
if (!$banner) {
    $banner = [
        'id' => '',
        'title' => 'GMIT Elim Dadibira',
        'description' => 'Tempat Berbagi Informasi dan Pengalaman',
        'image_url' => '',
        'is_active' => 1,
        'show_title' => 1,
        'show_description' => 1
    ];
}

// Debug banner data
if ($current_user && $current_user['is_admin']) {
    $debug_info = "Database: news_website<br>";
    $debug_info .= "Banner data: " . ($banner ? 'Found' : 'Not found') . "<br>";
    if ($banner) {
        $debug_info .= "Banner ID: " . ($banner['id'] ?? 'Not set') . "<br>";
        $debug_info .= "Banner Active: " . ($banner['is_active'] ? 'Yes' : 'No') . "<br>";
        $debug_info .= "Banner Image: " . ($banner['image_url'] ?? 'Not set') . "<br>";
        $debug_info .= "Show Title: " . ($banner['show_title'] ?? 'Not set') . "<br>";
        $debug_info .= "Show Description: " . ($banner['show_description'] ?? 'Not set') . "<br>";
    }
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cek jika ini adalah permintaan untuk menghapus banner
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && !empty($_POST['banner_id'])) {
        // Debug informasi
        error_log("Trying to delete banner with ID: " . $_POST['banner_id']);
        
        // Proses hapus banner
        if (delete_banner($_POST['banner_id'])) {
            $_SESSION['success'] = "Banner berhasil dihapus.";
            header("Location: banner-manager.php");
            exit;
        } else {
            $error = "Gagal menghapus banner. Silakan coba lagi.";
            error_log("Failed to delete banner with ID: " . $_POST['banner_id'] . " from the UI.");
        }
    } else {
        // Proses update banner
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $show_title = isset($_POST['show_title']) ? 1 : 0;
        $show_description = isset($_POST['show_description']) ? 1 : 0;
        $image_url = $banner['image_url'];
        
        // Validasi
        if (empty($title)) {
            $error = "Judul banner tidak boleh kosong.";
        } elseif (empty($description)) {
            $error = "Deskripsi banner tidak boleh kosong.";
        } else {
            // Upload gambar jika ada
            if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] == 0) {
                // Tentukan direktori upload
                $upload_dir = "../assets/images/uploads/";
                
                // Buat direktori jika belum ada
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Validasi file
                $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                $max_size = 5 * 1024 * 1024; // 5MB
                
                if (!in_array($_FILES['banner_image']['type'], $allowed_types)) {
                    $error = "Hanya file gambar (JPG, PNG, GIF) yang diperbolehkan.";
                } elseif ($_FILES['banner_image']['size'] > $max_size) {
                    $error = "Ukuran file terlalu besar. Maksimal 5MB.";
                } else {
                    // Generate nama file unik
                    $file_name = time() . '_' . basename($_FILES['banner_image']['name']);
                    $file_path = $upload_dir . $file_name;
                    
                    // Upload file
                    if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $file_path)) {
                        // Update image URL
                        $image_url = 'assets/images/uploads/' . $file_name;
                    } else {
                        $error = "Gagal mengupload file. Silakan coba lagi.";
                    }
                }
            }
            
            // Jika tidak ada error, update banner
            if (empty($error)) {
                $data = [
                    'id' => $banner['id'],
                    'title' => $title,
                    'description' => $description,
                    'image_url' => $image_url,
                    'is_active' => $is_active,
                    'show_title' => $show_title,
                    'show_description' => $show_description
                ];
                
                if (update_banner($data)) {
                    $success = "Banner berhasil diperbarui.";
                    // Refresh banner data
                    $banner = get_banner();
                    if (!$banner) {
                        $banner = $data;
                    }
                } else {
                    $error = "Gagal memperbarui banner. Silakan coba lagi.";
                }
            }
        }
    }
}

// Include header
include '../includes/admin-header.php';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kaushan+Script&display=swap" rel="stylesheet">

<style>
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 8px;
    }
    
    .status-indicator.active {
        background-color: #28a745;
        box-shadow: 0 0 8px rgba(40, 167, 69, 0.5);
    }
    
    .status-indicator.inactive {
        background-color: #dc3545;
        box-shadow: 0 0 8px rgba(220, 53, 69, 0.5);
    }
    
    .banner-status {
        padding: 10px 15px;
        border-radius: 8px;
        border: 1px dashed #ccc;
        background-color: #f9f9f9;
    }
</style>

<div class="admin-wrapper">
    <div class="admin-content">
        <div class="page-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <h1><i class="fas fa-image"></i> Kelola Banner</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid" style="margin-bottom: 2rem;">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Kelola Banner</li>
                </ol>
            </nav>
        </div>

        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <!-- <?php if (isset($debug_info)): ?>
                <div class="alert alert-info">
                    <strong>Debug Information:</strong>
                    <div class="debug-info">
                        <?php echo $debug_info; ?>
                    </div>
                </div>
            <?php endif; ?> -->
            
            <div class="content-card">
                <div class="card-header" style="padding: 28px;">
                    <h4>Pengaturan Banner</h4>
                </div>
                <div class="card-body">

                    <form action="banner-manager.php" method="post" enctype="multipart/form-data">

                        <div class="row">

                            <div class="col-lg-8 col-md-12 mb-4">

                                <div class="form-group mb-3">
                                    <label for="title" class="form-label">Judul Banner</label>
                                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($banner['title']); ?>">
                                    <small class="form-text text-muted">Judul utama yang akan ditampilkan di banner.</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="description" class="form-label">Deskripsi Banner</label>
                                    <input type="text" class="form-control" id="description" name="description" value="<?php echo htmlspecialchars($banner['description']); ?>">
                                    <small class="form-text text-muted">Deskripsi singkat yang akan ditampilkan di bawah judul.</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?php echo $banner['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">Banner Aktif</label>
                                    </div>
                                    <small class="form-text text-muted">Matikan banner jika tidak ingin menampilkannya di halaman utama.</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="show_title" name="show_title" <?php echo isset($banner['show_title']) && $banner['show_title'] ? 'checked' : 'unchecked'; ?>>
                                        <label class="form-check-label" for="show_title">Tampilkan Judul</label>
                                    </div>
                                    <small class="form-text text-muted">Tampilkan atau sembunyikan judul banner pada halaman depan.</small>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="show_description" name="show_description" <?php echo isset($banner['show_description']) && $banner['show_description'] ? 'checked' : 'unchecked'; ?>>
                                        <label class="form-check-label" for="show_description">Tampilkan Deskripsi</label>
                                    </div>
                                    <small class="form-text text-muted">Tampilkan atau sembunyikan deskripsi banner pada halaman depan.</small>
                                </div>
                                
                                <div class="banner-status mb-4">
                                    <div class="d-flex align-items-center">
                                        <div class="status-indicator <?php echo $banner['is_active'] ? 'active' : 'inactive'; ?>"></div>
                                        <span>Status: <strong><?php echo $banner['is_active'] ? 'Aktif' : 'Tidak Aktif'; ?></strong></span>
                                    </div>
                                    <small class="form-text text-muted">Status banner saat ini. Banner akan ditampilkan di halaman utama hanya jika status aktif.</small>
                                </div>
                            </div>
                            
                            <!-- Preview Banner -->
                            <div class="col-lg-4 col-md-12">
                                <div class="form-group mb-3">
                                    <label for="banner_image" class="form-label">Gambar Banner</label>
                                    <input type="file" class="form-control" id="banner_image" name="banner_image" accept="image/*">
                                    <small class="form-text text-muted">Format: JPG, PNG, GIF. Ukuran maksimal: 5MB.</small>
                                </div>
                                
                                <?php if (!empty($banner['image_url'])): ?>
                                    <div class="banner-preview mt-3">
                                        <label class="form-label">Preview Banner</label>
                                        <div class="preview-container" style="width: 100%; height: 150px; overflow: hidden; border-radius: 1px !important; position: relative;">
                                            <img src="../<?php echo $banner['image_url']; ?>" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="banner-preview mt-3">
                                        <label class="form-label">Preview Banner</label>
                                        <div class="preview-container" style="width: 100%; height: 150px; overflow: hidden; border-radius: 8px; position: relative; background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); display: flex; align-items: center; justify-content: center; flex-direction: column;">
                                            <div style="text-align: center; color: white;">
                                                <i class="fas fa-newspaper" style="font-size: 32px; margin-bottom: 10px;"></i>
                                                <div>Banner Default</div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>
                        
                        <!-- Button Simpan, Kembali, Hapus -->
                        <div class="form-actions mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Banner
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <?php if (isset($banner['id']) && !empty($banner['id'])): ?>
                            <button type="button" class="btn btn-danger float-end" onclick="confirmDeleteBanner(<?php echo htmlspecialchars($banner['id']); ?>)" id="deleteBannerBtn">
                                <i class="fas fa-trash-alt"></i> Hapus Banner
                            </button>
                            <?php else: ?>
                            <button type="button" class="btn btn-danger float-end" disabled title="Tidak ada banner yang dapat dihapus">
                                <i class="fas fa-trash-alt"></i> Hapus Banner
                            </button>
                            <?php endif; ?>
                        </div>

                    </form>
                    
                </div>

            </div>
            
            <div class="content-card mt-4">
                <div class="card-header">
                    <h4>Preview Banner</h4>
                </div>
                <div class="card-body">
                    <div class="banner-preview-full" style="width: 100%;">
                        <div class="preview-container" style="width: 100%; max-width: 1200px; height: 300px; overflow: hidden; border-radius: 5px; position: relative; margin: 0 auto;">
                            <?php if (!empty($banner['image_url'])): ?>
                                <img src="../<?php echo $banner['image_url']; ?>" alt="Banner Preview" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div class="default-banner" style="width: 100%; height: 100%; background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); display: flex; align-items: center; justify-content: center; flex-direction: column;">
                                    <div class="default-banner-text" style="text-align: center; color: white; padding: 20px;">
                                        <i class="fas fa-newspaper" style="font-size: 48px; margin-bottom: 20px;"></i>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 35px; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent); color: #fff; text-align: center;">
                                <?php if ($banner['show_title'] ?? true): ?>
                                <h2 style="font-size: 50px !important; margin-bottom: 10px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); font-family: 'Kaushan Script', cursive; font-weight: 600;"><?php echo htmlspecialchars($banner['title']); ?></h2>
                                <?php endif; ?>
                                <?php if ($banner['show_description'] ?? true): ?>
                                <p style="font-size: 20px; margin-bottom: 0; text-shadow: 1px 1px 3px rgba(0,0,0,0.5); line-height: 1.5; font-family:'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;"><?php echo htmlspecialchars($banner['description']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!$banner['is_active']): ?>
                            <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 10;">
                                <div style="background-color: rgba(220, 53, 69, 0.9); color: white; padding: 15px 30px; border-radius: 8px; text-align: center; box-shadow: 0 0 20px rgba(0,0,0,0.5);">
                                    <i class="fas fa-eye-slash" style="font-size: 32px; margin-bottom: 10px;"></i>
                                    <h3 style="margin: 0; font-size: 18px;">Banner Tidak Aktif</h3>
                                    <p style="margin: 5px 0 0 0; font-size: 14px;">Tidak akan ditampilkan di halaman depan</p>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                        </div>

                        <div class="form-text text-muted mt-2 text-center">Preview banner seperti yang akan ditampilkan di halaman utama.</div>
                        
                        <div class="mt-3 d-flex justify-content-center">
                            <div class="banner-visibility-status text-center">
                                <?php if ($banner['is_active']): ?>
                                <span class="badge bg-success"><i class="fas fa-check-circle"></i> Banner Aktif - Ditampilkan di halaman depan</span>
                                <?php else: ?>
                                <span class="badge bg-danger"><i class="fas fa-eye-slash"></i> Banner Tidak Aktif - Tidak ditampilkan di halaman depan</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Preview gambar sebelum upload
document.addEventListener('DOMContentLoaded', function() {
    const bannerImage = document.getElementById('banner_image');
    const isActive = document.getElementById('is_active');
    
    // Preview gambar
    if (bannerImage) {
        bannerImage.addEventListener('change', function(e) {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewContainers = document.querySelectorAll('.preview-container img');
                    previewContainers.forEach(container => {
                        container.src = e.target.result;
                    });
                    
                    // Jika belum ada preview container
                    if (previewContainers.length === 0) {
                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'banner-preview mt-3';
                        previewDiv.innerHTML = `
                            <label class="form-label">Preview Banner</label>
                            <div class="preview-container" style="width: 100%; height: 150px; overflow: hidden; border-radius: 8px; position: relative;">
                                <img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        `;
                        document.querySelector('.col-lg-4').appendChild(previewDiv);
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Toggle status banner
    if (isActive) {
        isActive.addEventListener('change', function() {
            // Update status indicator
            const statusIndicator = document.querySelector('.status-indicator');
            const statusText = statusIndicator.nextElementSibling.querySelector('strong');
            
            if (this.checked) {
                statusIndicator.classList.remove('inactive');
                statusIndicator.classList.add('active');
                statusText.textContent = 'Aktif';
            } else {
                statusIndicator.classList.remove('active');
                statusIndicator.classList.add('inactive');
                statusText.textContent = 'Tidak Aktif';
            }
            
            // Update preview overlay
            const previewOverlay = document.querySelector('.preview-container > div[style*="position: absolute; top: 0;"]');
            const badgeContainer = document.querySelector('.banner-visibility-status');
            
            if (this.checked) {
                // Aktif - hapus overlay
                if (previewOverlay) {
                    previewOverlay.style.display = 'none';
                }
                
                // Update badge
                if (badgeContainer) {
                    badgeContainer.innerHTML = '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Banner Aktif - Ditampilkan di halaman depan</span>';
                }
            } else {
                // Tidak aktif - tambahkan overlay jika belum ada
                if (previewOverlay) {
                    previewOverlay.style.display = 'flex';
                } else {
                    const previewContainer = document.querySelector('.preview-container');
                    if (previewContainer) {
                        const overlay = document.createElement('div');
                        overlay.style.cssText = 'position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 10;';
                        overlay.innerHTML = `
                            <div style="background-color: rgba(220, 53, 69, 0.9); color: white; padding: 15px 30px; border-radius: 8px; text-align: center; box-shadow: 0 0 20px rgba(0,0,0,0.5);">
                                <i class="fas fa-eye-slash" style="font-size: 32px; margin-bottom: 10px;"></i>
                                <h3 style="margin: 0; font-size: 18px;">Banner Tidak Aktif</h3>
                                <p style="margin: 5px 0 0 0; font-size: 14px;">Tidak akan ditampilkan di halaman depan</p>
                            </div>
                        `;
                        previewContainer.appendChild(overlay);
                    }
                }
                
                // Update badge
                if (badgeContainer) {
                    badgeContainer.innerHTML = '<span class="badge bg-danger"><i class="fas fa-eye-slash"></i> Banner Tidak Aktif - Tidak ditampilkan di halaman depan</span>';
                }
            }
        });
    }
    
    // Toggle show title
    const showTitle = document.getElementById('show_title');
    if (showTitle) {
        showTitle.addEventListener('change', function() {
            // Cari semua elemen judul di semua preview
            const bannerTitles = document.querySelectorAll('.preview-container h2, .banner-content h1');
            bannerTitles.forEach(title => {
                title.style.display = this.checked ? 'block' : 'none';
            });
            
            // Log untuk debugging
            console.log('Show title changed:', this.checked);
        });
    }
    
    // Toggle show description
    const showDescription = document.getElementById('show_description');
    if (showDescription) {
        showDescription.addEventListener('change', function() {
            // Cari semua elemen deskripsi di semua preview (kecuali yang adalah placeholder)
            const bannerDescriptions = document.querySelectorAll('.preview-container p:not([style*="color: #666"]), .banner-content p');
            bannerDescriptions.forEach(desc => {
                desc.style.display = this.checked ? 'block' : 'none';
            });
            
            // Log untuk debugging
            console.log('Show description changed:', this.checked);
        });
    }
});
</script>

<script>
// Fungsi untuk konfirmasi hapus banner dengan SweetAlert2
function confirmDeleteBanner(bannerId) {
    // Validasi ID
    if (!bannerId || isNaN(bannerId)) {
        console.error('Banner ID is invalid:', bannerId);
        
        Swal.fire({
            title: 'Error!',
            text: 'ID Banner tidak valid. Silakan coba lagi.',
            icon: 'error',
            confirmButtonColor: '#3085d6'
        });
        
        return;
    }
    
    console.log('Confirming deletion of banner ID:', bannerId);
    
    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: '<div style="text-align: center;">' +
              '<p>Apakah Anda yakin ingin menghapus banner ini?</p>' +
              '<p style="color: #dc3545; font-weight: bold;">Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data banner.</p>' +
              '</div>',
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
            // Buat form untuk mengirimkan data
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'banner-manager.php';
            form.style.display = 'none';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete';
            
            const bannerIdInput = document.createElement('input');
            bannerIdInput.type = 'hidden';
            bannerIdInput.name = 'banner_id';
            bannerIdInput.value = bannerId;
            
            form.appendChild(actionInput);
            form.appendChild(bannerIdInput);
            document.body.appendChild(form);
            
            // Tampilkan loading state
            Swal.fire({
                title: 'Menghapus Banner...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit form
            console.log('Submitting delete form for banner ID:', bannerId);
            form.submit();
        }
    });
}
</script>

<?php include '../includes/admin-footer.php'; ?> 