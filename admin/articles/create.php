<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Require login
require_login();

// Get all categories for the form
$categories = get_categories();

$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $title = clean_input($_POST['title']);
    $content = $_POST['content']; // Don't clean HTML content
    $category_id = clean_input($_POST['category_id']);
    $status = clean_input($_POST['status']);
    $image_description = clean_input($_POST['image_description']);
    $youtube_video_id = clean_input($_POST['youtube_video_id']);
    $is_editors_pick = isset($_POST['is_editors_pick']) ? 1 : 0;
    
    // Validate form data
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    
    if (empty($content)) {
        $errors[] = "Content is required";
    }
    
    if (empty($category_id)) {
        $errors[] = "Category is required";
    }
    
    // Process image if uploaded
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Jika koordinat crop tersedia, gunakan metode crop
        if (isset($_POST['crop_x'], $_POST['crop_y'], $_POST['crop_width'], $_POST['crop_height'])) {
            $crop_result = crop_and_upload_image(
                $_FILES['image'],
                intval($_POST['crop_width']),
                intval($_POST['crop_height']),
                intval($_POST['crop_x']),
                intval($_POST['crop_y'])
            );
            
            if ($crop_result['success']) {
                $image_path = $crop_result['path'];
            } else {
                $errors[] = $crop_result['message'];
            }
        } else {
            // Gunakan metode upload biasa jika koordinat crop tidak tersedia
            $image_path = upload_image($_FILES['image']);
            if (!$image_path) {
                $errors[] = "Failed to upload image. Please check file type and size.";
            }
        }
    }
    
    // If no errors, save article
    if (empty($errors)) {
        // Generate slug from title
        $slug = generate_slug($title);
        
        // Check if slug already exists
        $stmt = $pdo->prepare("SELECT id FROM articles WHERE slug = :slug");
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Append a unique identifier to make slug unique
            $slug = $slug . '-' . uniqid();
        }
        
        // Insert article
        $sql = "INSERT INTO articles (title, slug, content, image, image_description, category_id, user_id, status, youtube_video_id, is_editors_pick) 
                VALUES (:title, :slug, :content, :image, :image_description, :category_id, :user_id, :status, :youtube_video_id, :is_editors_pick)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':slug', $slug);
        $stmt->bindValue(':content', $content);
        $stmt->bindValue(':image', $image_path);
        $stmt->bindValue(':image_description', $image_description);
        $stmt->bindValue(':category_id', $category_id);
        $stmt->bindValue(':user_id', $_SESSION['user_id']);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':youtube_video_id', $youtube_video_id);
        $stmt->bindValue(':is_editors_pick', $is_editors_pick);
        
        if ($stmt->execute()) {
            $success = true;
            $article_id = $pdo->lastInsertId();
            
            // Redirect to edit page or show success message
            if (isset($_POST['save_and_continue'])) {
                header("Location: edit/" . $article_id . "&success=1");
                exit;
            }
        } else {
            $errors[] = "Failed to save article";
        }
    }
}

require_once __DIR__ . '/../../includes/admin-header.php';
?>

<!-- CKEditor -->
<script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>

<!-- Cropperjs - library untuk crop gambar -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<div class="admin-wrapper">
    <div class="admin-content">
        <div class="page-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <h1><i class="fas fa-edit"></i> Buat Artikel Baru</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid" style="margin-bottom: 2rem;">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo url_base2(); ?>admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo url_base2(); ?>admin/artikel/utama">Kelola Artikel</a></li>
                    <li class="breadcrumb-item active">Buat Baru</li>
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
                        <div class="alert-text">Artikel berhasil dibuat!</div>
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

            <form method="post" action="" enctype="multipart/form-data" class="article-form">
            <div class="row mt-4">
                <div class="col-lg-8">
                    <div class="content-card shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-gradient-light d-flex justify-content-between align-items-center" style="padding: 28px;">
                            <h4 class="mb-0 text-dark">Informasi Artikel</h4>
                        </div>
                            <div class="card-body p-4">
                                <div class="form-group mb-4">
                                    <label for="title" class="form-label fw-bold required">
                                        <i class="fas fa-heading text-primary me-1"></i> Judul Artikel
                                    </label>
                                    <input type="text" class="form-control custom-input rounded-3 border-0 shadow-sm bg-light p-3" 
                                           id="title" name="title" required 
                                           placeholder="Masukkan judul artikel yang menarik"
                                           value="<?php echo isset($_POST['title']) ? $_POST['title'] : ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label for="content" class="form-label fw-bold required">
                                        <i class="fas fa-paragraph text-primary me-1"></i> Konten Artikel
                                    </label>
                                    <div class="editor-container rounded-3 overflow-hidden">
                                        <textarea id="content" name="content" class="ckeditor-editor" required><?php echo isset($_POST['content']) ? $_POST['content'] : ''; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                    <div class="content-card shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-gradient-light d-flex justify-content-between align-items-center" style="padding: 28px;">
                            <h4 class="mb-0 text-dark">Pengaturan Artikel</h4>
                        </div>
                            <div class="card-body p-4">
                                <div class="form-group mb-4">
                                    <label for="category_id" class="form-label fw-bold required">
                                        <i class="fas fa-folder text-primary me-1"></i> Kategori
                                    </label>
                                    <select class="form-select custom-select rounded-3 border-0 shadow-sm bg-light p-3" id="category_id" name="category_id" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo $category['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group mb-4">
                                    <label for="image" class="form-label fw-bold">
                                        <i class="fas fa-image text-primary me-1"></i> Gambar Unggulan
                                    </label>
                                    <div class="custom-file-upload rounded-3 border-0 shadow-sm bg-light overflow-hidden">
                                        <input type="file" class="custom-file-input" id="image" name="image" accept="image/*">
                                        <label class="custom-file-label rounded-3 d-flex align-items-center p-3" for="image">
                                            <i class="fas fa-cloud-upload-alt text-primary me-2"></i>
                                            <span>Pilih file gambar...</span>
                                        </label>
                                    </div>
                                    <div class="form-text mt-2 text-muted"><small>Format: JPG, PNG, GIF (Maks. 5MB)</small></div>
                                    
                                    <!-- Image preview area -->
                                    <div class="image-preview-container mt-3" style="display: none;">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h5 class="mb-0">Potong Gambar</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="img-container mb-3" style="max-height: 400px;">
                                                    <img id="image-preview" src="" class="img-fluid">
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group mb-3">
                                                            <label for="crop-width">Lebar (px)</label>
                                                            <input type="number" class="form-control" id="crop-width" min="100">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group mb-3">
                                                            <label for="crop-height">Tinggi (px)</label>
                                                            <input type="number" class="form-control" id="crop-height" min="100">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group mb-3">
                                                            <label for="aspect-ratio">Rasio Aspek</label>
                                                            <select class="form-select" id="aspect-ratio">
                                                                <option value="free">Bebas</option>
                                                                <option value="1:1">1:1 (Kotak)</option>
                                                                <option value="4:3">4:3 (Standar)</option>
                                                                <option value="16:9">16:9 (Lebar)</option>
                                                                <option value="2:1" selected>2:1 (Panorama)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-center">
                                                    <button type="button" id="crop-btn" class="btn btn-outline-primary btn-sm" style="padding: 10px 20px;">
                                                        <i class="fas fa-crop-alt" style="margin-right: 10px;"></i> Terapkan Crop
                                                    </button>
                                                    <button type="button" id="cancel-crop-btn" class="btn btn-outline-danger btn-sm" style="padding: 10px 20px;">
                                                        <i class="fas fa-times" style="margin-right: 10px;"></i> Batal
                                                    </button>
                                                </div>
                                                
                                                <!-- Hidden fields for crop values -->
                                                <input type="hidden" name="crop_x" id="crop-x">
                                                <input type="hidden" name="crop_y" id="crop-y">
                                                <input type="hidden" name="crop_width" id="crop-width-field">
                                                <input type="hidden" name="crop_height" id="crop-height-field">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-4">
                                    <label for="image_description" class="form-label fw-bold">
                                        <i class="fas fa-align-left text-primary me-1"></i> Deskripsi Gambar
                                    </label>
                                    <input type="text" class="form-control bg-light border-0 shadow-sm p-3 rounded-3" 
                                           id="image_description" 
                                           name="image_description"
                                           placeholder="Masukkan deskripsi gambar..."
                                           value="<?php echo isset($_POST['image_description']) ? $_POST['image_description'] : ''; ?>">
                                    <div class="form-text mt-2 text-muted">
                                        <small>Deskripsi singkat untuk gambar artikel</small>
                                    </div>
                                </div>

                                <div class="form-group mb-4">
                                    <label for="youtube_video_id" class="form-label fw-bold">
                                        <i class="fab fa-youtube text-primary me-1"></i> ID Video YouTube
                                    </label>
                                    <input type="text" class="form-control bg-light border-0 shadow-sm p-3 rounded-3" 
                                           id="youtube_video_id" 
                                           name="youtube_video_id"
                                           placeholder="Contoh: dQw4w9WgXcQ"
                                           value="<?php echo isset($_POST['youtube_video_id']) ? $_POST['youtube_video_id'] : ''; ?>">
                                    <div class="form-text mt-2 text-muted">
                                        <small>Masukkan ID video YouTube (bagian terakhir dari URL, misalnya: https://www.youtube.com/watch?v=<b>dQw4w9WgXcQ</b>)</small>
                                    </div>
                                </div>

                                <div class="form-group mb-4">
                                    <label for="status" class="form-label fw-bold required">
                                        <i class="fas fa-toggle-on text-primary me-1"></i> Status
                                    </label>
                                    <select class="form-select custom-select rounded-3 border-0 shadow-sm bg-light p-3" id="status" name="status" required>
                                        <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                        <option value="published" <?php echo (isset($_POST['status']) && $_POST['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                                    </select>
                                </div>

                                <div class="form-group mb-4">
                                    <div class="form-check">
                                        <?php
                                        // Periksa apakah kolom is_editors_pick ada di tabel articles
                                        $column_editors_pick_exists = false;
                                        try {
                                            $check_column = $pdo->query("SHOW COLUMNS FROM articles LIKE 'is_editors_pick'");
                                            $column_editors_pick_exists = ($check_column->rowCount() > 0);
                                            
                                            // Jika kolom tidak ada, coba buat kolom baru
                                            if (!$column_editors_pick_exists) {
                                                $pdo->exec("ALTER TABLE articles ADD COLUMN is_editors_pick TINYINT(1) NOT NULL DEFAULT 0");
                                                $column_editors_pick_exists = true;
                                            }
                                        } catch (PDOException $e) {
                                            // Gagal membuat kolom
                                        }
                                        
                                        $is_checked = isset($_POST['is_editors_pick']) ? 'checked' : '';
                                        ?>
                                        <input class="form-check-input" type="checkbox" id="is_editors_pick" name="is_editors_pick" value="1" <?php echo $is_checked; ?>>
                                        <label class="form-check-label fw-bold" for="is_editors_pick">
                                            <i class="fas fa-star text-warning me-1"></i> Pilihan Editor
                                        </label>
                                        <div class="form-text text-muted">
                                            Centang jika artikel ini ingin ditampilkan di bagian "Pilihan Editor" pada halaman utama
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions pt-3">
                                    <button type="submit" name="save" class="btn btn-primary w-100 mb-2" style="border-radius: 25px;">
                                        <i class="fas fa-save"></i> Simpan Artikel
                                    </button>
                                    <button type="submit" name="save_and_continue" class="btn btn-primary w-100" style="border-radius: 25px;">
                                        <i class="fas fa-edit"></i> Simpan & Lanjut Edit
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Update label file input
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'Pilih file gambar...';
    const label = this.nextElementSibling.querySelector('span');
    label.textContent = fileName;
    
    // Jika file adalah gambar, tampilkan preview dan cropper
    if (e.target.files && e.target.files[0]) {
        const file = e.target.files[0];
        if (file.type.match('image.*')) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                // Tampilkan container preview
                document.querySelector('.image-preview-container').style.display = 'block';
                
                // Set preview image
                const imagePreview = document.getElementById('image-preview');
                imagePreview.src = e.target.result;
                
                // Destroy previous cropper instance if exists
                if (window.imageCropper) {
                    window.imageCropper.destroy();
                }
                
                // Initialize cropper setelah gambar dimuat
                imagePreview.addEventListener('load', function() {
                    // Default aspect ratio 2:1
                    const aspectRatio = 2/1;
                    
                    window.imageCropper = new Cropper(imagePreview, {
                        aspectRatio: aspectRatio,
                        viewMode: 1,
                        autoCropArea: 0.8,
                        responsive: true,
                        crop: function(event) {
                            // Update hidden fields dengan nilai crop
                            document.getElementById('crop-x').value = Math.round(event.detail.x);
                            document.getElementById('crop-y').value = Math.round(event.detail.y);
                            document.getElementById('crop-width-field').value = Math.round(event.detail.width);
                            document.getElementById('crop-height-field').value = Math.round(event.detail.height);
                        }
                    });
                });
            };
            
            reader.readAsDataURL(file);
        }
    }
});

// Handle aspek rasio
document.getElementById('aspect-ratio').addEventListener('change', function() {
    if (!window.imageCropper) return;
    
    let aspectRatio;
    switch(this.value) {
        case '1:1':
            aspectRatio = 1/1;
            break;
        case '4:3':
            aspectRatio = 4/3;
            break;
        case '16:9':
            aspectRatio = 16/9;
            break;
        case '2:1':
            aspectRatio = 2/1;
            break;
        default:
            aspectRatio = NaN; // free form
    }
    
    window.imageCropper.setAspectRatio(aspectRatio);
});

// Handle custom dimensions
document.getElementById('crop-width').addEventListener('change', function() {
    updateCropperData();
});

document.getElementById('crop-height').addEventListener('change', function() {
    updateCropperData();
});

function updateCropperData() {
    if (!window.imageCropper) return;
    
    const width = parseInt(document.getElementById('crop-width').value);
    const height = parseInt(document.getElementById('crop-height').value);
    
    if (width > 0 && height > 0) {
        // Update hidden fields
        document.getElementById('crop-width-field').value = width;
        document.getElementById('crop-height-field').value = height;
    }
}

// Tombol terapkan crop
document.getElementById('crop-btn').addEventListener('click', function() {
    if (!window.imageCropper) return;
    
    // Dapatkan data crop
    const cropData = window.imageCropper.getData();
    
    // Set nilai width/height custom dari input jika tersedia
    const customWidth = parseInt(document.getElementById('crop-width').value);
    const customHeight = parseInt(document.getElementById('crop-height').value);
    
    if (customWidth > 0 && customHeight > 0) {
        document.getElementById('crop-width-field').value = customWidth;
        document.getElementById('crop-height-field').value = customHeight;
    } else {
        document.getElementById('crop-width-field').value = Math.round(cropData.width);
        document.getElementById('crop-height-field').value = Math.round(cropData.height);
    }
    
    // Tandai bahwa crop sudah diterapkan
    window.cropApplied = true;
    
    alert('Pengaturan crop berhasil diterapkan. Gambar akan di-crop saat artikel disimpan.');
});

// Tombol batal crop
document.getElementById('cancel-crop-btn').addEventListener('click', function() {
    if (window.imageCropper) {
        window.imageCropper.destroy();
        window.imageCropper = null;
    }
    
    // Reset hidden fields
    document.getElementById('crop-x').value = '';
    document.getElementById('crop-y').value = '';
    document.getElementById('crop-width-field').value = '';
    document.getElementById('crop-height-field').value = '';
    
    // Sembunyikan preview
    document.querySelector('.image-preview-container').style.display = 'none';
    
    // Reset file input
    document.getElementById('image').value = '';
    document.querySelector('.custom-file-label span').textContent = 'Pilih file gambar...';
});

// Inisialisasi CKEditor dengan menangkap instance dan auto-save
let editor;

// Tambahkan debugging untuk upload gambar
window.addEventListener('error', function(event) {
    console.error('Global error caught:', event.error || event.message);
    alert('Error terjadi: ' + (event.error ? event.error.message : event.message));
});

// Custom upload adapter
class MyUploadAdapter {
    constructor(loader) {
        this.loader = loader;
    }

    upload() {
        return this.loader.file
            .then(file => {
                console.log('Uploading file:', file);
                
                const formData = new FormData();
                formData.append('upload', file);
                // Tambahkan parameter source untuk menandai upload dari CKEditor
                formData.append('source', 'ckeditor');
                
                // Log form data untuk debugging
                console.log('FormData entries:');
                for (let pair of formData.entries()) {
                    console.log(pair[0], pair[1]);
                }
                
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '../../includes/upload-image.php', true);
                    // Ubah responseType menjadi text untuk menghindari error responseText
                    xhr.responseType = 'text';
                    
                    // Setup event listeners
                    xhr.addEventListener('error', () => {
                        console.error('XHR error event triggered');
                        reject('Terjadi kesalahan jaringan.');
                    });
                    
                    xhr.addEventListener('abort', () => {
                        console.error('XHR abort event triggered');
                        reject('Upload dibatalkan.');
                    });
                    
                    xhr.addEventListener('load', () => {
                        console.log('XHR load event triggered');
                        console.log('Response status:', xhr.status);
                        console.log('Response text:', xhr.responseText);
                        
                        if (xhr.status !== 200) {
                            return reject('Upload gagal, server mengembalikan status: ' + xhr.status);
                        }
                        
                        let response;
                        try {
                            response = JSON.parse(xhr.responseText);
                            console.log('Parsed JSON response:', response);
                        } catch (e) {
                            console.error('Failed to parse JSON response:', e);
                            return reject('Format respons server tidak valid.');
                        }
                        
                        if (!response || response.error) {
                            return reject(response && response.error ? response.error.message : 'Tidak dapat mengupload gambar.');
                        }
                        
                        // Resolve dengan URL gambar
                        resolve({
                            default: response.url
                        });
                    });
                    
                    if (xhr.upload) {
                        xhr.upload.addEventListener('progress', evt => {
                            if (evt.lengthComputable) {
                                this.loader.uploadTotal = evt.total;
                                this.loader.uploaded = evt.loaded;
                                console.log(`Upload progress: ${evt.loaded}/${evt.total}`);
                            }
                        });
                    }
                    
                    // Kirim request
                    console.log('Sending XHR request to:', '../../includes/upload-image.php');
                    xhr.send(formData);
                });
            });
    }

    abort() {
        console.log('Upload aborted');
    }
}

// Plugin untuk inisialisasi custom upload adapter
function MyCustomUploadAdapterPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
        return new MyUploadAdapter(loader);
    };
}

ClassicEditor
    .create(document.querySelector('.ckeditor-editor'), {
        extraPlugins: [MyCustomUploadAdapterPlugin],
        toolbar: {
            items: [
                'heading', '|',
                'bold', 'italic', 'strikethrough', 'underline', '|',
                'bulletedList', 'numberedList', '|',
                'outdent', 'indent', '|',
                'alignment', '|',
                'link', 'blockQuote', 'insertTable', 'uploadImage', '|',
                'undo', 'redo'
            ]
        },
        image: {
            toolbar: [
                'imageTextAlternative',
                'toggleImageCaption',
                'imageStyle:inline',
                'imageStyle:block',
                'imageStyle:side'
            ],
            upload: {
                types: ['jpeg', 'png', 'gif', 'bmp', 'webp']
            }
        }
    })
    .then(newEditor => {
        editor = newEditor;
        
        // Setup auto-save untuk setiap perubahan pada editor
        editor.model.document.on('change:data', () => {
            const data = editor.getData();
            document.getElementById('content').value = data;
        });
        
        console.log('CKEditor successfully initialized');
        
        // Log detail editor dengan cara yang aman
        try {
            console.log('Plugins loaded:', Array.from(editor.plugins.getPluginNames()));
            console.log('Available commands:', Array.from(editor.commands.names()));
        } catch (e) {
            console.error('Error logging editor details:', e);
        }
        
        // Cek apakah upload image tersedia
        const fileRepository = editor.plugins.get('FileRepository');
        console.log('FileRepository plugin:', fileRepository);
        console.log('Upload adapter available:', !!fileRepository.createUploadAdapter);
        
        // Menambahkan event listener untuk upload
        editor.plugins.get('FileRepository').on('uploadComplete', (evt, data) => {
            try {
                console.log('Upload complete event:', evt);
                console.log('Upload data:', data);
            } catch (e) {
                console.error('Error in uploadComplete handler:', e);
            }
        });
    })
    .catch(error => {
        console.error('CKEditor initialization error:', error);
        alert('CKEditor tidak dapat diinisialisasi: ' + error.message);
    });

// Validasi form sebelum submit
document.querySelector('.article-form').addEventListener('submit', function(e) {
    // Validasi judul
    const title = document.getElementById('title').value.trim();
    if (!title) {
        e.preventDefault();
        alert('Judul artikel harus diisi!');
        document.getElementById('title').focus();
        return false;
    }
    
    // Validasi konten
    const content = document.getElementById('content').value.trim();
    if (!content) {
        e.preventDefault();
        alert('Konten artikel harus diisi!');
        if (editor) {
            editor.editing.view.focus();
        }
        return false;
    }
    
    // Validasi kategori
    const category = document.getElementById('category_id').value;
    if (!category) {
        e.preventDefault();
        alert('Kategori harus dipilih!');
        document.getElementById('category_id').focus();
        return false;
    }
    
    // Jika semua validasi lulus, form akan submit secara normal
    return true;
});

// Extract YouTube video ID from URL
document.getElementById('youtube_video_id').addEventListener('input', function() {
    const input = this.value.trim();
    if (input.includes('youtube.com') || input.includes('youtu.be')) {
        try {
            let videoId = '';
            if (input.includes('youtube.com/watch?v=')) {
                // Format: https://www.youtube.com/watch?v=VIDEO_ID
                const url = new URL(input);
                videoId = url.searchParams.get('v');
            } else if (input.includes('youtu.be/')) {
                // Format: https://youtu.be/VIDEO_ID
                const parts = input.split('youtu.be/');
                if (parts.length > 1) {
                    videoId = parts[1].split('?')[0];
                }
            } else if (input.includes('youtube.com/embed/')) {
                // Format: https://www.youtube.com/embed/VIDEO_ID
                const parts = input.split('youtube.com/embed/');
                if (parts.length > 1) {
                    videoId = parts[1].split('?')[0];
                }
            }
            
            if (videoId) {
                this.value = videoId;
            }
        } catch (e) {
            // URL parsing failed, keep original input
        }
    }
});
</script>

<?php include '../../includes/admin-footer.php'; ?>