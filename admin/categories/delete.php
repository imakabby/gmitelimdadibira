<?php
// admin/categories/delete.php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Require login
require_login();

// Get current user
$current_user = get_user($_SESSION['user_id']);

// Get category ID from URL
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($category_id <= 0) {
    header("Location: ../utama");
    exit;
}

// Check if category exists
$sql = "SELECT * FROM categories WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $category_id);
$stmt->execute();
$category = $stmt->fetch();

if (!$category) {
    header("Location: ../utama?error=" . urlencode("Category not found"));
    exit;
}

// Check if category has articles
$sql = "SELECT COUNT(*) as count FROM articles WHERE category_id = :category_id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':category_id', $category_id);
$stmt->execute();
$article_count = $stmt->fetch()['count'];

if ($article_count > 0) {
    header("Location: ../utama?error=" . urlencode("Cannot delete category with articles. Reassign articles first."));
    exit;
}

// Delete category
$sql = "DELETE FROM categories WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $category_id);

if ($stmt->execute()) {
    header("Location: ../utama?success=deleted");
} else {
    header("Location: ../utama?error=" . urlencode("Failed to delete category"));
}
exit;
?>