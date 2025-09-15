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
            <thead><tr><th>Tarehe</th><th>Jina</th><th>Kiasi</th><th>Status</th><th>Picha</th><th>Vitendo</th></tr></thead>
            <tbody>
              <?php foreach ($data['deposits'] as $d): ?>
                <tr>
                  <td><?= gp_sanitize($d['date']) ?></td>
                  <td><?= gp_sanitize($d['jina']) ?></td>
                  <td>KES <?= number_format((float)$d['amount'],2) ?></td>
                  <td><?= gp_sanitize($d['status'] ?? 'done') ?></td>
                  <td><?php if (!empty($d['screenshot'])): ?><a target="_blank" href="/<?= gp_sanitize($d['screenshot']) ?>">Ona</a><?php endif; ?></td>
                  <td>
                    <?php if (($d['status'] ?? 'done') === 'pending'): ?>
                      <form method="post" action="/admin/update_deposit.php" class="row" style="gap:6px">
                        <input type="hidden" name="csrf" value="<?= gp_csrf_token() ?>">
                        <input type="hidden" name="id" value="<?= gp_sanitize($d['id']) ?>">
                        <button class="btn" name="action" value="approve">Kubali</button>
                        <button class="btn danger" name="action" value="reject">Kataa</button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($data['deposits'])): ?><tr><td colspan="6" class="muted">Hakuna data</td></tr><?php endif; ?>
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