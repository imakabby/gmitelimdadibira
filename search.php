<?php
// search.php - Search for articles
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/date_helper.php';

// Get search query
$search_query = isset($_GET['q']) ? clean_input($_GET['q']) : '';

if (empty($search_query)) {
    header("Location: beranda");
    exit;
}

// Check if the featured_image column exists in the articles table
$column_exists = false;
try {
    $check_column = $pdo->query("SHOW COLUMNS FROM articles LIKE 'image'");
    $column_exists = ($check_column->rowCount() > 0);
} catch (PDOException $e) {
    // Column doesn't exist
}

// Get page number for pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 10; // Number of articles per page
$offset = ($page - 1) * $per_page;

// Get total number of matching articles
$sql = "SELECT COUNT(*) as total FROM articles 
        WHERE status = 'published' AND 
        (title LIKE :search1 OR content LIKE :search2)";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':search1', '%' . $search_query . '%');
$stmt->bindValue(':search2', '%' . $search_query . '%');
$stmt->execute();
$total_articles = $stmt->fetch()['total'];

// Calculate total pages
$total_pages = ceil($total_articles / $per_page);

// Get matching articles for current page
if ($column_exists) {
    $sql = "SELECT a.*, c.name as category_name, u.name as author 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE a.status = 'published' AND 
            (a.title LIKE :search1 OR a.content LIKE :search2) 
            ORDER BY a.created_at DESC 
            LIMIT :limit OFFSET :offset";
} else {
    $sql = "SELECT a.id, a.title, a.slug, a.category_id, a.user_id, a.status, a.created_at, 
            c.name as category_name, u.name as author 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE a.status = 'published' AND 
            (a.title LIKE :search1 OR a.content LIKE :search2) 
            ORDER BY a.created_at DESC 
            LIMIT :limit OFFSET :offset";
}

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':search1', '%' . $search_query . '%');
$stmt->bindValue(':search2', '%' . $search_query . '%');
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll();

// If featured_image column doesn't exist, set it to null for all articles
if (!$column_exists) {
    foreach ($articles as &$article) {
        $article['image'] = null;
    }
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

// Get latest articles for running text
$sql = "SELECT a.*, c.name as category_name, u.name as author 
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.id 
        LEFT JOIN users u ON a.user_id = u.id 
        WHERE a.status = 'published' 
        ORDER BY a.created_at DESC 
        LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$latest_articles = $stmt->fetchAll();

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
            <?php foreach ($latest_articles as $article): ?>
                <a href="artikel/<?php echo $article['slug']; ?>">
                    <?php echo htmlspecialchars($article['title']); ?>
                    <small>(<?php echo formatTanggalIndonesia($article['created_at']); ?>)</small>
                </a>
            <?php endforeach; ?>
        </p>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2 style="padding-left: 50px;">Hasil Pencarian: "<?php echo htmlspecialchars($search_query); ?>"</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($articles)): ?>
                        <div class="search-empty">
                            <div class="search-empty-icon" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                <i class="fas fa-search" style="font-size: 50px; color: #CC6C2A; margin-top: 30px;"></i>
                                <h3 style="margin-top: 30px;">Tidak ada hasil pencarian yang sesuai</h3>
                            </div>
                            <div class="search-empty-tips" style="margin-top: 30px; margin-left: 30px;">
                                <p>Saran pencarian:</p>
                                <ul style="list-style-type: none; padding-left: 0;">
                                    <li><i class="fas fa-check-circle" style="color:rgb(34, 206, 92);"></i> Pastikan semua kata ditulis dengan benar</li>
                                    <li><i class="fas fa-check-circle" style="color:rgb(34, 206, 92);"></i> Coba kata kunci yang berbeda</li>
                                    <li><i class="fas fa-check-circle" style="color:rgb(34, 206, 92);"></i> Coba kata kunci yang lebih umum</li>
                                </ul>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="search-result-count">Ditemukan <?php echo $total_articles; ?> hasil</p>
                        
                        <?php foreach ($articles as $article): ?>
                            <div class="article-card">
                                <?php if (!empty($article['image'])): ?>
                                    <div class="article-image">
                                        <a href="artikel/<?php echo $article['slug']; ?>">
                                            <img src="<?php echo $article['image']; ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="article-image">
                                        <a href="artikel/<?php echo $article['slug']; ?>">
                                            <img src="assets/images/placeholder.png" alt="No Image" style="opacity:0.7;filter:grayscale(1);">
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="article-content">
                                    <div class="article-main">
                                        <h2>
                                            <a href="artikel/<?php echo $article['slug']; ?>">
                                                <?php echo highlight(htmlspecialchars($article['title']), $search_query); ?>
                                            </a>
                                        </h2>
                                        
                                        <div class="article-meta">
                                            <span class="article-category">
                                                <i class="fas fa-folder"></i>
                                                <a href="kategori/<?php echo $article['category_id']; ?>-<?php echo slugify($article['category_name']); ?>">
                                                    <?php echo htmlspecialchars($article['category_name']); ?>
                                                </a>
                                            </span>
                                            <span class="article-date">
                                                <i class="far fa-calendar-alt"></i>
                                                <?php echo formatTanggalIndonesia($article['created_at']); ?>
                                            </span>
                                            <span class="article-author">
                                                <i class="far fa-user"></i>
                                                <?php echo htmlspecialchars($article['author']); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="article-excerpt">
                                            <?php 
                                            $excerpt = strip_tags($article['content']);
                                            $excerpt = substr($excerpt, 0, 200);
                                            $excerpt = rtrim($excerpt, "!,.-");
                                            $lastSpace = strrpos($excerpt, ' ');
                                            if ($lastSpace !== false) {
                                                $excerpt = substr($excerpt, 0, $lastSpace);
                                            }
                                            echo highlight($excerpt, $search_query) . '...';
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <a href="artikel/<?php echo $article['slug']; ?>" class="read-more">
                                        Baca selengkapnya
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="cari?q=<?php echo urlencode($search_query); ?>&page=<?php echo ($page - 1); ?>" class="prev">&laquo; Previous</a>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <span class="current"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="cari?q=<?php echo urlencode($search_query); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="cari?q=<?php echo urlencode($search_query); ?>&page=<?php echo ($page + 1); ?>" class="next">Next &raquo;</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-4">
            <?php include 'includes/sidebar.php'; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>