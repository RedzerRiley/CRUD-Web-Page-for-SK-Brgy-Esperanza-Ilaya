<?php
// reports.php - Public Reports Page
require_once 'includes/db.php';

// Fetch posts under "Accomplishments" category (used as reports)
// Also fetch any category named "Reports" if it exists
$reports_stmt = $pdo->query("
    SELECT p.*, c.category_name,
           (SELECT file_path FROM post_media WHERE post_id = p.post_id LIMIT 1) AS thumbnail
    FROM post p
    JOIN category c ON p.category_id = c.category_id
    WHERE c.category_name IN ('Accomplishments', 'Reports')
    ORDER BY p.created_at DESC
");
$reports = $reports_stmt->fetchAll();

// Group by year for the timeline layout
$by_year = [];
foreach ($reports as $r) {
    $year = date('Y', strtotime($r['created_at']));
    $by_year[$year][] = $r;
}
krsort($by_year);

// Stats
$total_reports    = count($reports);
$completed_count  = count(array_filter($reports, fn($r) => $r['status'] === 'Completed'));
$ongoing_count    = count(array_filter($reports, fn($r) => $r['status'] === 'Ongoing'));
$upcoming_count   = count(array_filter($reports, fn($r) => $r['status'] === 'Upcoming'));

function statusClass($s) {
    return match($s) {
        'Upcoming'  => 'status-upcoming',
        'Ongoing'   => 'status-ongoing',
        'Completed' => 'status-completed',
        default     => 'status-upcoming'
    };
}

function truncate($text, $limit = 130) {
    $plain = strip_tags($text);
    return strlen($plain) > $limit ? substr($plain, 0, $limit) . '...' : $plain;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reports & Accomplishments — SK Barangay Esperanza Ilaya</title>
  <link rel="icon" type="image/png" href="assets/images/sk-logo.png" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <style>
    /* ── PAGE HERO ── */
    .page-hero {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 60%, var(--primary-light) 100%);
      padding: 72px 0 56px;
      position: relative;
      overflow: hidden;
    }

    .page-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .page-hero-inner {
      position: relative;
      z-index: 1;
      text-align: center;
    }

    .page-hero-label {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: rgba(200,150,12,0.15);
      border: 1px solid rgba(200,150,12,0.35);
      color: var(--accent-light);
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      padding: 6px 16px;
      border-radius: 20px;
      margin-bottom: 18px;
    }

    .page-hero-title {
      font-family: var(--font-display);
      font-size: clamp(1.8rem, 4vw, 2.8rem);
      font-weight: 700;
      color: var(--white);
      line-height: 1.2;
      margin-bottom: 16px;
    }

    .page-hero-desc {
      font-size: 1rem;
      color: rgba(255,255,255,0.7);
      max-width: 560px;
      margin: 0 auto;
      line-height: 1.7;
    }

    /* ── STATS BAR ── */
    .reports-stats {
      background: var(--white);
      border-bottom: 1px solid var(--light-gray);
      padding: 24px 0;
      box-shadow: var(--shadow-sm);
    }

    .reports-stats .container {
      display: flex;
      justify-content: center;
      gap: 0;
    }

    .rstat-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
      padding: 0 40px;
      border-right: 1px solid var(--light-gray);
    }

    .rstat-item:last-child { border-right: none; }

    .rstat-number {
      font-family: var(--font-display);
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary);
      line-height: 1;
    }

    .rstat-number.gold   { color: var(--accent); }
    .rstat-number.green  { color: var(--success); }
    .rstat-number.orange { color: #D97706; }

    .rstat-label {
      font-size: 0.75rem;
      color: var(--text-light);
      letter-spacing: 0.5px;
      text-transform: uppercase;
      font-weight: 600;
    }

    /* ── BREADCRUMB ── */
    .breadcrumb {
      background: var(--off-white);
      border-bottom: 1px solid var(--light-gray);
      padding: 12px 0;
    }

    .breadcrumb-list {
      display: flex;
      align-items: center;
      gap: 8px;
      list-style: none;
      font-size: 0.82rem;
    }

    .breadcrumb-list a { color: var(--primary); text-decoration: none; }
    .breadcrumb-list a:hover { text-decoration: underline; }
    .breadcrumb-list .sep { color: var(--mid-gray); }
    .breadcrumb-list .current { color: var(--text-light); }

    /* ── MAIN CONTENT ── */
    .reports-section {
      padding: var(--section-py) 0;
      background: var(--off-white);
    }

    /* ── YEAR TIMELINE ── */
    .year-block { margin-bottom: 56px; }
    .year-block:last-child { margin-bottom: 0; }

    .year-heading {
      display: flex;
      align-items: center;
      gap: 16px;
      margin-bottom: 28px;
    }

    .year-label {
      font-family: var(--font-display);
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary);
      white-space: nowrap;
    }

    .year-line {
      flex: 1;
      height: 2px;
      background: linear-gradient(to right, var(--accent), transparent);
      border-radius: 2px;
    }

    .year-count {
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--mid-gray);
      letter-spacing: 0.5px;
      white-space: nowrap;
    }

    /* ── REPORT CARDS ── */
    .reports-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 24px;
    }

    .report-card {
      background: var(--white);
      border: 1px solid var(--light-gray);
      border-radius: 10px;
      overflow: hidden;
      box-shadow: var(--shadow-sm);
      transition: transform var(--transition), box-shadow var(--transition);
      display: flex;
      flex-direction: column;
    }

    .report-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-md);
    }

    .report-card-header {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
      padding: 20px 22px;
      position: relative;
    }

    .report-card-icon {
      width: 42px;
      height: 42px;
      background: rgba(255,255,255,0.12);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--accent-light);
      font-size: 1.2rem;
      margin-bottom: 12px;
    }

    .report-card-title {
      font-family: var(--font-display);
      font-size: 1rem;
      font-weight: 700;
      color: var(--white);
      line-height: 1.35;
    }

    .report-card-category {
      position: absolute;
      top: 14px;
      right: 14px;
      background: rgba(200,150,12,0.2);
      border: 1px solid rgba(200,150,12,0.35);
      color: var(--accent-light);
      font-size: 0.65rem;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      padding: 3px 10px;
      border-radius: 20px;
    }

    .report-card-body {
      padding: 20px 22px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .report-card-meta {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 12px;
    }

    .report-card-date {
      font-size: 0.78rem;
      color: var(--text-light);
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .report-card-desc {
      font-size: 0.875rem;
      color: var(--text-light);
      line-height: 1.65;
      flex: 1;
      margin-bottom: 16px;
    }

    .report-card-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-top: 14px;
      border-top: 1px solid var(--light-gray);
    }

    .report-card-author {
      font-size: 0.75rem;
      color: var(--mid-gray);
      display: flex;
      align-items: center;
      gap: 5px;
    }

    /* ── EMPTY STATE ── */
    .reports-empty {
      text-align: center;
      padding: 80px 24px;
      color: var(--text-light);
    }

    .reports-empty i {
      font-size: 3rem;
      color: var(--mid-gray);
      display: block;
      margin-bottom: 16px;
    }

    .reports-empty h3 {
      font-family: var(--font-display);
      font-size: 1.2rem;
      color: var(--primary);
      margin-bottom: 8px;
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 768px) {
      .reports-stats .container { flex-wrap: wrap; gap: 16px; }
      .rstat-item { border-right: none; padding: 0 20px; }
      .reports-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<!-- TOP BAR -->
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

<!-- NAVBAR -->
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
      <li><a href="reports.php" class="active">Reports</a></li>
      <li><a href="contact.php">Contact</a></li>
    </ul>
    <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- BREADCRUMB -->
<div class="breadcrumb">
  <div class="container">
    <ul class="breadcrumb-list">
      <li><a href="index.php"><i class="bi bi-house-fill"></i> Home</a></li>
      <li class="sep">›</li>
      <li class="current">Reports & Accomplishments</li>
    </ul>
  </div>
</div>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-inner">
      <div class="page-hero-label">
        <i class="bi bi-bar-chart-fill"></i> Transparency & Accountability
      </div>
      <h1 class="page-hero-title">Reports & Accomplishments</h1>
      <p class="page-hero-desc">
        A transparent record of the Sangguniang Kabataan's programs, initiatives,
        and accomplishments for the youth of Barangay Esperanza Ilaya.
      </p>
    </div>
  </div>
</section>

<!-- STATS BAR -->
<div class="reports-stats">
  <div class="container">
    <div class="rstat-item">
      <span class="rstat-number"><?= $total_reports ?></span>
      <span class="rstat-label">Total Reports</span>
    </div>
    <div class="rstat-item">
      <span class="rstat-number green"><?= $completed_count ?></span>
      <span class="rstat-label">Completed</span>
    </div>
    <div class="rstat-item">
      <span class="rstat-number orange"><?= $ongoing_count ?></span>
      <span class="rstat-label">Ongoing</span>
    </div>
    <div class="rstat-item">
      <span class="rstat-number gold"><?= $upcoming_count ?></span>
      <span class="rstat-label">Upcoming</span>
    </div>
  </div>
</div>

<!-- REPORTS SECTION -->
<section class="reports-section">
  <div class="container">

    <?php if (empty($reports)): ?>
      <div class="reports-empty">
        <i class="bi bi-clipboard2-x"></i>
        <h3>No Reports Yet</h3>
        <p>Check back soon for updates on the SK's accomplishments and programs.</p>
      </div>

    <?php else: ?>
      <?php foreach ($by_year as $year => $year_reports): ?>
        <div class="year-block">

          <!-- Year Heading -->
          <div class="year-heading">
            <span class="year-label"><?= $year ?></span>
            <div class="year-line"></div>
            <span class="year-count"><?= count($year_reports) ?> report<?= count($year_reports) > 1 ? 's' : '' ?></span>
          </div>

          <!-- Cards Grid -->
          <div class="reports-grid">
            <?php foreach ($year_reports as $report): ?>
              <article class="report-card">
                <div class="report-card-header">
                  <div class="report-card-icon">
                    <?php
                      $icon = match($report['category_name']) {
                          'Accomplishments' => 'bi bi-trophy-fill',
                          'Reports'         => 'bi bi-file-earmark-bar-graph-fill',
                          default           => 'bi bi-file-earmark-text-fill'
                      };
                    ?>
                    <i class="<?= $icon ?>"></i>
                  </div>
                  <div class="report-card-title"><?= htmlspecialchars($report['title']) ?></div>
                  <span class="report-card-category"><?= htmlspecialchars($report['category_name']) ?></span>
                </div>

                <div class="report-card-body">
                  <div class="report-card-meta">
                    <span class="report-card-date">
                      <i class="bi bi-calendar3"></i>
                      <?= date('M j, Y', strtotime($report['created_at'])) ?>
                    </span>
                    <span class="card-status <?= statusClass($report['status']) ?>">
                      <?= htmlspecialchars($report['status']) ?>
                    </span>
                  </div>

                  <p class="report-card-desc"><?= htmlspecialchars(truncate($report['description'])) ?></p>

                  <div class="report-card-footer">
                    <span class="report-card-author">
                      <i class="bi bi-person-fill"></i>
                      <?= htmlspecialchars($report['author_name'] ?? 'SK Admin') ?>
                    </span>
                    <a href="post-detail.php?id=<?= $report['post_id'] ?>" class="card-link">
                      View Details <i class="bi bi-arrow-right"></i>
                    </a>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>

        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</section>

<!-- FOOTER -->
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
          <li><a href="reports.php">Annual Reports</a></li>
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
  const toggle = document.getElementById('navToggle');
  const links  = document.getElementById('navLinks');
  toggle.addEventListener('click', () => links.classList.toggle('open'));
</script>

</body>
</html>