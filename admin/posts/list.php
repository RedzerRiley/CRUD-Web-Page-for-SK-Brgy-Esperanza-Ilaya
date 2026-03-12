<?php
// admin/posts/list.php - List All Posts
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../index.php'); exit; }
require_once '../../includes/db.php';

// Filters
$filter_category = $_GET['cat'] ?? '';
$filter_status   = $_GET['status'] ?? '';
$search          = trim($_GET['search'] ?? '');
$page            = max(1, (int)($_GET['page'] ?? 1));
$per_page        = 10;

// Build query
$where  = [];
$params = [];

if ($filter_category) {
    $where[]  = 'c.category_id = ?';
    $params[] = $filter_category;
}
if ($filter_status) {
    $where[]  = 'p.status = ?';
    $params[] = $filter_status;
}
if ($search) {
    $where[]  = '(p.title LIKE ? OR p.description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM post p JOIN category c ON p.category_id = c.category_id $where_sql");
$count_stmt->execute($params);
$total       = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);
$offset      = ($page - 1) * $per_page;

// Fetch posts
$stmt = $pdo->prepare("
    SELECT p.*, c.category_name, a.full_name AS author_name,
           (SELECT file_path FROM post_media WHERE post_id = p.post_id LIMIT 1) AS thumbnail
    FROM post p
    JOIN category c ON p.category_id = c.category_id
    JOIN admin a ON p.admin_id = a.admin_id
    $where_sql
    ORDER BY p.created_at DESC
    LIMIT $per_page OFFSET $offset
");
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Fetch categories for filter dropdown
$categories = $pdo->query("SELECT * FROM category ORDER BY category_name")->fetchAll();

function statusClass($s) {
    return match($s) { 'Upcoming' => 'status-upcoming', 'Ongoing' => 'status-ongoing', 'Completed' => 'status-completed', default => 'status-upcoming' };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>All Posts — SK Admin</title>
  <link rel="icon" type="image/png" href="/assets/images/sk-logo.png" />
  <link rel="stylesheet" href="../../assets/css/style.css" />
  <link rel="stylesheet" href="../../assets/css/admin.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
</head>
<body class="admin-body">

<?php include '../../includes/admin-sidebar.php'; ?>

<div class="admin-main">
  <?php include '../../includes/admin-topbar.php'; ?>

  <div class="admin-content">

    <!-- Page Header -->
    <div class="admin-page-header">
      <div>
        <h2 class="admin-page-title">All Posts</h2>
        <p class="admin-page-sub">Manage all SK posts, projects, and announcements.</p>
      </div>
      <a href="create.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> New Post
      </a>
    </div>

    <!-- Filters -->
    <div class="admin-card">
      <div class="admin-card-body">
        <form method="GET" action="list.php" class="admin-filters">
          <div class="filter-group">
            <input type="text" name="search" class="form-input" placeholder="Search posts..." value="<?= htmlspecialchars($search) ?>" style="padding-left:14px;" />
          </div>
          <div class="filter-group">
            <select name="cat" class="form-input" style="padding-left:14px;">
              <option value="">All Categories</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category_id'] ?>" <?= $filter_category == $cat['category_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['category_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="filter-group">
            <select name="status" class="form-input" style="padding-left:14px;">
              <option value="">All Statuses</option>
              <option value="Upcoming"  <?= $filter_status === 'Upcoming'  ? 'selected' : '' ?>>Upcoming</option>
              <option value="Ongoing"   <?= $filter_status === 'Ongoing'   ? 'selected' : '' ?>>Ongoing</option>
              <option value="Completed" <?= $filter_status === 'Completed' ? 'selected' : '' ?>>Completed</option>
            </select>
          </div>
          <button type="submit" class="btn btn-navy btn-sm">
            <i class="bi bi-search"></i> Filter
          </button>
          <a href="list.php" class="btn btn-sm" style="background:var(--light-gray); color:var(--text);">
            <i class="bi bi-x"></i> Clear
          </a>
        </form>
      </div>
    </div>

    <!-- Posts Table -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h3><i class="bi bi-file-earmark-text"></i> Posts (<?= $total ?>)</h3>
      </div>
      <div class="admin-card-body" style="padding:0;">
        <?php if (empty($posts)): ?>
          <div class="admin-empty">
            <i class="bi bi-inbox"></i>
            <p>No posts found. <a href="create.php">Create your first post</a></p>
          </div>
        <?php else: ?>
          <div class="admin-table-wrapper">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Thumbnail</th>
                  <th>Title</th>
                  <th>Category</th>
                  <th>Status</th>
                  <th>Author</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($posts as $post): ?>
                  <tr>
                    <td>
                      <?php if ($post['thumbnail']): ?>
                        <img src="../../<?= htmlspecialchars($post['thumbnail']) ?>" alt="thumbnail"
                             style="width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid var(--light-gray);" />
                      <?php else: ?>
                        <div style="width:48px;height:48px;background:var(--light-gray);border-radius:6px;display:flex;align-items:center;justify-content:center;color:var(--mid-gray);">
                          <i class="bi bi-image"></i>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td><span class="post-title-cell"><?= htmlspecialchars($post['title']) ?></span></td>
                    <td><span class="category-badge"><?= htmlspecialchars($post['category_name']) ?></span></td>
                    <td><span class="card-status <?= statusClass($post['status']) ?>"><?= htmlspecialchars($post['status']) ?></span></td>
                    <td><?= htmlspecialchars($post['author_name']) ?></td>
                    <td><?= date('M j, Y', strtotime($post['created_at'])) ?></td>
                    <td>
                      <div class="table-actions">
                        <a href="edit.php?id=<?= $post['post_id'] ?>" class="table-btn table-btn-edit" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                        <a href="../../post-detail.php?id=<?= $post['post_id'] ?>" target="_blank" class="table-btn table-btn-view" title="View"><i class="bi bi-eye-fill"></i></a>
                        <a href="delete.php?id=<?= $post['post_id'] ?>" class="table-btn table-btn-delete" title="Delete"
                           onclick="return confirm('Delete this post and all its media?')"><i class="bi bi-trash-fill"></i></a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <?php if ($total_pages > 1): ?>
            <div class="admin-pagination">
              <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&cat=<?= urlencode($filter_category) ?>&status=<?= urlencode($filter_status) ?>&search=<?= urlencode($search) ?>"
                   class="page-btn <?= $i === $page ? 'active' : '' ?>">
                  <?= $i ?>
                </a>
              <?php endfor; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<script>
  const toggle  = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('adminSidebar');
  if (toggle) toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
</script>
</body>
</html>