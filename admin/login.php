<?php
/**
 * admin/login.php
 * Simple password-protected login for the admin dashboard.
 */

session_start();

// ── If already logged in, go straight to dashboard ───────────────────────────
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

// ── Admin credentials (change these!) ────────────────────────────────────────
// For better security, move these to a config file outside the web root
// or use PHP's password_hash() to store a hashed password.
define('ADMIN_USERNAME', 'wakoli_admin');
define('ADMIN_PASSWORD', 'Wakoli@2026');   // ← change this

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        session_regenerate_id(true);            // prevent session fixation
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = $username;
        header('Location: dashboard.php');
        exit;
    } else {
        // Vague message on purpose — don't reveal which field is wrong
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Login | Wakoli Portfolio</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
      font-family: 'Segoe UI', system-ui, sans-serif;
    }

    .login-card {
      background: #ffffff;
      border-radius: 1.5rem;
      padding: 2.5rem 2rem;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 30px 80px rgba(0,0,0,0.35);
    }

    .login-card .logo {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 1.75rem;
    }

    .login-card .logo img {
      height: 48px;
      width: auto;
      object-fit: contain;
    }

    .login-card .logo-text h1 {
      font-size: 1.15rem;
      font-weight: 700;
      color: #0f172a;
    }

    .login-card .logo-text p {
      font-size: 0.8rem;
      color: #64748b;
    }

    h2 {
      font-size: 1.5rem;
      color: #0f172a;
      margin-bottom: 0.4rem;
    }

    .subtitle {
      color: #64748b;
      font-size: 0.9rem;
      margin-bottom: 1.75rem;
    }

    .field {
      display: flex;
      flex-direction: column;
      gap: 0.4rem;
      margin-bottom: 1.1rem;
    }

    label {
      font-size: 0.875rem;
      font-weight: 600;
      color: #334155;
    }

    input {
      border: 1px solid #cbd5e1;
      border-radius: 0.75rem;
      padding: 0.85rem 1rem;
      font-size: 1rem;
      background: #f8fafc;
      transition: border-color 0.2s;
      width: 100%;
    }

    input:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
    }

    .error-msg {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fca5a5;
      border-radius: 0.75rem;
      padding: 0.75rem 1rem;
      font-size: 0.9rem;
      margin-bottom: 1.1rem;
    }

    .btn-login {
      width: 100%;
      background: #2563eb;
      color: #fff;
      border: none;
      border-radius: 999px;
      padding: 0.95rem;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: background 0.2s, transform 0.15s;
      margin-top: 0.5rem;
    }

    .btn-login:hover { background: #1d4ed8; transform: translateY(-1px); }

    .back-link {
      display: block;
      text-align: center;
      margin-top: 1.25rem;
      font-size: 0.875rem;
      color: #2563eb;
      text-decoration: none;
    }

    .back-link:hover { text-decoration: underline; }
  </style>
</head>
<body>

<div class="login-card">
  <div class="logo">
    <img src="../images/logo rmove bg (1).png" alt="Marvels Empire Creations logo" />
    <div class="logo-text">
      <h1>Marvels Empire Creations</h1>
      <p>Admin Portal</p>
    </div>
  </div>

  <h2>Welcome back</h2>
  <p class="subtitle">Sign in to view your contact messages.</p>

  <?php if ($error): ?>
    <div class="error-msg" role="alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" action="login.php" novalidate>
    <div class="field">
      <label for="username">Username</label>
      <input
        type="text"
        id="username"
        name="username"
        autocomplete="username"
        required
        placeholder="Enter username"
        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
      />
    </div>

    <div class="field">
      <label for="password">Password</label>
      <input
        type="password"
        id="password"
        name="password"
        autocomplete="current-password"
        required
        placeholder="Enter password"
      />
    </div>

    <button class="btn-login" type="submit">Sign In</button>
  </form>

  <a class="back-link" href="../INDEX.HTML">← Back to Portfolio</a>
</div>

</body>
</html>
