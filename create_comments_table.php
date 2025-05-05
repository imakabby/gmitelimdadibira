<?php
// Script untuk membuat tabel komentar

require_once 'config/database.php';

try {
    // Cek apakah tabel comments sudah ada
    $check_table = $pdo->query("SHOW TABLES LIKE 'comments'");
    if ($check_table->rowCount() == 0) {
        // Buat tabel comments jika belum ada
        $sql = "CREATE TABLE comments (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            article_id INT(11) UNSIGNED NOT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            comment TEXT NOT NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (article_id, status)
        )";
        
        $pdo->exec($sql);
        echo "Tabel comments berhasil dibuat!";
    } else {
        echo "Tabel comments sudah ada.";
    }
    
    // Tampilkan struktur tabel
    echo "<h2>Struktur Tabel Comments:</h2>";
    $columns = $pdo->query("SHOW COLUMNS FROM comments");
    echo "<pre>";
    while ($column = $columns->fetch(PDO::FETCH_ASSOC)) {
        print_r($column);
    }
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
} 