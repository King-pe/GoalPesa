<?php
require __DIR__ . '/config/functions.php';
gp_require_login();
$user = gp_current_user();
$error = '';
$success = '';
$preview = [
    'balance' => (float)$user['balance'],
    'fee' => gp_calculate_payout_fee((float)$user['balance'], (float)$user['initialDeposit']),
];
$preview['after'] = max($preview['balance'] - $preview['fee'], 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!gp_verify_csrf($_POST['csrf'] ?? '')) {
        $error = 'CSRF kosa, jaribu tena.';
    } else {
        try {
            [$user, $w] = gp_perform_payout((int)$user['id']);
            $success = 'Umetoa KES ' . number_format((float)$w['amount'],2) . ' (makato ' . number_format((float)$w['fee'],2) . '). Umepokea KES ' . number_format((float)$w['after'],2) . '.';
            $preview = [
                'balance' => (float)$user['balance'],
                'fee' => 0,
                'after' => 0,
            ];
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="sw">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Toa Fedha - GoalPesa</title>
    <link rel="stylesheet" href="/assets/css/style.css">
  </head>
  <body>
    <main class="container" style="padding:40px 0">
      <div class="card" style="max-width:620px;margin:0 auto">
        <h2>Toa fedha</h2>
        <?php if ($success): ?><p style="color:#86efac"><?= gp_sanitize($success) ?></p><?php endif; ?>
        <?php if ($error): ?><p style="color:#fca5a5"><?= gp_sanitize($error) ?></p><?php endif; ?>
        <div class="grid" style="grid-template-columns:1fr 1fr">
          <div class="card">
            <div class="muted">Salio</div>
            <div style="font-size:1.6rem">KES <?= number_format($preview['balance'],2) ?></div>
          </div>
          <div class="card">
            <div class="muted">Makato</div>
            <div style="font-size:1.6rem">KES <?= number_format($preview['fee'],2) ?></div>
          </div>
          <div class="card" style="grid-column:1 / span 2">
            <div class="muted">Utakachopokea</div>
            <div style="font-size:1.6rem">KES <?= number_format($preview['after'],2) ?></div>
          </div>
        </div>
        <form method="post" class="row" style="margin-top:12px">
          <input type="hidden" name="csrf" value="<?= gp_csrf_token() ?>">
          <button class="btn" type="submit" <?= $preview['balance'] > 0 ? '' : 'disabled' ?>>Thibitisha kutoa</button>
          <a class="btn" href="/dashboard.php">Ghairi</a>
        </form>
      </div>
    </main>
  </body>
  </html>
<?php
session_start();
require_once __DIR__ . '/config/functions.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php?redirect=dashboard.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard.php'); exit; }

$data = read_json();
$user = current_user($data);
if (!$user) { header('Location: dashboard.php'); exit; }

if (!csrf_validate($_POST['csrf'] ?? '')) { header('Location: dashboard.php'); exit; }

$today = date('Y-m-d');
if (empty($user['payout_date']) || $today < $user['payout_date']) {
    header('Location: dashboard.php');
    exit;
}

$amount = (float)($user['jumla_uwekezaji'] ?? 0);
$months = (int)($user['payout_period'] ?? 0);
$feePct = compute_fee_percentage($months, $amount);
$fee = round($amount * $feePct, 2);

$data['payouts'][] = [
    'id' => generate_id(),
    'kind' => 'payout',
    'user_id' => $user['id'],
    'amount' => $amount,
    'fee' => $fee,
    'status' => 'processed',
    'date' => date('Y-m-d H:i:s')
];

// reset user balance after payout
$user['jumla_uwekezaji'] = 0;
$user['makato'] = 0;
$user['payout_date'] = date('Y-m-d', strtotime('+' . max(1, $months) . ' months'));
update_user($data, $user);
write_json($data);

header('Location: dashboard.php');
exit;