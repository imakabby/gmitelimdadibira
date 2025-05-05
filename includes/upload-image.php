<?php
require_once 'functions.php';
require_once '../config/database.php';

// Pastikan user sudah login
require_login();

// Set debugging ke file log
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Log mulai proses upload
error_log("====== MULAI PROSES UPLOAD GAMBAR ======");
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("CONTENT_TYPE: " . $_SERVER['CONTENT_TYPE']);
error_log("FILES: " . print_r($_FILES, true));
error_log("POST: " . print_r($_POST, true));

// Set header JSON untuk respons
header('Content-Type: application/json');

// Cek apakah ini adalah permintaan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("ERROR: Bukan metode POST");
    echo json_encode([
        'uploaded' => 0,
        'error' => [
            'message' => 'Metode HTTP tidak valid. Harap gunakan POST.'
        ]
    ]);
    exit;
}

// Deteksi sumber upload (CKEditor atau form upload biasa)
$source = isset($_POST['source']) ? $_POST['source'] : 'ckeditor';
error_log("Sumber upload: $source");

// Cek apakah ada file yang diupload - periksa semua kemungkinan nama field
$upload_field_names = ['upload', 'file', 'files', 'image', 'images'];
$file = null;
$field_name = null;

foreach ($upload_field_names as $field) {
    if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$field];
        $field_name = $field;
        break;
    }
}

if (!$file) {
    error_log("ERROR: Tidak ada file yang diupload. Field yang dicek: " . implode(', ', $upload_field_names));
    error_log("Semua FILES: " . print_r($_FILES, true));
    
    $error_message = 'Tidak ada file yang diupload.';
    if (isset($_FILES) && !empty($_FILES)) {
        foreach ($_FILES as $key => $file_info) {
            if (isset($file_info['error'])) {
                switch ($file_info['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                        $error_message = 'File terlalu besar (melebihi upload_max_filesize di php.ini).';
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $error_message = 'File terlalu besar (melebihi MAX_FILE_SIZE di form HTML).';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $error_message = 'File hanya terupload sebagian.';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $error_message = 'Tidak ada file yang diupload.';
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $error_message = 'Folder sementara tidak ditemukan.';
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $error_message = 'Gagal menyimpan file ke disk.';
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $error_message = 'Upload dihentikan oleh ekstensi PHP.';
                        break;
                    default:
                        $error_message = 'Terjadi kesalahan upload yang tidak diketahui. Kode: ' . $file_info['error'];
                }
            }
        }
    }
    
    echo json_encode([
        'uploaded' => 0,
        'error' => [
            'message' => $error_message
        ]
    ]);
    exit;
}

error_log("File ditemukan dengan nama field: $field_name");
error_log("Informasi file: " . print_r($file, true));

// Validasi tipe file
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'];
if (!in_array($file['type'], $allowed_types)) {
    error_log("ERROR: Tipe file tidak valid: " . $file['type']);
    echo json_encode([
        'uploaded' => 0,
        'error' => [
            'message' => 'Tipe file tidak valid. Hanya diperbolehkan: JPG, PNG, GIF, BMP, dan WEBP.'
        ]
    ]);
    exit;
}

// Validasi ukuran file (max 5MB)
$max_size = 5 * 1024 * 1024; // 5MB dalam bytes
if ($file['size'] > $max_size) {
    error_log("ERROR: File terlalu besar: " . $file['size'] . " bytes");
    echo json_encode([
        'uploaded' => 0,
        'error' => [
            'message' => 'File terlalu besar. Ukuran maksimum adalah 5MB.'
        ]
    ]);
    exit;
}

// Generate nama file unik
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$original_filename = pathinfo($file['name'], PATHINFO_FILENAME);
$filename = uniqid() . '.' . $extension;

// Path untuk menyimpan file berdasarkan sumber upload
$base_upload_dir = '../assets/images/uploads/';
$upload_dir = $source === 'ckeditor' ? $base_upload_dir . 'ckeditor/' : $base_upload_dir;

// Pastikan direktori ada
if (!file_exists($upload_dir)) {
    error_log("Membuat direktori upload: $upload_dir");
    if (!mkdir($upload_dir, 0777, true)) {
        error_log("ERROR: Gagal membuat direktori upload");
        echo json_encode([
            'uploaded' => 0,
            'error' => [
                'message' => 'Gagal membuat direktori untuk upload.'
            ]
        ]);
        exit;
    }
}

// Pastikan direktori writable
if (!is_writable($upload_dir)) {
    error_log("ERROR: Direktori upload tidak writable: $upload_dir");
    echo json_encode([
        'uploaded' => 0,
        'error' => [
            'message' => 'Direktori upload tidak dapat ditulis oleh server.'
        ]
    ]);
    exit;
}

$target_path = $upload_dir . $filename;

// Hapus file temporari sebelum mencoba move_uploaded_file
if (file_exists($target_path)) {
    error_log("File dengan nama yang sama sudah ada, menghapus: $target_path");
    unlink($target_path);
}

// Upload file
$upload_success = false;
try {
    // Coba dengan move_uploaded_file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        error_log("Berhasil upload file dengan move_uploaded_file ke: $target_path");
        $upload_success = true;
    } 
    // Jika gagal, coba dengan copy
    else if (copy($file['tmp_name'], $target_path)) {
        error_log("Berhasil upload file dengan copy ke: $target_path");
        $upload_success = true;
    } 
    // Jika masih gagal, coba dengan file_put_contents
    else {
        $content = file_get_contents($file['tmp_name']);
        if ($content !== false && file_put_contents($target_path, $content)) {
            error_log("Berhasil upload file dengan file_put_contents ke: $target_path");
            $upload_success = true;
        } else {
            error_log("ERROR: Semua metode upload gagal");
            $php_error = error_get_last();
            error_log("PHP error: " . print_r($php_error, true));
        }
    }
} catch (Exception $e) {
    error_log("EXCEPTION pada upload: " . $e->getMessage());
}

// Verifikasi apakah file berhasil diupload
if ($upload_success && file_exists($target_path)) {
    // Dapatkan ukuran gambar
    $image_size = getimagesize($target_path);
    $width = $image_size[0];
    $height = $image_size[1];
    
    // Simpan metadata gambar ke database
    try {
        $stmt = $pdo->prepare("INSERT INTO image_metadata (filename, original_filename, path, source, width, height, file_size, file_type, upload_date) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                               
        $relative_path = '' . str_replace('../', '/', $target_path);
        
        $stmt->execute([
            $filename,
            $original_filename,
            $relative_path,
            $source,
            $width,
            $height,
            $file['size'],
            $file['type']
        ]);
        
        error_log("Metadata gambar berhasil disimpan ke database untuk: $filename");
    } catch (PDOException $e) {
        error_log("ERROR: Gagal menyimpan metadata gambar: " . $e->getMessage());
        // Tetap lanjutkan meskipun gagal menyimpan metadata
    }
    
    // Return URL gambar untuk CKEditor
    $image_url = '' . str_replace('../', '/', $target_path);
    error_log("File berhasil diupload. Image URL: $image_url");
    
    // Format respons sesuai dengan yang diharapkan oleh CKEditor
    echo json_encode([
        'uploaded' => 1,
        'fileName' => $filename,
        'url' => $image_url,
        'width' => $width,
        'height' => $height
    ]);
} else {
    // Log semua error yang terjadi
    $php_error = error_get_last();
    error_log("ERROR: Gagal mengupload file. PHP error: " . ($php_error ? print_r($php_error, true) : 'Tidak ada PHP error'));
    error_log("File tmp_name ada: " . (file_exists($file['tmp_name']) ? 'Ya' : 'Tidak'));
    error_log("Target path ada setelah upload: " . (file_exists($target_path) ? 'Ya' : 'Tidak'));
    
    echo json_encode([
        'uploaded' => 0,
        'error' => [
            'message' => 'Gagal mengupload file. Silakan coba lagi atau hubungi administrator.'
        ]
    ]);
}

error_log("====== SELESAI PROSES UPLOAD GAMBAR ======"); 