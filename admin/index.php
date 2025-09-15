<?php
require __DIR__ . '/../config/functions.php';
gp_require_admin();
$data = gp_load_data();
?>
<!doctype html>
<html lang="sw">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - GoalPesa</title>
    <link rel="stylesheet" href="/assets/css/style.css">
  </head>
  <body>
    <header>
      <div class="container nav">
        <div class="brand"><span style="width:10px;height:10px;border-radius:999px;background:#22c55e;display:inline-block"></span><span>GoalPesa</span><span class="pill">Admin</span></div>
        <div>
          <a class="btn" href="/">Nyumbani</a>
          <a class="btn" href="/admin/logout.php">Toka</a>
        </div>
      </div>
    </header>
    <main class="container" style="padding:24px 0">
      <div class="grid" style="grid-template-columns:1fr 1fr 1fr">
        <section class="card">
          <h3>Watumiaji</h3>
          <table>
            <thead><tr><th>Jina</th><th>Balance</th><th>Initial</th></tr></thead>
            <tbody>
              <?php foreach ($data['users'] as $u): ?>
                <tr>
                  <td><?= gp_sanitize($u['jina']) ?></td>
                  <td>KES <?= number_format((float)$u['balance'],2) ?></td>
                  <td>KES <?= number_format((float)$u['initialDeposit'],2) ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($data['users'])): ?><tr><td colspan="3" class="muted">Hakuna data</td></tr><?php endif; ?>
            </tbody>
          </table>
        </section>
        <section class="card">
          <h3>Deposits</h3>
          <table>
            <thead><tr><th>Tarehe</th><th>Jina</th><th>Kiasi</th></tr></thead>
            <tbody>
              <?php foreach ($data['deposits'] as $d): ?>
                <tr>
                  <td><?= gp_sanitize($d['date']) ?></td>
                  <td><?= gp_sanitize($d['jina']) ?></td>
                  <td>KES <?= number_format((float)$d['amount'],2) ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($data['deposits'])): ?><tr><td colspan="3" class="muted">Hakuna data</td></tr><?php endif; ?>
            </tbody>
          </table>
        </section>
        <section class="card">
          <h3>Withdrawals</h3>
          <table>
            <thead><tr><th>Tarehe</th><th>Jina</th><th>Kiasi</th><th>Makato</th><th>Baada</th></tr></thead>
            <tbody>
              <?php foreach ($data['withdrawals'] as $w): ?>
                <tr>
                  <td><?= gp_sanitize($w['date']) ?></td>
                  <td><?= gp_sanitize($w['jina']) ?></td>
                  <td>KES <?= number_format((float)$w['amount'],2) ?></td>
                  <td>KES <?= number_format((float)$w['fee'],2) ?></td>
                  <td>KES <?= number_format((float)$w['after'],2) ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($data['withdrawals'])): ?><tr><td colspan="5" class="muted">Hakuna data</td></tr><?php endif; ?>
            </tbody>
          </table>
        </section>
      </div>
    </main>
  </body>
  </html>
<?php
session_start();
require_once __DIR__ . '/../config/functions.php';

$data = read_json();
$user = current_user($data);
if (!$user || !is_admin($user)) { header('Location: /login.php?redirect=/admin/index.php'); exit; }

$items = array_values(array_filter($data['payouts'] ?? [], function($p){ return ($p['kind'] ?? 'deposit') === 'deposit'; }));
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