<?php
// category.php - Display articles by category
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/date_helper.php';

// Get category ID from URL
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($category_id <= 0) {
    header("Location: index");
    exit;
}

// Get category details
$sql = "SELECT * FROM categories WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $category_id);
$stmt->execute();
$category = $stmt->fetch();

if (!$category) {
    header("Location: index");
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

// Get published articles in this category
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 5; // Number of articles per page
$offset = ($page - 1) * $per_page;

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

// Get total number of articles in this category
$sql = "SELECT COUNT(*) as total FROM articles WHERE category_id = :category_id AND status = 'published'";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':category_id', $category_id);
$stmt->execute();
$total_articles = $stmt->fetch()['total'];

// Calculate total pages
$total_pages = ceil($total_articles / $per_page);

// Get articles for current page
if ($column_exists) {
    $sql = "SELECT a.*, c.name as category_name, u.name as author 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE a.category_id = :category_id AND a.status = 'published' 
            ORDER BY a.created_at DESC 
            LIMIT :limit OFFSET :offset";
} else {
    $sql = "SELECT a.id, a.title, a.slug, a.excerpt, a.category_id, a.user_id, a.status, a.created_at, 
            c.name as category_name, u.name as author 
            FROM articles a 
            LEFT JOIN categories c ON a.category_id = c.id 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE a.category_id = :category_id AND a.status = 'published' 
            ORDER BY a.created_at DESC 
            LIMIT :limit OFFSET :offset";
}

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':category_id', $category_id);
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

// Get all categories for the sidebar
$sql = "SELECT c.*, COUNT(a.id) as article_count 
        FROM categories c 
        LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published' 
        GROUP BY c.id 
        ORDER BY c.name";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll();

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
                    <h2 style="padding-left: 50px;"><?php echo htmlspecialchars($category['name']); ?></h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($category['description'])): ?>
                        <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                    <?php endif; ?>
                    
                    <?php if (empty($articles)): ?>
                        <div class="alert alert-info">Tidak ada artikel dalam kategori ini.</div>
                    <?php else: ?>
                        <p class="search-result-count">Ditemukan <?php echo $total_articles; ?> artikel</p>
                        
                        <?php foreach ($articles as $article): ?>
                            <div class="article-card">
                                <?php if (!empty($article['image'])): ?>
                                    <div class="article-image">
                                        <span class="article-category" style="position: absolute; top: 10px; left: 10px;">
                                            <a href="kategori/<?php echo $article['category_id']; ?>">
                                                <?php echo htmlspecialchars($article['category_name']); ?>
                                            </a>
                                        </span>
                                        <a href="artikel/<?php echo $article['slug']; ?>">
                                            <img src="<?php echo $article['image']; ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="article-content">
                                    <div class="article-main">
                                        <h2>
                                            <a href="artikel/<?php echo $article['slug']; ?>">
                                                <?php echo htmlspecialchars($article['title']); ?>
                                            </a>
                                        </h2>
                                        
                                        <div class="article-meta">
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
                                            $excerpt = substr($excerpt, 0, strrpos($excerpt, ' '));
                                            echo $excerpt . '...';
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
                                    <a href="kategori/<?php echo $category_id; ?>&page=<?php echo ($page - 1); ?>" class="prev">&laquo; Sebelum</a>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <span class="current"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="kategori/<?php echo $category_id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="kategori/<?php echo $category_id; ?>&page=<?php echo ($page + 1); ?>" class="next">Berikutnya &raquo;</a>
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

<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7436399062257055"
     crossorigin="anonymous"></script>