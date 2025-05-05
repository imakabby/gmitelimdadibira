-- Script untuk menambahkan kolom view_count ke tabel articles
USE news_website;
ALTER TABLE articles ADD COLUMN view_count INT DEFAULT 0; 