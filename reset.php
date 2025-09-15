<?php
session_start();
require_once __DIR__ . '/config/functions.php';

$data = read_json();
$token = $_GET['token'] ?? '';
$record = null; $idx = null;
foreach ($data['password_resets'] as $i => $r) {
    if (($r['token'] ?? '') === $token) { $record = $r; $idx = $i; break; }
}

$invalid = false;
if (!$record) { $invalid = true; }
else if (strtotime($record['expires_at']) < time()) { $invalid = true; }

$message = '';
if (!$invalid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf'] ?? '')) {
        $message = 'Hali ya usalama haipo sahihi.';
    } else {
        $new = $_POST['password'] ?? '';
        if (strlen($new) < 6) {
            $message = 'Nenosiri liwe angalau herufi 6.';
        } else {
            // find user and update password
            foreach ($data['users'] as $i => $u) {
                if ($u['id'] === $record['user_id']) {
                    $data['users'][$i]['password'] = hash_password($new);
                    break;
                }
            }
            // consume token
            array_splice($data['password_resets'], $idx, 1);
            write_json($data);
            $message = 'Nenosiri limebadilishwa. Tafadhali ingia.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Badilisha Nenosiri - GoalPesa</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="logo">
    <img src="assets/images/logo.png" height="40" alt="GoalPesa">
    <span class="brand">GoalPesa</span>
  </div>
  <nav>
    <a href="index.php" class="btn">Nyumbani</a>
  </nav>
</header>
<main class="dashboard">
  <div class="card">
    <h2>Weka Nenosiri Jipya</h2>
    <?php if ($invalid): ?>
      <p class="note">Kiungo sio sahihi au kimeisha muda.</p>
    <?php else: ?>
      <?php if ($message): ?><p class="note"><?= htmlspecialchars($message) ?></p><?php endif; ?>
      <form method="POST">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
        <label>Nenosiri Jipya</label>
        <input type="password" name="password" required>
        <button type="submit" class="btn btn-primary">Hifadhi</button>
      </form>
      <p><a href="login.php">Rudi Kuingia</a></p>
    <?php endif; ?>
  </div>
</main>
</body>
</html>