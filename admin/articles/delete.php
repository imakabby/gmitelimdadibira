<?php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Require login
require_login();

// Get article ID from URL
$article_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($article_id <= 0) {
    header("Location: ../utama");
    exit;
}

// Check if article exists
$stmt = $pdo->prepare("SELECT id FROM articles WHERE id = :id");
$stmt->bindValue(':id', $article_id);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    header("Location: ../utama");
    exit;
}

// Delete article
$stmt = $pdo->prepare("DELETE FROM articles WHERE id = :id");
$stmt->bindValue(':id', $article_id);
$stmt->execute();

// Redirect back to articles list
header("Location: ../utama?success=deleted");
exit;
?>