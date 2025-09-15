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