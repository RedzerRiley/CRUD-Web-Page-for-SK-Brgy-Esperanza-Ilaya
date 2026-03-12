<?php
// contact.php - SK Barangay Esperanza Ilaya - Contact Page
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Contact the Sangguniang Kabataan of Barangay Esperanza Ilaya." />
  <title>Contact Us — SK Barangay Esperanza Ilaya</title>
  <link rel="icon" type="image/png" href="/assets/images/sk-logo.png" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <style>
    /* ── CONTACT PAGE SPECIFIC ── */
    .contact-hero {
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 60%, var(--primary-light) 100%);
      padding: 56px 0 48px;
      position: relative;
      overflow: hidden;
    }

    .contact-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .contact-hero .container {
      position: relative;
      z-index: 1;
      text-align: center;
    }

    .contact-hero .section-label { color: var(--accent-light); }
    .contact-hero .section-label::before { display: none; }

    .contact-hero-title {
      font-family: var(--font-display);
      font-size: clamp(1.75rem, 4vw, 2.5rem);
      font-weight: 700;
      color: var(--white);
      margin-bottom: 12px;
    }

    .contact-hero-desc {
      font-size: 1rem;
      color: rgba(255,255,255,0.7);
      max-width: 520px;
      margin: 0 auto;
      line-height: 1.7;
    }

    /* ── MAIN CONTACT SECTION ── */
    .contact-main {
      padding: var(--section-py) 0;
      background: var(--off-white);
    }

    .contact-layout {
      display: grid;
      grid-template-columns: 1fr 1.4fr;
      gap: 40px;
      align-items: start;
    }

    /* ── INFO CARDS ── */
    .contact-info-cards {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .contact-info-card {
      background: var(--white);
      border: 1px solid var(--light-gray);
      border-radius: 10px;
      padding: 22px 24px;
      display: flex;
      gap: 18px;
      align-items: flex-start;
      box-shadow: var(--shadow-sm);
      transition: all var(--transition);
    }

    .contact-info-card:hover {
      box-shadow: var(--shadow-md);
      transform: translateY(-2px);
      border-color: rgba(0,51,102,0.15);
    }

    .contact-info-card-icon {
      width: 48px;
      height: 48px;
      background: var(--primary);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--white);
      font-size: 1.2rem;
      flex-shrink: 0;
    }

    .contact-info-card-body strong {
      display: block;
      font-size: 0.72rem;
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: var(--mid-gray);
      margin-bottom: 4px;
    }

    .contact-info-card-body span,
    .contact-info-card-body a {
      font-size: 0.95rem;
      color: var(--text);
      font-weight: 500;
      line-height: 1.5;
    }

    .contact-info-card-body a {
      color: var(--primary);
      transition: color var(--transition);
    }

    .contact-info-card-body a:hover { color: var(--accent-dark); }

    /* ── OFFICE HOURS ── */
    .office-hours-card {
      background: var(--primary);
      border-radius: 10px;
      padding: 24px;
      margin-top: 4px;
    }

    .office-hours-title {
      font-family: var(--font-display);
      font-size: 1rem;
      font-weight: 600;
      color: var(--white);
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .office-hours-title i { color: var(--accent-light); }

    .office-hours-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .office-hours-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.875rem;
      padding-bottom: 10px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    .office-hours-row:last-child {
      border-bottom: none;
      padding-bottom: 0;
    }

    .office-hours-row .day { color: rgba(255,255,255,0.65); }

    .office-hours-row .time {
      color: var(--accent-light);
      font-weight: 600;
    }

    .office-hours-row .closed {
      color: rgba(255,255,255,0.35);
      font-style: italic;
    }

    /* ── MAP ── */
    .contact-map-section {
      background: var(--white);
      border: 1px solid var(--light-gray);
      border-radius: 10px;
      overflow: hidden;
      box-shadow: var(--shadow-sm);
    }

    .contact-map-header {
      padding: 20px 24px;
      border-bottom: 1px solid var(--light-gray);
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .contact-map-header i {
      color: var(--primary);
      font-size: 1.2rem;
    }

    .contact-map-header h3 {
      font-family: var(--font-display);
      font-size: 1rem;
      font-weight: 600;
      color: var(--text);
    }

    .contact-map-embed {
      background: var(--light-gray);
      height: 320px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: var(--mid-gray);
      gap: 12px;
      font-size: 0.875rem;
    }

    .contact-map-embed i { font-size: 2.5rem; }

    .contact-map-embed p { max-width: 280px; text-align: center; line-height: 1.6; }

    .contact-map-footer {
      padding: 16px 24px;
      border-top: 1px solid var(--light-gray);
      background: var(--off-white);
    }

    .contact-map-footer a {
      font-size: 0.875rem;
      font-weight: 600;
      color: var(--primary);
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: gap var(--transition), color var(--transition);
    }

    .contact-map-footer a:hover { gap: 10px; color: var(--accent-dark); }

    /* ── SK OFFICIALS STRIP ── */
    .officials-strip {
      padding: var(--section-py) 0;
      background: var(--white);
      border-top: 1px solid var(--light-gray);
    }

    .officials-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 24px;
      margin-top: 40px;
    }

    .official-card {
      background: var(--off-white);
      border: 1px solid var(--light-gray);
      border-radius: 10px;
      padding: 24px 20px;
      text-align: center;
      transition: all var(--transition);
      box-shadow: var(--shadow-sm);
    }

    .official-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg);
      border-color: rgba(0,51,102,0.15);
    }

    .official-avatar {
      width: 64px;
      height: 64px;
      background: var(--primary);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 14px;
      color: var(--white);
      font-family: var(--font-display);
      font-size: 1.4rem;
      font-weight: 700;
    }

    .official-name {
      font-family: var(--font-display);
      font-size: 0.95rem;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 4px;
    }

    .official-position {
      font-size: 0.75rem;
      color: var(--primary);
      font-weight: 600;
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }

    /* ── RESPONSIVE ── */
    @media (max-width: 900px) {
      .contact-layout { grid-template-columns: 1fr; }
      .officials-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 480px) {
      .officials-grid { grid-template-columns: 1fr 1fr; }
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
      <li><a href="announcements.php">Announcements</a></li>
      <li><a href="reports.php">Reports</a></li>
      <li><a href="contact.php" class="active">Contact</a></li>
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
      <li class="current">Contact</li>
    </ul>
  </div>
</div>

<!-- ══════════════════════════════════════
     CONTACT HERO
══════════════════════════════════════ -->
<section class="contact-hero">
  <div class="container">
    <span class="section-label" style="padding-left:0;">Get in Touch</span>
    <h1 class="contact-hero-title">Contact the SK Office</h1>
    <p class="contact-hero-desc">
      Have questions, concerns, or suggestions? We'd love to hear from the
      youth and residents of Barangay Esperanza Ilaya.
    </p>
  </div>
</section>

<!-- ══════════════════════════════════════
     MAIN CONTACT SECTION
══════════════════════════════════════ -->
<section class="contact-main">
  <div class="container">
    <div class="contact-layout">

      <!-- LEFT: Contact Info -->
      <div>
        <div class="section-header" style="margin-bottom:28px;">
          <span class="section-label">Reach Us</span>
          <h2 class="section-title">Contact Information</h2>
          <p class="section-desc">You can reach the SK office through any of the following:</p>
        </div>

        <div class="contact-info-cards">
          <div class="contact-info-card">
            <div class="contact-info-card-icon">
              <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div class="contact-info-card-body">
              <strong>Address</strong>
              <span>Barangay Esperanza Ilaya,<br>Philippines</span>
            </div>
          </div>

          <div class="contact-info-card">
            <div class="contact-info-card-icon">
              <i class="bi bi-telephone-fill"></i>
            </div>
            <div class="contact-info-card-body">
              <strong>Phone</strong>
              <a href="tel:0000000000">(000) 000-0000</a>
            </div>
          </div>

          <div class="contact-info-card">
            <div class="contact-info-card-icon">
              <i class="bi bi-envelope-fill"></i>
            </div>
            <div class="contact-info-card-body">
              <strong>Email</strong>
              <a href="mailto:sk.esperanzailaya@gmail.com">sk.esperanzailaya@gmail.com</a>
            </div>
          </div>

          <div class="contact-info-card">
            <div class="contact-info-card-icon">
              <i class="bi bi-facebook"></i>
            </div>
            <div class="contact-info-card-body">
              <strong>Facebook Page</strong>
              <a href="#" target="_blank">SK Barangay Esperanza Ilaya</a>
            </div>
          </div>
        </div>

        <!-- Office Hours -->
        <div class="office-hours-card" style="margin-top:20px;">
          <div class="office-hours-title">
            <i class="bi bi-clock-fill"></i>
            Office Hours
          </div>
          <div class="office-hours-list">
            <div class="office-hours-row">
              <span class="day">Monday – Friday</span>
              <span class="time">8:00 AM – 5:00 PM</span>
            </div>
            <div class="office-hours-row">
              <span class="day">Saturday</span>
              <span class="time">8:00 AM – 12:00 PM</span>
            </div>
            <div class="office-hours-row">
              <span class="day">Sunday</span>
              <span class="closed">Closed</span>
            </div>
            <div class="office-hours-row">
              <span class="day">Public Holidays</span>
              <span class="closed">Closed</span>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT: Map -->
      <div>
        <div class="section-header" style="margin-bottom:28px;">
          <span class="section-label">Our Location</span>
          <h2 class="section-title">Find Us</h2>
          <p class="section-desc">Visit us at the SK office in Barangay Esperanza Ilaya.</p>
        </div>

        <div class="contact-map-section">
          <div class="contact-map-header">
            <i class="bi bi-pin-map-fill"></i>
            <h3>Barangay Esperanza Ilaya</h3>
          </div>

          <!-- Replace this div with an actual Google Maps embed iframe -->
          <div class="contact-map-embed">
            <i class="bi bi-map"></i>
            <p>
              Replace this with a Google Maps embed.<br>
              Go to Google Maps → Share → Embed a map → copy the iframe.
            </p>
          </div>
          <!-- Example embed:
          <iframe
            src="https://www.google.com/maps/embed?pb=YOUR_EMBED_URL"
            width="100%" height="320" style="border:0;" allowfullscreen=""
            loading="lazy" referrerpolicy="no-referrer-when-downgrade">
          </iframe>
          -->

          <div class="contact-map-footer">
            <a href="https://maps.google.com" target="_blank">
              <i class="bi bi-box-arrow-up-right"></i>
              Open in Google Maps
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ══════════════════════════════════════
     SK OFFICIALS
══════════════════════════════════════ -->
<section class="officials-strip">
  <div class="container">
    <div class="section-header centered">
      <span class="section-label">The Team</span>
      <h2 class="section-title">SK Officials</h2>
      <p class="section-desc">Meet the elected officials of the Sangguniang Kabataan of Barangay Esperanza Ilaya.</p>
    </div>

    <div class="officials-grid">
      <?php
      // SK officials — update names and positions as needed
      $officials = [
        ['name' => 'SK Chairperson',    'position' => 'Chairperson',         'initials' => 'CH'],
        ['name' => 'Kagawad 1',         'position' => 'Kagawad',             'initials' => 'K1'],
        ['name' => 'Kagawad 2',         'position' => 'Kagawad',             'initials' => 'K2'],
        ['name' => 'Kagawad 3',         'position' => 'Kagawad',             'initials' => 'K3'],
        ['name' => 'Kagawad 4',         'position' => 'Kagawad',             'initials' => 'K4'],
        ['name' => 'Kagawad 5',         'position' => 'Kagawad',             'initials' => 'K5'],
        ['name' => 'Kagawad 6',         'position' => 'Kagawad',             'initials' => 'K6'],
        ['name' => 'SK Secretary',      'position' => 'Secretary',           'initials' => 'SE'],
      ];
      foreach ($officials as $official): ?>
        <div class="official-card">
          <div class="official-avatar"><?= $official['initials'] ?></div>
          <div class="official-name"><?= htmlspecialchars($official['name']) ?></div>
          <div class="official-position"><?= htmlspecialchars($official['position']) ?></div>
        </div>
      <?php endforeach; ?>
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