<?php
session_start();
require_once __DIR__ . '/../config/functions.php';

$data = read_json();
$user = current_user($data);
if (!$user || !is_admin($user)) { header('Location: /login.php?redirect=/admin/index.php'); exit; }

$usersById = [];
foreach (($data['users'] ?? []) as $u) {
  $usersById[$u['id']] = $u;
}

$items = array_values(array_filter($data['payouts'] ?? [], function($p){ return in_array(($p['kind'] ?? 'deposit'), ['deposit','payout'], true); }));
?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - GoalPesa</title>
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
    <a href="/logout.php" class="btn">Toka</a>
  </nav>
</header>
<main class="dashboard">
  <div class="card">
    <h2>Muamala: Deposits</h2>
    <?php if (!$items): ?>
      <p>Hakuna muamala kwa sasa.</p>
    <?php else: ?>
      <?php foreach ($items as $p): ?>
        <div class="card" style="margin: 10px 0;">
          <?php if (($p['kind'] ?? 'deposit') === 'deposit'): ?>
            <p><strong>User:</strong> <?= htmlspecialchars($usersById[$p['user_id']]['jina'] ?? $p['user_id']) ?> | <strong>Kiasi:</strong> <?= number_format((float)$p['amount']) ?> | <strong>Status:</strong> <?= htmlspecialchars($p['status']) ?></p>
            <p><strong>Transaction:</strong> <?= htmlspecialchars($p['transaction_no']) ?> | <a target="_blank" href="/<?= htmlspecialchars($p['screenshot']) ?>">Screenshot</a></p>
            <form method="POST" action="/admin/update.php" style="display:inline;">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= htmlspecialchars($p['id'] ?? '') ?>">
              <button class="btn btn-primary" name="action" value="approve">Kubali</button>
              <button class="btn btn-danger" name="action" value="reject" onclick="return confirm('Kukataa muamala huu?')">Kataa</button>
              <button class="btn" name="action" value="delete" onclick="return confirm('Futa kabisa muamala huu?')">Futa</button>
            </form>
          <?php else: ?>
            <p><strong>Payout:</strong> <?= htmlspecialchars($usersById[$p['user_id']]['jina'] ?? $p['payer_name'] ?? $p['user_id']) ?> | <strong>Net:</strong> <?= number_format((float)($p['net_amount'] ?? 0), 2) ?> | <strong>Status:</strong> <?= htmlspecialchars($p['status']) ?></p>
            <form method="POST" action="/admin/update_payout.php" style="display:inline;">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
              <input type="hidden" name="id" value="<?= htmlspecialchars($p['id'] ?? '') ?>">
              <button class="btn btn-primary" name="action" value="approve">Lipa</button>
              <button class="btn btn-danger" name="action" value="reject" onclick="return confirm('Kataa ombi la payout?')">Kataa</button>
              <button class="btn" name="action" value="delete" onclick="return confirm('Futa ombi hili?')">Futa</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>
</body>
</html>