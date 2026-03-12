<?php
// admin/index.php - Admin Login Page
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Regenerate session ID for security
                session_regenerate_id(true);

                $_SESSION['admin_id']       = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name']     = $admin['full_name'];
                $_SESSION['admin_position'] = $admin['sk_position'];
                $_SESSION['is_super_admin'] = $admin['is_super_admin'];

                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'A database error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login — SK Barangay Esperanza Ilaya</title>
  <link rel="icon" type="image/png" href="/assets/images/sk-logo.png" />
  <link rel="stylesheet" href="../assets/css/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <style>
    body {
      min-height: 100vh;
      background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 60%, var(--primary-light) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      position: relative;
      overflow: hidden;
    }

    body::before {
      content: '';
      position: absolute;
      inset: 0;
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.02'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    body::after {
      content: '';
      position: absolute;
      right: -120px;
      top: -120px;
      width: 500px;
      height: 500px;
      border-radius: 50%;
      border: 80px solid rgba(200,150,12,0.06);
    }

    .login-wrapper {
      width: 100%;
      max-width: 440px;
      position: relative;
      z-index: 1;
    }

    /* Brand header above card */
    .login-brand {
      text-align: center;
      margin-bottom: 28px;
    }

    .login-brand img {
      width: 72px;
      height: 72px;
      object-fit: contain;
      margin: 0 auto 14px;
      display: block;
      filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
    }

    .login-brand-name {
      font-family: var(--font-display);
      font-size: 1.15rem;
      font-weight: 700;
      color: var(--white);
      margin-bottom: 4px;
    }

    .login-brand-sub {
      font-size: 0.72rem;
      color: var(--accent-light);
      letter-spacing: 1.5px;
      text-transform: uppercase;
      font-weight: 500;
    }

    /* Login Card */
    .login-card {
      background: var(--white);
      border-radius: 14px;
      padding: 40px 36px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.25);
    }

    .login-card-header {
      margin-bottom: 28px;
    }

    .login-card-header h2 {
      font-family: var(--font-display);
      font-size: 1.4rem;
      font-weight: 700;
      color: var(--primary);
      margin-bottom: 4px;
    }

    .login-card-header p {
      font-size: 0.875rem;
      color: var(--text-light);
    }

    /* Form */
    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      display: block;
      font-size: 0.8rem;
      font-weight: 700;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      color: var(--text-light);
      margin-bottom: 8px;
    }

    .form-input-wrapper {
      position: relative;
    }

    .form-input-icon {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--mid-gray);
      font-size: 1rem;
      pointer-events: none;
    }

    .form-input {
      width: 100%;
      padding: 12px 14px 12px 42px;
      border: 1.5px solid var(--light-gray);
      border-radius: 7px;
      font-family: var(--font-body);
      font-size: 0.95rem;
      color: var(--text);
      background: var(--off-white);
      transition: all var(--transition);
      outline: none;
    }

    .form-input:focus {
      border-color: var(--primary);
      background: var(--white);
      box-shadow: 0 0 0 3px rgba(0,51,102,0.08);
    }

    .form-input::placeholder { color: var(--mid-gray); }

    /* Password toggle */
    .password-toggle {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--mid-gray);
      cursor: pointer;
      font-size: 1rem;
      padding: 0;
      transition: color var(--transition);
    }

    .password-toggle:hover { color: var(--primary); }

    /* Error alert */
    .alert-error {
      background: #FEF2F2;
      border: 1px solid #FECACA;
      border-left: 4px solid var(--danger);
      border-radius: 7px;
      padding: 12px 16px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 0.875rem;
      color: #991B1B;
    }

    .alert-error i { font-size: 1rem; color: var(--danger); flex-shrink: 0; }

    /* Submit button */
    .btn-login {
      width: 100%;
      padding: 13px;
      background: var(--primary);
      color: var(--white);
      border: none;
      border-radius: 7px;
      font-family: var(--font-body);
      font-size: 0.95rem;
      font-weight: 600;
      letter-spacing: 0.3px;
      cursor: pointer;
      transition: all var(--transition);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-top: 8px;
    }

    .btn-login:hover {
      background: var(--primary-dark);
      transform: translateY(-1px);
      box-shadow: 0 4px 16px rgba(0,51,102,0.3);
    }

    .btn-login:active { transform: translateY(0); }

    /* Divider */
    .login-divider {
      border: none;
      border-top: 1px solid var(--light-gray);
      margin: 24px 0 20px;
    }

    .login-back {
      text-align: center;
      font-size: 0.83rem;
      color: var(--text-light);
    }

    .login-back a {
      color: var(--primary);
      font-weight: 600;
      transition: color var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }

    .login-back a:hover { color: var(--accent-dark); }

    /* Security badge */
    .login-security {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      margin-top: 20px;
      font-size: 0.75rem;
      color: rgba(255,255,255,0.45);
    }

    .login-security i { color: var(--accent-light); }
  </style>
</head>
<body>

<div class="login-wrapper">

  <!-- Brand -->
  <div class="login-brand">
    <img src="../assets/images/sk-logo.png" alt="SK Logo" />
    <div class="login-brand-name">SK Barangay Esperanza Ilaya</div>
    <div class="login-brand-sub">Admin Portal</div>
  </div>

  <!-- Card -->
  <div class="login-card">
    <div class="login-card-header">
      <h2>Welcome Back</h2>
      <p>Sign in to access the admin dashboard.</p>
    </div>

    <?php if ($error): ?>
      <div class="alert-error">
        <i class="bi bi-exclamation-circle-fill"></i>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="index.php">
      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <div class="form-input-wrapper">
          <i class="bi bi-person-fill form-input-icon"></i>
          <input
            type="text"
            id="username"
            name="username"
            class="form-input"
            placeholder="Enter your username"
            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
            autocomplete="username"
            required
          />
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="form-input-wrapper">
          <i class="bi bi-lock-fill form-input-icon"></i>
          <input
            type="password"
            id="password"
            name="password"
            class="form-input"
            placeholder="Enter your password"
            autocomplete="current-password"
            required
          />
          <button type="button" class="password-toggle" id="passwordToggle">
            <i class="bi bi-eye-fill" id="passwordToggleIcon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-login">
        <i class="bi bi-box-arrow-in-right"></i>
        Sign In
      </button>
    </form>

    <hr class="login-divider" />

    <div class="login-back">
      <a href="../index.php">
        <i class="bi bi-arrow-left"></i> Back to Public Site
      </a>
    </div>
  </div>

  <div class="login-security">
    <i class="bi bi-shield-lock-fill"></i>
    Restricted access — authorized personnel only
  </div>

</div>

<script>
  // Password show/hide toggle
  const toggle = document.getElementById('passwordToggle');
  const input  = document.getElementById('password');
  const icon   = document.getElementById('passwordToggleIcon');

  toggle.addEventListener('click', () => {
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    icon.className = isPassword ? 'bi bi-eye-slash-fill' : 'bi bi-eye-fill';
  });
</script>

</body>
</html>