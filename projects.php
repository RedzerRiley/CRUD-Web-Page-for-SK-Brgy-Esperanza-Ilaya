<?php
// projects.php - SK Barangay Esperanza Ilaya - Projects Page
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Explore the projects, events, and accomplishments of the Sangguniang Kabataan of Barangay Esperanza Ilaya." />
  <title>Projects — SK Barangay Esperanza Ilaya</title>
  <link rel="icon" type="image/png" href="/assets/images/sk-logo.png" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <style>
    /* ── PROJECTS HERO ── */
    .projects-hero {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 60%, var(--primary-light) 100%);
      padding: 56px 0 48px;
      position: relative;
      overflow: hidden;
    }

    .projects-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='64' height='64' viewBox='0 0 64 64' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M32 0h4v12h-4zM32 52h4v12h-4zM0 32h12v4H0zM52 32h12v4H52zM9.4 6.6l2.8-2.8 8.5 8.5-2.8 2.8zM43.3 40.5l2.8-2.8 8.5 8.5-2.8 2.8zM6.6 54.6l8.5-8.5 2.8 2.8-8.5 8.5zM40.5 20.7l8.5-8.5 2.8 2.8-8.5 8.5z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .projects-hero .container {
      position: relative;
      z-index: 1;
      text-align: center;
    }

    .projects-hero .section-label { color: var(--accent-light); }
    .projects-hero .section-label::before { display: none; }

    .projects-hero-title {
      font-family: var(--font-display);
      font-size: clamp(1.8rem, 4vw, 2.7rem);
      font-weight: 700;
      color: var(--white);
      margin-bottom: 12px;
    }

    .projects-hero-desc {
      font-size: 1rem;
      color: rgba(255,255,255,0.72);
      max-width: 650px;
      margin: 0 auto;
      line-height: 1.7;
    }

    /* ── PROJECT STATS ── */
    .projects-stats {
      background: var(--white);
      padding: 26px 0;
      border-bottom: 1px solid var(--light-gray);
    }

    .projects-stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 18px;
    }

    .projects-stat-card {
      background: var(--off-white);
      border: 1px solid var(--light-gray);
      border-radius: 10px;
      padding: 22px 20px;
      text-align: center;
      box-shadow: var(--shadow-sm);
    }

    .projects-stat-icon {
      width: 52px;
      height: 52px;
      margin: 0 auto 12px;
      border-radius: 12px;
      background: var(--primary);
      color: var(--white);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
    }

    .projects-stat-value {
      font-family: var(--font-display);
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--text);
      line-height: 1;
      margin-bottom: 6px;
    }

    .projects-stat-label {
      font-size: 0.82rem;
      color: var(--mid-gray);
      text-transform: uppercase;
      letter-spacing: 0.8px;
      font-weight: 600;
    }

    /* ── MAIN PROJECT SECTION ── */
    .projects-main {
      padding: var(--section-py) 0;
      background: var(--off-white);
    }

    .projects-layout {
      display: grid;
      grid-template-columns: 280px 1fr;
      gap: 32px;
      align-items: start;
    }

    /* ── SIDEBAR ── */
    .projects-sidebar {
      position: sticky;
      top: 24px;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .projects-sidebar-card {
      background: var(--white);
      border: 1px solid var(--light-gray);
      border-radius: 10px;
      padding: 22px;
      box-shadow: var(--shadow-sm);
    }

    .projects-sidebar-title {
      font-family: var(--font-display);
      font-size: 1rem;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 14px;
    }

    .projects-filter-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .projects-filter-link {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 12px;
      border-radius: 8px;
      background: var(--off-white);
      color: var(--text);
      font-size: 0.92rem;
      font-weight: 600;
      border: 1px solid transparent;
      transition: all var(--transition);
    }

    .projects-filter-link:hover,
    .projects-filter-link.active {
      background: rgba(0,51,102,0.06);
      color: var(--primary);
      border-color: rgba(0,51,102,0.12);
    }

    .projects-filter-link span:last-child {
      font-size: 0.78rem;
      color: var(--mid-gray);
    }

    .projects-highlight-box {
      background: var(--primary);
      border-radius: 10px;
      padding: 22px;
      color: var(--white);
    }

    .projects-highlight-box h3 {
      font-family: var(--font-display);
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .projects-highlight-box p {
      color: rgba(255,255,255,0.72);
      font-size: 0.92rem;
      line-height: 1.7;
      margin-bottom: 16px;
    }

    .projects-highlight-box a {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-size: 0.9rem;
      font-weight: 600;
      color: var(--accent-light);
      transition: gap var(--transition);
    }

    .projects-highlight-box a:hover { gap: 12px; }

    /* ── PROJECT GRID ── */
    .projects-content .section-header {
      margin-bottom: 28px;
    }

    .projects-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 24px;
    }

    .project-card {
      background: var(--white);
      border: 1px solid var(--light-gray);
      border-radius: 12px;
      overflow: hidden;
      box-shadow: var(--shadow-sm);
      transition: all var(--transition);
      display: flex;
      flex-direction: column;
    }

    .project-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg);
      border-color: rgba(0,51,102,0.15);
    }

    .project-card-media {
      height: 210px;
      background: linear-gradient(135deg, rgba(0,51,102,0.95), rgba(0,102,204,0.8));
      position: relative;
      display: flex;
      align-items: flex-end;
      padding: 20px;
      overflow: hidden;
    }

    .project-card-media::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at top right, rgba(255,255,255,0.22), transparent 40%);
    }

    .project-badge {
      position: absolute;
      top: 18px;
      left: 18px;
      z-index: 1;
      padding: 6px 10px;
      border-radius: 999px;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      background: rgba(255,255,255,0.14);
      color: var(--white);
      backdrop-filter: blur(4px);
    }

    .project-media-icon {
      position: relative;
      z-index: 1;
      width: 58px;
      height: 58px;
      border-radius: 14px;
      background: rgba(255,255,255,0.14);
      color: var(--white);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.45rem;
      box-shadow: inset 0 0 0 1px rgba(255,255,255,0.08);
    }

    .project-card-body {
      padding: 22px;
      display: flex;
      flex-direction: column;
      flex: 1;
    }

    .project-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 14px;
      margin-bottom: 12px;
      font-size: 0.8rem;
      color: var(--mid-gray);
      font-weight: 500;
    }

    .project-meta span {
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .project-title {
      font-family: var(--font-display);
      font-size: 1.08rem;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 10px;
      line-height: 1.4;
    }

    .project-desc {
      color: var(--mid-gray);
      font-size: 0.93rem;
      line-height: 1.7;
      margin-bottom: 18px;
    }

    .project-progress {
      margin-bottom: 18px;
    }

    .project-progress-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.84rem;
      font-weight: 600;
      margin-bottom: 8px;
      color: var(--text);
    }

    .project-progress-bar {
      width: 100%;
      height: 8px;
      border-radius: 999px;
      background: var(--light-gray);
      overflow: hidden;
    }

    .project-progress-fill {
      height: 100%;
      background: linear-gradient(90deg, var(--primary), var(--accent));
      border-radius: 999px;
    }

    .project-footer {
      margin-top: auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding-top: 16px;
      border-top: 1px solid var(--light-gray);
    }

    .project-status {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      font-size: 0.82rem;
      font-weight: 600;
      color: var(--primary);
    }

    .project-status i {
      font-size: 0.7rem;
    }

    .project-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: var(--primary);
      font-size: 0.88rem;
      font-weight: 700;
      transition: gap var(--transition), color var(--transition);
    }

    .project-link:hover {
      gap: 12px;
      color: var(--accent-dark);
    }

    /* ── CTA SECTION ── */
    .projects-cta {
      padding: var(--section-py) 0;
      background: var(--white);
      border-top: 1px solid var(--light-gray);
    }

    .projects-cta-box {
      background: linear-gradient(135deg, var(--primary-dark), var(--primary));
      border-radius: 14px;
      padding: 38px 34px;
      color: var(--white);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 24px;
      box-shadow: var(--shadow-md);
    }

    .projects-cta-text h2 {
      font-family: var(--font-display);
      font-size: clamp(1.35rem, 3vw, 2rem);
      margin-bottom: 10px;
      color: var(--white);
    }

    .projects-cta-text p {
      color: rgba(255,255,255,0.74);
      max-width: 620px;
      line-height: 1.7;
      font-size: 0.96rem;
    }

    .projects-cta-btn {
      flex-shrink: 0;
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: var(--white);
      color: var(--primary);
      padding: 14px 20px;
      border-radius: 10px;
      font-weight: 700;
      transition: all var(--transition);
    }

    .projects-cta-btn:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 1024px) {
      .projects-layout {
        grid-template-columns: 1fr;
      }

      .projects-sidebar {
        position: static;
      }
    }

    @media (max-width: 900px) {
      .projects-stats-grid,
      .projects-grid {
        grid-template-columns: repeat(2, 1fr);
      }

      .projects-cta-box {
        flex-direction: column;
        align-items: flex-start;
      }
    }

    @media (max-width: 600px) {
      .projects-stats-grid,
      .projects-grid {
        grid-template-columns: 1fr;
      }
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
      <li><a href="projects.php" class="active">Projects</a></li>
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
      <li class="current">Projects</li>
    </ul>
  </div>
</div>

<!-- ══════════════════════════════════════
     PROJECTS HERO
══════════════════════════════════════ -->
<section class="projects-hero">
  <div class="container">
    <span class="section-label" style="padding-left:0;">Youth Initiatives</span>
    <h1 class="projects-hero-title">Projects and Community Programs</h1>
    <p class="projects-hero-desc">
      Discover the programs, outreach activities, and youth-centered initiatives
      led by the Sangguniang Kabataan of Barangay Esperanza Ilaya for community development.
    </p>
  </div>
</section>

</section>

<?php
    $stats = [
    'projects' => 0,
    'events' => 0,
    'completed' => 0
    ];

    try {

    $stats['projects'] = $pdo->query("
        SELECT COUNT(*) 
        FROM post p
        JOIN category c ON p.category_id = c.category_id
        WHERE c.category_name = 'Projects'
    ")->fetchColumn();

    $stats['events'] = $pdo->query("
        SELECT COUNT(*) 
        FROM post p
        JOIN category c ON p.category_id = c.category_id
        WHERE c.category_name = 'Events'
    ")->fetchColumn();

    $stats['completed'] = $pdo->query("
        SELECT COUNT(*) 
        FROM post
        WHERE status = 'Completed'
    ")->fetchColumn();

    } catch (PDOException $e) {}
?>

<!-- ══════════════════════════════════════
     PROJECT STATS
══════════════════════════════════════ -->
<section class="projects-stats">
  <div class="container">
    <div class="projects-stats-grid">
      <div class="projects-stat-card">
        <div class="projects-stat-icon"><i class="bi bi-kanban-fill"></i></div>
        <div class="projects-stat-value"><?= $stats['projects'] ?></div>
        <div class="projects-stat-label">Total Projects</div>
      </div>
      <div class="projects-stat-card">
        <div class="projects-stat-icon"><i class="bi bi-calendar-event-fill"></i></div>
        <div class="projects-stat-value"><?= $stats['events'] ?></div>
        <div class="projects-stat-label">Upcoming Events</div>
      </div>
      <div class="projects-stat-card">
        <div class="projects-stat-icon"><i class="bi bi-check-circle-fill"></i></div>
        <div class="projects-stat-value"><?= $stats['completed'] ?></div>
        <div class="projects-stat-label">Completed</div>
      </div>
      <div class="projects-stat-card">
        <div class="projects-stat-icon"><i class="bi bi-people-fill"></i></div>
        <div class="projects-stat-value">350+</div>
        <div class="projects-stat-label">Youth Reached</div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════
     MAIN PROJECTS SECTION
══════════════════════════════════════ -->
<section class="projects-main">
  <div class="container">
    <?php
    $selectedCategory = isset($_GET['cat']) ? trim($_GET['cat']) : 'All';

    $projects = [];

    try {

        $sql = "
            SELECT p.*, c.category_name,
                (SELECT file_path FROM post_media WHERE post_id = p.post_id LIMIT 1) AS thumbnail
            FROM post p
            JOIN category c ON p.category_id = c.category_id
            WHERE c.category_name IN ('Projects','Events','Accomplishments')
        ";

        if ($selectedCategory !== 'All') {
            $sql .= " AND c.category_name = :category";
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $pdo->prepare($sql);

        if ($selectedCategory !== 'All') {
            $stmt->bindParam(':category', $selectedCategory);
        }

        $stmt->execute();
        $projects = $stmt->fetchAll();

    } catch (PDOException $e) {
        $projects = [];
    }


    /* Category counts */

    $categories = [
        'All' => 0,
        'Projects' => 0,
        'Events' => 0,
        'Accomplishments' => 0
    ];

    try {

        $categories['Projects'] = $pdo->query("
            SELECT COUNT(*) 
            FROM post p 
            JOIN category c ON p.category_id = c.category_id 
            WHERE c.category_name = 'Projects'
        ")->fetchColumn();

        $categories['Events'] = $pdo->query("
            SELECT COUNT(*) 
            FROM post p 
            JOIN category c ON p.category_id = c.category_id 
            WHERE c.category_name = 'Events'
        ")->fetchColumn();

        $categories['Accomplishments'] = $pdo->query("
            SELECT COUNT(*) 
            FROM post p 
            JOIN category c ON p.category_id = c.category_id 
            WHERE c.category_name = 'Accomplishments'
        ")->fetchColumn();

        $categories['All'] =
            $categories['Projects'] +
            $categories['Events'] +
            $categories['Accomplishments'];

    } catch (PDOException $e) {}
    ?>

    <div class="projects-layout">

      <!-- LEFT: Sidebar -->
      <aside class="projects-sidebar">
        <div class="projects-sidebar-card">
          <div class="projects-sidebar-title">Browse Categories</div>
          <div class="projects-filter-list">
            <?php foreach ($categories as $category => $count): ?>
              <a
                href="projects.php<?= $category !== 'All' ? '?cat=' . urlencode($category) : '' ?>"
                class="projects-filter-link <?= $selectedCategory === $category ? 'active' : '' ?>"
              >
                <span><?= htmlspecialchars($category) ?></span>
                <span><?= $count ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="projects-highlight-box">
          <h3>Want to participate?</h3>
          <p>
            Stay updated on youth-led activities, volunteer opportunities,
            and SK community programs by checking our announcements regularly.
          </p>
          <a href="announcements.php">
            View Announcements
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </aside>

      <!-- RIGHT: Project Cards -->
      <div class="projects-content">
        <div class="section-header">
          <span class="section-label">Our Initiatives</span>
          <h2 class="section-title">
            <?= $selectedCategory === 'All' ? 'All Projects and Programs' : htmlspecialchars($selectedCategory) ?>
          </h2>
          <p class="section-desc">
            Explore the different initiatives and activities organized for the youth and community of Barangay Esperanza Ilaya.
          </p>
        </div>

        <div class="projects-grid">
          <?php if (!empty($projects)): ?>
            <?php foreach ($projects as $project): ?>
              <article class="project-card">
               <div class="project-card-media"
                    style="<?= !empty($project['thumbnail']) ? "background-image:url('" . htmlspecialchars($project['thumbnail']) . "'); background-size:cover; background-position:center;" : "" ?>">

                <span class="project-badge">
                    <?= htmlspecialchars($project['category_name']) ?>
                </span>
                
                </div>

                <div class="project-card-body">
                    <div class="project-meta">
                        <span><i class="bi bi-calendar3"></i> <?= date('M j, Y', strtotime($project['created_at'])) ?></span>
                        <span><i class="bi bi-tag"></i> <?= htmlspecialchars($project['category_name']) ?></span>
                    </div>

                    <h3 class="project-title"><?= htmlspecialchars($project['title']) ?></h3>
                    <p class="project-desc">
                        <?= htmlspecialchars(strlen(strip_tags($project['description'])) > 120 ? substr(strip_tags($project['description']), 0, 120) . '...' : strip_tags($project['description'])) ?>
                    </p>

                    <div class="project-footer">
                        <span class="project-status">
                        <i class="bi bi-circle-fill"></i>
                        <?= htmlspecialchars($project['status']) ?>
                        </span>
                        <a href="post-detail.php?id=<?= $project['post_id'] ?>" class="project-link">
                        Learn More
                        <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
              </article>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="projects-sidebar-card" style="grid-column: 1 / -1;">
              <div class="projects-sidebar-title">No projects found</div>
              <p style="color: var(--mid-gray); line-height: 1.7; font-size: 0.94rem;">
                There are currently no entries under this category. Please check back later for updates.
              </p>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ══════════════════════════════════════
     CTA SECTION
══════════════════════════════════════ -->
<section class="projects-cta">
  <div class="container">
    <div class="projects-cta-box">
      <div class="projects-cta-text">
        <h2>Support Youth Development in Our Barangay</h2>
        <p>
          The SK continues to create programs that empower young people through
          education, sports, environmental action, and leadership opportunities.
          Stay connected and be part of future activities.
        </p>
      </div>
      <a href="contact.php" class="projects-cta-btn">
        <i class="bi bi-envelope-fill"></i>
        Contact the SK Office
      </a>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════
     FOOTER
══════════════════════════════════════ -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <img src="assets/images/sk-logo.png" alt="SK Logo" class="footer-brand-img" />
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
  const toggle = document.getElementById('navToggle');
  const links  = document.getElementById('navLinks');
  toggle.addEventListener('click', () => links.classList.toggle('open'));
</script>

</body>
</html>