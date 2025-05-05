<?php
require_once 'includes/date_helper.php';
// includes/sidebar.php

// If categories are not already defined, fetch them
if (!isset($categories) || empty($categories)) {
    // Get all categories for the sidebar with article count
    $sql = "SELECT c.*, COUNT(a.id) as article_count 
            FROM categories c 
            LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published' 
            GROUP BY c.id 
            ORDER BY c.name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll();
}
?>
<div class="sidebar">
    <div class="sidebar-widget category-widget">
        <h3>Kategori</h3>
        <ul class="category-list">
            <?php foreach ($categories as $cat): ?>
                <li>
                    <a href="<?php echo url_base(); ?>kategori/<?php echo $cat['id']; ?>">
                        <?php echo $cat['name']; ?>
                        <span class="count">(<?php echo isset($cat['article_count']) ? $cat['article_count'] : 0; ?>)</span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <div class="sticky-container">
        <div class="sidebar-widget recent-posts-widget">
            <h3>Terbaru</h3>
            <?php
            // Get recent articles
            $sql = "SELECT id, title, slug, created_at FROM articles WHERE status = 'published' ORDER BY created_at DESC LIMIT 3";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $recent_posts = $stmt->fetchAll();
            ?>
            
            <ul class="recent-posts">
                <?php foreach ($recent_posts as $post): ?>
                    <li>
                        <a href="<?php echo url_base(); ?>artikel/<?php echo $post['slug']; ?>" style="text-decoration: none; font-weight: bold;">
                            <?php echo $post['title']; ?>
                        </a>
                        <span class="post-date"><?php echo waktuYangLalu($post['created_at']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="sidebar-widget recent-posts-widget" style="margin-top: 0px !important;">
            <h3>Terpopuler</h3>
            <?php
            // Get recent articles
            $sql = "SELECT id, title, slug, created_at, image FROM articles WHERE status = 'published' ORDER BY view_count DESC LIMIT 4";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $recent_posts = $stmt->fetchAll();
            ?>

            <ul class="recent-posts">
                <?php foreach ($recent_posts as $post): ?>
                    <li style="padding: 5px;">
                        <img style="border-radius: 5px;" src="<?php echo $post['image']; ?>" alt="<?php echo $post['title']; ?>" style="width: 100%; height: 100%;">
                        <span style="display: flex; flex-direction: column; gap: 0px; padding: 0 10px 10px 10px;">
                            <a style="text-decoration: none; font-weight: bold;" href="<?php echo url_base(); ?>artikel/<?php echo $post['slug']; ?>">
                                <?php echo $post['title']; ?>
                            </a>
                            <span class="post-date"><?php echo waktuYangLalu($post['created_at']); ?></span>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>