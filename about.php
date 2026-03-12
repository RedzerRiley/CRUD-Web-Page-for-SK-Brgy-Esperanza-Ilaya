<?php
// about.php - SK Barangay Esperanza Ilaya - About Page
require_once 'includes/db.php';

// Fetch SK Officials (assuming an 'officials' table; gracefully fails if absent)
$officials = [];
try {
    $officials = $pdo->query("
        SELECT * FROM officials ORDER BY position_order ASC, full_name ASC
    ")->fetchAll();
} catch (PDOException $e) {}

// Fetch stats
$stats = ['total_posts' => 0, 'projects' => 0, 'events' => 0, 'completed' => 0];
try {
    $stats['total_posts'] = $pdo->query("SELECT COUNT(*) FROM post")->fetchColumn();
    $stats['projects']    = $pdo->query("SELECT COUNT(*) FROM post p JOIN category c ON p.category_id = c.category_id WHERE c.category_name = 'Projects'")->fetchColumn();
    $stats['events']      = $pdo->query("SELECT COUNT(*) FROM post p JOIN category c ON p.category_id = c.category_id WHERE c.category_name = 'Events'")->fetchColumn();
    $stats['completed']   = $pdo->query("SELECT COUNT(*) FROM post WHERE status = 'Completed'")->fetchColumn();
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Learn about the Sangguniang Kabataan of Barangay Esperanza Ilaya — our mission, vision, officials, and programs." />
  <title>About — SK Barangay Esperanza Ilaya</title>
  <link rel="icon" type="image/png" href="/assets/images/sk-logo.png" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <style>
    /* ─── Page Hero ──────────────────────────────────────── */
    .page-hero {
      background: linear-gradient(135deg, var(--primary,#1a56db) 0%, var(--primary-dark,#1e429f) 100%);
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
      max-width: 540px;
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

    /* ─── Generic section spacing ────────────────────────── */
    .about-page-section { padding: 64px 0; }
    .about-page-section:nth-child(even) { background: var(--light-bg, #f9fafb); }

    /* ─── Intro split ────────────────────────────────────── */
    .intro-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 56px;
      align-items: center;
    }
    .intro-image-placeholder {
      background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
      border-radius: 16px;
      min-height: 340px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 12px;
      color: #93c5fd;
      font-size: .9rem;
      border: 2px dashed #bfdbfe;
    }
    .intro-text .section-label {
      display: inline-block;
      font-size: .78rem;
      font-weight: 700;
      letter-spacing: .1em;
      text-transform: uppercase;
      color: var(--primary,#1a56db);
      margin-bottom: 10px;
    }
    .intro-text h2 {
      font-size: clamp(1.5rem, 2.5vw, 2rem);
      font-weight: 800;
      color: var(--text,#111827);
      margin: 0 0 16px;
      line-height: 1.25;
    }
    .intro-text p {
      color: var(--mid-gray,#6b7280);
      line-height: 1.75;
      margin-bottom: 14px;
      font-size: .95rem;
    }

    /* ─── Mission / Vision / Goal cards ─────────────────── */
    .mvg-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
    }
    .mvg-card {
      background: #fff;
      border: 1px solid var(--light-border,#e5e7eb);
      border-radius: 14px;
      padding: 32px 28px;
      text-align: center;
      transition: transform .22s, box-shadow .22s;
    }
    .mvg-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 32px rgba(0,0,0,.08);
    }
    .mvg-icon {
      width: 60px;
      height: 60px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      margin: 0 auto 20px;
    }
    .mvg-icon.blue   { background:#eff6ff; color:#1d4ed8; }
    .mvg-icon.green  { background:#f0fdf4; color:#16a34a; }
    .mvg-icon.orange { background:#fff7ed; color:#ea580c; }
    .mvg-card h3 {
      font-size: 1.05rem;
      font-weight: 700;
      color: var(--text,#111827);
      margin: 0 0 12px;
    }
    .mvg-card p {
      font-size: .875rem;
      color: var(--mid-gray,#6b7280);
      line-height: 1.7;
      margin: 0;
    }

    /* ─── Stats strip ────────────────────────────────────── */
    .stats-strip {
      background: linear-gradient(135deg, var(--primary,#1a56db) 0%, var(--primary-dark,#1e429f) 100%);
      padding: 48px 0;
      color: #fff;
    }
    .stats-strip .stat-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 24px;
      text-align: center;
    }
    .stats-strip .stat-number {
      font-size: 2.5rem;
      font-weight: 800;
      line-height: 1;
      margin-bottom: 6px;
    }
    .stats-strip .stat-label {
      font-size: .875rem;
      opacity: .8;
      letter-spacing: .04em;
    }
    .stats-strip .stat-divider {
      border-left: 1px solid rgba(255,255,255,.2);
    }
    .stats-strip .stat-divider:first-child { border: none; }

    /* ─── Core values ────────────────────────────────────── */
    .values-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      gap: 20px;
    }
    .value-card {
      background: #fff;
      border: 1px solid var(--light-border,#e5e7eb);
      border-radius: 12px;
      padding: 24px 22px;
      display: flex;
      align-items: flex-start;
      gap: 16px;
      transition: transform .2s, box-shadow .2s;
    }
    .value-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 24px rgba(0,0,0,.07);
    }
    .value-icon {
      width: 44px;
      height: 44px;
      min-width: 44px;
      border-radius: 10px;
      background: #eff6ff;
      color: #1d4ed8;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.15rem;
    }
    .value-text strong {
      display: block;
      font-size: .95rem;
      font-weight: 700;
      color: var(--text,#111827);
      margin-bottom: 4px;
    }
    .value-text span {
      font-size: .82rem;
      color: var(--mid-gray,#6b7280);
      line-height: 1.55;
    }

    /* ─── Officials ──────────────────────────────────────── */
    .officials-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 24px;
    }
    .official-card {
      background: #fff;
      border: 1px solid var(--light-border,#e5e7eb);
      border-radius: 14px;
      padding: 28px 20px 22px;
      text-align: center;
      transition: transform .22s, box-shadow .22s;
    }
    .official-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 28px rgba(0,0,0,.09);
    }
    .official-avatar {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: linear-gradient(135deg,#dbeafe,#eff6ff);
      border: 3px solid #bfdbfe;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      color: #93c5fd;
      margin: 0 auto 16px;
      overflow: hidden;
    }
    .official-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .official-name {
      font-size: .95rem;
      font-weight: 700;
      color: var(--text,#111827);
      margin-bottom: 4px;
    }
    .official-position {
      font-size: .78rem;
      font-weight: 600;
      color: var(--primary,#1a56db);
      text-transform: uppercase;
      letter-spacing: .05em;
      background: #eff6ff;
      border-radius: 100px;
      padding: 3px 10px;
      display: inline-block;
      margin-bottom: 10px;
    }
    .official-term {
      font-size: .78rem;
      color: var(--mid-gray,#9ca3af);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
    }

    /* Placeholder officials (shown when DB table is empty) */
    .officials-placeholder-note {
      text-align: center;
      padding: 48px 24px;
      color: var(--mid-gray,#9ca3af);
    }
    .officials-placeholder-note i {
      font-size: 2.8rem;
      display: block;
      margin-bottom: 12px;
      opacity: .4;
    }
    .officials-placeholder-note p { font-size: .9rem; margin: 0; }

    /* ─── Programs / Mandate ─────────────────────────────── */
    .programs-list {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }
    .program-item {
      display: flex;
      align-items: flex-start;
      gap: 14px;
      background: #fff;
      border: 1px solid var(--light-border,#e5e7eb);
      border-radius: 12px;
      padding: 20px;
    }
    .program-num {
      min-width: 36px;
      height: 36px;
      border-radius: 8px;
      background: var(--primary,#1a56db);
      color: #fff;
      font-size: .85rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .program-text strong {
      display: block;
      font-size: .92rem;
      font-weight: 700;
      color: var(--text,#111827);
      margin-bottom: 4px;
    }
    .program-text span {
      font-size: .83rem;
      color: var(--mid-gray,#6b7280);
      line-height: 1.55;
    }

    /* ─── CTA Strip ──────────────────────────────────────── */
    .cta-strip {
      background: linear-gradient(135deg, #1e3a8a 0%, var(--primary,#1a56db) 100%);
      color: #fff;
      padding: 64px 0;
      text-align: center;
    }
    .cta-strip h2 {
      font-size: clamp(1.5rem,3vw,2.1rem);
      font-weight: 800;
      margin: 0 0 12px;
    }
    .cta-strip p {
      opacity: .82;
      margin: 0 auto 28px;
      max-width: 500px;
      font-size: .97rem;
      line-height: 1.65;
    }
    .cta-actions {
      display: flex;
      gap: 14px;
      justify-content: center;
      flex-wrap: wrap;
    }
    .btn-white {
      background: #fff;
      color: var(--primary,#1a56db);
      padding: 12px 28px;
      border-radius: 8px;
      font-weight: 700;
      font-size: .93rem;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 7px;
      transition: opacity .2s;
    }
    .btn-white:hover { opacity: .9; }
    .btn-ghost-white {
      background: transparent;
      color: #fff;
      padding: 12px 28px;
      border-radius: 8px;
      font-weight: 700;
      font-size: .93rem;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 7px;
      border: 2px solid rgba(255,255,255,.45);
      transition: border-color .2s;
    }
    .btn-ghost-white:hover { border-color: #fff; }

    /* ─── Responsive ─────────────────────────────────────── */
    @media (max-width: 900px) {
      .intro-grid  { grid-template-columns: 1fr; gap: 36px; }
      .mvg-grid    { grid-template-columns: 1fr; }
      .programs-list { grid-template-columns: 1fr; }
      .stats-strip .stat-grid { grid-template-columns: repeat(2,1fr); }
    }
    @media (max-width: 600px) {
      .stats-strip .stat-grid { grid-template-columns: 1fr 1fr; }
      .officials-grid { grid-template-columns: repeat(2,1fr); }
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
      <div class="nav-brand-text">
        <span class="title">SK Esperanza Ilaya</span>
        <span class="subtitle">Sangguniang Kabataan</span>
      </div>
    </a>

    <ul class="nav-links" id="navLinks">
      <li><a href="index.php">Home</a></li>
      <li><a href="about.php" class="active">About</a></li>
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
     PAGE HERO
══════════════════════════════════════ -->
<section class="page-hero">
  <div class="container">
    <div class="page-hero-label">
      <i class="bi bi-people-fill"></i> &nbsp;Who We Are
    </div>
    <h1>About the SK</h1>
    <p>Get to know the Sangguniang Kabataan of Barangay Esperanza Ilaya — our story, our people, and our purpose.</p>
    <div class="breadcrumb">
      <a href="index.php"><i class="bi bi-house-fill"></i> Home</a>
      <i class="bi bi-chevron-right"></i>
      <span>About</span>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════
     INTRO — Who We Are
══════════════════════════════════════ -->
<section class="about-page-section">
  <div class="container">
    <div class="intro-grid">
      <div class="intro-image-placeholder">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
          <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
          <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/>
        </svg>
        SK Officials Photo
      </div>
      <div class="intro-text">
        <span class="section-label">Our Organization</span>
        <h2>Sangguniang Kabataan of Barangay Esperanza Ilaya</h2>
        <p>
          The Sangguniang Kabataan (SK) is the official youth legislative body of Barangay Esperanza Ilaya.
          Established under the Local Government Code, the SK is mandated to look after the welfare of
          the youth in our barangay and give voice to their concerns in local governance.
        </p>
        <p>
          We are a team of elected youth officials committed to delivering transparent, accountable, and
          inclusive programs that uplift the lives of young people — from livelihood and education to
          health, sports, and cultural enrichment.
        </p>
        <p>
          Our council is guided by the principle that the youth of today are the leaders of tomorrow,
          and every initiative we undertake is rooted in that belief.
        </p>
        <div style="display:flex; gap:16px; margin-top:24px; flex-wrap:wrap;">
          <a href="projects.php" class="btn btn-primary">
            <i class="bi bi-grid-fill"></i> Our Projects
          </a>
          <a href="contact.php" class="btn btn-outline">
            <i class="bi bi-envelope-fill"></i> Get in Touch
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════
     STATS STRIP
══════════════════════════════════════ -->
<div class="stats-strip">
  <div class="container">
    <div class="stat-grid">
      <div class="stat-divider">
        <div class="stat-number"><?= $stats['total_posts'] ?>+</div>
        <div class="stat-label">Total Posts</div>
      </div>
      <div class="stat-divider">
        <div class="stat-number"><?= $stats['projects'] ?>+</div>
        <div class="stat-label">Projects Launched</div>
      </div>
      <div class="stat-divider">
        <div class="stat-number"><?= $stats['events'] ?>+</div>
        <div class="stat-label">Events Organized</div>
      </div>
      <div class="stat-divider">
        <div class="stat-number"><?= $stats['completed'] ?>+</div>
        <div class="stat-label">Completed Initiatives</div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════
     MISSION / VISION / GOAL
══════════════════════════════════════ -->
<section class="about-page-section">
  <div class="container">
    <div class="section-header" style="text-align:center; margin-bottom:40px;">
      <span class="section-label">Our Direction</span>
      <h2 class="section-title">Mission, Vision &amp; Goal</h2>
    </div>
    <div class="mvg-grid">
      <div class="mvg-card">
        <div class="mvg-icon blue"><i class="bi bi-bullseye"></i></div>
        <h3>Mission</h3>
        <p>
          To empower the youth of Barangay Esperanza Ilaya through inclusive, transparent, and
          participatory programs that address their social, educational, cultural, and economic needs,
          while upholding the values of integrity and public service.
        </p>
      </div>
      <div class="mvg-card">
        <div class="mvg-icon green"><i class="bi bi-eye-fill"></i></div>
        <h3>Vision</h3>
        <p>
          A barangay where every young person is empowered, engaged, and equipped to contribute
          meaningfully to their community — a generation of leaders grounded in integrity,
          compassion, and civic responsibility.
        </p>
      </div>
      <div class="mvg-card">
        <div class="mvg-icon orange"><i class="bi bi-flag-fill"></i></div>
        <h3>Goal</h3>
        <p>
          To deliver impactful and sustainable youth programs, maintain open and accountable
          governance, and foster active youth participation in the development of Barangay
          Esperanza Ilaya.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════
     CORE VALUES
══════════════════════════════════════ -->
<section class="about-page-section">
  <div class="container">
    <div class="section-header" style="text-align:center; margin-bottom:40px;">
      <span class="section-label">What Guides Us</span>
      <h2 class="section-title">Our Core Values</h2>
    </div>
    <div class="values-grid">
      <div class="value-card">
        <div class="value-icon"><i class="bi bi-shield-check-fill"></i></div>
        <div class="value-text">
          <strong>Integrity</strong>
          <span>We act with honesty and accountability in everything we do, upholding the public trust placed in us.</span>
        </div>
      </div>
      <div class="value-card">
        <div class="value-icon"><i class="bi bi-people-fill"></i></div>
        <div class="value-text">
          <strong>Service</strong>
          <span>We are dedicated to placing the needs of the youth and the community above our own interests.</span>
        </div>
      </div>
      <div class="value-card">
        <div class="value-icon"><i class="bi bi-transparency"></i></div>
        <div class="value-text">
          <strong>Transparency</strong>
          <span>We keep our constituents informed through open communication and accessible public records.</span>
        </div>
      </div>
      <div class="value-card">
        <div class="value-icon"><i class="bi bi-lightbulb-fill"></i></div>
        <div class="value-text">
          <strong>Innovation</strong>
          <span>We seek creative and forward-thinking solutions to the challenges facing our youth and community.</span>
        </div>
      </div>
      <div class="value-card">
        <div class="value-icon"><i class="bi bi-heart-fill"></i></div>
        <div class="value-text">
          <strong>Inclusivity</strong>
          <span>We champion programs that serve all youth regardless of background, gender, or circumstance.</span>
        </div>
      </div>
      <div class="value-card">
        <div class="value-icon"><i class="bi bi-award-fill"></i></div>
        <div class="value-text">
          <strong>Excellence</strong>
          <span>We strive for quality and impact in every project, event, and program we undertake.</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════
     SK OFFICIALS
══════════════════════════════════════ -->
<section class="about-page-section">
  <div class="container">
    <div class="section-header" style="text-align:center; margin-bottom:40px;">
      <span class="section-label">Leadership</span>
      <h2 class="section-title">SK Officials</h2>
    </div>

    <?php if (!empty($officials)): ?>
      <div class="officials-grid">
        <?php foreach ($officials as $official): ?>
          <div class="official-card">
            <div class="official-avatar">
              <?php if (!empty($official['photo'])): ?>
                <img src="<?= htmlspecialchars($official['photo']) ?>"
                     alt="<?= htmlspecialchars($official['full_name']) ?>" />
              <?php else: ?>
                <i class="bi bi-person-fill"></i>
              <?php endif; ?>
            </div>
            <div class="official-name"><?= htmlspecialchars($official['full_name']) ?></div>
            <div class="official-position"><?= htmlspecialchars($official['position'] ?? 'SK Official') ?></div>
            <?php if (!empty($official['term'])): ?>
              <div class="official-term">
                <i class="bi bi-calendar3"></i>
                <?= htmlspecialchars($official['term']) ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

    <?php else: ?>
      <!-- Static placeholder officials when DB table is absent -->
      <div class="officials-grid">
        <?php
        $placeholder_officials = [
          ['name' => 'SK Chairperson',        'pos' => 'Chairperson'],
          ['name' => 'Kagawad 1',              'pos' => 'Kagawad'],
          ['name' => 'Kagawad 2',              'pos' => 'Kagawad'],
          ['name' => 'Kagawad 3',              'pos' => 'Kagawad'],
          ['name' => 'Kagawad 4',              'pos' => 'Kagawad'],
          ['name' => 'Kagawad 5',              'pos' => 'Kagawad'],
          ['name' => 'Kagawad 6',              'pos' => 'Kagawad'],
          ['name' => 'SK Secretary',           'pos' => 'Secretary'],
          ['name' => 'SK Treasurer',           'pos' => 'Treasurer'],
        ];
        foreach ($placeholder_officials as $p): ?>
          <div class="official-card">
            <div class="official-avatar">
              <i class="bi bi-person-fill"></i>
            </div>
            <div class="official-name"><?= $p['name'] ?></div>
            <div class="official-position"><?= $p['pos'] ?></div>
            <div class="official-term">
              <i class="bi bi-calendar3"></i> Current Term
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <p style="text-align:center; color:var(--mid-gray,#9ca3af); font-size:.82rem; margin-top:20px;">
        <i class="bi bi-info-circle"></i>
        Official names will appear here once the <code>officials</code> table is populated.
      </p>
    <?php endif; ?>
  </div>
</section>

<!-- ══════════════════════════════════════
     PROGRAMS & MANDATE
══════════════════════════════════════ -->
<section class="about-page-section">
  <div class="container">
    <div class="section-header" style="text-align:center; margin-bottom:40px;">
      <span class="section-label">What We Do</span>
      <h2 class="section-title">Programs &amp; Mandate</h2>
    </div>
    <div class="programs-list">
      <div class="program-item">
        <div class="program-num">01</div>
        <div class="program-text">
          <strong>Education &amp; Scholarship Support</strong>
          <span>Facilitating access to scholarships, tutorials, and learning materials for the youth of the barangay.</span>
        </div>
      </div>
      <div class="program-item">
        <div class="program-num">02</div>
        <div class="program-text">
          <strong>Health &amp; Wellness Programs</strong>
          <span>Organizing medical missions, sports activities, and mental health awareness campaigns for the youth.</span>
        </div>
      </div>
      <div class="program-item">
        <div class="program-num">03</div>
        <div class="program-text">
          <strong>Livelihood &amp; Skills Training</strong>
          <span>Equipping young residents with practical skills and livelihood opportunities to support their families.</span>
        </div>
      </div>
      <div class="program-item">
        <div class="program-num">04</div>
        <div class="program-text">
          <strong>Environmental Stewardship</strong>
          <span>Leading clean-up drives, tree-planting activities, and environmental advocacy in the community.</span>
        </div>
      </div>
      <div class="program-item">
        <div class="program-num">05</div>
        <div class="program-text">
          <strong>Cultural &amp; Arts Promotion</strong>
          <span>Celebrating local culture, history, and arts through festivals, exhibits, and performances.</span>
        </div>
      </div>
      <div class="program-item">
        <div class="program-num">06</div>
        <div class="program-text">
          <strong>Civic Participation &amp; Governance</strong>
          <span>Encouraging youth engagement in local legislation, community planning, and barangay affairs.</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════
     CTA STRIP
══════════════════════════════════════ -->
<section class="cta-strip">
  <div class="container">
    <h2>Want to Get Involved?</h2>
    <p>
      Join us in building a stronger, more connected community for the youth of
      Barangay Esperanza Ilaya. Reach out to the SK office today.
    </p>
    <div class="cta-actions">
      <a href="contact.php" class="btn-white">
        <i class="bi bi-envelope-fill"></i> Contact Us
      </a>
      <a href="projects.php" class="btn-ghost-white">
        <i class="bi bi-grid-fill"></i> View Projects
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
          <li><a href="about.php">SK Officials</a></li>
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