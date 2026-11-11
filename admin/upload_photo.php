<?php
/**
 * admin/upload_photo.php
 * Lets the admin replace the portfolio hero photo.
 * Protected: only accessible after logging in via login.php
 */

session_start();

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once '../db_connect.php';

// ── Allowed image types ───────────────────────────────────────────────────────
const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
const MAX_SIZE_MB   = 5;
const UPLOAD_DIR    = '../images/';

$conn    = get_db_connection();
$message = '';
$msgType = '';

// ── Fetch current photo ───────────────────────────────────────────────────────
$current = 'images/wakoli-picc.jpeg';
try {
    $res     = $conn->query("SELECT setting_value FROM site_settings WHERE setting_key = 'portfolio_photo'");
    $current = $res ? ($res->fetch_assoc()['setting_value'] ?? 'images/wakoli-picc.jpeg') : 'images/wakoli-picc.jpeg';
} catch (RuntimeException $e) {
    // use default
}

// ── Handle upload ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $file     = $_FILES['photo'];
    $maxBytes = MAX_SIZE_MB * 1024 * 1024;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = 'Upload failed. Please try again.';
        $msgType = 'error';

    } elseif ($file['size'] > $maxBytes) {
        $message = 'File is too large. Maximum size is ' . MAX_SIZE_MB . ' MB.';
        $msgType = 'error';

    } elseif (!in_array($file['type'], ALLOWED_TYPES, true)) {
        $message = 'Invalid file type. Please upload a JPG, PNG, WebP, or GIF.';
        $msgType = 'error';

    } else {
        // Verify it's really an image (not a spoofed MIME type)
        $imgInfo = @getimagesize($file['tmp_name']);
        if (!$imgInfo) {
            $message = 'File does not appear to be a valid image.';
            $msgType = 'error';
        } else {
            // Build a safe filename: portfolio-photo-{timestamp}.{ext}
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $newName  = 'portfolio-photo-' . time() . '.' . $ext;
            $destPath = UPLOAD_DIR . $newName;
            $dbPath   = 'images/' . $newName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                // Save new path to DB
                $stmt = $conn->prepare(
                    "INSERT INTO site_settings (setting_key, setting_value)
                     VALUES ('portfolio_photo', ?)
                     ON DUPLICATE KEY UPDATE setting_value = ?"
                );
                $stmt->bind_param('ss', $dbPath, $dbPath);
                $stmt->execute();
                $stmt->close();

                $current = $dbPath;
                $message = 'Portfolio photo updated successfully!';
                $msgType = 'success';
            } else {
                $message = 'Could not save the file. Check folder permissions.';
                $msgType = 'error';
            }
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Update Portfolio Photo | Admin</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:      #f1f5f9;
      --sidebar: #0f172a;
      --accent:  #2563eb;
      --success: #16a34a;
      --danger:  #dc2626;
      --border:  #e2e8f0;
      --white:   #ffffff;
      --text:    #1e293b;
      --muted:   #64748b;
      --radius:  0.85rem;
    }

    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* ── Top bar ── */
    .topbar {
      background: var(--sidebar);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.85rem 2rem;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 12px rgba(0,0,0,0.3);
      flex-wrap: wrap;
      gap: 0.75rem;
    }

    .topbar .brand {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .topbar .brand img { height: 38px; width: auto; object-fit: contain; }
    .topbar .brand-name { font-size: 1rem; font-weight: 700; }
    .topbar .brand-sub  { font-size: 0.75rem; color: #94a3b8; }

    .topbar-right {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      flex-wrap: wrap;
    }

    .topbar-right a {
      text-decoration: none;
      font-size: 0.875rem;
      font-weight: 600;
      padding: 0.4rem 1rem;
      border-radius: 999px;
      transition: background 0.2s;
    }

    .btn-nav     { background: rgba(255,255,255,0.1); color: #e2e8f0; }
    .btn-nav:hover { background: rgba(255,255,255,0.2); }
    .btn-logout  { background: #ef4444; color: #fff; }
    .btn-logout:hover { background: #b91c1c; }

    /* ── Main ── */
    .main {
      flex: 1;
      padding: 2rem;
      max-width: 760px;
      width: 100%;
      margin-inline: auto;
    }

    .page-heading { font-size: 1.75rem; font-weight: 800; margin-bottom: 0.3rem; }
    .page-sub     { color: var(--muted); font-size: 0.9rem; margin-bottom: 2rem; }

    /* ── Alert ── */
    .alert {
      padding: 1rem 1.25rem;
      border-radius: var(--radius);
      font-weight: 600;
      margin-bottom: 1.5rem;
    }
    .alert.success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
    .alert.error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

    /* ── Card ── */
    .card {
      background: var(--white);
      border-radius: 1.25rem;
      border: 1px solid var(--border);
      box-shadow: 0 4px 16px rgba(15,23,42,0.06);
      overflow: hidden;
      margin-bottom: 1.5rem;
    }

    .card-header {
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid var(--border);
      font-weight: 700;
      font-size: 1rem;
      color: var(--text);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .card-body { padding: 1.5rem; }

    /* ── Current photo preview ── */
    .photo-preview {
      width: 100%;
      max-width: 340px;
      aspect-ratio: 4/5;
      object-fit: cover;
      object-position: center top;
      border-radius: 1.5rem;
      border: 3px solid var(--border);
      display: block;
      margin: 0 auto 0.75rem;
    }

    .photo-label {
      text-align: center;
      font-size: 0.8rem;
      color: var(--muted);
    }

    /* ── Upload form ── */
    .upload-area {
      border: 2px dashed #cbd5e1;
      border-radius: 1rem;
      padding: 2.5rem 1.5rem;
      text-align: center;
      cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
      background: #f8fafc;
      position: relative;
    }

    .upload-area:hover,
    .upload-area.dragover {
      border-color: var(--accent);
      background: #eff6ff;
    }

    .upload-area input[type="file"] {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      opacity: 0;
      cursor: pointer;
    }

    .upload-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }

    .upload-area h3 {
      font-size: 1rem;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 0.4rem;
    }

    .upload-area p {
      font-size: 0.85rem;
      color: var(--muted);
    }

    /* Preview of newly selected image */
    #new-preview-wrap { margin-top: 1.25rem; display: none; }
    #new-preview-wrap img {
      width: 100%;
      max-width: 280px;
      aspect-ratio: 4/5;
      object-fit: cover;
      border-radius: 1rem;
      border: 2px solid var(--accent);
      display: block;
      margin: 0 auto 0.5rem;
    }
    #new-preview-wrap p {
      text-align: center;
      font-size: 0.8rem;
      color: var(--accent);
      font-weight: 600;
    }

    .btn-upload {
      width: 100%;
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 999px;
      padding: 0.95rem;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      margin-top: 1.25rem;
      transition: background 0.2s, transform 0.15s;
    }

    .btn-upload:hover { background: #1d4ed8; transform: translateY(-1px); }
    .btn-upload:disabled { background: #94a3b8; cursor: not-allowed; transform: none; }

    .hint {
      text-align: center;
      font-size: 0.8rem;
      color: var(--muted);
      margin-top: 0.75rem;
    }

    /* ── Footer ── */
    .admin-footer {
      text-align: center;
      padding: 1.25rem;
      font-size: 0.8rem;
      color: var(--muted);
      border-top: 1px solid var(--border);
      background: var(--white);
    }

    @media (max-width: 600px) {
      .topbar { padding: 0.75rem 1rem; }
      .main   { padding: 1rem; }
    }
  </style>
</head>
<body>

<!-- Top bar -->
<header class="topbar">
  <div class="brand">
    <img src="../images/logo rmove bg (1).png" alt="Logo" />
    <div>
      <p class="brand-name">Marvels Empire Creations</p>
      <p class="brand-sub">Admin — Update Portfolio Photo</p>
    </div>
  </div>
  <div class="topbar-right">
    <a href="dashboard.php" class="btn-nav">← Messages</a>
    <a href="logout.php"    class="btn-logout">Logout</a>
  </div>
</header>

<!-- Main -->
<main class="main">
  <h1 class="page-heading">Portfolio Photo</h1>
  <p class="page-sub">Upload a new photo to replace the one shown in your portfolio hero section.</p>

  <?php if ($message): ?>
    <div class="alert <?= $msgType ?>"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <!-- Current photo -->
  <div class="card">
    <div class="card-header">🖼️ Current Photo</div>
    <div class="card-body">
      <img
        class="photo-preview"
        src="../<?= htmlspecialchars($current) ?>"
        alt="Current portfolio photo"
      />
      <p class="photo-label"><?= htmlspecialchars($current) ?></p>
    </div>
  </div>

  <!-- Upload new photo -->
  <div class="card">
    <div class="card-header">📤 Upload New Photo</div>
    <div class="card-body">
      <form method="post" enctype="multipart/form-data" id="upload-form">

        <div class="upload-area" id="upload-area">
          <input
            type="file"
            name="photo"
            id="photo-input"
            accept="image/jpeg,image/png,image/webp,image/gif"
            required
          />
          <div class="upload-icon">📷</div>
          <h3>Click to choose a photo</h3>
          <p>or drag and drop it here</p>
          <p style="margin-top:0.5rem;">JPG, PNG, WebP or GIF · Max <?= MAX_SIZE_MB ?>MB</p>
        </div>

        <!-- Preview of selected image -->
        <div id="new-preview-wrap">
          <img id="new-preview" src="" alt="New photo preview" />
          <p>New photo preview — click Upload to save</p>
        </div>

        <button class="btn-upload" type="submit" id="submit-btn" disabled>
          Upload & Set as Portfolio Photo
        </button>
      </form>
      <p class="hint">The photo will appear in the hero section of your portfolio immediately after upload.</p>
    </div>
  </div>
</main>

<footer class="admin-footer">
  &copy; <?= date('Y') ?> Wenyaa Brian Wakoli &mdash; Admin Dashboard
</footer>

<script>
  const input      = document.getElementById('photo-input');
  const area       = document.getElementById('upload-area');
  const previewWrap = document.getElementById('new-preview-wrap');
  const preview    = document.getElementById('new-preview');
  const submitBtn  = document.getElementById('submit-btn');

  // Show preview when file is chosen
  input.addEventListener('change', () => {
    const file = input.files[0];
    if (!file) return;
    const url = URL.createObjectURL(file);
    preview.src = url;
    previewWrap.style.display = 'block';
    submitBtn.disabled = false;
    submitBtn.textContent = `Upload "${file.name}"`;
  });

  // Drag-and-drop highlight
  area.addEventListener('dragover', (e) => { e.preventDefault(); area.classList.add('dragover'); });
  area.addEventListener('dragleave', ()  => area.classList.remove('dragover'));
  area.addEventListener('drop',      ()  => area.classList.remove('dragover'));
</script>

</body>
</html>
