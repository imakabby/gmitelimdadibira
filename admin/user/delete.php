<?php
// admin/users/delete.php
require_once '../../config/database.php';
require_once '../../includes/functions.php';

// Require login
require_login();

// Get current user
$current_user = get_user($_SESSION['user_id']);

// Check if user is admin
if (!$current_user || !$current_user['is_admin']) {
    header("Location: ../dashboard.php");
    exit;
}

// Get user ID from URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    header("Location: index.php");
    exit;
}

// Prevent deleting yourself
if ($user_id == $_SESSION['user_id']) {
    header("Location: index.php?error=Anda tidak dapat menghapus akun Anda sendiri");
    exit;
}

// Delete user
$result = delete_user($user_id);

if ($result['success']) {
    header("Location: index.php?success=deleted");
} else {
    header("Location: index.php?error=" . urlencode($result['message']));
}
exit;
?>