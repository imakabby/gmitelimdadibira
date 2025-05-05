-- File add_youtube_video_id.sql
-- Menambahkan kolom youtube_video_id ke tabel articles

USE news_website;

-- Periksa apakah kolom youtube_video_id sudah ada
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'webberita'
    AND TABLE_NAME = 'articles'
    AND COLUMN_NAME = 'youtube_video_id'
);

-- Tambahkan kolom jika belum ada
SET @query = IF(
    @column_exists = 0,
    'ALTER TABLE articles ADD COLUMN youtube_video_id VARCHAR(20) DEFAULT NULL AFTER content',
    'SELECT "Kolom youtube_video_id sudah ada."'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt; 