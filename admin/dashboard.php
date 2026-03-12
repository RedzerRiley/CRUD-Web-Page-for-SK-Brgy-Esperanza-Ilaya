<?php
// admin/dashboard.php - Admin Dashboard
session_start();

// Auth guard - redirect to login if not logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once '../includes/db.php';

// Fetch stats
$stats = [
    'total_posts'    => 0,
    'projects'       => 0,
    'announcements'  => 0,
    'events'         => 0,
    'accomplishments'=> 0,
    'upcoming'       => 0,
    'ongoing'        => 0,
    'completed'      => 0,
];

try {
    $stats['total_posts']     = $pdo->query("SELECT COUNT(*) FROM post")->fetchColumn();
    $stats['upcoming']        = $pdo->query("SELECT COUNT(*) FROM post WHERE status = 'Upcoming'")->fetchColumn();
    $stats['ongoing']         = $pdo->query("SELECT COUNT(*) FROM post WHERE status = 'Ongoing'")->fetchColumn();
    $stats['completed']       = $pdo->query("SELECT COUNT(*) FROM post WHERE status = 'Completed'")->fetchColumn();
    $stats['projects']        = $pdo->query("SELECT COUNT(*) FROM post p JOIN category c ON p.category_id = c.category_id WHERE c.category_name = 'Projects'")->fetchColumn();
    $stats['announcements']   = $pdo->query("SELECT COUNT(*) FROM post p JOIN category c ON p.category_id = c.category_id WHERE c.category_name = 'Announcements'")->fetchColumn();
    $stats['events']          = $pdo->query("SELECT COUNT(*) FROM post p JOIN category c ON p.category_id = c.category_id WHERE c.category_name = 'Events'")->fetchColumn();
    $stats['accomplishments']  = $pdo->query("SELECT COUNT(*) FROM post p JOIN category c ON p.category_id = c.category_id WHERE c.category_name = 'Accomplishments'")->fetchColumn();
} catch (PDOException $e) {}

// Fetch recent posts
$recent_posts = [];
try {
    $stmt = $pdo->query("
        SELECT p.*, c.category_name,
               a.full_name AS author_name
        FROM post p
        JOIN category c ON p.category_id = c.category_id
        JOIN admin a ON p.admin_id = a.admin_id
        ORDER BY p.created_at DESC
        LIMIT 8
    ");
    $recent_posts = $stmt->fetchAll();
} catch (PDOException $e) {}

function statusClass($status) {
    return match($status) {
        'Upcoming'  => 'status-upcoming',
        'Ongoing'   => 'status-ongoing',
        'Completed' => 'status-completed',
        default     => 'status-upcoming'
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard — SK Admin Panel</title>
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="stylesheet" href="../assets/css/admin.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
</head>
<body class="admin-body">

<!-- ══════════════════════════════════════
     ADMIN SIDEBAR
══════════════════════════════════════ -->
<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-brand">
    <img src="../assets/images/sk-logo.png" alt="SK Logo" class="sidebar-logo" />
    <div>
      <div class="sidebar-brand-name">SK Admin</div>
      <div class="sidebar-brand-sub">Esperanza Ilaya</div>
    </div>
  </div>

  <div class="sidebar-admin-info">
    <div class="sidebar-admin-avatar">
      <?= strtoupper(substr($_SESSION['admin_name'], 0, 1)) ?>
    </div>
    <div>
      <div class="sidebar-admin-name"><?= htmlspecialchars($_SESSION['admin_name']) ?></div>
      <div class="sidebar-admin-role"><?= htmlspecialchars($_SESSION['admin_position'] ?? 'Admin') ?></div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-nav-label">Main</div>
    <ul>
      <li>
        <a href="dashboard.php" class="active">
          <i class="bi bi-speedometer2"></i> Dashboard
        </a>
      </li>
    </ul>

    <div class="sidebar-nav-label">Content</div>
    <ul>
      <li>
        <a href="posts/list.php">
          <i class="bi bi-file-earmark-text"></i> All Posts
        </a>
      </li>
      <li>
        <a href="posts/create.php">
          <i class="bi bi-plus-circle"></i> New Post
        </a>
      </li>
      <li>
        <a href="categories/manage.php">
          <i class="bi bi-tags"></i> Categories
        </a>
      </li>
    </ul>

    <div class="sidebar-nav-label">System</div>
    <ul>
      <li>
        <a href="../index.php" target="_blank">
          <i class="bi bi-globe"></i> View Public Site
        </a>
      </li>
      <li>
        <a href="logout.php" class="sidebar-logout">
          <i class="bi bi-box-arrow-left"></i> Logout
        </a>
      </li>
    </ul>
  </nav>
</aside>

<!-- ══════════════════════════════════════
     MAIN CONTENT
══════════════════════════════════════ -->
<div class="admin-main">

  <!-- Top Bar -->
  <header class="admin-topbar">
    <div class="admin-topbar-left">
      <button class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-list"></i>
      </button>
      <div class="admin-topbar-title">
        <h1>Dashboard</h1>
        <span>Welcome back, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</span>
      </div>
    </div>
    <div class="admin-topbar-right">
      <a href="posts/create.php" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> New Post
      </a>
      <a href="logout.php" class="btn btn-sm" style="background:var(--light-gray); color:var(--text);">
        <i class="bi bi-box-arrow-left"></i> Logout
      </a>
    </div>
  </header>

  <div class="admin-content">

    <!-- ── STAT CARDS ── -->
    <div class="admin-stats-grid">
      <div class="admin-stat-card">
        <div class="admin-stat-icon" style="background:#EBF5FB; color:#2E86C1;">
          <i class="bi bi-file-earmark-text-fill"></i>
        </div>
        <div class="admin-stat-body">
          <div class="admin-stat-number"><?= $stats['total_posts'] ?></div>
          <div class="admin-stat-label">Total Posts</div>
        </div>
      </div>

      <div class="admin-stat-card">
        <div class="admin-stat-icon" style="background:#EBF8EF; color:#1A6B3C;">
          <i class="bi bi-check-circle-fill"></i>
        </div>
        <div class="admin-stat-body">
          <div class="admin-stat-number"><?= $stats['completed'] ?></div>
          <div class="admin-stat-label">Completed</div>
        </div>
      </div>

      <div class="admin-stat-card">
        <div class="admin-stat-icon" style="background:#FFF8EB; color:#C8960C;">
          <i class="bi bi-hourglass-split"></i>
        </div>
        <div class="admin-stat-body">
          <div class="admin-stat-number"><?= $stats['ongoing'] ?></div>
          <div class="admin-stat-label">Ongoing</div>
        </div>
      </div>

      <div class="admin-stat-card">
        <div class="admin-stat-icon" style="background:#F0F4FF; color:#3730A3;">
          <i class="bi bi-calendar-event-fill"></i>
        </div>
        <div class="admin-stat-body">
          <div class="admin-stat-number"><?= $stats['upcoming'] ?></div>
          <div class="admin-stat-label">Upcoming</div>
        </div>
      </div>
    </div>

    <!-- ── CATEGORY BREAKDOWN ── -->
    <div class="admin-row">
      <div class="admin-card" style="flex:1;">
        <div class="admin-card-header">
          <h3><i class="bi bi-pie-chart-fill"></i> Posts by Category</h3>
        </div>
        <div class="admin-card-body">
          <div class="category-breakdown">
            <?php
            $categories = [
              ['label' => 'Projects',        'count' => $stats['projects'],        'color' => '#003366', 'icon' => 'bi-briefcase-fill'],
              ['label' => 'Announcements',   'count' => $stats['announcements'],   'color' => '#C8960C', 'icon' => 'bi-megaphone-fill'],
              ['label' => 'Events',          'count' => $stats['events'],          'color' => '#2E86C1', 'icon' => 'bi-calendar-event-fill'],
              ['label' => 'Accomplishments', 'count' => $stats['accomplishments'], 'color' => '#1A6B3C', 'icon' => 'bi-award-fill'],
            ];
            $total = max($stats['total_posts'], 1);
            foreach ($categories as $cat):
              $pct = round(($cat['count'] / $total) * 100);
            ?>
              <div class="category-bar-item">
                <div class="category-bar-label">
                  <i class="bi <?= $cat['icon'] ?>" style="color:<?= $cat['color'] ?>"></i>
                  <span><?= $cat['label'] ?></span>
                  <strong><?= $cat['count'] ?></strong>
                </div>
                <div class="category-bar-track">
                  <div class="category-bar-fill" style="width:<?= $pct ?>%; background:<?= $cat['color'] ?>;"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="admin-card" style="width:260px; flex-shrink:0;">
        <div class="admin-card-header">
          <h3><i class="bi bi-lightning-fill"></i> Quick Actions</h3>
        </div>
        <div class="admin-card-body">
          <div class="quick-actions">
            <a href="posts/create.php" class="quick-action-btn">
              <i class="bi bi-plus-circle-fill"></i>
              <span>New Post</span>
            </a>
            <a href="posts/list.php" class="quick-action-btn">
              <i class="bi bi-list-ul"></i>
              <span>All Posts</span>
            </a>
            <a href="categories/manage.php" class="quick-action-btn">
              <i class="bi bi-tags-fill"></i>
              <span>Categories</span>
            </a>
            <a href="../index.php" target="_blank" class="quick-action-btn">
              <i class="bi bi-globe"></i>
              <span>Public Site</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- ── RECENT POSTS TABLE ── -->
    <div class="admin-card">
      <div class="admin-card-header">
        <h3><i class="bi bi-clock-history"></i> Recent Posts</h3>
        <a href="posts/list.php" class="admin-card-link">View All <i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="admin-card-body" style="padding:0;">
        <?php if (empty($recent_posts)): ?>
          <div class="admin-empty">
            <i class="bi bi-inbox"></i>
            <p>No posts yet. <a href="posts/create.php">Create your first post</a></p>
          </div>
        <?php else: ?>
          <div class="admin-table-wrapper">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Title</th>
                  <th>Category</th>
                  <th>Status</th>
                  <th>Author</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent_posts as $post): ?>
                  <tr>
                    <td>
                      <span class="post-title-cell"><?= htmlspecialchars($post['title']) ?></span>
                    </td>
                    <td>
                      <span class="category-badge"><?= htmlspecialchars($post['category_name']) ?></span>
                    </td>
                    <td>
                      <span class="card-status <?= statusClass($post['status']) ?>">
                        <?= htmlspecialchars($post['status']) ?>
                      </span>
                    </td>
                    <td><?= htmlspecialchars($post['author_name']) ?></td>
                    <td><?= date('M j, Y', strtotime($post['created_at'])) ?></td>
                    <td>
                      <div class="table-actions">
                        <a href="posts/edit.php?id=<?= $post['post_id'] ?>" class="table-btn table-btn-edit" title="Edit">
                          <i class="bi bi-pencil-fill"></i>
                        </a>
                        <a href="../post-detail.php?id=<?= $post['post_id'] ?>" target="_blank" class="table-btn table-btn-view" title="View">
                          <i class="bi bi-eye-fill"></i>
                        </a>
                        <a href="posts/delete.php?id=<?= $post['post_id'] ?>" class="table-btn table-btn-delete" title="Delete"
                           onclick="return confirm('Are you sure you want to delete this post?')">
                          <i class="bi bi-trash-fill"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div><!-- /admin-content -->
</div><!-- /admin-main -->

<script>
  // Sidebar toggle for mobile
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('adminSidebar');
  sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
  });
</script>

</body>
</html>