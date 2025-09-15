<?php
require __DIR__ . '/config/functions.php';
gp_start_session();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!gp_verify_csrf($_POST['csrf'] ?? '')) {
        $error = 'CSRF kosa, jaribu tena.';
    } else {
        $name = gp_sanitize($_POST['name'] ?? '');
        $pin = trim($_POST['pin'] ?? '');
        try {
            $user = gp_create_user($name, $pin);
            gp_login_user($user);
            header('Location: /dashboard.php');
            exit;
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
    <title>Jisajili - GoalPesa</title>
    <link rel="stylesheet" href="/assets/css/style.css">
  </head>
  <body>
    <main class="container" style="padding:40px 0">
      <div class="card" style="max-width:520px;margin:0 auto">
        <h2>Jisajili</h2>
        <?php if ($error): ?><p class="muted" style="color:#fca5a5"><?= gp_sanitize($error) ?></p><?php endif; ?>
        <form method="post" class="grid">
          <input type="hidden" name="csrf" value="<?= gp_csrf_token() ?>">
          <div>
            <label>Jina kamili</label>
            <input required name="name" placeholder="Mf. Peter Joram">
          </div>
          <div>
            <label>PIN (namba ya siri)</label>
            <input required name="pin" type="password" minlength="4" maxlength="32" placeholder="Weka PIN">
          </div>
          <div class="row">
            <button class="btn primary" type="submit">Unda akaunti</button>
            <a class="btn" href="/login.php">Tayari na akaunti?</a>
          </div>
        </form>
      </div>
    </main>
  </body>
  </html>
<?php
session_start();
require_once __DIR__ . '/config/functions.php';

$data = read_json();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!csrf_validate($token)) {
        $errors[] = 'Hali ya usalama haipo sahihi. Jaribu tena.';
    } else {
        $jina = trim($_POST['jina'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $period = (int)($_POST['payout_period'] ?? 0);

        if ($jina === '' || $email === '' || $password === '') $errors[] = 'Jaza taarifa zote.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Barua pepe sio sahihi.';
        if ($period <= 0) $errors[] = 'Chagua kipindi cha payout.';
        if (find_user_by_email($data, $email)) $errors[] = 'Akaunti tayari ipo.';

        if (!$errors) {
            $user = [
                'id' => generate_id(),
                'jina' => $jina,
                'email' => strtolower($email),
                'password' => hash_password($password),
                'role' => 'user',
                'jumla_uwekezaji' => 0,
                'makato' => 0,
                'payout_period' => $period,
                'payout_date' => date('Y-m-d', strtotime('+' . $period . ' months')),
                'status' => 'active'
            ];
            $data['users'][] = $user;
            write_json($data);
            $_SESSION['user_id'] = $user['id'];
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Jisajili - GoalPesa</title>
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
    <h2>Jisajili</h2>
    <?php if ($errors): ?><div class="note"><?= htmlspecialchars(implode(' ', $errors)) ?></div><?php endif; ?>
    <form method="POST">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <label>Jina Kamili</label>
      <input type="text" name="jina" required>

      <label>Barua Pepe</label>
      <input type="email" name="email" required>

      <label>Nenosiri</label>
      <input type="password" name="password" required>

      <label>Kipindi cha Payout (miezi)</label>
      <input type="number" name="payout_period" min="1" max="36" required>

      <button type="submit" class="btn btn-primary">Sajili</button>
    </form>
  </div>
</main>
</body>
</html>