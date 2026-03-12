<?php
// includes/admin-sidebar.php - Shared Admin Sidebar
$current     = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

$script = $_SERVER['PHP_SELF'];
if (strpos($script, '/admin/posts/')      !== false ||
    strpos($script, '/admin/categories/') !== false ||
    strpos($script, '/admin/media/')      !== false) {
    $base = '../../';
} elseif (strpos($script, '/admin/') !== false) {
    $base = '../';
} else {
    $base = '';
}
?>
<aside class="admin-sidebar" id="adminSidebar">
  <div class="sidebar-brand">
    <img src="<?= $base ?>assets/images/sk-logo.png"
         alt="SK Logo" class="sidebar-logo" />
    <div>
      <div class="sidebar-brand-name">SK Admin</div>
      <div class="sidebar-brand-sub">Esperanza Ilaya</div>
    </div>
  </div>

  <div class="sidebar-admin-info">
    <div class="sidebar-admin-avatar">
      <?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?>
    </div>
    <div>
      <div class="sidebar-admin-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></div>
      <div class="sidebar-admin-role"><?= htmlspecialchars($_SESSION['admin_position'] ?? 'Admin') ?></div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-nav-label">Main</div>
    <ul>
      <li>
        <a href="<?= $base ?>admin/dashboard.php"
           class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">
          <i class="bi bi-speedometer2"></i> Dashboard
        </a>
      </li>
    </ul>

    <div class="sidebar-nav-label">Content</div>
    <ul>
      <li>
        <a href="<?= $base ?>admin/posts/list.php"
           class="<?= ($current === 'list.php' && $current_dir === 'posts') ? 'active' : '' ?>">
          <i class="bi bi-file-earmark-text"></i> All Posts
        </a>
      </li>
      <li>
        <a href="<?= $base ?>admin/posts/create.php"
           class="<?= ($current === 'create.php' && $current_dir === 'posts') ? 'active' : '' ?>">
          <i class="bi bi-plus-circle"></i> New Post
        </a>
      </li>
      <li>
        <a href="<?= $base ?>admin/categories/manage.php"
           class="<?= ($current === 'manage.php') ? 'active' : '' ?>">
          <i class="bi bi-tags"></i> Categories
        </a>
      </li>
    </ul>

    <div class="sidebar-nav-label">System</div>
    <ul>
      <li>
        <a href="<?= $base ?>index.php" target="_blank">
          <i class="bi bi-globe"></i> View Public Site
        </a>
      </li>
      <li>
        <a href="<?= $base ?>admin/logout.php" class="sidebar-logout">
          <i class="bi bi-box-arrow-left"></i> Logout
        </a>
      </li>
    </ul>
  </nav>
</aside>