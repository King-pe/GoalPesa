<?php
session_start();
require_once __DIR__ . '/../config/functions.php';

$data = read_json();
$user = current_user($data);
if (!$user || !is_admin($user)) { header('Location: /admin/login.php?redirect=/admin/dashboard.php'); exit; }

// Stats overview
$users = $data['users'] ?? [];
$deposits = array_values(array_filter($data['payouts'] ?? [], function($p){ return ($p['kind'] ?? 'deposit') === 'deposit'; }));
$pendingCount = 0;
$approvedCount = 0;
$rejectedCount = 0;
$approvedSum = 0.0;
foreach ($deposits as $d) {
  $status = $d['status'] ?? 'pending';
  if ($status === 'approved') { $approvedCount++; $approvedSum += (float)$d['amount']; }
  elseif ($status === 'rejected') { $rejectedCount++; }
  else { $pendingCount++; }
}
$totalUsers = count($users);
$totalBalance = 0.0;
foreach ($users as $u) { $totalBalance += (float)($u['jumla_uwekezaji'] ?? 0); }
?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Dashboard</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="logo">
    <img src="/assets/images/logo.png" height="40" alt="GoalPesa">
    <span class="brand">Admin</span>
  </div>
  <nav>
    <a href="/index.php" class="btn">Nyumbani</a>
    <a href="/admin/post.php" class="btn">Posts</a>
    <a href="/admin/logout.php" class="btn">Toka</a>
  </nav>
</header>
<main class="dashboard">
  <div class="card">
    <h2>Muhtasari</h2>
    <p><strong>Users wote:</strong> <?= (int)$totalUsers ?> | <strong>Salio zote:</strong> <?= number_format($totalBalance) ?> TZS</p>
    <p><strong>Deposits Pending:</strong> <?= (int)$pendingCount ?> | <strong>Approved:</strong> <?= (int)$approvedCount ?> | <strong>Rejected:</strong> <?= (int)$rejectedCount ?> | <strong>Approved Amount:</strong> <?= number_format($approvedSum) ?> TZS</p>
  </div>

  <div class="card">
    <h2>Muamala: Deposits</h2>
    <?php if (!$deposits): ?>
      <p>Hakuna muamala kwa sasa.</p>
    <?php else: ?>
      <?php foreach ($deposits as $p): ?>
        <div class="card" style="margin: 10px 0;">
          <p><strong>User:</strong> <?= htmlspecialchars($p['user_id']) ?> | <strong>Kiasi:</strong> <?= number_format((float)$p['amount']) ?> | <strong>Status:</strong> <?= htmlspecialchars($p['status']) ?></p>
          <p><strong>Transaction:</strong> <?= htmlspecialchars($p['transaction_no']) ?> | <a target="_blank" href="/<?= htmlspecialchars($p['screenshot']) ?>">Screenshot</a></p>
          <form method="POST" action="/admin/update.php" style="display:inline;">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars($p['id'] ?? '') ?>">
            <button class="btn btn-primary" name="action" value="approve">Kubali</button>
            <button class="btn btn-danger" name="action" value="reject" onclick="return confirm('Kukataa muamala huu?')">Kataa</button>
            <button class="btn" name="action" value="delete" onclick="return confirm('Futa kabisa muamala huu?')">Futa</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>
</body>
</html>

