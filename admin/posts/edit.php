<?php
// admin/posts/delete.php - Delete Post Handler
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../index.php'); exit; }
require_once '../../includes/db.php';

$post_id = (int)($_GET['id'] ?? 0);
if (!$post_id) { header('Location: list.php'); exit; }

// Verify post exists
$stmt = $pdo->prepare("SELECT * FROM post WHERE post_id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) { header('Location: list.php'); exit; }

try {
    // post_media rows are deleted automatically via ON DELETE CASCADE
    $stmt = $pdo->prepare("DELETE FROM post WHERE post_id = ?");
    $stmt->execute([$post_id]);
    header('Location: list.php?deleted=1');
    exit;
} catch (PDOException $e) {
    header('Location: list.php?error=1');
    exit;
}
?>