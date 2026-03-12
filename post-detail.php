<?php
// post-detail.php - Public Post Detail Page
require_once 'includes/db.php';

$post_id = (int)($_GET['id'] ?? 0);
if (!$post_id) {
    header('Location: index.php');
    exit;
}

// Fetch post with category and author
$stmt = $pdo->prepare("
    SELECT p.*, c.category_name, a.full_name AS author_name, a.sk_position
    FROM post p
    JOIN category c ON p.category_id = c.category_id
    JOIN admin a ON p.admin_id = a.admin_id
    WHERE p.post_id = ?
");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: index.php');
    exit;
}

// Fetch all media for this post
$media_stmt = $pdo->prepare("SELECT * FROM post_media WHERE post_id = ? ORDER BY uploaded_at ASC");
$media_stmt->execute([$post_id]);
$media = $media_stmt->fetchAll();

// Fetch related posts (same category, exclude current)
$related_stmt = $pdo->prepare("
    SELECT p.*, c.category_name,
           (SELECT file_path FROM post_media WHERE post_id = p.post_id LIMIT 1) AS thumbnail
    FROM post p
    JOIN category c ON p.category_id = c.category_id
    WHERE p.category_id = ? AND p.post_id != ?
    ORDER BY p.created_at DESC
    LIMIT 3
");
$related_stmt->execute([$post['category_id'], $post_id]);
$related = $related_stmt->fetchAll();

function statusClass($s) {
    return match($s) {
        'Upcoming'  => 'status-upcoming',
        'Ongoing'   => 'status-ongoing',
        'Completed' => 'status-completed',
        default     => 'status-upcoming'
    };
}

function truncate($text, $limit = 120) {
    $plain = strip_tags($text);
    return strlen($plain) > $limit ? substr($plain, 0, $limit) . '...' : $plain;
}

$thumbnail = $media[0]['file_path'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= htmlspecialchars(truncate($post['description'], 160)) ?>" />
  <title><?= htmlspecialchars($post['title']) ?> — SK Barangay Esperanza Ilaya</title>
  <link rel="icon" type="image/png" href="assets/images/sk-logo.png" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <style>
    /* ── POST DETAIL SPECIFIC ── */

    .post-hero {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 60%, var(--primary-light) 100%);
      padding: 48px 0 0;
      position: relative;
      overflow: hidden;
    }

    .post-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .post-hero-content {
      position: relative;
      z-index: 1;
      padding-bottom: 48px;
    }

    .post-meta-row {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 16px;
    }

    .post-category-badge {
      background: rgba(200,150,12,0.2);
      border: 1px solid rgba(200,150,12,0.4);
      color: var(--accent-light);
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      padding: 4px 12px;
      border-radius: 20px;
    }

    .post-hero-title {
      font-family: var(--font-display);
      font-size: clamp(1.6rem, 4vw, 2.4rem);
      font-weight: 700;
      color: var(--white);
      line-height: 1.2;
      margin-bottom: 16px;
      max-width: 760px;
    }

    .post-info-row {
      display: flex;
      align-items: center;
      gap: 20px;
      flex-wrap: wrap;
    }

    .post-info-item {
      display: flex;
      align-items: center;
      gap: 7px;
      font-size: 0.82rem;
      color: rgba(255,255,255,0.65);
    }

    .post-info-item i { color: var(--accent-light); }

    /* ── FEATURED IMAGE ── */
    .post-featured-image {
      width: 100%;
      max-height: 480px;
      object-fit: cover;
      display: block;
      border-radius: 0;
    }

    /* ── MAIN LAYOUT ── */
    .post-layout {
      padding: var(--section-py) 0;
      background: var(--off-white);
    }

    .post-layout .container {
      display: grid;
      grid-template-columns: 1fr 320px;
      gap: 40px;
      align-items: start;
    }

    /* ── POST CONTENT ── */
    .post-content-card {
      background: var(--white);
      border: 1px solid var(--light-gray);
      border-radius: 10px;
      overflow: hidden;
      box-shadow: var(--shadow-sm);
    }

    .post-content-body {
      padding: 36px 40px;
    }

    .post-description {
      font-size: 1rem;
      color: var(--text);
      line-height: 1.85;
      white-space: pre-line;
    }

    .post-description p { margin-bottom: 16px; }

    /* ── MEDIA GALLERY ── */
    .post-gallery {
      padding: 0 40px 36px;
    }

    .post-gallery-title {
      font-family: var(--font-display);
      font-size: 1rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 16px;
      padding-top: 24px;
      border-top: 1px solid var(--light-gray);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .gallery-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 12px;
    }

    .gallery-item {
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid var(--light-gray);
      aspect-ratio: 4/3;
      cursor: pointer;
      transition: transform var(--transition), box-shadow var(--transition);
    }

    .gallery-item:hover {
      transform: scale(1.02);
      box-shadow: var(--shadow-md);
    }

    .gallery-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    /* ── SIDEBAR ── */
    .post-sidebar {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .sidebar-card {
      background: var(--white);
      border: 1px solid var(--light-gray);
      border-radius: 10px;
      overflow: hidden;
      box-shadow: var(--shadow-sm);
    }

    .sidebar-card-header {
      padding: 16px 20px;
      border-bottom: 1px solid var(--light-gray);
      background: var(--off-white);
    }

    .sidebar-card-header h3 {
      font-family: var(--font-display);
      font-size: 0.9rem;
      font-weight: 700;
      color: var(--primary);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .sidebar-card-body {
      padding: 20px;
    }

    .detail-list {
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .detail-item {
      display: flex;
      flex-direction: column;
      gap: 3px;
    }

    .detail-item-label {
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: var(--mid-gray);
    }

    .detail-item-value {
      font-size: 0.9rem;
      color: var(--text);
      font-weight: 500;
    }

    /* ── RELATED POSTS ── */
    .related-section {
      padding: 60px 0;
      background: var(--white);
      border-top: 1px solid var(--light-gray);
    }

    /* ── LIGHTBOX ── */
    .lightbox {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.92);
      z-index: 9999;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }

    .lightbox.open { display: flex; }

    .lightbox img {
      max-width: 90vw;
      max-height: 85vh;
      object-fit: contain;
      border-radius: 6px;
    }

    .lightbox-close {
      position: absolute;
      top: 20px;
      right: 24px;
      background: none;
      border: none;
      color: white;
      font-size: 2rem;
      cursor: pointer;
      opacity: 0.7;
      transition: opacity 0.2s;
    }

    .lightbox-close:hover { opacity: 1; }

    /* ── RESPONSIVE ── */
    @media (max-width: 900px) {
      .post-layout .container { grid-template-columns: 1fr; }
      .post-content-body { padding: 24px; }
      .post-gallery { padding: 0 24px 24px; }
    }

    @media (max-width: 480px) {
      .gallery-grid { grid-template-columns: repeat(2, 1fr); }
    }
  </style>
</head>
<body>

<!-- ══════════════════════════════════════
     TOP BAR
══════════════════════════════════════ -->
<div class="top-bar">
  <div class="container">
    <span><i class="bi bi-geo-alt-fill"></i> Barangay Esperanza Ilaya, Philippines</span>
    <span>
      <i class="bi bi-telephone-fill"></i> (000) 000-0000
      &nbsp;|&nbsp;
      <i class="bi bi-envelope-fill"></i>
      <a href="mailto:sk.esperanzailaya@gmail.com">sk.esperanzailaya@gmail.com</a>
    </span>
  </div>
</div>

<!-- ══════════════════════════════════════
     NAVBAR
══════════════════════════════════════ -->
<nav class="navbar">
  <div class="container">
    <a href="index.php" class="nav-brand">
      <img src="assets/images/sk-logo.png" alt="SK Logo" class="nav-brand-img" />
      <div class="nav-brand-divider"></div>
      <img src="assets/images/brgy-logo.png" alt="Barangay Logo" class="nav-brand-img" />
      <div class="nav-brand-text">
        <span class="title">SK Esperanza Ilaya</span>
        <span class="subtitle">Sangguniang Kabataan</span>
      </div>
    </a>
    <ul class="nav-links" id="navLinks">
      <li><a href="index.php">Home</a></li>
      <li><a href="about.php">About</a></li>
      <li><a href="projects.php">Projects</a></li>
      <li><a href="announcements.php">Announcements</a></li>
      <li><a href="reports.php">Reports</a></li>
      <li><a href="contact.php">Contact</a></li>
    </ul>
    <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- ══════════════════════════════════════
     BREADCRUMB
══════════════════════════════════════ -->
<div class="breadcrumb">
  <div class="container">
    <ul class="breadcrumb-list">
      <li><a href="index.php"><i class="bi bi-house-fill"></i> Home</a></li>
      <li class="sep">›</li>
      <li><a href="projects.php"><?= htmlspecialchars($post['category_name']) ?></a></li>
      <li class="sep">›</li>
      <li class="current"><?= htmlspecialchars(truncate($post['title'], 50)) ?></li>
    </ul>
  </div>
</div>

<!-- ══════════════════════════════════════
     POST HERO
══════════════════════════════════════ -->
<section class="post-hero">
  <div class="container">
    <div class="post-hero-content">
      <div class="post-meta-row">
        <span class="post-category-badge"><?= htmlspecialchars($post['category_name']) ?></span>
        <span class="card-status <?= statusClass($post['status']) ?>"><?= htmlspecialchars($post['status']) ?></span>
      </div>
      <h1 class="post-hero-title"><?= htmlspecialchars($post['title']) ?></h1>
      <div class="post-info-row">
        <div class="post-info-item">
          <i class="bi bi-person-fill"></i>
          <span><?= htmlspecialchars($post['author_name']) ?> — <?= htmlspecialchars($post['sk_position'] ?? 'SK Admin') ?></span>
        </div>
        <div class="post-info-item">
          <i class="bi bi-calendar3"></i>
          <span><?= date('F j, Y', strtotime($post['created_at'])) ?></span>
        </div>
        <?php if ($post['event_date']): ?>
        <div class="post-info-item">
          <i class="bi bi-calendar-event-fill"></i>
          <span>Event: <?= date('F j, Y', strtotime($post['event_date'])) ?></span>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Featured image full width at bottom of hero -->
  <?php if ($thumbnail): ?>
    <img src="<?= htmlspecialchars($thumbnail) ?>" alt="<?= htmlspecialchars($post['title']) ?>"
         class="post-featured-image" />
  <?php endif; ?>
</section>

<!-- ══════════════════════════════════════
     POST CONTENT + SIDEBAR
══════════════════════════════════════ -->
<div class="post-layout">
  <div class="container">

    <!-- Main Content -->
    <div>
      <div class="post-content-card">
        <div class="post-content-body">
          <div class="post-description">
            <?= nl2br(htmlspecialchars($post['description'])) ?>
          </div>
        </div>

        <!-- Image Gallery -->
        <?php if (count($media) > 0): ?>
        <div class="post-gallery">
          <div class="post-gallery-title">
            <i class="bi bi-images"></i>
            Photos (<?= count($media) ?>)
          </div>
          <div class="gallery-grid">
            <?php foreach ($media as $img): ?>
              <div class="gallery-item" onclick="openLightbox('<?= htmlspecialchars($img['file_path']) ?>')">
                <img src="<?= htmlspecialchars($img['file_path']) ?>"
                     alt="<?= htmlspecialchars($post['title']) ?>"
                     onerror="this.parentElement.style.display='none'" />
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Sidebar -->
    <aside class="post-sidebar">

      <!-- Post Details -->
      <div class="sidebar-card">
        <div class="sidebar-card-header">
          <h3><i class="bi bi-info-circle-fill"></i> Post Details</h3>
        </div>
        <div class="sidebar-card-body">
          <div class="detail-list">
            <div class="detail-item">
              <span class="detail-item-label">Category</span>
              <span class="detail-item-value"><?= htmlspecialchars($post['category_name']) ?></span>
            </div>
            <div class="detail-item">
              <span class="detail-item-label">Status</span>
              <span class="card-status <?= statusClass($post['status']) ?>"><?= htmlspecialchars($post['status']) ?></span>
            </div>
            <?php if ($post['event_date']): ?>
            <div class="detail-item">
              <span class="detail-item-label">Event Date</span>
              <span class="detail-item-value"><?= date('F j, Y', strtotime($post['event_date'])) ?></span>
            </div>
            <?php endif; ?>
            <div class="detail-item">
              <span class="detail-item-label">Posted By</span>
              <span class="detail-item-value"><?= htmlspecialchars($post['author_name']) ?></span>
            </div>
            <div class="detail-item">
              <span class="detail-item-label">Position</span>
              <span class="detail-item-value"><?= htmlspecialchars($post['sk_position'] ?? '—') ?></span>
            </div>
            <div class="detail-item">
              <span class="detail-item-label">Date Posted</span>
              <span class="detail-item-value"><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
            </div>
            <div class="detail-item">
              <span class="detail-item-label">Last Updated</span>
              <span class="detail-item-value"><?= date('M j, Y', strtotime($post['updated_at'])) ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Back buttons -->
      <div class="sidebar-card">
        <div class="sidebar-card-body">
          <a href="javascript:history.back()" class="btn btn-navy" style="width:100%;justify-content:center;">
            <i class="bi bi-arrow-left"></i> Go Back
          </a>
          <a href="index.php" class="btn" style="width:100%;margin-top:10px;background:var(--light-gray);color:var(--text);justify-content:center;">
            <i class="bi bi-house-fill"></i> Home
          </a>
        </div>
      </div>

    </aside>
  </div>
</div>

<!-- ══════════════════════════════════════
     RELATED POSTS
══════════════════════════════════════ -->
<?php if (!empty($related)): ?>
<section class="related-section">
  <div class="container">
    <div class="section-header">
      <span class="section-label">More from <?= htmlspecialchars($post['category_name']) ?></span>
      <h2 class="section-title">Related Posts</h2>
    </div>
    <div class="announcements-grid">
      <?php foreach ($related as $rel): ?>
        <article class="announcement-card">
          <div class="card-image">
            <?php if ($rel['thumbnail']): ?>
              <img src="<?= htmlspecialchars($rel['thumbnail']) ?>" alt="<?= htmlspecialchars($rel['title']) ?>" />
            <?php else: ?>
              <span>No Image</span>
            <?php endif; ?>
            <span class="card-category"><?= htmlspecialchars($rel['category_name']) ?></span>
          </div>
          <div class="card-body">
            <div class="card-meta">
              <span class="card-date">
                <i class="bi bi-calendar3"></i>
                <?= date('M j, Y', strtotime($rel['created_at'])) ?>
              </span>
              <span class="card-status <?= statusClass($rel['status']) ?>"><?= htmlspecialchars($rel['status']) ?></span>
            </div>
            <h3 class="card-title"><?= htmlspecialchars($rel['title']) ?></h3>
            <p class="card-excerpt"><?= htmlspecialchars(truncate($rel['description'])) ?></p>
            <a href="post-detail.php?id=<?= $rel['post_id'] ?>" class="card-link">
              Read More <i class="bi bi-arrow-right"></i>
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════
     FOOTER
══════════════════════════════════════ -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-brand-logos">
          <img src="assets/images/sk-logo.png" alt="SK Logo" class="footer-brand-img" />
          <div class="footer-brand-logo-divider"></div>
          <img src="assets/images/brgy-logo.png" alt="Barangay Logo" class="footer-brand-img" />
        </div>
        <div class="footer-brand-name">SK Barangay Esperanza Ilaya</div>
        <div class="footer-brand-sub">Sangguniang Kabataan</div>
        <p class="footer-brand-desc">
          The official digital platform of the Sangguniang Kabataan of
          Barangay Esperanza Ilaya — committed to transparent and youth-centered governance.
        </p>
      </div>
      <div>
        <div class="footer-col-title">Quick Links</div>
        <ul class="footer-links">
          <li><a href="index.php">Home</a></li>
          <li><a href="about.php">About SK</a></li>
          <li><a href="projects.php">Projects</a></li>
          <li><a href="announcements.php">Announcements</a></li>
          <li><a href="contact.php">Contact</a></li>
        </ul>
      </div>
      <div>
        <div class="footer-col-title">Categories</div>
        <ul class="footer-links">
          <li><a href="projects.php?cat=Projects">Projects</a></li>
          <li><a href="projects.php?cat=Events">Events</a></li>
          <li><a href="announcements.php">Announcements</a></li>
          <li><a href="projects.php?cat=Accomplishments">Accomplishments</a></li>
        </ul>
      </div>
      <div>
        <div class="footer-col-title">Information</div>
        <ul class="footer-links">
          <li><a href="#">SK Officials</a></li>
          <li><a href="#">Barangay Profile</a></li>
          <li><a href="#">Annual Reports</a></li>
          <li><a href="admin/index.php">Admin Login</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> Sangguniang Kabataan — Barangay Esperanza Ilaya. All rights reserved.</span>
      <span>Powered by SK Web Platform</span>
    </div>
  </div>
</footer>

<!-- Lightbox -->
<div class="lightbox" id="lightbox">
  <button class="lightbox-close" onclick="closeLightbox()">
    <i class="bi bi-x-lg"></i>
  </button>
  <img id="lightboxImg" src="" alt="Photo" />
</div>

<script>
  // Mobile nav
  const toggle = document.getElementById('navToggle');
  const links  = document.getElementById('navLinks');
  toggle.addEventListener('click', () => links.classList.toggle('open'));

  // Lightbox
  function openLightbox(src) {
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    document.getElementById('lightbox').classList.remove('open');
    document.body.style.overflow = '';
  }

  // Close on background click
  document.getElementById('lightbox').addEventListener('click', function(e) {
    if (e.target === this) closeLightbox();
  });

  // Close on Escape key
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeLightbox();
  });
</script>

</body>
</html>