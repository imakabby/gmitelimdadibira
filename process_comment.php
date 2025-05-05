<?php
// process_comment.php - Memproses pengiriman komentar
require_once 'config/database.php';
require_once 'includes/functions.php';

// Inisialisasi respons
$response = [
    'success' => false,
    'message' => ''
];

// Hanya proses POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $article_id = isset($_POST['article_id']) ? (int)$_POST['article_id'] : 0;
    $name = isset($_POST['name']) ? clean_input($_POST['name']) : '';
    $email = isset($_POST['email']) ? clean_input($_POST['email']) : '';
    $comment = isset($_POST['comment']) ? clean_input($_POST['comment']) : '';
    $created_at = date('Y-m-d H:i:s');
    
    // Validasi input
    if (empty($article_id) || empty($name) || empty($email) || empty($comment)) {
        $response['message'] = 'Semua field harus diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Email tidak valid.';
    } else {
        try {
            // Verifikasi artikel ada
            $check_article = $pdo->prepare("SELECT id FROM articles WHERE id = ?");
            $check_article->execute([$article_id]);
            if ($check_article->rowCount() === 0) {
                $response['message'] = 'Artikel tidak ditemukan.';
            } else {
                // Simpan komentar
                $sql = "INSERT INTO comments (article_id, name, email, comment, status, created_at) 
                        VALUES (:article_id, :name, :email, :comment, :status, :created_at)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':article_id', $article_id, PDO::PARAM_INT);
                $stmt->bindValue(':name', $name);
                $stmt->bindValue(':email', $email);
                $stmt->bindValue(':comment', $comment);
                $stmt->bindValue(':status', 'pending');
                $stmt->bindValue(':created_at', $created_at);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Komentar berhasil dikirim dan sedang dalam proses moderasi.';
                } else {
                    $response['message'] = 'Gagal menyimpan komentar. Silakan coba lagi nanti.';
                    error_log('Gagal insert komentar: ' . print_r($stmt->errorInfo(), true));
                }
            }
        } catch (PDOException $e) {
            $response['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
            error_log('PDOException komentar: ' . $e->getMessage());
        }
    }
} else {
    $response['message'] = 'Metode request tidak valid.';
}

// Return JSON response untuk AJAX
header('Content-Type: application/json');
echo json_encode($response);
exit; 