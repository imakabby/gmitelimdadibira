<?php
// article.php - Display a single article
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/date_helper.php';

// Get article slug from URL
$slug = isset($_GET['slug']) ? clean_input($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: ../beranda");
    exit;
}

// Check if the featured_image column exists in the articles table
$column_exists = false;
try {
    $check_column = $pdo->query("SHOW COLUMNS FROM articles LIKE 'featured_image'");
    $column_exists = ($check_column->rowCount() > 0);
} catch (PDOException $e) {
    // Column doesn't exist
}

// Get article details
if ($column_exists) {
    $sql = "SELECT a.*, c.name as category_name, c.id as category_id, u.name as author 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE a.slug = :slug AND a.status = 'published'";
} else {
    $sql = "SELECT a.id, a.title, a.slug, a.content, a.excerpt, a.category_id, a.user_id, a.status, a.created_at, a.updated_at, 
            c.name as category_name, c.id as category_id, u.name as author 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE a.slug = :slug AND a.status = 'published'";
}

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':slug', $slug);
$stmt->execute();
$article = $stmt->fetch();

if (!$article) {
    header("Location: ../beranda");
    exit;
}

// Siapkan variabel untuk header
$page_title = $article['title'] . ' - GMIT Elim Dadibira';

// Buat meta description dari excerpt atau content
$excerpt = strip_tags($article['content']);
$excerpt = substr($excerpt, 0, 160);
$excerpt = rtrim($excerpt, "!,.-");
$excerpt = substr($excerpt, 0, strrpos($excerpt, ' '));
$page_description = $excerpt . '...';

// Set favicon (gunakan favicon default atau gambar artikel jika tersedia)
$favicon = !empty($article['image']) ? $article['image'] : 'assets/images/favicon.ico';

// Set canonical URL untuk SEO (format URL baru tanpa .php)
$canonical_url = 'https://' . $_SERVER['HTTP_HOST'] . '/artikel/' . $slug;

// Rekam jumlah akses artikel
record_article_view($article['id']);

// Get latest articles for running text
$sql = "SELECT id, title, slug, created_at FROM articles WHERE status = 'published' ORDER BY created_at DESC LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$latest_articles = $stmt->fetchAll();

// If featured_image column doesn't exist, set it to null
if (!$column_exists) {
    $article['image'] = null;
}

// Get all categories for the sidebar with article count
$sql = "SELECT c.*, COUNT(a.id) as article_count 
        FROM categories c 
        LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published' 
        GROUP BY c.id 
        ORDER BY c.name";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll();

// Get related articles
if ($column_exists) {
    $sql = "SELECT a.id, a.title, a.slug, a.image, a.image_description, a.created_at 
            FROM articles a 
            WHERE a.category_id = :category_id AND a.id != :article_id AND a.status = 'published' 
            ORDER BY a.created_at DESC 
            LIMIT 4";
} else {
    $sql = "SELECT a.id, a.title, a.slug, a.created_at 
            FROM articles a 
            WHERE a.category_id = :category_id AND a.id != :article_id AND a.status = 'published' 
            ORDER BY a.created_at DESC 
            LIMIT 4";
}

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':category_id', $article['category_id']);
$stmt->bindValue(':article_id', $article['id']);
$stmt->execute();
$related_articles = $stmt->fetchAll();

// If featured_image column doesn't exist, set it to null for related articles
if (!$column_exists) {
    foreach ($related_articles as &$related) {
        $related['image'] = null;
    }
}

// Include header
include 'includes/header.php';
?>

<!-- Running Text -->
<div class="running-text">
    <div class="running-text-label">
        <span>Berita Terbaru:</span>
    </div>
    <div class="running-text-content">
        <p>
            <?php foreach ($latest_articles as $latest): ?>
                <a href="<?php echo url_base(); ?>artikel/<?php echo $latest['slug']; ?>">
                    <?php echo htmlspecialchars($latest['title']); ?>
                    <small>(<?php echo waktuYangLalu($latest['created_at']); ?>)</small>
                </a>
            <?php endforeach; ?>
        </p>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-8">
            <article class="single-article">
                <h1 class="article-title"><?php echo $article['title']; ?></h1>

                <div class="article-excerpt">
                    <?php 
                    $excerpt = strip_tags($article['content']);
                    $excerpt = substr($excerpt, 0, 200);
                    $excerpt = rtrim($excerpt, "!,.-");
                    $excerpt = substr($excerpt, 0, strrpos($excerpt, ' '));
                    echo $excerpt . '...';
                    ?>
                </div>

                <div class="article-meta">
                    <span class="article-date">
                        <i class="far fa-calendar-alt"></i>
                        <?php echo waktuYangLalu($article['created_at']); ?>
                    </span>
                    <span class="article-author">
                        <i class="far fa-user"></i>
                        <?php echo $article['author']; ?>
                    </span>
                    <span class="article-views">
                        <i class="fas fa-eye"></i>
                        <?php echo number_format($article['view_count']); ?> kali dibaca
                    </span>
                </div>

                <div class="article-meta-share">
                    <div class="category-badge">
                        <a href="<?php echo url_base(); ?>kategori/<?php echo $article['category_id']; ?>">
                            <?php echo $article['category_name']; ?>
                        </a>
                    </div>
                    <div class="share-buttons">
                        <a href="https://wa.me/?text=<?php echo urlencode($article['title'] . ' - ' . current_url()); ?>" class="share-button whatsapp" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(current_url()); ?>" class="share-button facebook" target="_blank">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($article['title']); ?>&url=<?php echo urlencode(current_url()); ?>" class="share-button twitter" target="_blank">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <button class="share-button copy-link" onclick="copyCurrentURL()" title="Salin tautan">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>
                
                <?php if (!empty($article['image'])): ?>
                    <div class="article-featured-image">
                        <img src="<?php echo $article['image']; ?>" alt="<?php echo $article['title']; ?>">
                        <div class="image-caption">
                            <i class="fas fa-camera"></i>
                            <span>Foto: <?php echo $article['image_description']; ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="article-content">
                    <?php echo $article['content']; ?>
                </div>

                <div class="article-meta-share" style="margin-top: 20px; padding-bottom: 10px;">
                    <div class="share-buttons">
                        <a href="https://wa.me/?text=<?php echo urlencode($article['title'] . ' - ' . current_url()); ?>" class="share-button whatsapp" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(current_url()); ?>" class="share-button facebook" target="_blank">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($article['title']); ?>&url=<?php echo urlencode(current_url()); ?>" class="share-button twitter" target="_blank">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <button class="share-button copy-link" onclick="copyCurrentURL()" title="Salin tautan">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>
            </article>

            <?php if (!empty($article['youtube_video_id'])): ?>
            <div class="article-video">
                <h2>Video</h2>
                <div class="video-container">
                    <iframe width="100%" height="315" 
                            src="https://www.youtube.com/embed/<?php echo $article['youtube_video_id']; ?>" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                    </iframe>
                </div>
                <div class="video-title">
                    <h3><?php echo $article['title']; ?></h3>
                </div>
            </div>
            <?php endif; ?>

            <!-- Komentar -->
            <div class="article-comments">
                <h2>Komentar</h2>
                
                <div class="comment-container">
                    <div class="comment-form">
                        <h3><i class="fas fa-edit"></i> Tinggalkan Komentar</h3>
                        <p class="form-info">Silakan bagikan pendapat Anda tentang artikel ini. Komentar akan ditampilkan setelah dimoderasi.</p>
                        
                        <form id="commentForm" method="post">
                            <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                            
                            <div class="form-group mb-3">
                                <label for="name" class="form-label required">Nama</label>
                                <input type="text" class="form-control" id="name" name="name" required placeholder="Masukkan nama Anda">
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="email" class="form-label required">Email (tidak akan ditampilkan)</label>
                                <input type="email" class="form-control" id="email" name="email" required placeholder="Masukkan email Anda">
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="comment" class="form-label required">Komentar</label>
                                <textarea class="form-control" id="comment" name="comment" rows="4" required placeholder="Tulis komentar Anda di sini..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn-comment">Kirim Komentar</button>
                        </form>
                        <div id="comment-alert"></div>
                    </div>
                    
                    <div class="comment-list">
                        <h3><i class="fas fa-comments"></i> Komentar Pembaca</h3>
                        
                        <?php
                        // Ambil komentar yang disetujui untuk artikel ini
                        $comments_sql = "SELECT * FROM comments 
                                        WHERE article_id = :article_id AND status = 'approved' 
                                        ORDER BY created_at DESC";
                        $comments_stmt = $pdo->prepare($comments_sql);
                        $comments_stmt->bindValue(':article_id', $article['id']);
                        $comments_stmt->execute();
                        $comments = $comments_stmt->fetchAll();
                        
                        if (count($comments) > 0): ?>
                            <div class="comments-wrapper">
                                <?php foreach ($comments as $comment): ?>
                                    <div class="comment-item">
                                        <div class="comment-header">
                                            <div class="comment-author">
                                                <?php echo htmlspecialchars($comment['name']); ?>
                                            </div>
                                            <div class="comment-date">
                                                <i class="far fa-clock"></i> 
                                                <?php echo waktuYangLalu($comment['created_at']); ?>
                                            </div>
                                        </div>
                                        <div class="comment-content">
                                            <?php echo htmlspecialchars($comment['comment']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-comments">
                                <p>Belum ada komentar untuk artikel ini. Jadilah yang pertama berkomentar!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($related_articles)): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h2>Artikel Terkait</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($related_articles as $related): ?>
                                <div class="col-md-4">
                                    <div class="article-card">
                                        <?php if (!empty($related['image'])): ?>
                                            <div class="article-image">
                                                <a href="<?php echo url_base(); ?>artikel/<?php echo $related['slug']; ?>">
                                                    <img src="<?php echo $related['image']; ?>" alt="<?php echo $related['title']; ?>">
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <div class="article-content">
                                            <h2>
                                                <a href="<?php echo url_base(); ?>artikel/<?php echo $related['slug']; ?>">
                                                    <?php echo $related['title']; ?>
                                                </a>
                                            </h2>
                                            <div class="article-meta">
                                                <span class="article-date">
                                                    <i class="far fa-calendar-alt"></i>
                                                    <?php echo waktuYangLalu($related['created_at']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
            <?php include 'includes/sidebar.php'; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const content = document.querySelector('.article-content');
        const firstParagraph = content.querySelector('p:first-child');
        const alamatText = "GMIT Elim Dadibira, Kalabahi - ";
        
        if (firstParagraph) {
            // Cek apakah paragraf pertama sudah mengandung alamat
            if (!firstParagraph.textContent.includes(alamatText)) {
                // Buat span untuk alamat agar bisa diberi style
                const alamatSpan = document.createElement('span');
                alamatSpan.textContent = alamatText;
                alamatSpan.style.fontWeight = "bold";
                alamatSpan.style.color = "#CC6C2A";
                
                // Tambahkan alamat ke awal paragraf
                firstParagraph.insertBefore(alamatSpan, firstParagraph.firstChild);
            }
        }
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('commentForm');
    const alertBox = document.getElementById('comment-alert');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Form submit intercepted!');
        alertBox.innerHTML = '';
        const formData = new FormData(form);
        fetch('<?php echo url_base(); ?>process_comment.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alertBox.innerHTML = '<div class="alert alert-success" style="margin: 0 10px 10px 10px;"><i class="fas fa-check-circle"></i> ' + data.message + '</div>';
                form.reset();
            } else {
                alertBox.innerHTML = '<div class="alert alert-danger" style="margin: 0 10px 10px 10px;"><i class="fas fa-exclamation-circle"></i> ' + data.message + '</div>';
            }
        })
        .catch(err => {
            alertBox.innerHTML = '<div class="alert alert-danger" style="margin: 0 10px 10px 10px;"><i class="fas fa-exclamation-circle"></i> Gagal mengirim komentar. Silakan coba lagi.</div>';
        });
    });
});
</script>