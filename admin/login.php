<?php
require __DIR__ . '/../config/functions.php';
gp_start_session();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!gp_verify_csrf($_POST['csrf'] ?? '')) {
        $error = 'CSRF kosa, jaribu tena.';
    } else {
        $u = trim($_POST['username'] ?? '');
        $p = (string)($_POST['password'] ?? '');
        if (gp_admin_login($u, $p)) {
            header('Location: /admin/dashboard.php');
            exit;
        }
        $error = 'Taarifa si sahihi.';
    }
}
?>
<!doctype html>
<html lang="sw">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - GoalPesa</title>
    <link rel="stylesheet" href="/assets/css/style.css">
  </head>
  <body>
    <main class="container" style="padding:40px 0">
      <div class="card" style="max-width:520px;margin:0 auto">
        <h2>Admin Login</h2>
        <?php if ($error): ?><p style="color:#fca5a5"><?= gp_sanitize($error) ?></p><?php endif; ?>
        <form method="post" class="grid">
          <input type="hidden" name="csrf" value="<?= gp_csrf_token() ?>">
          <div>
            <label>Username</label>
            <input required name="username" placeholder="admin">
          </div>
          <div>
            <label>Password</label>
            <input required name="password" type="password" placeholder="Password">
          </div>
          <div class="row">
            <button class="btn primary" type="submit">Ingia</button>
            <a class="btn" href="/">Nyumbani</a>
          </div>
        </form>
      </div>
    </main>
  </body>
  </html>
