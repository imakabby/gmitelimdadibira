<?php
// Start session if not already started
function start_session() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function is_logged_in() {
    start_session();
    return isset($_SESSION['user_id']);
}

// Require login
function require_login() {
    if (!is_logged_in()) {
        header("Location: login");
        exit;
    }
}

// Clean input data
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// User login function
function login_user($username, $password) {
    global $pdo;
    
    // Validate input
    if (empty($username) || empty($password)) {
        return [
            'success' => false,
            'message' => 'Nama pengguna dan kata sandi wajib diisi'
        ];
    }
    
    // Check if user exists
    $sql = "SELECT id, username, name, password FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':username', $username);
    $stmt->execute();
    
    $user = $stmt->fetch();
    
    if (!$user) {
        return [
            'success' => false,
            'message' => 'Nama pengguna atau kata sandi tidak valid'
        ];
    }
    
    // Verify password
    if (password_verify($password, $user['password'])) {
        // Start session
        start_session();
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        return [
            'success' => true,
            'user_id' => $user['id'],
            'username' => $user['username'],
            'name' => $user['name']
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Nama pengguna atau kata sandi tidak valid'
        ];
    }
}

// User logout function
function logout_user() {
    start_session();
    
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header("Location: login");
    exit;
}

// Create new user function
function create_user($username, $password, $email, $name, $is_admin = false) {
    global $pdo;
    
    // Validate input
    if (empty($username) || empty($password) || empty($email) || empty($name)) {
        return [
            'success' => false,
            'message' => 'Semua kolom wajib diisi'
        ];
    }
    
    // Check if username already exists
    $sql = "SELECT id FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        return [
            'success' => false,
            'message' => 'Nama pengguna sudah digunakan'
        ];
    }
    
    // Check if email already exists
    $sql = "SELECT id FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        return [
            'success' => false,
            'message' => 'Email sudah digunakan'
        ];
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $sql = "INSERT INTO users (username, password, email, name, is_admin) VALUES (:username, :password, :email, :name, :is_admin)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':password', $hashed_password);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':is_admin', $is_admin ? 1 : 0, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'user_id' => $pdo->lastInsertId(),
            'message' => 'Pengguna berhasil dibuat'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Gagal membuat pengguna'
        ];
    }
}

// Get user by ID
function get_user($user_id) {
    global $pdo;
    
    $sql = "SELECT id, username, email, name, is_admin, created_at FROM users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $user_id);
    $stmt->execute();
    
    return $stmt->fetch();
}

// Get all users
function get_users() {
    global $pdo;
    
    $sql = "SELECT id, username, email, name, is_admin, created_at FROM users ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

// Update user function
function update_user($user_id, $username, $email, $name, $is_admin = false) {
    global $pdo;
    
    // Validate input
    if (empty($username) || empty($email) || empty($name)) {
        return [
            'success' => false,
            'message' => 'Nama pengguna, email, dan nama lengkap wajib diisi'
        ];
    }
    
    // Check if username already exists (excluding current user)
    $sql = "SELECT id FROM users WHERE username = :username AND id != :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        return [
            'success' => false,
            'message' => 'Nama pengguna sudah digunakan'
        ];
    }
    
    // Check if email already exists (excluding current user)
    $sql = "SELECT id FROM users WHERE email = :email AND id != :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        return [
            'success' => false,
            'message' => 'Email sudah digunakan'
        ];
    }
    
    // Update user
    $sql = "UPDATE users SET username = :username, email = :email, name = :name, is_admin = :is_admin WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':name', $name);
    $stmt->bindValue(':is_admin', $is_admin ? 1 : 0, PDO::PARAM_INT);
    $stmt->bindValue(':id', $user_id);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => 'Pengguna berhasil diperbarui'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Gagal memperbarui pengguna'
        ];
    }
}

// Change user password
function change_password($user_id, $current_password, $new_password) {
    global $pdo;
    
    // Validate input
    if (empty($current_password) || empty($new_password)) {
        return [
            'success' => false,
            'message' => 'Password lama dan baru wajib diisi'
        ];
    }
    
    // Get user's current password
    $sql = "SELECT password FROM users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $user_id);
    $stmt->execute();
    
    $user = $stmt->fetch();
    
    if (!$user) {
        return [
            'success' => false,
            'message' => 'Pengguna tidak ditemukan'
        ];
    }
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        return [
            'success' => false,
            'message' => 'Password lama tidak sesuai'
        ];
    }
    
    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $sql = "UPDATE users SET password = :password WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':password', $hashed_password);
    $stmt->bindValue(':id', $user_id);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => 'Password berhasil diubah'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Gagal mengubah password'
        ];
    }
}

// Delete user
function delete_user($user_id) {
    global $pdo;
    
    // Check if user exists
    $sql = "SELECT id FROM users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        return [
            'success' => false,
            'message' => 'Pengguna tidak ditemukan'
        ];
    }
    
    // Delete user
    $sql = "DELETE FROM users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $user_id);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => 'Pengguna berhasil dihapus'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Gagal menghapus pengguna'
        ];
    }
}

// Generate slug from title
function generate_slug($title) {
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9\s]/', '', $slug);
    $slug = preg_replace('/\s+/', '-', $slug);
    return $slug;
}

// Get all published articles
function get_articles($limit = 10, $offset = 0, $category_id = null, $status = 'published') {
    global $pdo;
    
    $sql = "SELECT a.*, c.name as category_name, u.name as author 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE a.status = :status";
    
    $params = [':status' => $status];
    
    if ($category_id) {
        $sql .= " AND a.category_id = :category_id";
        $params[':category_id'] = $category_id;
    }
    
    $sql .= " ORDER BY a.created_at DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql);
    
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    return $stmt->fetchAll();
}

// Get single article by slug
function get_article_by_slug($slug) {
    global $pdo;
    
    $sql = "SELECT a.*, c.name as category_name, u.name as author 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE a.slug = :slug";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':slug', $slug);
    $stmt->execute();
    
    return $stmt->fetch();
}

// Get all categories
function get_categories() {
    global $pdo;
    
    $sql = "SELECT * FROM categories ORDER BY name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll();
}

// Upload image function
function upload_image($file, $source = 'form') {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'Tipe file tidak didukung'];
    }
    $max_size = 5 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'Ukuran file terlalu besar (maks. 5MB)'];
    }
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $original_filename = pathinfo($file['name'], PATHINFO_FILENAME);
    $filename = uniqid() . '.' . $extension;
    $base_upload_dir = 'assets/images/uploads/';
    $upload_dir = $source === 'ckeditor' ? $base_upload_dir . 'ckeditor/' : $base_upload_dir;
    if (!file_exists($upload_dir) && !mkdir($upload_dir, 0777, true)) {
        return ['success' => false, 'message' => 'Gagal membuat direktori upload'];
    }
    $target_path = $upload_dir . $filename;
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        try {
            global $pdo;
            $image_size = getimagesize($target_path);
            $width = $image_size[0];
            $height = $image_size[1];
            $stmt = $pdo->prepare("INSERT INTO image_metadata (filename, original_filename, path, source, width, height, file_size, file_type, upload_date) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $path_for_db = '/' . $target_path;
            $stmt->execute([
                $filename,
                $original_filename,
                $path_for_db,
                $source,
                $width,
                $height,
                $file['size'],
                $file['type']
            ]);
            error_log("Berhasil menyimpan metadata gambar: $filename, source: $source, path: $path_for_db");
        } catch (PDOException $e) {
            error_log('Gagal menyimpan metadata gambar: ' . $e->getMessage());
        }
        return ['success' => true, 'path' => $target_path, 'filename' => $filename];
    }
    return ['success' => false, 'message' => 'Gagal upload file'];
}

// Crop and upload image
function crop_and_upload_image($file, $crop_width = 800, $crop_height = 600, $x = 0, $y = 0, $quality = 90) {
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/images/uploads/";
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            return [
                'success' => false,
                'message' => 'Gagal membuat direktori upload'
            ];
        }
    }
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $original_filename = pathinfo($file["name"], PATHINFO_FILENAME);
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return [
            'success' => false,
            'message' => 'File bukan gambar yang valid'
        ];
    }
    if ($file["size"] > 5000000) {
        return [
            'success' => false,
            'message' => 'Ukuran file terlalu besar (maks. 5MB)'
        ];
    }
    if($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg" && $file_extension != "gif") {
        return [
            'success' => false,
            'message' => 'Hanya file JPG, JPEG, PNG, dan GIF yang diperbolehkan'
        ];
    }
    list($width_orig, $height_orig, $image_type) = getimagesize($file["tmp_name"]);
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($file["tmp_name"]);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($file["tmp_name"]);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($file["tmp_name"]);
            break;
        default:
            return [
                'success' => false,
                'message' => 'Format gambar tidak didukung'
            ];
    }
    $cropped_image = imagecreatetruecolor($crop_width, $crop_height);
    if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
        imagealphablending($cropped_image, false);
        imagesavealpha($cropped_image, true);
        $transparent = imagecolorallocatealpha($cropped_image, 255, 255, 255, 127);
        imagefilledrectangle($cropped_image, 0, 0, $crop_width, $crop_height, $transparent);
    }
    imagecopyresampled(
        $cropped_image,
        $image,
        0, 0,
        $x, $y,
        $crop_width, $crop_height,
        $crop_width, $crop_height
    );
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            imagejpeg($cropped_image, $target_file, $quality);
            break;
        case IMAGETYPE_PNG:
            $png_quality = round(9 - (($quality / 100) * 9));
            imagepng($cropped_image, $target_file, $png_quality);
            break;
        case IMAGETYPE_GIF:
            imagegif($cropped_image, $target_file);
            break;
    }
    imagedestroy($image);
    imagedestroy($cropped_image);
    try {
        global $pdo;
        $path_for_db = "/assets/images/uploads/" . $new_filename;
        $file_type = "image/";
        switch ($image_type) {
            case IMAGETYPE_JPEG:
                $file_type .= "jpeg";
                break;
            case IMAGETYPE_PNG:
                $file_type .= "png";
                break;
            case IMAGETYPE_GIF:
                $file_type .= "gif";
                break;
        }
        $file_size = filesize($target_file);
        $stmt = $pdo->prepare("INSERT INTO image_metadata (filename, original_filename, path, source, width, height, file_size, file_type, upload_date) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $new_filename,
            $original_filename,
            $path_for_db,
            'form',
            $crop_width,
            $crop_height,
            $file_size,
            $file_type
        ]);
        error_log("Berhasil menyimpan metadata gambar (crop): $new_filename, path: $path_for_db");
    } catch (PDOException $e) {
        error_log('Gagal menyimpan metadata gambar (crop): ' . $e->getMessage());
    }
    return [
        'success' => true,
        'path' => "/assets/images/uploads/" . $new_filename,
        'filename' => $new_filename
    ];
}

// Record article view
function record_article_view($article_id) {
    global $pdo;
    
    $sql = "UPDATE articles SET view_count = view_count + 1 WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $article_id);
    
    return $stmt->execute();
}

// Get article view count
function get_article_view_count($article_id) {
    global $pdo;
    
    $sql = "SELECT view_count FROM articles WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $article_id);
    $stmt->execute();
    
    $result = $stmt->fetch();
    return $result ? $result['view_count'] : 0;
}

function current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Membuat tabel banners jika belum ada
 */
function create_banners_table() {
    global $pdo;
    
    try {
        $sql = "CREATE TABLE IF NOT EXISTS banners (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL DEFAULT 'LangkahKata.com',
            description VARCHAR(255) NOT NULL DEFAULT 'Tempat Berbagi Informasi dan Pengalaman',
            image_url VARCHAR(255) NOT NULL DEFAULT '',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            show_title TINYINT(1) NOT NULL DEFAULT 1,
            show_description TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $pdo->exec($sql);
        
        // Periksa apakah kolom show_title dan show_description sudah ada
        try {
            $pdo->query("SELECT show_title FROM banners LIMIT 1");
        } catch (PDOException $e) {
            // Kolom belum ada, tambahkan
            $pdo->exec("ALTER TABLE banners ADD COLUMN show_title TINYINT(1) NOT NULL DEFAULT 1");
            $pdo->exec("ALTER TABLE banners ADD COLUMN show_description TINYINT(1) NOT NULL DEFAULT 1");
            error_log("Added show_title and show_description columns to banners table");
        }
        
        // Cek apakah ada data banner, jika belum tambahkan banner default
        $stmt = $pdo->query("SELECT COUNT(*) FROM banners");
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            // Tambahkan banner default
            $stmt = $pdo->prepare("INSERT INTO banners 
                (title, description, image_url, is_active, show_title, show_description, created_at, updated_at) 
                VALUES (:title, :description, :image_url, :is_active, :show_title, :show_description, NOW(), NOW())");
            
            $stmt->execute([
                'title' => 'LangkahKata.com',
                'description' => 'Tempat Berbagi Informasi dan Pengalaman',
                'image_url' => '',
                'is_active' => 1,
                'show_title' => 1,
                'show_description' => 1
            ]);
            
            error_log("Default banner created successfully");
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Error creating banners table: " . $e->getMessage());
        return false;
    }
}

/**
 * Mendapatkan data banner saat ini
 * 
 * @param bool $active_only Jika true, hanya ambil banner aktif
 * @return array|false Array berisi data banner atau false jika tidak ada
 */
function get_banner($active_only = false) {
    global $pdo;
    
    try {
        // Periksa apakah tabel banners sudah ada
        $tableExists = false;
        try {
            $check = $pdo->query("SHOW TABLES LIKE 'banners'");
            $tableExists = ($check->rowCount() > 0);
        } catch (PDOException $e) {
            error_log("Error checking banners table: " . $e->getMessage());
        }
        
        // Jika tabel tidak ada, buat terlebih dahulu
        if (!$tableExists) {
            if (create_banners_table() === false) {
                error_log("Failed to create banners table");
                return false;
            }
        }
        
        // Ambil data banner
        if ($active_only) {
            $stmt = $pdo->prepare("SELECT * FROM banners WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM banners ORDER BY id DESC LIMIT 1");
        }
        $stmt->execute();
        $banner = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Pastikan semua field memiliki nilai default yang valid
        if ($banner) {
            // Ensure show_title and show_description are properly set
            if (!isset($banner['show_title'])) {
                $banner['show_title'] = 1;
            }
            if (!isset($banner['show_description'])) {
                $banner['show_description'] = 1;
            }
            // Ensure is_active is properly set
            if (!isset($banner['is_active'])) {
                $banner['is_active'] = 1;
            }
        }
        
        // Log hasil untuk debugging
        error_log("Banner fetch result: " . ($banner ? json_encode($banner) : 'Not found'));
        
        return $banner;
    } catch (PDOException $e) {
        // Log error
        error_log("Error fetching banner: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
        
        // Jika error disebabkan tabel belum ada, coba buat tabel
        if ($e->getCode() == '42S02') { // SQLState for "table does not exist"
            if (create_banners_table() === false) {
                return false;
            }
            // Coba lagi setelah tabel dibuat
            return get_banner($active_only);
        }
        
        // Jika masih error, kembalikan false
        return false;
    }
}

/**
 * Mengupdate banner
 * 
 * @param array $data Data banner untuk diupdate
 * @return bool Sukses atau gagal
 */
function update_banner($data) {
    global $pdo;
    
    try {
        // Cek apakah sudah ada banner
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM banners");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count > 0 && !empty($data['id'])) {
            // Update banner yang ada
            $stmt = $pdo->prepare("UPDATE banners SET 
                title = :title,
                description = :description,
                image_url = :image_url,
                is_active = :is_active,
                show_title = :show_title,
                show_description = :show_description,
                updated_at = NOW()
                WHERE id = :id");
            
            $result = $stmt->execute([
                'title' => $data['title'],
                'description' => $data['description'],
                'image_url' => $data['image_url'],
                'is_active' => $data['is_active'] ?? 1,
                'show_title' => $data['show_title'] ?? 1,
                'show_description' => $data['show_description'] ?? 1,
                'id' => $data['id']
            ]);
            
            if ($result) {
                error_log("Banner updated successfully: ID=" . $data['id']);
            } else {
                error_log("Failed to update banner: ID=" . $data['id']);
            }
            
            return $result;
        } else {
            // Buat banner baru
            $stmt = $pdo->prepare("INSERT INTO banners 
                (title, description, image_url, is_active, show_title, show_description, created_at, updated_at) 
                VALUES (:title, :description, :image_url, :is_active, :show_title, :show_description, NOW(), NOW())");
            
            $result = $stmt->execute([
                'title' => $data['title'],
                'description' => $data['description'],
                'image_url' => $data['image_url'],
                'is_active' => $data['is_active'] ?? 1,
                'show_title' => $data['show_title'] ?? 1,
                'show_description' => $data['show_description'] ?? 1
            ]);
            
            if ($result) {
                error_log("New banner created successfully: Title=" . $data['title']);
            } else {
                error_log("Failed to create new banner");
            }
            
            return $result;
        }
    } catch (PDOException $e) {
        // Jika tabel belum dibuat, buat terlebih dahulu
        error_log("Error in update_banner: " . $e->getMessage());
        create_banners_table();
        
        // Coba lagi
        return update_banner($data);
    }
}

/**
 * Menghapus banner berdasarkan ID
 * 
 * @param int $banner_id ID banner yang akan dihapus
 * @return bool Sukses atau gagal
 */
function delete_banner($banner_id) {
    global $pdo;
    
    // Validasi ID
    if (empty($banner_id) || !is_numeric($banner_id)) {
        error_log("Invalid banner ID for deletion: " . var_export($banner_id, true));
        return false;
    }
    
    try {
        // Cek apakah banner dengan ID tersebut ada
        $stmt = $pdo->prepare("SELECT * FROM banners WHERE id = :id");
        $stmt->bindValue(':id', $banner_id, PDO::PARAM_INT);
        $stmt->execute();
        $banner = $stmt->fetch();
        
        if (!$banner) {
            error_log("Banner with ID $banner_id not found for deletion");
            return false;
        }
        
        // Hapus file gambar jika ada
        if (!empty($banner['image_url']) && file_exists('../' . $banner['image_url'])) {
            @unlink('../' . $banner['image_url']);
            error_log("Deleted banner image file: " . $banner['image_url']);
        }
        
        // Hapus data dari database
        $stmt = $pdo->prepare("DELETE FROM banners WHERE id = :id");
        $stmt->bindValue(':id', $banner_id, PDO::PARAM_INT);
        $result = $stmt->execute();
        
        if ($result) {
            error_log("Banner with ID $banner_id successfully deleted");
        } else {
            error_log("Failed to delete banner with ID $banner_id from database");
        }
        
        return $result;
    } catch (PDOException $e) {
        error_log("Database error when deleting banner: " . $e->getMessage());
        return false;
    }
}

// Fungsi url_base() ini secara umum sudah benar dalam menentukan base URL aplikasi
// Namun, ada beberapa hal yang bisa diperbaiki:
// 1. Penambahan '/' di akhir $path agar konsisten dengan url_base2()
// 2. Penghapusan '/' di akhir $host agar tidak double slash
// 3. Penggunaan rtrim() untuk memastikan tidak ada double slash
// 4. Penyesuaian agar lebih fleksibel jika domain berubah

function url_base() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
    // Hilangkan '/' di akhir host agar tidak double slash
    $host = ($_SERVER['HTTP_HOST'] === 'langkahkata.com') ? 'langkahkata.com' : 'localhost';
    $path = dirname($_SERVER['PHP_SELF']);
    // Pastikan $path tidak hanya berisi '.' (artinya root)
    $path = ($path === '/' || $path === '\\' || $path === '.') ? '' : $path;
    // Gabungkan dan pastikan hanya ada satu '/' di antara host dan path
    $url = rtrim($protocol . $host . '/' . ltrim($path, '/'), '/') . '/';
    return $url;
}

function url_base2() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']);
    $path = str_replace('/includes', '', $path);
    $path = str_replace('/articles', '', $path);
    $path = str_replace('/categories', '', $path);
    $path = str_replace('/comments', '', $path);
    $path = str_replace('/media-manager', '', $path);
    $path = str_replace('/banner-manager', '', $path);
    $path = str_replace('/user', '', $path);
    $path = str_replace('/admin', '', $path);
    return $protocol . $host . $path . '/';
}

// Fungsi slugify untuk membuat slug dari nama kategori atau judul
function slugify($text) {
    // Ganti karakter non huruf/angka dengan strip
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // Transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // Hapus karakter yang tidak diinginkan
    $text = preg_replace('~[^-a-z0-9]+~i', '', $text);
    // Hilangkan strip di awal/akhir
    $text = trim($text, '-');
    // Hilangkan duplikasi strip
    $text = preg_replace('~-+~', '-', $text);
    // Lowercase
    $text = strtolower($text);
    if (empty($text)) {
        return 'n-a';
    }
    return $text;
}

?>