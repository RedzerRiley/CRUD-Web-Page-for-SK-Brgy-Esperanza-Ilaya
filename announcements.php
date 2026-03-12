<?php
// announcements.php - SK Barangay Esperanza Ilaya - Announcements Page
require_once 'includes/db.php';

// Pagination
$perPage     = 9;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$offset      = ($currentPage - 1) * $perPage;

// Filters
$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['cat']    ?? '');
$status   = trim($_GET['status'] ?? '');

// Build WHERE clause
$where  = [];
$params = [];

if ($search !== '') {
    $where[]          = "(p.title LIKE :search OR p.description LIKE :search2)";
    $params[':search']  = "%$search%";
    $params[':search2'] = "%$search%";
}
if ($category !== '') {
    $where[]           = "c.category_name = :cat";
    $params[':cat']    = $category;
}
if ($status !== '') {
    $where[]           = "p.status = :status";
    $params[':status'] = $status;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Fetch categories for filter
$categories = [];
try {
    $categories = $pdo->query("SELECT DISTINCT category_name FROM category ORDER BY category_name")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {}

// Count total posts for pagination
$total = 0;
try {
    $countSQL = "SELECT COUNT(*) FROM post p JOIN category c ON p.category_id = c.category_id $whereSQL";
    $stmt     = $pdo->prepare($countSQL);
    $stmt->execute($params);
    $total    = (int)$stmt->fetchColumn();
} catch (PDOException $e) {}

$totalPages = max(1, (int)ceil($total / $perPage));

// Fetch posts
$posts = [];
try {
    $sql  = "
        SELECT p.*, c.category_name,
               (SELECT file_path FROM post_media WHERE post_id = p.post_id LIMIT 1) AS thumbnail
        FROM post p
        JOIN category c ON p.category_id = c.category_id
        $whereSQL
        ORDER BY p.created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
    $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {}

// Helpers
function statusClass($status) {
    return match($status) {
        'Upcoming'  => 'status-upcoming',
        'Ongoing'   => 'status-ongoing',
        'Completed' => 'status-completed',
        default     => 'status-upcoming'
    };
}
function truncate($text, $limit = 130) {
    $plain = strip_tags($text);
    return strlen($plain) > $limit ? substr($plain, 0, $limit) . '…' : $plain;
}

// Build pagination URL helper
function pageUrl($page, $search, $category, $status) {
    $q = http_build_query(array_filter([
        'page'   => $page,
        'search' => $search,
        'cat'    => $category,
        'status' => $status,
    ]));
    return 'announcements.php' . ($q ? "?$q" : '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Announcements from the Sangguniang Kabataan of Barangay Esperanza Ilaya." />
  <title>Announcements — SK Barangay Esperanza Ilaya</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <style>
    /* ─── Page Hero ─────────────────────────────────────── */
    .page-hero {
      background: linear-gradient(135deg, var(--primary, #1a56db) 0%, var(--primary-dark, #1e429f) 100%);
      color: #fff;
      padding: 64px 0 48px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .page-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
      pointer-events: none;
    }
    .page-hero-label {
      display: inline-block;
      background: rgba(255,255,255,.15);
      border: 1px solid rgba(255,255,255,.25);
      border-radius: 100px;
      padding: 4px 16px;
      font-size: .78rem;
      font-weight: 600;
      letter-spacing: .08em;
      text-transform: uppercase;
      margin-bottom: 16px;
    }
    .page-hero h1 {
      font-size: clamp(1.8rem, 4vw, 2.8rem);
      font-weight: 800;
      margin: 0 0 12px;
      line-height: 1.15;
    }
    .page-hero p {
      font-size: 1.05rem;
      opacity: .85;
      max-width: 520px;
      margin: 0 auto;
    }
    .breadcrumb {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      margin-top: 20px;
      font-size: .85rem;
      opacity: .75;
    }
    .breadcrumb a { color: #fff; text-decoration: none; }
    .breadcrumb a:hover { text-decoration: underline; }

    /* ─── Filter Bar ─────────────────────────────────────── */
    .filter-bar {
      background: var(--white, #fff);
      border-bottom: 1px solid var(--light-border, #e5e7eb);
      padding: 20px 0;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 8px rgba(0,0,0,.06);
    }
    .filter-bar .container {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }
    .filter-search {
      flex: 1;
      min-width: 200px;
      display: flex;
      align-items: center;
      gap: 8px;
      background: var(--light-bg, #f9fafb);
      border: 1.5px solid var(--light-border, #e5e7eb);
      border-radius: 8px;
      padding: 8px 14px;
      transition: border-color .2s;
    }
    .filter-search:focus-within { border-color: var(--primary, #1a56db); }
    .filter-search i { color: var(--mid-gray, #9ca3af); font-size: .95rem; }
    .filter-search input {
      border: none;
      background: transparent;
      outline: none;
      font-size: .9rem;
      width: 100%;
      color: var(--text, #111827);
    }
    .filter-select {
      padding: 9px 14px;
      border: 1.5px solid var(--light-border, #e5e7eb);
      border-radius: 8px;
      background: var(--light-bg, #f9fafb);
      font-size: .88rem;
      color: var(--text, #111827);
      cursor: pointer;
      outline: none;
      transition: border-color .2s;
    }
    .filter-select:focus { border-color: var(--primary, #1a56db); }
    .filter-btn {
      padding: 9px 20px;
      background: var(--primary, #1a56db);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: .88rem;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: background .2s;
      white-space: nowrap;
    }
    .filter-btn:hover { background: var(--primary-dark, #1e429f); }
    .filter-reset {
      padding: 9px 16px;
      background: transparent;
      color: var(--mid-gray, #6b7280);
      border: 1.5px solid var(--light-border, #e5e7eb);
      border-radius: 8px;
      font-size: .88rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: all .2s;
      white-space: nowrap;
      text-decoration: none;
    }
    .filter-reset:hover {
      border-color: var(--primary, #1a56db);
      color: var(--primary, #1a56db);
    }

    /* ─── Main Content ───────────────────────────────────── */
    .announcements-page { padding: 48px 0 64px; }
    .results-info {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 28px;
      flex-wrap: wrap;
      gap: 10px;
    }
    .results-count {
      font-size: .9rem;
      color: var(--mid-gray, #6b7280);
    }
    .results-count strong { color: var(--text, #111827); }
    .active-filters {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }
    .active-filter-tag {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: #eff6ff;
      color: #1d4ed8;
      border: 1px solid #bfdbfe;
      border-radius: 100px;
      padding: 3px 10px;
      font-size: .8rem;
      font-weight: 500;
    }

    /* ─── Cards Grid ─────────────────────────────────────── */
    .ann-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
      gap: 28px;
    }
    .ann-card {
      background: var(--white, #fff);
      border: 1px solid var(--light-border, #e5e7eb);
      border-radius: 14px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      transition: transform .22s, box-shadow .22s;
    }
    .ann-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 32px rgba(0,0,0,.1);
    }
    .ann-card-img {
      position: relative;
      height: 190px;
      background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #93c5fd;
      font-size: .85rem;
      overflow: hidden;
    }
    .ann-card-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .ann-card-cat {
      position: absolute;
      top: 12px;
      left: 12px;
      background: var(--primary, #1a56db);
      color: #fff;
      font-size: .72rem;
      font-weight: 700;
      letter-spacing: .05em;
      text-transform: uppercase;
      border-radius: 100px;
      padding: 3px 10px;
    }
    .ann-card-body {
      padding: 20px 22px 22px;
      display: flex;
      flex-direction: column;
      flex: 1;
    }
    .ann-card-meta {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 10px;
      flex-wrap: wrap;
      gap: 6px;
    }
    .ann-card-date {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: .8rem;
      color: var(--mid-gray, #6b7280);
    }
    .ann-card-title {
      font-size: 1rem;
      font-weight: 700;
      color: var(--text, #111827);
      margin: 0 0 10px;
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    .ann-card-excerpt {
      font-size: .875rem;
      color: var(--mid-gray, #6b7280);
      line-height: 1.6;
      flex: 1;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
      margin-bottom: 16px;
    }
    .ann-card-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-top: 1px solid var(--light-border, #f0f0f0);
      padding-top: 14px;
      margin-top: auto;
    }

    /* ─── Status badges (match main style.css) ───────────── */
    .status-upcoming  { background:#fef3c7; color:#92400e; }
    .status-ongoing   { background:#d1fae5; color:#065f46; }
    .status-completed { background:#e0e7ff; color:#3730a3; }
    .card-status {
      font-size: .75rem;
      font-weight: 700;
      letter-spacing: .04em;
      text-transform: uppercase;
      padding: 3px 10px;
      border-radius: 100px;
    }
    .card-link {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      color: var(--primary, #1a56db);
      font-size: .85rem;
      font-weight: 600;
      text-decoration: none;
      transition: gap .2s;
    }
    .card-link:hover { gap: 9px; }

    /* ─── Empty State ────────────────────────────────────── */
    .empty-state {
      text-align: center;
      padding: 72px 24px;
      color: var(--mid-gray, #9ca3af);
    }
    .empty-state-icon {
      font-size: 3.5rem;
      margin-bottom: 16px;
      display: block;
      opacity: .5;
    }
    .empty-state h3 {
      font-size: 1.2rem;
      color: var(--text, #374151);
      margin: 0 0 8px;
    }
    .empty-state p { font-size: .9rem; margin: 0 0 20px; }
    .empty-state a {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: var(--primary, #1a56db);
      color: #fff;
      padding: 10px 22px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      font-size: .9rem;
    }

    /* ─── Pagination ─────────────────────────────────────── */
    .pagination {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 52px;
      flex-wrap: wrap;
    }
    .page-btn {
      min-width: 40px;
      height: 40px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      font-size: .875rem;
      font-weight: 600;
      text-decoration: none;
      color: var(--text, #374151);
      border: 1.5px solid var(--light-border, #e5e7eb);
      background: var(--white, #fff);
      transition: all .2s;
      padding: 0 10px;
    }
    .page-btn:hover:not(.disabled):not(.active) {
      border-color: var(--primary, #1a56db);
      color: var(--primary, #1a56db);
    }
    .page-btn.active {
      background: var(--primary, #1a56db);
      border-color: var(--primary, #1a56db);
      color: #fff;
    }
    .page-btn.disabled {
      opacity: .4;
      pointer-events: none;
    }
    .page-ellipsis {
      color: var(--mid-gray, #9ca3af);
      padding: 0 4px;
    }

    /* ─── Responsive ─────────────────────────────────────── */
    @media (max-width: 640px) {
      .filter-bar .container { flex-direction: column; align-items: stretch; }
      .filter-select, .filter-btn, .filter-reset { width: 100%; justify-content: center; }
      .ann-grid { grid-template-columns: 1fr; }
      .results-info { flex-direction: column; align-items: flex-start; }
    }
  </style>
</head>
<body>

<!-- ══════════════════════════════════════
     TOP BAR
══════════════════════════════════════ -->
<div class="top-bar">
  <div class="container">
    <span>
      <i class="bi bi-geo-alt-fill"></i>
      Barangay Esperanza Ilaya, Philippines
    </span>
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
      <li><a href="announcements.php" class="active">Announcements</a></li>
      <li><a href="reports.php">Reports</a></li>
      <li><a href="contact.php">Contact</a></li>
    </ul>

    <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- ══════════════════════════════════════
     PAGE HERO
══════════════════════════════════════ -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-label">
      <i class="bi bi-megaphone-fill"></i> &nbsp;Official Updates
    </div>
    <h1>Announcements</h1>
    <p>Stay informed with the latest news, updates, and notices from the SK Barangay Esperanza Ilaya.</p>
    <div class="breadcrumb">
      <a href="index.php"><i class="bi bi-house-fill"></i> Home</a>
      <i class="bi bi-chevron-right"></i>
      <span>Announcements</span>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════
     FILTER BAR
══════════════════════════════════════ -->
<div class="filter-bar">
  <div class="container">
    <form method="GET" action="announcements.php" style="display:contents;">
      <label class="filter-search">
        <i class="bi bi-search"></i>
        <input
          type="text"
          name="search"
          placeholder="Search announcements…"
          value="<?= htmlspecialchars($search) ?>"
          autocomplete="off"
        />
      </label>

      <select name="cat" class="filter-select">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <select name="status" class="filter-select">
        <option value="">All Statuses</option>
        <option value="Upcoming"  <?= $status === 'Upcoming'  ? 'selected' : '' ?>>Upcoming</option>
        <option value="Ongoing"   <?= $status === 'Ongoing'   ? 'selected' : '' ?>>Ongoing</option>
        <option value="Completed" <?= $status === 'Completed' ? 'selected' : '' ?>>Completed</option>
      </select>

      <button type="submit" class="filter-btn">
        <i class="bi bi-funnel-fill"></i> Filter
      </button>

      <?php if ($search || $category || $status): ?>
        <a href="announcements.php" class="filter-reset">
          <i class="bi bi-x-circle"></i> Clear
        </a>
      <?php endif; ?>
    </form>
  </div>
</div>

<!-- ══════════════════════════════════════
     ANNOUNCEMENTS CONTENT
══════════════════════════════════════ -->
<main class="announcements-page">
  <div class="container">

    <!-- Results info & active filters -->
    <div class="results-info">
      <div class="results-count">
        Showing <strong><?= count($posts) ?></strong> of <strong><?= $total ?></strong> announcement<?= $total !== 1 ? 's' : '' ?>
        <?php if ($currentPage > 1): ?>
          &mdash; Page <?= $currentPage ?> of <?= $totalPages ?>
        <?php endif; ?>
      </div>
      <div class="active-filters">
        <?php if ($search): ?>
          <span class="active-filter-tag">
            <i class="bi bi-search"></i> "<?= htmlspecialchars($search) ?>"
          </span>
        <?php endif; ?>
        <?php if ($category): ?>
          <span class="active-filter-tag">
            <i class="bi bi-tag-fill"></i> <?= htmlspecialchars($category) ?>
          </span>
        <?php endif; ?>
        <?php if ($status): ?>
          <span class="active-filter-tag">
            <i class="bi bi-circle-fill"></i> <?= htmlspecialchars($status) ?>
          </span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Grid -->
    <?php if (empty($posts)): ?>
      <div class="empty-state">
        <i class="bi bi-megaphone empty-state-icon"></i>
        <h3>No announcements found</h3>
        <p>
          <?php if ($search || $category || $status): ?>
            Try adjusting your filters or search terms.
          <?php else: ?>
            There are no announcements yet. Check back soon!
          <?php endif; ?>
        </p>
        <?php if ($search || $category || $status): ?>
          <a href="announcements.php"><i class="bi bi-arrow-left"></i> View all announcements</a>
        <?php endif; ?>
      </div>

    <?php else: ?>
      <div class="ann-grid">
        <?php foreach ($posts as $post): ?>
          <article class="ann-card">
            <div class="ann-card-img">
              <?php if (!empty($post['thumbnail'])): ?>
                <img src="<?= htmlspecialchars($post['thumbnail']) ?>"
                     alt="<?= htmlspecialchars($post['title']) ?>" />
              <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                  <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/>
                </svg>
              <?php endif; ?>
              <span class="ann-card-cat"><?= htmlspecialchars($post['category_name']) ?></span>
            </div>

            <div class="ann-card-body">
              <div class="ann-card-meta">
                <span class="ann-card-date">
                  <i class="bi bi-calendar3"></i>
                  <?= date('M j, Y', strtotime($post['created_at'])) ?>
                </span>
                <span class="card-status <?= statusClass($post['status']) ?>">
                  <?= htmlspecialchars($post['status']) ?>
                </span>
              </div>

              <h3 class="ann-card-title"><?= htmlspecialchars($post['title']) ?></h3>
              <p class="ann-card-excerpt"><?= htmlspecialchars(truncate($post['description'])) ?></p>

              <div class="ann-card-footer">
                <span style="font-size:.78rem; color:var(--mid-gray,#9ca3af);">
                  <i class="bi bi-person-fill"></i>
                  <?= htmlspecialchars($post['author'] ?? 'SK Office') ?>
                </span>
                <a href="post-detail.php?id=<?= $post['post_id'] ?>" class="card-link">
                  Read More <i class="bi bi-arrow-right"></i>
                </a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Pagination">

          <!-- Previous -->
          <a href="<?= pageUrl($currentPage - 1, $search, $category, $status) ?>"
             class="page-btn <?= $currentPage <= 1 ? 'disabled' : '' ?>"
             aria-label="Previous page">
            <i class="bi bi-chevron-left"></i>
          </a>

          <?php
          // Page range logic
          $delta = 2;
          $range = [];
          for ($i = max(1, $currentPage - $delta); $i <= min($totalPages, $currentPage + $delta); $i++) {
              $range[] = $i;
          }
          $showFirst = !in_array(1, $range);
          $showLast  = !in_array($totalPages, $range);

          if ($showFirst) {
              echo '<a href="' . pageUrl(1, $search, $category, $status) . '" class="page-btn">1</a>';
              if (!in_array(2, $range)) echo '<span class="page-ellipsis">…</span>';
          }
          foreach ($range as $pg) {
              $active = $pg === $currentPage ? 'active' : '';
              echo "<a href=\"" . pageUrl($pg, $search, $category, $status) . "\" class=\"page-btn $active\" aria-current=\"" . ($active ? 'page' : 'false') . "\">$pg</a>";
          }
          if ($showLast) {
              if (!in_array($totalPages - 1, $range)) echo '<span class="page-ellipsis">…</span>';
              echo '<a href="' . pageUrl($totalPages, $search, $category, $status) . '" class="page-btn">' . $totalPages . '</a>';
          }
          ?>

          <!-- Next -->
          <a href="<?= pageUrl($currentPage + 1, $search, $category, $status) ?>"
             class="page-btn <?= $currentPage >= $totalPages ? 'disabled' : '' ?>"
             aria-label="Next page">
            <i class="bi bi-chevron-right"></i>
          </a>

        </nav>
      <?php endif; ?>

    <?php endif; ?>
  </div>
</main>

<!-- ══════════════════════════════════════
     FOOTER
══════════════════════════════════════ -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div>
         <img src="assets/images/sk-logo.png" alt="SK Logo" class="nav-brand-img" />
        <div class="footer-brand-name">SK Barangay Esperanza Ilaya</div>
        <div class="footer-brand-sub">Sangguniang Kabataan</div>
        <p class="footer-brand-desc">
          The official digital platform of the Sangguniang Kabataan of
          Barangay Esperanza Ilaya — committed to transparent and
          youth-centered governance.
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

<script>
  // Mobile nav toggle
  const toggle = document.getElementById('navToggle');
  const links  = document.getElementById('navLinks');
  toggle.addEventListener('click', () => links.classList.toggle('open'));
</script>

</body>
</html>