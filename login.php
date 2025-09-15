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
        $user = gp_verify_user_login($name, $pin);
        if ($user) {
            gp_login_user($user);
            header('Location: /dashboard.php');
            exit;
        } else {
            $error = 'Jina au PIN si sahihi.';
        }
    }
}
?>
<!doctype html>
<html lang="sw">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingia - GoalPesa</title>
    <link rel="stylesheet" href="/assets/css/style.css">
  </head>
  <body>
    <main class="container" style="padding:40px 0">
      <div class="card" style="max-width:520px;margin:0 auto">
        <h2>Ingia</h2>
        <?php if ($error): ?><p class="muted" style="color:#fca5a5"><?= gp_sanitize($error) ?></p><?php endif; ?>
        <form method="post" class="grid">
          <input type="hidden" name="csrf" value="<?= gp_csrf_token() ?>">
          <div>
            <label>Jina kamili</label>
            <input required name="name" placeholder="Mf. Peter Joram">
          </div>
          <div>
            <label>PIN</label>
            <input required name="pin" type="password" minlength="4" maxlength="32" placeholder="Weka PIN">
          </div>
          <div class="row">
            <button class="btn primary" type="submit">Ingia</button>
            <a class="btn" href="/register.php">Sina akaunti</a>
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!csrf_validate($token)) {
        $errors[] = 'Hali ya usalama haipo sahihi. Jaribu tena.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $user = find_user_by_email($data, $email);
        if (!$user || !verify_password($password, $user['password'])) {
            $errors[] = 'Taarifa za kuingia sio sahihi.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'dashboard.php';
            header('Location: ' . $redirect);
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
  <title>Ingia - GoalPesa</title>
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
    <h2>Ingia</h2>
    <?php if ($errors): ?><div class="note"><?= htmlspecialchars(implode(' ', $errors)) ?></div><?php endif; ?>
    <form method="POST">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <label>Barua Pepe</label>
      <input type="email" name="email" required>
      <label>Nenosiri</label>
      <input type="password" name="password" required>
      <button type="submit" class="btn btn-primary">Ingia</button>
    </form>
    <p><a href="forgot.php">Umesahau nenosiri?</a></p>
  </div>
</main>
</body>
</html>