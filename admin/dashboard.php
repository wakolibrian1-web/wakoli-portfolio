<?php
/**
 * admin/dashboard.php
 * Admin dashboard — displays all contact form submissions from the database.
 * Protected: only accessible after logging in via login.php
 */

session_start();

// ── Auth guard ────────────────────────────────────────────────────────────────
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once '../db_connect.php';

$conn = get_db_connection();

// ── Handle mark-as-read toggle ────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_read'])) {
    $toggle_id = (int) $_POST['toggle_id'];
    $new_state  = (int) $_POST['new_state'];
    $upd = $conn->prepare('UPDATE contact_messages SET is_read = ? WHERE id = ?');
    $upd->bind_param('ii', $new_state, $toggle_id);
    $upd->execute();
    $upd->close();
    header('Location: dashboard.php');
    exit;
}

// ── Handle delete ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del_id = (int) $_POST['delete_id'];
    $del = $conn->prepare('DELETE FROM contact_messages WHERE id = ?');
    $del->bind_param('i', $del_id);
    $del->execute();
    $del->close();
    header('Location: dashboard.php');
    exit;
}

// ── Fetch data ────────────────────────────────────────────────────────────────
// Filter
$filter    = $_GET['filter'] ?? 'all';
$search    = trim($_GET['search'] ?? '');
$page      = max(1, (int) ($_GET['page'] ?? 1));
$per_page  = 10;
$offset    = ($page - 1) * $per_page;

$where_clauses = [];
$params        = [];
$types         = '';

if ($filter === 'unread') {
    $where_clauses[] = 'is_read = 0';
} elseif ($filter === 'read') {
    $where_clauses[] = 'is_read = 1';
}

if ($search !== '') {
    $where_clauses[] = '(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)';
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
    $types .= 'ssss';
}

$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Count total for pagination
$count_sql  = "SELECT COUNT(*) AS total FROM contact_messages $where_sql";
$count_stmt = $conn->prepare($count_sql);
if ($params) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total_rows  = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = (int) ceil($total_rows / $per_page);
$count_stmt->close();

// Fetch current page
$data_sql  = "SELECT * FROM contact_messages $where_sql ORDER BY submitted_at DESC LIMIT ? OFFSET ?";
$data_stmt = $conn->prepare($data_sql);
$fetch_params = array_merge($params, [$per_page, $offset]);
$fetch_types  = $types . 'ii';
$data_stmt->bind_param($fetch_types, ...$fetch_params);
$data_stmt->execute();
$messages = $data_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$data_stmt->close();

// Stats
$stats_res  = $conn->query('SELECT COUNT(*) AS total, SUM(is_read=0) AS unread, SUM(is_read=1) AS `read` FROM contact_messages');
$stats      = $stats_res->fetch_assoc();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard | Wakoli Portfolio</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:       #f1f5f9;
      --sidebar:  #0f172a;
      --accent:   #2563eb;
      --danger:   #dc2626;
      --success:  #16a34a;
      --text:     #1e293b;
      --muted:    #64748b;
      --border:   #e2e8f0;
      --white:    #ffffff;
      --radius:   0.85rem;
    }

    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* ── Top bar ─────────────────────────────────── */
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
    }

    .topbar .brand {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .topbar .brand img {
      height: 38px;
      width: auto;
      object-fit: contain;
    }

    .topbar .brand-name {
      font-size: 1rem;
      font-weight: 700;
      line-height: 1.2;
    }

    .topbar .brand-sub {
      font-size: 0.75rem;
      color: #94a3b8;
    }

    .topbar .topbar-right {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .topbar .admin-badge {
      font-size: 0.8rem;
      background: rgba(255,255,255,0.1);
      border-radius: 999px;
      padding: 0.3rem 0.85rem;
      color: #cbd5e1;
    }

    .btn-logout {
      background: #ef4444;
      color: #fff;
      border: none;
      border-radius: 999px;
      padding: 0.45rem 1.1rem;
      font-size: 0.875rem;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.2s;
    }

    .btn-logout:hover { background: #b91c1c; }

    /* ── Main content ────────────────────────────── */
    .main {
      flex: 1;
      padding: 2rem;
      max-width: 1300px;
      width: 100%;
      margin-inline: auto;
    }

    .page-heading {
      font-size: 1.75rem;
      font-weight: 800;
      margin-bottom: 0.3rem;
    }

    .page-sub {
      color: var(--muted);
      font-size: 0.9rem;
      margin-bottom: 1.75rem;
    }

    /* ── Stats cards ─────────────────────────────── */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
      gap: 1rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: var(--white);
      border-radius: var(--radius);
      padding: 1.25rem 1.5rem;
      border: 1px solid var(--border);
      box-shadow: 0 2px 8px rgba(15,23,42,0.05);
    }

    .stat-card .stat-label {
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--muted);
      margin-bottom: 0.4rem;
    }

    .stat-card .stat-value {
      font-size: 2rem;
      font-weight: 800;
      color: var(--text);
    }

    .stat-card.accent .stat-value { color: var(--accent); }
    .stat-card.success .stat-value { color: var(--success); }

    /* ── Toolbar (search + filter) ───────────────── */
    .toolbar {
      display: flex;
      flex-wrap: wrap;
      gap: 0.75rem;
      align-items: center;
      margin-bottom: 1.25rem;
    }

    .search-form {
      display: flex;
      gap: 0.5rem;
      flex: 1;
      min-width: 220px;
    }

    .search-form input {
      flex: 1;
      border: 1px solid var(--border);
      border-radius: 999px;
      padding: 0.6rem 1rem;
      font-size: 0.9rem;
      background: var(--white);
    }

    .search-form input:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }

    .btn-search {
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: 999px;
      padding: 0.6rem 1.25rem;
      font-size: 0.875rem;
      font-weight: 600;
      cursor: pointer;
      white-space: nowrap;
      transition: background 0.2s;
    }

    .btn-search:hover { background: #1d4ed8; }

    .filter-links {
      display: flex;
      gap: 0.5rem;
    }

    .filter-links a {
      text-decoration: none;
      padding: 0.5rem 1rem;
      border-radius: 999px;
      font-size: 0.875rem;
      font-weight: 600;
      border: 1px solid var(--border);
      background: var(--white);
      color: var(--muted);
      transition: all 0.15s;
    }

    .filter-links a.active,
    .filter-links a:hover {
      background: var(--accent);
      color: #fff;
      border-color: var(--accent);
    }

    /* ── Table card ──────────────────────────────── */
    .table-card {
      background: var(--white);
      border-radius: var(--radius);
      border: 1px solid var(--border);
      box-shadow: 0 2px 12px rgba(15,23,42,0.05);
      overflow: hidden;
    }

    .table-responsive { overflow-x: auto; }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.9rem;
    }

    thead {
      background: #f8fafc;
      border-bottom: 2px solid var(--border);
    }

    thead th {
      padding: 0.85rem 1rem;
      text-align: left;
      font-size: 0.78rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: var(--muted);
      white-space: nowrap;
    }

    tbody tr {
      border-bottom: 1px solid var(--border);
      transition: background 0.12s;
    }

    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #f8fafc; }
    tbody tr.unread { background: #eff6ff; }
    tbody tr.unread:hover { background: #dbeafe; }

    td {
      padding: 0.95rem 1rem;
      vertical-align: top;
    }

    .td-name { font-weight: 600; color: var(--text); white-space: nowrap; }
    .td-email { color: var(--accent); font-size: 0.875rem; }
    .td-subject .badge {
      display: inline-block;
      background: #e0e7ff;
      color: #3730a3;
      border-radius: 999px;
      padding: 0.25rem 0.7rem;
      font-size: 0.78rem;
      font-weight: 600;
      white-space: nowrap;
    }
    .td-message {
      max-width: 280px;
      color: #475569;
      font-size: 0.875rem;
      line-height: 1.5;
    }
    .td-date {
      white-space: nowrap;
      font-size: 0.8rem;
      color: var(--muted);
    }
    .td-status .dot {
      display: inline-block;
      width: 8px; height: 8px;
      border-radius: 50%;
      margin-right: 5px;
    }
    .dot.unread { background: #f59e0b; }
    .dot.read   { background: #22c55e; }
    .td-status span {
      font-size: 0.8rem;
      font-weight: 600;
    }

    /* ── Action buttons inside table ─────────────── */
    .actions { display: flex; gap: 0.4rem; align-items: center; }

    .btn-sm {
      border: none;
      border-radius: 0.5rem;
      padding: 0.4rem 0.75rem;
      font-size: 0.78rem;
      font-weight: 600;
      cursor: pointer;
      white-space: nowrap;
      transition: opacity 0.15s;
    }

    .btn-sm:hover { opacity: 0.85; }

    .btn-mark-read   { background: #dcfce7; color: #166534; }
    .btn-mark-unread { background: #fef9c3; color: #854d0e; }
    .btn-delete      { background: #fee2e2; color: #991b1b; }

    /* ── Empty state ─────────────────────────────── */
    .empty-state {
      text-align: center;
      padding: 3.5rem 1rem;
      color: var(--muted);
    }

    .empty-state .icon { font-size: 3rem; margin-bottom: 0.75rem; }
    .empty-state p { font-size: 0.95rem; }

    /* ── Pagination ──────────────────────────────── */
    .pagination {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 1.25rem;
      border-top: 1px solid var(--border);
      font-size: 0.875rem;
      color: var(--muted);
    }

    .pagination-links { display: flex; gap: 0.4rem; }

    .pagination-links a,
    .pagination-links span {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 34px; height: 34px;
      border-radius: 0.5rem;
      border: 1px solid var(--border);
      font-size: 0.875rem;
      font-weight: 600;
      text-decoration: none;
      color: var(--text);
      background: var(--white);
      transition: all 0.15s;
    }

    .pagination-links a:hover { background: var(--accent); color: #fff; border-color: var(--accent); }
    .pagination-links span.current { background: var(--accent); color: #fff; border-color: var(--accent); }
    .pagination-links span.disabled { color: #cbd5e1; pointer-events: none; }

    /* ── Footer ──────────────────────────────────── */
    .admin-footer {
      text-align: center;
      padding: 1.25rem;
      font-size: 0.8rem;
      color: var(--muted);
      border-top: 1px solid var(--border);
      background: var(--white);
    }

    @media (max-width: 640px) {
      .topbar { padding: 0.75rem 1rem; }
      .main   { padding: 1rem; }
      .filter-links a { padding: 0.4rem 0.75rem; font-size: 0.8rem; }
    }
  </style>
</head>
<body>

<!-- ── Top bar ──────────────────────────────────────────────────────────── -->
<header class="topbar">
  <div class="brand">
    <img src="../images/logo rmove bg (1).png" alt="Logo" />
    <div>
      <p class="brand-name">Marvels Empire Creations</p>
      <p class="brand-sub">Admin Dashboard</p>
    </div>
  </div>
  <div class="topbar-right">
    <span class="admin-badge">👤 <?= htmlspecialchars($_SESSION['admin_user']) ?></span>
    <a class="btn-logout" href="logout.php">Logout</a>
  </div>
</header>

<!-- ── Main ─────────────────────────────────────────────────────────────── -->
<main class="main">

  <h1 class="page-heading">Contact Messages</h1>
  <p class="page-sub">All messages submitted through the portfolio contact form.</p>

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat-card">
      <p class="stat-label">Total Messages</p>
      <p class="stat-value"><?= (int) $stats['total'] ?></p>
    </div>
    <div class="stat-card accent">
      <p class="stat-label">Unread</p>
      <p class="stat-value"><?= (int) $stats['unread'] ?></p>
    </div>
    <div class="stat-card success">
      <p class="stat-label">Read</p>
      <p class="stat-value"><?= (int) $stats['read'] ?></p>
    </div>
    <div class="stat-card">
      <p class="stat-label">Current Page</p>
      <p class="stat-value"><?= $page ?> / <?= max(1, $total_pages) ?></p>
    </div>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <form class="search-form" method="get" action="dashboard.php">
      <input
        type="search"
        name="search"
        placeholder="Search name, email, subject…"
        value="<?= htmlspecialchars($search) ?>"
        aria-label="Search messages"
      />
      <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>" />
      <button class="btn-search" type="submit">Search</button>
    </form>

    <div class="filter-links">
      <a href="dashboard.php?filter=all&search=<?= urlencode($search) ?>"    class="<?= $filter==='all'   ? 'active' : '' ?>">All</a>
      <a href="dashboard.php?filter=unread&search=<?= urlencode($search) ?>" class="<?= $filter==='unread'? 'active' : '' ?>">Unread</a>
      <a href="dashboard.php?filter=read&search=<?= urlencode($search) ?>"   class="<?= $filter==='read'  ? 'active' : '' ?>">Read</a>
    </div>
  </div>

  <!-- Table -->
  <div class="table-card">
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Subject</th>
            <th>Message</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($messages)): ?>
            <tr>
              <td colspan="8">
                <div class="empty-state">
                  <div class="icon">📭</div>
                  <p>No messages found<?= $search ? ' for "' . htmlspecialchars($search) . '"' : '' ?>.</p>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($messages as $msg): ?>
              <tr class="<?= $msg['is_read'] ? 'read' : 'unread' ?>">
                <td><?= (int) $msg['id'] ?></td>
                <td>
                  <div class="td-name"><?= htmlspecialchars($msg['name']) ?></div>
                </td>
                <td>
                  <a class="td-email" href="mailto:<?= htmlspecialchars($msg['email']) ?>">
                    <?= htmlspecialchars($msg['email']) ?>
                  </a>
                </td>
                <td class="td-subject">
                  <span class="badge"><?= htmlspecialchars($msg['subject']) ?></span>
                </td>
                <td class="td-message">
                  <?= nl2br(htmlspecialchars(mb_strimwidth($msg['message'], 0, 120, '…'))) ?>
                </td>
                <td class="td-date">
                  <?= date('d M Y', strtotime($msg['submitted_at'])) ?><br>
                  <span style="font-size:0.75rem;"><?= date('H:i', strtotime($msg['submitted_at'])) ?></span>
                </td>
                <td class="td-status">
                  <span class="dot <?= $msg['is_read'] ? 'read' : 'unread' ?>"></span>
                  <span><?= $msg['is_read'] ? 'Read' : 'Unread' ?></span>
                </td>
                <td>
                  <div class="actions">
                    <?php if ($msg['is_read']): ?>
                      <form method="post">
                        <input type="hidden" name="toggle_read" value="1" />
                        <input type="hidden" name="toggle_id"   value="<?= (int) $msg['id'] ?>" />
                        <input type="hidden" name="new_state"   value="0" />
                        <button class="btn-sm btn-mark-unread" type="submit" title="Mark as unread">Unread</button>
                      </form>
                    <?php else: ?>
                      <form method="post">
                        <input type="hidden" name="toggle_read" value="1" />
                        <input type="hidden" name="toggle_id"   value="<?= (int) $msg['id'] ?>" />
                        <input type="hidden" name="new_state"   value="1" />
                        <button class="btn-sm btn-mark-read" type="submit" title="Mark as read">Read ✓</button>
                      </form>
                    <?php endif; ?>

                    <form method="post" onsubmit="return confirm('Delete this message from <?= htmlspecialchars(addslashes($msg['name'])) ?>? This cannot be undone.');">
                      <input type="hidden" name="delete_id" value="<?= (int) $msg['id'] ?>" />
                      <button class="btn-sm btn-delete" type="submit" title="Delete message">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <span>Showing <?= count($messages) ?> of <?= $total_rows ?> messages</span>
      <div class="pagination-links">
        <?php
          $base_url = 'dashboard.php?filter=' . urlencode($filter) . '&search=' . urlencode($search) . '&page=';
        ?>
        <?php if ($page > 1): ?>
          <a href="<?= $base_url . ($page - 1) ?>" aria-label="Previous page">‹</a>
        <?php else: ?>
          <span class="disabled">‹</span>
        <?php endif; ?>

        <?php for ($p = max(1, $page - 2); $p <= min($total_pages, $page + 2); $p++): ?>
          <?php if ($p === $page): ?>
            <span class="current"><?= $p ?></span>
          <?php else: ?>
            <a href="<?= $base_url . $p ?>"><?= $p ?></a>
          <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
          <a href="<?= $base_url . ($page + 1) ?>" aria-label="Next page">›</a>
        <?php else: ?>
          <span class="disabled">›</span>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

</main>

<footer class="admin-footer">
  &copy; <?= date('Y') ?> Wenyaa Brian Wakoli &mdash; Admin Dashboard
</footer>

</body>
</html>
