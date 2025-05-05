-- File create_comments_table.sql
-- Script untuk membuat tabel komentar di database news_website

USE news_website;

-- Cek apakah tabel comments sudah ada
SET @table_exists = (
    SELECT COUNT(*)
    FROM information_schema.tables
    WHERE table_schema = 'news_website'
    AND table_name = 'comments'
);

-- Drop tabel jika sudah ada (opsional, uncomment jika perlu)
-- DROP TABLE IF EXISTS comments;

-- Buat tabel comments jika belum ada
SET @create_table_sql = IF(
    @table_exists = 0,
    'CREATE TABLE comments (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        article_id INT(11) UNSIGNED NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        comment TEXT NOT NULL,
        status ENUM("pending", "approved", "rejected") DEFAULT "pending",
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (article_id, status)
    )',
    'SELECT "Tabel comments sudah ada."'
);

PREPARE stmt FROM @create_table_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Tampilkan struktur tabel
SHOW COLUMNS FROM comments; 