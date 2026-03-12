<?php
// index.php - SK Barangay Esperanza Ilaya - Public Homepage
require_once 'includes/db.php';

// Fetch latest announcements (limit 3)
$announcements = [];
try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.category_name,
               (SELECT file_path FROM post_media WHERE post_id = p.post_id LIMIT 1) AS thumbnail
        FROM post p
        JOIN category c ON p.category_id = c.category_id
        ORDER BY p.created_at DESC
        LIMIT 3
    ");
    $stmt->execute();
    $announcements = $stmt->fetchAll();
} catch (PDOException $e) {
    // silently fail on frontend
}

// Fetch latest projects (limit 3)
$projects = [];
try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.category_name
        FROM post p
        JOIN category c ON p.category_id = c.category_id
        WHERE c.category_name = 'Projects'
        ORDER BY p.created_at DESC
        LIMIT 3
    ");
    $stmt->execute();
    $projects = $stmt->fetchAll();
} catch (PDOException $e) {}

// Fetch stats
$stats = ['total_posts' => 0, 'projects' => 0, 'events' => 0, 'completed' => 0];
try {
    $stats['total_posts'] = $pdo->query("SELECT COUNT(*) FROM post")->fetchColumn();
    $stats['projects']    = $pdo->query("SELECT COUNT(*) FROM post p JOIN category c ON p.category_id = c.category_id WHERE c.category_name = 'Projects'")->fetchColumn();
    $stats['events']      = $pdo->query("SELECT COUNT(*) FROM post p JOIN category c ON p.category_id = c.category_id WHERE c.category_name = 'Events'")->fetchColumn();
    $stats['completed']   = $pdo->query("SELECT COUNT(*) FROM post WHERE status = 'Completed'")->fetchColumn();
} catch (PDOException $e) {}

// Helper: status badge class
function statusClass($status) {
    return match($status) {
        'Upcoming'  => 'status-upcoming',
        'Ongoing'   => 'status-ongoing',
        'Completed' => 'status-completed',
        default     => 'status-upcoming'
    };
}

// Helper: truncate text
function truncate($text, $limit = 120) {
    $plain = strip_tags($text);
    return strlen($plain) > $limit ? substr($plain, 0, $limit) . '...' : $plain;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Official website of the Sangguniang Kabataan of Barangay Esperanza Ilaya. Stay updated on projects, programs, events, and announcements." />
  <title>SK Barangay Esperanza Ilaya — Official Website</title>
  <link rel="icon" type="image/png" href="assets/images/sk-logo.png" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
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
      <li><a href="index.php" class="active">Home</a></li>
      <li><a href="about.php">About</a></li>
      <li><a href="projects.php">Projects</a></li>
      <li><a href="announcements.php">Announcements</a></li>
      <li><a href="reports.php">Reports</a></li>
      <li><a href="contact.php">Contact</a></li>
    </ul>

    <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </div>
</nav>

<!-- ══════════════════════════════════════
     HERO SECTION
══════════════════════════════════════ -->
<section class="hero">
  <div class="container">
    <div class="hero-content">
      <div class="hero-badge">Official SK Website</div>
      <h1 class="hero-title">Sangguniang Kabataan</h1>
      <p class="hero-subtitle">Barangay Esperanza Ilaya</p>
      <p class="hero-desc">
        Serving the youth of Barangay Esperanza Ilaya through transparent governance,
        meaningful programs, and community-driven initiatives.
      </p>
      <div class="hero-actions">
        <a href="projects.php" class="btn btn-primary">
          <i class="bi bi-grid-fill"></i> View Projects
        </a>
        <a href="announcements.php" class="btn btn-outline">
          <i class="bi bi-megaphone-fill"></i> Announcements
        </a>
      </div>
    </div>

    <div class="hero-image">
      <img src="assets/images/hero-section.png" alt="SK Barangay Esperanza Ilaya" />
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════
     STATS BAR
══════════════════════════════════════ -->
<div class="hero-stats">
  <div class="container">
    <div class="stat-item">
      <div class="stat-number"><?= $stats['total_posts'] ?>+</div>
      <div class="stat-label">Total Posts</div>
    </div>
    <div class="stat-item">
      <div class="stat-number"><?= $stats['projects'] ?>+</div>
      <div class="stat-label">Projects</div>
    </div>
    <div class="stat-item">
      <div class="stat-number"><?= $stats['events'] ?>+</div>
      <div class="stat-label">Events</div>
    </div>
    <div class="stat-item">
      <div class="stat-number"><?= $stats['completed'] ?>+</div>
      <div class="stat-label">Completed</div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════
     LATEST ANNOUNCEMENTS
══════════════════════════════════════ -->
<section class="announcements-section">
  <div class="container">
    <div class="section-header-row">
      <div class="section-header" style="margin-bottom:0">
        <span class="section-label">Stay Informed</span>
        <h2 class="section-title">Latest Announcements</h2>
      </div>
      <a href="announcements.php" class="view-all">
        View All <i class="bi bi-arrow-right"></i>
      </a>
    </div>

    <?php if (empty($announcements)): ?>
      <div style="text-align:center; padding:48px; color:var(--mid-gray);">
        <i class="bi bi-megaphone" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
        No announcements yet. Check back soon.
      </div>
    <?php else: ?>
      <div class="announcements-grid">
        <?php foreach ($announcements as $post): ?>
          <article class="announcement-card">
            <div class="card-image">
              <?php if ($post['thumbnail']): ?>
                <img src="<?= htmlspecialchars($post['thumbnail']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" />
              <?php else: ?>
                <span>Announcement Image</span>
              <?php endif; ?>
              <span class="card-category"><?= htmlspecialchars($post['category_name']) ?></span>
            </div>
            <div class="card-body">
              <div class="card-meta">
                <span class="card-date">
                  <i class="bi bi-calendar3"></i>
                  <?= date('M j, Y', strtotime($post['created_at'])) ?>
                </span>
                <span class="card-status <?= statusClass($post['status']) ?>">
                  <?= htmlspecialchars($post['status']) ?>
                </span>
              </div>
              <h3 class="card-title"><?= htmlspecialchars($post['title']) ?></h3>
              <p class="card-excerpt"><?= htmlspecialchars(truncate($post['description'])) ?></p>
              <a href="post-detail.php?id=<?= $post['post_id'] ?>" class="card-link">
                Read More <i class="bi bi-arrow-right"></i>
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ══════════════════════════════════════
     PROJECTS SECTION
══════════════════════════════════════ -->
<section class="projects-section">
  <div class="container">
    <div class="section-header-row">
      <div class="section-header" style="margin-bottom:0">
        <span class="section-label">What We Do</span>
        <h2 class="section-title">Featured Projects</h2>
      </div>
      <a href="projects.php" class="view-all">
        View All <i class="bi bi-arrow-right"></i>
      </a>
    </div>

    <?php if (empty($projects)): ?>
      <div style="text-align:center; padding:48px; color:var(--mid-gray);">
        <i class="bi bi-briefcase" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
        No projects posted yet.
      </div>
    <?php else: ?>
      <div class="projects-grid">
        <?php foreach ($projects as $i => $project): 
          $icons = ['bi-people-fill', 'bi-tree-fill', 'bi-heart-fill', 'bi-star-fill', 'bi-lightbulb-fill'];
          $icon  = $icons[$i % count($icons)];
        ?>
          <div class="project-card">
            <div class="project-card-header">
              <div class="project-icon">
                <i class="bi <?= $icon ?>" style="font-size:1.1rem;"></i>
              </div>
              <h3 class="project-card-title"><?= htmlspecialchars($project['title']) ?></h3>
              <span class="project-card-category"><?= htmlspecialchars($project['category_name']) ?></span>
            </div>
            <div class="project-card-body">
              <p class="project-card-desc"><?= htmlspecialchars(truncate($project['description'])) ?></p>
              <div class="project-card-footer">
                <span class="card-status <?= statusClass($project['status']) ?>">
                  <?= htmlspecialchars($project['status']) ?>
                </span>
                <a href="post-detail.php?id=<?= $project['post_id'] ?>" class="card-link">
                  Details <i class="bi bi-arrow-right"></i>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ══════════════════════════════════════
     ABOUT STRIP
══════════════════════════════════════ -->
<section class="about-strip">
  <div class="container">
    <div class="about-content">
      <span class="section-label">Who We Are</span>
      <h2 class="section-title">Serving the Youth of<br>Barangay Esperanza Ilaya</h2>
      <p class="section-desc">
        The Sangguniang Kabataan is the official youth council of Barangay Esperanza Ilaya,
        dedicated to implementing programs and projects that empower the youth and
        contribute to the development of the community.
      </p>
      <div class="about-features">
        <div class="about-feature">
          <div class="about-feature-icon"><i class="bi bi-shield-check-fill"></i></div>
          <div class="about-feature-text">
            <strong>Transparent Governance</strong>
            <span>Open and accountable leadership</span>
          </div>
        </div>
        <div class="about-feature">
          <div class="about-feature-icon"><i class="bi bi-people-fill"></i></div>
          <div class="about-feature-text">
            <strong>Youth Empowerment</strong>
            <span>Programs for the community</span>
          </div>
        </div>
        <div class="about-feature">
          <div class="about-feature-icon"><i class="bi bi-calendar-check-fill"></i></div>
          <div class="about-feature-text">
            <strong>Active Programs</strong>
            <span>Regular events and activities</span>
          </div>
        </div>
        <div class="about-feature">
          <div class="about-feature-icon"><i class="bi bi-award-fill"></i></div>
          <div class="about-feature-text">
            <strong>Accomplishments</strong>
            <span>Proven track record of results</span>
          </div>
        </div>
      </div>
      <a href="about.php" class="btn btn-primary" style="margin-top:28px;">
        Learn More <i class="bi bi-arrow-right"></i>
      </a>
    </div>
    <div class="about-image">
      <img src="assets/images/about-strip.png" alt="SK Barangay Esperanza Ilaya" />
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════
     CONTACT STRIP
══════════════════════════════════════ -->
<section class="contact-strip">
  <div class="container">
    <div>
      <span class="section-label">Get in Touch</span>
      <h2 class="section-title">Contact the SK Office</h2>
      <p class="section-desc">Have questions or concerns? Reach out to the SK of Barangay Esperanza Ilaya.</p>
      <div class="contact-info-list">
        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="bi bi-geo-alt-fill"></i></div>
          <div class="contact-info-text">
            <strong>Address</strong>
            <span>Barangay Esperanza Ilaya, Philippines</span>
          </div>
        </div>
        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="bi bi-telephone-fill"></i></div>
          <div class="contact-info-text">
            <strong>Phone</strong>
            <span>(000) 000-0000</span>
          </div>
        </div>
        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="bi bi-envelope-fill"></i></div>
          <div class="contact-info-text">
            <strong>Email</strong>
            <span>sk.esperanzailaya@gmail.com</span>
          </div>
        </div>
        <div class="contact-info-item">
          <div class="contact-info-icon"><i class="bi bi-clock-fill"></i></div>
          <div class="contact-info-text">
            <strong>Office Hours</strong>
            <span>Monday – Friday, 8:00 AM – 5:00 PM</span>
          </div>
        </div>
      </div>
    </div>
    <div class="contact-map-placeholder">
      <i class="bi bi-map" style="font-size:2.5rem;"></i>
      <span>Map / Location Embed</span>
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
        <div class="footer-brand-logos">
          <img src="assets/images/sk-logo.png" alt="SK Logo" class="footer-brand-img" />
          <div class="footer-brand-logo-divider"></div>
          <img src="assets/images/brgy-logo.png" alt="Barangay Logo" class="footer-brand-img" />
        </div>
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