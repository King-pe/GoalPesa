<?php
session_start();
require_once __DIR__ . '/config/functions.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php?redirect=dashboard.php'); exit; }
$data = read_json();
$currentUser = current_user($data);
if (!$currentUser) { echo 'Akaunti haipatikani.'; exit; }

$jumla = (float)($currentUser['jumla_uwekezaji'] ?? 0);
$makato = (float)($currentUser['makato'] ?? 0);
$salio = $jumla - $makato;

$today = date('Y-m-d');
$payoutBtn = (!empty($currentUser['payout_date']) && $today >= $currentUser['payout_date']);

$depositMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deposit_submit'])) {
    $token = $_POST['csrf'] ?? '';
    if (!csrf_validate($token)) {
        $depositMessage = 'Hali ya usalama haipo sahihi. Jaribu tena.';
    } else {
        $amount = (float)($_POST['amount'] ?? 0);
        $txnNo = trim($_POST['transaction_no'] ?? '');
        $screenshot = $_FILES['screenshot'] ?? null;
        if ($amount < 5000) {
            $depositMessage = 'Kiasi cha chini ni 5000 TZS.';
        } elseif (!$screenshot || !in_array($screenshot['type'], ['image/jpeg','image/png','image/jpg'])) {
            $depositMessage = 'Tafadhali upload screenshot sahihi (jpg/png).';
        } elseif (!preg_match('/^CI\d{6}\.\d{4}\.T\d{5}$/', $txnNo)) {
            $depositMessage = 'Transaction Number sio sahihi. Mfano: CI250820.1131.T92433';
        } else {
            $duplicate = false;
            foreach (($data['payouts'] ?? []) as $p) {
                if (($p['transaction_no'] ?? '') === $txnNo) { $duplicate = true; break; }
            }
            if ($duplicate) {
                $depositMessage = 'Transaction hii tayari imeingizwa. Tafadhali jaribu nyingine.';
            } else {
                $uploadDir = __DIR__ . '/uploads';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $ext = pathinfo($screenshot['name'], PATHINFO_EXTENSION);
                $filePath = $uploadDir . '/' . date('Ymd_His') . '_' . generate_id() . '.' . $ext;
                move_uploaded_file($screenshot['tmp_name'], $filePath);
                $data['payouts'][] = [
                    'id' => generate_id(),
                    'kind' => 'deposit',
                    'user_id' => $currentUser['id'],
                    'amount' => $amount,
                    'transaction_no' => $txnNo,
                    'screenshot' => 'uploads/' . basename($filePath),
                    'status' => 'pending',
                    'date' => date('Y-m-d H:i:s')
                ];
                write_json($data);
                $depositMessage = 'Muamala wako umepokelewa. Tutakuthibitisha hivi karibuni.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - GoalPesa</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="logo">
    <img src="assets/images/logo.png" alt="GoalPesa" height="40">
    <span class="brand">GoalPesa</span>
  </div>
  <nav>
    <span>Karibu, <?= htmlspecialchars($currentUser['jina']) ?></span>
    <a href="logout.php" class="btn">Toka</a>
  </nav>
</header>
<main class="dashboard">
  <h2>Dashboard Yako</h2>
  <div class="card">
    <p><strong>Jumla ya Uwekezaji:</strong> <?= number_format($jumla) ?> TZS</p>
    <p><strong>Makato:</strong> <?= number_format($makato) ?> TZS</p>
    <p><strong>Salio Baada ya Makato:</strong> <?= number_format($salio) ?> TZS</p>
    <p><strong>Payout Period:</strong> <?= htmlspecialchars((string)($currentUser['payout_period'] ?? 'Haijawekwa')) ?></p>
    <p><strong>Payout Date:</strong> <?= htmlspecialchars((string)($currentUser['payout_date'] ?? 'Haijawekwa')) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($currentUser['status'])) ?></p>
  </div>

  <div class="card">
    <h3>Weka Deposit / Tuma Muamala</h3>
    <?php if($depositMessage): ?>
      <p class="note"><?= htmlspecialchars($depositMessage) ?></p>
    <?php endif; ?>
    <div class="note">
      <p><strong>Maelekezo ya Malipo:</strong></p>
      <ul>
        <li>Tuma kupitia <strong>Mpesa</strong> au <strong>Tigopesa</strong>.</li>
        <li>Kiasi cha chini: <strong>5000 TZS</strong>.</li>
        <li>Hifadhi <strong>Screenshot</strong> ya uthibitisho na ujaze <strong>Transaction No</strong>.</li>
        <li>Mfano wa Transaction No: <code>CI250820.1131.T92433</code>.</li>
      </ul>
    </div>
    <form action="" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <label>Kiasi cha Pesa:</label>
      <input type="number" name="amount" step="0.01" min="5000" required>

      <label>Upload Screenshot:</label>
      <input type="file" name="screenshot" accept="image/*" required>

      <label>Transaction No (mfano: CI250820.1131.T92433):</label>
      <input type="text" name="transaction_no" required pattern="CI\d{6}\.\d{4}\.T\d{5}">

      <button type="submit" name="deposit_submit" class="btn btn-primary">Tuma Muamala</button>
    </form>
  </div>

  <?php if ($payoutBtn): ?>
    <form class="card" action="payout.php" method="POST">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <button type="submit" class="btn btn-primary">Omba Payout</button>
    </form>
  <?php else: ?>
    <div class="card"><p class="note">Hutaweza kutoa mpaka tarehe ya payout ifike.</p></div>
  <?php endif; ?>

  <form class="card" action="cancel.php" method="POST" onsubmit="return confirm('Una uhakika unataka kuvunja mkataba? Utakatwa <?= (int)(compute_early_withdrawal_fee()*100) ?>%');">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
    <button type="submit" class="btn btn-danger">Cancel Order</button>
  </form>
</main>
</body>
</html>