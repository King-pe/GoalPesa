<?php
session_start();
require_once __DIR__ . '/config/functions.php';

$data = read_json();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (csrf_validate($token)) {
        $email = trim($_POST['email'] ?? '');
        $user = find_user_by_email($data, $email);
        if ($user) {
            $resetToken = bin2hex(random_bytes(16));
            $data['password_resets'][] = [
                'token' => $resetToken,
                'user_id' => $user['id'],
                'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour'))
            ];
            write_json($data);
            $link = (isset($_SERVER['HTTPS'])? 'https':'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/reset.php?token=' . $resetToken;
            send_mail_stub($user['email'], 'Password Reset', "Hello " . ($user['jina'] ?? '') . "\nVisit: $link");
        }
        $message = 'Kama akaunti ipo, tume tuma maelekezo kwenye barua pepe.';
    } else {
        $message = 'Hali ya usalama haipo sahihi. Jaribu tena.';
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sahau Nenosiri - GoalPesa</title>
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
    <h2>Rejesha Nenosiri</h2>
    <?php if ($message): ?><p class="note"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <form method="POST">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <label>Barua Pepe</label>
      <input type="email" name="email" required>
      <button type="submit" class="btn btn-primary">Tuma Kiungo</button>
    </form>
  </div>
</main>
</body>
</html>