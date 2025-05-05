<?php
$host = 'localhost';
$db   = 'u545696814_news_website';
$user = 'u545696814_imakabby_news';
$pass = 'Imanuel31#@';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Otomatis buat tabel banners jika belum ada
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
    
    // Periksa apakah ada data banner, jika tidak buat data default
    $stmt = $pdo->query("SELECT COUNT(*) FROM banners");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Tambahkan banner default
        $stmt = $pdo->prepare("INSERT INTO banners 
            (title, description, image_url, is_active, created_at, updated_at) 
            VALUES (:title, :description, :image_url, :is_active, NOW(), NOW())");
        
        $stmt->execute([
            'title' => 'LangkahKata.com',
            'description' => 'Tempat Berbagi Informasi dan Pengalaman',
            'image_url' => '',
            'is_active' => 1
        ]);
        
        error_log("Default banner created");
    }
    
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    
    // Jika database tidak ada, coba buatkan databasenya
    if ($e->getCode() == 1049) {
        try {
            $pdo_without_db = new PDO("mysql:host=$host", $user, $pass);
            $pdo_without_db->exec("CREATE DATABASE IF NOT EXISTS $db");
            $pdo_without_db->exec("USE $db");
            
            // Buat tabel banners
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
            
            $pdo_without_db->exec($sql);
            
            // Tambahkan banner default
            $stmt = $pdo_without_db->prepare("INSERT INTO banners 
                (title, description, image_url, is_active, created_at, updated_at) 
                VALUES (:title, :description, :image_url, :is_active, NOW(), NOW())");
            
            $stmt->execute([
                'title' => 'LangkahKata.com',
                'description' => 'Tempat Berbagi Informasi dan Pengalaman',
                'image_url' => '',
                'is_active' => 1
            ]);
            
            error_log("Database and default banner created");
            
            // Reconnect dengan database baru
            $pdo = new PDO($dsn, $user, $pass, $options);
            
        } catch (PDOException $inner_ex) {
            error_log("Failed to create database: " . $inner_ex->getMessage());
            die("Could not connect to database: " . $inner_ex->getMessage());
        }
    } else {
        die("Could not connect to database: " . $e->getMessage());
    }
}
?>