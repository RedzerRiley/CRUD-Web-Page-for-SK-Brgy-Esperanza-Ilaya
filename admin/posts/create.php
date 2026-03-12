<?php
// admin/posts/create.php - Create New Post
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: ../index.php'); exit; }
require_once '../../includes/db.php';

$errors  = [];
$success = '';

// Fetch categories
$categories = $pdo->query("SELECT * FROM category ORDER BY category_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $status      = $_POST['status'] ?? 'Upcoming';
    $event_date  = $_POST['event_date'] ?? null;
    $image_path  = trim($_POST['image_path'] ?? '');

    // Validation
    if (empty($title))       $errors[] = 'Title is required.';
    if (empty($description)) $errors[] = 'Description is required.';
    if (!$category_id)       $errors[] = 'Please select a category.';
    if (!in_array($status, ['Upcoming', 'Ongoing', 'Completed'])) $errors[] = 'Invalid status.';

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Insert post
            $stmt = $pdo->prepare("
                INSERT INTO post (admin_id, category_id, title, description, status, event_date)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['admin_id'],
                $category_id,
                $title,
                $description,
                $status,
                $event_date ?: null
            ]);
            $post_id = $pdo->lastInsertId();

            // Insert media if image path provided
            if (!empty($image_path)) {
                $media_stmt = $pdo->prepare("INSERT INTO post_media (post_id, file_path) VALUES (?, ?)");
                $media_stmt->execute([$post_id, $image_path]);
            }

            $pdo->commit();
            header('Location: list.php?created=1');
            exit;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Post — SK Admin</title>
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
        <h2 class="admin-page-title">Create New Post</h2>
        <p class="admin-page-sub">Add a new project, event, or announcement.</p>
      </div>
      <a href="list.php" class="btn btn-sm" style="background:var(--light-gray);color:var(--text);">
        <i class="bi bi-arrow-left"></i> Back to Posts
      </a>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert-box alert-error-box">
        <i class="bi bi-exclamation-circle-fill"></i>
        <ul style="margin:0;padding-left:16px;">
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" action="create.php">
      <div class="admin-form-layout">

        <!-- Left: Main Fields -->
        <div class="admin-form-main">
          <div class="admin-card">
            <div class="admin-card-header">
              <h3><i class="bi bi-pencil-square"></i> Post Content</h3>
            </div>
            <div class="admin-card-body">

              <div class="form-group">
                <label class="form-label">Title <span class="required">*</span></label>
                <input type="text" name="title" class="form-input" style="padding-left:14px;"
                       placeholder="Enter post title"
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required />
              </div>

              <div class="form-group">
                <label class="form-label">Description <span class="required">*</span></label>
                <textarea name="description" class="form-textarea" rows="8"
                          placeholder="Enter full description..."
                          required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
              </div>

              <div class="form-group">
                <label class="form-label">Image Path</label>
                <input type="text" name="image_path" class="form-input" style="padding-left:14px;"
                       placeholder="assets/images/posts/your-image.jpg"
                       value="<?= htmlspecialchars($_POST['image_path'] ?? '') ?>" />
                <p class="form-hint">
                  <i class="bi bi-info-circle"></i>
                  Add your image to <code>assets/images/posts/</code> then enter the path here.
                </p>
              </div>

            </div>
          </div>
        </div>

        <!-- Right: Meta Fields -->
        <div class="admin-form-sidebar">
          <div class="admin-card">
            <div class="admin-card-header">
              <h3><i class="bi bi-sliders"></i> Post Settings</h3>
            </div>
            <div class="admin-card-body">

              <div class="form-group">
                <label class="form-label">Category <span class="required">*</span></label>
                <select name="category_id" class="form-input" style="padding-left:14px;" required>
                  <option value="">Select category...</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>"
                      <?= ($_POST['category_id'] ?? '') == $cat['category_id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">Status <span class="required">*</span></label>
                <select name="status" class="form-input" style="padding-left:14px;" required>
                  <?php foreach (['Upcoming', 'Ongoing', 'Completed'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($_POST['status'] ?? 'Upcoming') === $s ? 'selected' : '' ?>>
                      <?= $s ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">Event Date</label>
                <input type="datetime-local" name="event_date" class="form-input" style="padding-left:14px;"
                       value="<?= htmlspecialchars($_POST['event_date'] ?? '') ?>" />
                <p class="form-hint"><i class="bi bi-info-circle"></i> Optional for events.</p>
              </div>

            </div>
          </div>

          <div class="admin-card" style="margin-top:16px;">
            <div class="admin-card-body">
              <button type="submit" class="btn btn-primary" style="width:100%;">
                <i class="bi bi-check-lg"></i> Publish Post
              </button>
              <a href="list.php" class="btn" style="width:100%;margin-top:10px;background:var(--light-gray);color:var(--text);text-align:center;">
                Cancel
              </a>
            </div>
          </div>
        </div>

      </div>
    </form>

  </div>
</div>

<script>
  const toggle  = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('adminSidebar');
  if (toggle) toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
</script>
</body>
</html>