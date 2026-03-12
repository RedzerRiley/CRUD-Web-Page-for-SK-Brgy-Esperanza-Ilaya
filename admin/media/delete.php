<?php
// admin/media/delete.php - Delete Media File
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../index.php'); exit; }
require_once '../../includes/db.php';

$media_id = (int)($_GET['id'] ?? 0);
$post_id  = (int)($_GET['post_id'] ?? 0);

if (!$media_id) { header('Location: ../posts/list.php'); exit; }

try {
    $stmt = $pdo->prepare("DELETE FROM post_media WHERE media_id = ?");
    $stmt->execute([$media_id]);
    header("Location: ../posts/edit.php?id=$post_id&media_deleted=1");
    exit;
} catch (PDOException $e) {
    header("Location: ../posts/edit.php?id=$post_id&error=1");
    exit;
}
?>