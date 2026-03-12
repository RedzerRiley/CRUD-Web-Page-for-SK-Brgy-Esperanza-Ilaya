<?php
// admin/categories/manage.php - Manage Categories
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../index.php'); exit; }
require_once '../../includes/db.php';

$errors  = [];
$success = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Add category
    if ($action === 'add') {
        $name = trim($_POST['category_name'] ?? '');
        if (empty($name)) {
            $errors[] = 'Category name is required.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO category (category_name) VALUES (?)");
                $stmt->execute([$name]);
                $success = "Category \"$name\" added successfully!";
            } catch (PDOException $e) {
                $errors[] = 'Could not add category: ' . $e->getMessage();
            }
        }
    }

    // Edit category
    if ($action === 'edit') {
        $id   = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['category_name'] ?? '');
        if (empty($name) || !$id) {
            $errors[] = 'Category name is required.';
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE category SET category_name = ? WHERE category_id = ?");
                $stmt->execute([$name, $id]);
                $success = "Category updated successfully!";
            } catch (PDOException $e) {
                $errors[] = 'Could not update category.';
            }
        }
    }

    // Delete category
    if ($action === 'delete') {
        $id = (int)($_POST['category_id'] ?? 0);
        if ($id) {
            try {
                // Check if category has posts
                $count = $pdo->prepare("SELECT COUNT(*) FROM post WHERE category_id = ?");
                $count->execute([$id]);
                if ($count->fetchColumn() > 0) {
                    $errors[] = 'Cannot delete — this category has posts assigned to it.';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM category WHERE category_id = ?");
                    $stmt->execute([$id]);
                    $success = 'Category deleted successfully.';
                }
            } catch (PDOException $e) {
                $errors[] = 'Could not delete category.';
            }
        }
    }
}

// Fetch all categories with post counts
$categories = $pdo->query("
    SELECT c.*, COUNT(p.post_id) AS post_count
    FROM category c
    LEFT JOIN post p ON p.category_id = c.category_id
    GROUP BY c.category_id
    ORDER BY c.category_name
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Categories — SK Admin</title>
  <link rel="stylesheet" href="../../assets/css/style.css" />
  <link rel="stylesheet" href="../../assets/css/admin.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
</head>
<body class="admin-body">

<?php include '../../includes/admin-sidebar.php'; ?>

<div class="admin-main">
  <?php include '../../includes/admin-topbar.php'; ?>

  <div class="admin-content">

    <div class="admin-page-header">
      <div>
        <h2 class="admin-page-title">Categories</h2>
        <p class="admin-page-sub">Manage content categories for posts.</p>
      </div>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert-box alert-error-box">
        <i class="bi bi-exclamation-circle-fill"></i>
        <?= htmlspecialchars($errors[0]) ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert-box alert-success-box">
        <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <div class="admin-row" style="align-items:flex-start;">

      <!-- Add Category Form -->
      <div class="admin-card" style="width:300px;flex-shrink:0;">
        <div class="admin-card-header">
          <h3><i class="bi bi-plus-circle"></i> Add Category</h3>
        </div>
        <div class="admin-card-body">
          <form method="POST" action="manage.php">
            <input type="hidden" name="action" value="add" />
            <div class="form-group">
              <label class="form-label">Category Name <span class="required">*</span></label>
              <input type="text" name="category_name" class="form-input" style="padding-left:14px;"
                     placeholder="e.g. Livelihood" required />
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">
              <i class="bi bi-plus-lg"></i> Add Category
            </button>
          </form>
        </div>
      </div>

      <!-- Categories Table -->
      <div class="admin-card" style="flex:1;">
        <div class="admin-card-header">
          <h3><i class="bi bi-tags"></i> All Categories (<?= count($categories) ?>)</h3>
        </div>
        <div class="admin-card-body" style="padding:0;">
          <?php if (empty($categories)): ?>
            <div class="admin-empty">
              <i class="bi bi-tags"></i>
              <p>No categories yet.</p>
            </div>
          <?php else: ?>
            <table class="admin-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Category Name</th>
                  <th>Posts</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($categories as $cat): ?>
                  <tr>
                    <td style="color:var(--mid-gray);font-size:0.8rem;"><?= $cat['category_id'] ?></td>
                    <td>
                      <span class="category-badge"><?= htmlspecialchars($cat['category_name']) ?></span>
                    </td>
                    <td>
                      <span style="font-size:0.875rem;font-weight:600;color:var(--text);"><?= $cat['post_count'] ?></span>
                      <span style="font-size:0.75rem;color:var(--mid-gray);"> posts</span>
                    </td>
                    <td>
                      <div class="table-actions">
                        <!-- Edit button triggers inline modal -->
                        <button class="table-btn table-btn-edit" title="Edit"
                                onclick="openEditModal(<?= $cat['category_id'] ?>, '<?= htmlspecialchars($cat['category_name'], ENT_QUOTES) ?>')">
                          <i class="bi bi-pencil-fill"></i>
                        </button>

                        <!-- Delete -->
                        <?php if ($cat['post_count'] == 0): ?>
                          <form method="POST" action="manage.php" style="display:inline;">
                            <input type="hidden" name="action" value="delete" />
                            <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>" />
                            <button type="submit" class="table-btn table-btn-delete" title="Delete"
                                    onclick="return confirm('Delete this category?')">
                              <i class="bi bi-trash-fill"></i>
                            </button>
                          </form>
                        <?php else: ?>
                          <button class="table-btn table-btn-delete" title="Cannot delete — has posts" disabled
                                  style="opacity:0.3;cursor:not-allowed;">
                            <i class="bi bi-trash-fill"></i>
                          </button>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-overlay" style="display:none;">
  <div class="modal-box">
    <div class="modal-header">
      <h3><i class="bi bi-pencil-square"></i> Edit Category</h3>
      <button onclick="closeEditModal()" class="modal-close"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <form method="POST" action="manage.php">
        <input type="hidden" name="action" value="edit" />
        <input type="hidden" name="category_id" id="editCategoryId" />
        <div class="form-group">
          <label class="form-label">Category Name <span class="required">*</span></label>
          <input type="text" name="category_name" id="editCategoryName"
                 class="form-input" style="padding-left:14px;" required />
        </div>
        <div style="display:flex;gap:10px;margin-top:4px;">
          <button type="submit" class="btn btn-primary" style="flex:1;">
            <i class="bi bi-check-lg"></i> Save
          </button>
          <button type="button" onclick="closeEditModal()"
                  class="btn" style="flex:1;background:var(--light-gray);color:var(--text);">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.5);
  z-index: 999;
  display: flex;
  align-items: center;
  justify-content: center;
}
.modal-box {
  background: var(--white);
  border-radius: 12px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.25);
  overflow: hidden;
}
.modal-header {
  padding: 18px 22px;
  border-bottom: 1px solid var(--light-gray);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.modal-header h3 {
  font-family: var(--font-display);
  font-size: 1rem;
  font-weight: 700;
  color: var(--primary);
  display: flex;
  align-items: center;
  gap: 8px;
}
.modal-close {
  background: none;
  border: none;
  font-size: 1rem;
  color: var(--mid-gray);
  cursor: pointer;
  padding: 4px;
  transition: color 0.2s;
}
.modal-close:hover { color: var(--danger); }
.modal-body { padding: 22px; }
</style>

<script>
  function openEditModal(id, name) {
    document.getElementById('editCategoryId').value   = id;
    document.getElementById('editCategoryName').value = name;
    document.getElementById('editModal').style.display = 'flex';
  }
  function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
  }
  const toggle  = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('adminSidebar');
  if (toggle) toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
</script>
</body>
</html>