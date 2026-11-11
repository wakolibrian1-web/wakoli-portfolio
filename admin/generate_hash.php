<?php
/**
 * generate_hash.php
 * Run this ONCE in your browser via XAMPP: http://localhost/wakoli%20portfolio/admin/generate_hash.php
 * Copy the hash it outputs, paste it into login.php as ADMIN_PASSWORD_HASH, then DELETE this file.
 */
$password = 'Wakoli@2026'; // ← change to your desired password before running
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Hash Generator</title>
<style>
  body { font-family: monospace; padding: 2rem; background: #0f172a; color: #fff; }
  .box { background: #1e293b; padding: 1.5rem; border-radius: 1rem; margin-top: 1rem; word-break: break-all; }
  .label { color: #94a3b8; font-size: 0.85rem; margin-bottom: 0.5rem; }
  .hash { color: #4ade80; font-size: 1rem; }
  .warn { background: #7f1d1d; color: #fca5a5; padding: 1rem; border-radius: 0.5rem; margin-top: 1.5rem; }
</style>
</head>
<body>
  <h2>🔐 Bcrypt Hash Generator</h2>
  <div class="box">
    <p class="label">Password entered:</p>
    <p><?= htmlspecialchars($password) ?></p>
  </div>
  <div class="box">
    <p class="label">Generated hash — copy this into login.php as ADMIN_PASSWORD_HASH:</p>
    <p class="hash"><?= htmlspecialchars($hash) ?></p>
  </div>
  <div class="warn">
    ⚠️ <strong>DELETE this file immediately after copying the hash.</strong>
    It should never be accessible on a live server.
  </div>
</body>
</html>
