# Panduan Implementasi Jumlah Akses Artikel

Berikut adalah langkah-langkah untuk mengimplementasikan fitur jumlah akses/baca artikel pada website:

## 1. Menambahkan Kolom View Count pada Database

Pertama, tambahkan kolom view_count pada tabel articles dengan menjalankan SQL berikut:

```sql
ALTER TABLE articles ADD COLUMN view_count INT DEFAULT 0;
```

Anda bisa menggunakan phpMyAdmin atau menjalankan file SQL `add_view_count.sql` yang telah dibuat.

## 2. Fungsi untuk Mencatat dan Mendapatkan Jumlah Akses

Pada file `includes/functions.php` telah ditambahkan dua fungsi berikut:

```php
// Fungsi untuk mencatat jumlah akses artikel
function record_article_view($article_id) {
    global $pdo;
    
    $sql = "UPDATE articles SET view_count = view_count + 1 WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $article_id);
    
    return $stmt->execute();
}

// Fungsi untuk mendapatkan jumlah akses artikel
function get_article_view_count($article_id) {
    global $pdo;
    
    $sql = "SELECT view_count FROM articles WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $article_id);
    $stmt->execute();
    
    $result = $stmt->fetch();
    return $result ? $result['view_count'] : 0;
}
```

## 3. Merekam Akses Artikel

Pada file `article.php`, setelah mengambil data artikel dari database, tambahkan kode berikut:

```php
// Rekam jumlah akses artikel
record_article_view($article['id']);
```

## 4. Menampilkan Jumlah Akses di Halaman Admin

Pada file `admin/articles/index.php` telah ditambahkan kolom Jumlah Akses di tabel artikel dengan kode:

```php
<th style="width: 10%;">Jumlah Akses</th>
```

Dan pada baris tabel untuk setiap artikel:

```php
<td>
    <span class="view-count">
        <i class="fas fa-eye"></i> <?php echo number_format($article['view_count']); ?>
    </span>
</td>
```

## 5. Menampilkan Jumlah Akses di Halaman Dashboard Admin

Pada file `admin/dashboard.php` juga telah ditambahkan kolom Jumlah Akses dengan kode yang sama.

## 6. Menampilkan Jumlah Akses di Halaman Artikel

Pada file `article.php`, di bagian meta artikel, telah ditambahkan kode:

```php
<span class="article-views">
    <i class="fas fa-eye"></i>
    <?php echo number_format($article['view_count']); ?> kali dibaca
</span>
```

## 7. Styling CSS

Pada file `assets/css/style.css` telah ditambahkan CSS untuk tampilan jumlah akses:

```css
/* Styling untuk jumlah akses/view */
.view-count {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
    color: #555;
    background-color: #f5f5f5;
    padding: 4px 8px;
    border-radius: 4px;
    margin-right: 10px;
}

.view-count i {
    color: #ff6b2b;
    font-size: 14px;
}

.article-views {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
    color: #555;
    margin-left: 15px;
}

.article-views i {
    color: #ff6b2b;
    font-size: 14px;
}
```

## Pengembangan Lebih Lanjut

Beberapa pengembangan yang bisa dilakukan selanjutnya:
1. Implementasi pencegahan duplikasi penghitung saat halaman di-refresh
2. Menambahkan statistik jumlah akses harian/mingguan/bulanan
3. Fitur artikel populer berdasarkan jumlah akses
4. Dashboard statistik view untuk admin 