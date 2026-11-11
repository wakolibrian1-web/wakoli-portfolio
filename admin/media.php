<?php
/**
 * admin/media.php
 * Unified media manager — update every photo, skill image,
 * project image, and CV file without touching any code.
 */
session_start();
if (empty($_SESSION['admin_logged_in'])) { header('Location: login.php'); exit; }

require_once '../db_connect.php';

// ── Config ────────────────────────────────────────────────────────────────────
define('IMG_TYPES',  ['image/jpeg','image/png','image/webp','image/gif']);
define('CV_TYPES',   ['application/pdf']);
define('MAX_IMG_MB', 5);
define('MAX_CV_MB',  10);
define('IMG_DIR',    '../images/');
define('CV_DIR',     '../cv/');

// ── All manageable assets ─────────────────────────────────────────────────────
$assets = [
  'hero' => [
    'label' => 'Hero Section',
    'icon'  => '🏠',
    'items' => [
      'portfolio_photo' => [
        'label'   => 'Your Profile Photo',
        'hint'    => 'Shown as the main photo in the hero section.',
        'type'    => 'image',
        'default' => 'images/wakoli-picc.jpeg',
        'prefix'  => 'portfolio-photo',
        'ratio'   => '4/5',
      ],
    ],
  ],
  'about' => [
    'label' => 'About Section',
    'icon'  => '👤',
    'items' => [
      'about_photo' => [
        'label'   => '"What I Bring" Card Photo',
        'hint'    => 'Shown in the About section right-side card.',
        'type'    => 'image',
        'default' => 'images/WAA.jpg',
        'prefix'  => 'about-photo',
        'ratio'   => '4/5',
      ],
    ],
  ],
  'skills' => [
    'label' => 'Skills Section',
    'icon'  => '🛠️',
    'items' => [
      'skill_img_frontend' => [
        'label'   => 'Frontend Development Image',
        'hint'    => 'Card image for the Frontend Development skill.',
        'type'    => 'image',
        'default' => 'images/twwwo.jpg',
        'prefix'  => 'skill-frontend',
        'ratio'   => '16/9',
      ],
      'skill_img_javascript' => [
        'label'   => 'JavaScript Image',
        'hint'    => 'Card image for the JavaScript skill.',
        'type'    => 'image',
        'default' => 'images/js.jpg',
        'prefix'  => 'skill-javascript',
        'ratio'   => '16/9',
      ],
      'skill_img_design' => [
        'label'   => 'Design & UX Image',
        'hint'    => 'Card image for the Design & UX skill.',
        'type'    => 'image',
        'default' => 'images/ui and ux.jpg',
        'prefix'  => 'skill-design',
        'ratio'   => '16/9',
      ],
      'skill_img_portfolio' => [
        'label'   => 'Portfolio Presentation Image',
        'hint'    => 'Card image for the Portfolio Presentation skill.',
        'type'    => 'image',
        'default' => 'images/ONE.jpg',
        'prefix'  => 'skill-portfolio',
        'ratio'   => '16/9',
      ],
    ],
  ],
  'projects' => [
    'label' => 'Projects Section',
    'icon'  => '💼',
    'items' => [
      'project_img_1' => [
        'label'   => 'Project 1 — Portfolio Website',
        'hint'    => 'Thumbnail for "Professional Portfolio Website".',
        'type'    => 'image',
        'default' => 'images/focus (1).jpg',
        'prefix'  => 'project-1',
        'ratio'   => '16/10',
      ],
      'project_img_2' => [
        'label'   => 'Project 2 — Landing Page Redesign',
        'hint'    => 'Thumbnail for "Landing Page Redesign".',
        'type'    => 'image',
        'default' => 'images/web.png',
        'prefix'  => 'project-2',
        'ratio'   => '16/10',
      ],
      'project_img_3' => [
        'label'   => 'Project 3 — Responsive Web App',
        'hint'    => 'Thumbnail for "Responsive Web App Interface".',
        'type'    => 'image',
        'default' => 'images/dashbord.jpg',
        'prefix'  => 'project-3',
        'ratio'   => '16/10',
      ],
    ],
  ],
  'cv' => [
    'label' => 'CV / Resume',
    'icon'  => '📄',
    'items' => [
      'cv_file' => [
        'label'   => 'CV / Resume File (PDF)',
        'hint'    => 'The file visitors download when they click "Download CV".',
        'type'    => 'cv',
        'default' => 'cv/Wenyaa-Brian-Wakoli-CV.pdf',
        'prefix'  => 'cv',
        'ratio'   => null,
      ],
    ],
  ],
];

// ── Load all current values from DB ──────────────────────────────────────────
$settings = [];
try {
    $conn = get_db_connection();
    $res  = $conn->query("SELECT setting_key, setting_value FROM site_settings");
    while ($row = $res->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (RuntimeException $e) {
    // DB unavailable — settings will show defaults; uploads will also fail gracefully
}

// ── Handle upload POST ────────────────────────────────────────────────────────
$alerts = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_FILES as $fieldName => $file) {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) continue;

        // Find matching asset config
        $assetCfg = null;
        foreach ($assets as $group) {
            if (isset($group['items'][$fieldName])) {
                $assetCfg = $group['items'][$fieldName];
                break;
            }
        }
        if (!$assetCfg) continue;

        $isCV     = ($assetCfg['type'] === 'cv');
        $allowed  = $isCV ? CV_TYPES  : IMG_TYPES;
        $maxMB    = $isCV ? MAX_CV_MB : MAX_IMG_MB;
        $maxBytes = $maxMB * 1024 * 1024;
        $dir      = $isCV ? CV_DIR    : IMG_DIR;

        // Validate
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $alerts[$fieldName] = ['type' => 'error', 'msg' => "Upload failed for \"{$assetCfg['label']}\"." ];
            continue;
        }
        if ($file['size'] > $maxBytes) {
            $alerts[$fieldName] = ['type' => 'error', 'msg' => "\"{$assetCfg['label']}\": file exceeds {$maxMB}MB limit." ];
            continue;
        }
        if (!in_array($file['type'], $allowed, true)) {
            $t = $isCV ? 'PDF' : 'JPG, PNG, WebP, or GIF';
            $alerts[$fieldName] = ['type' => 'error', 'msg' => "\"{$assetCfg['label']}\": must be {$t}." ];
            continue;
        }
        if (!$isCV && !@getimagesize($file['tmp_name'])) {
            $alerts[$fieldName] = ['type' => 'error', 'msg' => "\"{$assetCfg['label']}\": not a valid image." ];
            continue;
        }

        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newName = $assetCfg['prefix'] . '-' . time() . '.' . $ext;
        $dest    = $dir . $newName;
        $dbPath  = ($isCV ? 'cv/' : 'images/') . $newName;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            try {
                $dbConn = get_db_connection();
                $stmt   = $dbConn->prepare(
                    "INSERT INTO site_settings (setting_key, setting_value)
                     VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?"
                );
                $stmt->bind_param('sss', $fieldName, $dbPath, $dbPath);
                $stmt->execute();
                $stmt->close();
                $dbConn->close();
            } catch (RuntimeException $e) {
                $alerts[$fieldName] = ['type' => 'error', 'msg' => "File saved but database update failed. Try again." ];
                continue;
            }
            $settings[$fieldName] = $dbPath;
            $alerts[$fieldName]   = ['type' => 'success', 'msg' => "✅ \"{$assetCfg['label']}\" updated successfully!" ];
        } else {
            $alerts[$fieldName] = ['type' => 'error', 'msg' => "Could not save \"{$assetCfg['label']}\". Check folder permissions." ];
        }
    }
}

// Active tab
$activeTab = $_GET['tab'] ?? array_key_first($assets);
if (!array_key_exists($activeTab, $assets)) $activeTab = array_key_first($assets);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Media Manager | Admin</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg:      #f1f5f9;
      --dark:    #0f172a;
      --accent:  #2563eb;
      --green:   #16a34a;
      --red:     #dc2626;
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

    /* ── Topbar ───────────────────────── */
    .topbar {
      background: var(--dark);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.85rem 2rem;
      position: sticky;
      top: 0;
      z-index: 200;
      box-shadow: 0 2px 12px rgba(0,0,0,0.3);
      flex-wrap: wrap;
      gap: 0.75rem;
    }
    .topbar .brand { display: flex; align-items: center; gap: 0.75rem; }
    .topbar .brand img { height: 36px; object-fit: contain; }
    .brand-name { font-size: 1rem; font-weight: 700; }
    .brand-sub  { font-size: 0.75rem; color: #94a3b8; }
    .topbar-right { display: flex; gap: 0.6rem; flex-wrap: wrap; align-items: center; }
    .topbar-right a {
      text-decoration: none; font-size: 0.85rem; font-weight: 600;
      padding: 0.4rem 1rem; border-radius: 999px; transition: background 0.2s;
    }
    .btn-nav    { background: rgba(255,255,255,0.1); color: #e2e8f0; }
    .btn-nav:hover { background: rgba(255,255,255,0.2); }
    .btn-logout { background: #ef4444; color: #fff; }
    .btn-logout:hover { background: #b91c1c; }

    /* ── Layout ───────────────────────── */
    .page-wrap {
      display: flex;
      flex: 1;
      max-width: 1200px;
      width: 100%;
      margin-inline: auto;
      padding: 2rem 1.5rem;
      gap: 1.75rem;
      align-items: flex-start;
    }

    /* ── Sidebar tabs ─────────────────── */
    .sidebar {
      width: 220px;
      flex-shrink: 0;
      position: sticky;
      top: 90px;
    }
    .sidebar h2 { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--muted); margin-bottom: 0.75rem; }
    .tab-list { display: flex; flex-direction: column; gap: 0.3rem; }
    .tab-link {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      padding: 0.7rem 1rem;
      border-radius: 0.75rem;
      font-size: 0.9rem;
      font-weight: 500;
      color: var(--text);
      text-decoration: none;
      transition: background 0.15s, color 0.15s;
    }
    .tab-link:hover { background: #e2e8f0; }
    .tab-link.active { background: var(--accent); color: #fff; font-weight: 700; }
    .tab-link .icon { font-size: 1.1rem; }

    /* ── Content area ─────────────────── */
    .content { flex: 1; min-width: 0; }
    .content-heading { font-size: 1.5rem; font-weight: 800; margin-bottom: 0.3rem; }
    .content-sub { color: var(--muted); font-size: 0.9rem; margin-bottom: 1.5rem; }

    /* ── Alert ────────────────────────── */
    .alert {
      padding: 0.85rem 1.1rem;
      border-radius: var(--radius);
      font-size: 0.9rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }
    .alert.success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
    .alert.error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

    /* ── Asset cards grid ─────────────── */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1.25rem;
    }

    .asset-card {
      background: var(--white);
      border-radius: 1.25rem;
      border: 1px solid var(--border);
      box-shadow: 0 4px 16px rgba(15,23,42,0.05);
      overflow: hidden;
    }

    .asset-card-header {
      padding: 1rem 1.25rem 0.75rem;
      border-bottom: 1px solid var(--border);
    }
    .asset-card-header h3 { font-size: 0.95rem; font-weight: 700; margin-bottom: 0.2rem; }
    .asset-card-header p  { font-size: 0.8rem; color: var(--muted); }

    .asset-card-body { padding: 1.1rem 1.25rem 1.25rem; }

    /* Current file preview */
    .current-preview {
      width: 100%;
      border-radius: 0.75rem;
      border: 2px solid var(--border);
      display: block;
      margin-bottom: 0.6rem;
      object-fit: cover;
      background: #f1f5f9;
    }
    .current-preview.img-portrait  { aspect-ratio: 4/5;  max-height: 220px; }
    .current-preview.img-landscape { aspect-ratio: 16/9; max-height: 160px; }
    .current-preview.img-wide      { aspect-ratio: 16/10; max-height: 160px; }

    .current-label {
      font-size: 0.75rem;
      color: var(--muted);
      margin-bottom: 0.9rem;
      word-break: break-all;
    }

    /* CV current */
    .cv-preview-box {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      background: #f8fafc;
      border: 1px solid var(--border);
      border-radius: 0.75rem;
      padding: 0.85rem 1rem;
      margin-bottom: 0.9rem;
    }
    .cv-preview-box .cv-icon { font-size: 2rem; }
    .cv-preview-box .cv-info { flex: 1; min-width: 0; }
    .cv-preview-box .cv-name { font-size: 0.85rem; font-weight: 600; word-break: break-all; }
    .cv-preview-box .cv-link { font-size: 0.75rem; color: var(--accent); }

    /* Upload zone */
    .upload-zone {
      border: 2px dashed #cbd5e1;
      border-radius: 0.85rem;
      padding: 1.25rem 1rem;
      text-align: center;
      cursor: pointer;
      background: #f8fafc;
      position: relative;
      transition: border-color 0.2s, background 0.2s;
    }
    .upload-zone:hover, .upload-zone.over { border-color: var(--accent); background: #eff6ff; }
    .upload-zone input[type="file"] {
      position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;
    }
    .upload-zone .uz-icon  { font-size: 1.75rem; margin-bottom: 0.4rem; }
    .upload-zone .uz-title { font-size: 0.875rem; font-weight: 700; color: var(--text); margin-bottom: 0.2rem; }
    .upload-zone .uz-hint  { font-size: 0.75rem; color: var(--muted); }

    /* New image preview (before submit) */
    .new-preview-wrap { display: none; margin-top: 0.75rem; text-align: center; }
    .new-preview-wrap img {
      max-width: 100%;
      max-height: 160px;
      border-radius: 0.6rem;
      border: 2px solid var(--accent);
      object-fit: cover;
      display: block;
      margin: 0 auto 0.4rem;
    }
    .new-preview-wrap .new-label { font-size: 0.75rem; color: var(--accent); font-weight: 600; }

    /* Submit button */
    .btn-save {
      width: 100%;
      margin-top: 0.85rem;
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 999px;
      padding: 0.75rem;
      font-size: 0.9rem;
      font-weight: 700;
      cursor: pointer;
      transition: background 0.2s, transform 0.15s;
    }
    .btn-save:hover    { background: #1d4ed8; transform: translateY(-1px); }
    .btn-save:disabled { background: #94a3b8; cursor: not-allowed; transform: none; }

    /* CV submit button — teal */
    .btn-save.cv { background: #0f766e; }
    .btn-save.cv:hover { background: #0d6460; }

    /* ── Footer ───────────────────────── */
    .admin-footer {
      text-align: center; padding: 1.1rem; font-size: 0.8rem;
      color: var(--muted); border-top: 1px solid var(--border); background: var(--white);
    }

    /* ── Responsive ───────────────────── */
    @media (max-width: 768px) {
      .page-wrap    { flex-direction: column; padding: 1rem; }
      .sidebar      { width: 100%; position: static; }
      .tab-list     { flex-direction: row; flex-wrap: wrap; }
      .tab-link     { flex: 1; justify-content: center; padding: 0.55rem 0.75rem; font-size: 0.8rem; }
      .cards-grid   { grid-template-columns: 1fr; }
    }
    @media (max-width: 480px) {
      .topbar { padding: 0.75rem 1rem; }
    }
  </style>
</head>
<body>

<!-- Topbar -->
<header class="topbar">
  <div class="brand">
    <img src="../images/logo rmove bg (1).png" alt="Logo"/>
    <div>
      <p class="brand-name">Marvels Empire Creations</p>
      <p class="brand-sub">Media Manager</p>
    </div>
  </div>
  <div class="topbar-right">
    <a href="dashboard.php" class="btn-nav">✉️ Messages</a>
    <a href="../index.php" class="btn-nav" target="_blank">🌐 View Portfolio</a>
    <a href="logout.php" class="btn-logout">Logout</a>
  </div>
</header>

<div class="page-wrap">

  <!-- Sidebar tabs -->
  <aside class="sidebar">
    <h2>Sections</h2>
    <nav class="tab-list">
      <?php foreach ($assets as $tabKey => $group): ?>
        <a href="?tab=<?= $tabKey ?>"
           class="tab-link <?= $activeTab === $tabKey ? 'active' : '' ?>">
          <span class="icon"><?= $group['icon'] ?></span>
          <?= htmlspecialchars($group['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <!-- Content -->
  <main class="content">
    <?php
      $group = $assets[$activeTab];
    ?>
    <h1 class="content-heading"><?= $group['icon'] ?> <?= htmlspecialchars($group['label']) ?></h1>
    <p class="content-sub">Upload new files below. Changes appear on your portfolio immediately.</p>

    <!-- Show all alerts for this tab -->
    <?php foreach ($group['items'] as $key => $cfg): ?>
      <?php if (isset($alerts[$key])): ?>
        <div class="alert <?= $alerts[$key]['type'] ?>">
          <?= htmlspecialchars($alerts[$key]['msg']) ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>

    <!-- Asset cards -->
    <form method="post" enctype="multipart/form-data">
      <div class="cards-grid">
        <?php foreach ($group['items'] as $key => $cfg):
          $current = $settings[$key] ?? $cfg['default'];
          $isCV    = ($cfg['type'] === 'cv');
          $ext     = strtolower(pathinfo($current, PATHINFO_EXTENSION));

          // Determine image shape class
          $shapeClass = 'img-landscape';
          if ($cfg['ratio'] === '4/5')  $shapeClass = 'img-portrait';
          if ($cfg['ratio'] === '16/10') $shapeClass = 'img-wide';
        ?>
        <div class="asset-card">
          <div class="asset-card-header">
            <h3><?= htmlspecialchars($cfg['label']) ?></h3>
            <p><?= htmlspecialchars($cfg['hint']) ?></p>
          </div>
          <div class="asset-card-body">

            <?php if ($isCV): ?>
              <!-- CV current file -->
              <div class="cv-preview-box">
                <span class="cv-icon">📄</span>
                <div class="cv-info">
                  <p class="cv-name"><?= htmlspecialchars(basename($current)) ?></p>
                  <a class="cv-link" href="../<?= htmlspecialchars($current) ?>" target="_blank">Preview / Download</a>
                </div>
              </div>
            <?php else: ?>
              <!-- Image current preview -->
              <img
                class="current-preview <?= $shapeClass ?>"
                src="../<?= htmlspecialchars($current) ?>"
                alt="<?= htmlspecialchars($cfg['label']) ?>"
              />
            <?php endif; ?>

            <p class="current-label">Current: <?= htmlspecialchars($current) ?></p>

            <!-- Upload zone -->
            <div class="upload-zone" id="zone-<?= $key ?>">
              <input
                type="file"
                name="<?= $key ?>"
                id="file-<?= $key ?>"
                accept="<?= $isCV ? 'application/pdf' : 'image/jpeg,image/png,image/webp,image/gif' ?>"
              />
              <div class="uz-icon"><?= $isCV ? '📂' : '🖼️' ?></div>
              <p class="uz-title">Click to choose <?= $isCV ? 'PDF' : 'image' ?></p>
              <p class="uz-hint">or drag & drop · <?= $isCV ? 'PDF only · Max '.MAX_CV_MB.'MB' : 'JPG/PNG/WebP/GIF · Max '.MAX_IMG_MB.'MB' ?></p>
            </div>

            <!-- JS preview (images only) -->
            <?php if (!$isCV): ?>
            <div class="new-preview-wrap" id="preview-<?= $key ?>">
              <img id="previewimg-<?= $key ?>" src="" alt="preview"/>
              <p class="new-label">New image selected — click Save to apply</p>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn-save <?= $isCV ? 'cv' : '' ?>"
                    id="btn-<?= $key ?>" disabled>
              Save <?= htmlspecialchars($cfg['label']) ?>
            </button>

          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </form>
  </main>
</div>

<footer class="admin-footer">
  &copy; <?= date('Y') ?> Wenyaa Brian Wakoli &mdash; Admin Media Manager
</footer>

<script>
// Wire up each upload zone for preview + enable submit
(function() {
  const zones = document.querySelectorAll('.upload-zone');

  zones.forEach(zone => {
    const input   = zone.querySelector('input[type="file"]');
    const key     = input.name;
    const preview = document.getElementById('preview-' + key);
    const preImg  = document.getElementById('previewimg-' + key);
    const btn     = document.getElementById('btn-' + key);

    // Drag highlight
    zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('over'); });
    zone.addEventListener('dragleave', ()  => zone.classList.remove('over'));
    zone.addEventListener('drop',      ()  => zone.classList.remove('over'));

    input.addEventListener('change', () => {
      const file = input.files[0];
      if (!file) return;
      btn.disabled = false;
      btn.textContent = `Save "${file.name}"`;

      // Show image preview (not for PDF)
      if (preview && preImg && file.type.startsWith('image/')) {
        preImg.src = URL.createObjectURL(file);
        preview.style.display = 'block';
      }
    });
  });
})();
</script>

</body>
</html>
