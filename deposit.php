<?php
require __DIR__ . '/config/functions.php';
gp_require_login();
$user = gp_current_user();
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!gp_verify_csrf($_POST['csrf'] ?? '')) {
        $error = 'CSRF kosa, jaribu tena.';
    } else {
        $amount = (float)($_POST['amount'] ?? 0);
        try {
            if (!isset($_FILES['screenshot'])) {
                throw new RuntimeException('Weka picha ya muamala.');
            }
            $dep = gp_add_deposit_request((int)$user['id'], $amount, $_FILES['screenshot']);
            $message = 'Ombi la amana limetumwa (KES ' . number_format((float)$dep['amount'],2) . '). Subiri uhakiki wa admin.';
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
    <title>Weka Fedha - GoalPesa</title>
    <link rel="stylesheet" href="/assets/css/style.css">
  </head>
  <body>
    <main class="container" style="padding:40px 0">
      <div class="card" style="max-width:520px;margin:0 auto">
        <h2>Weka fedha</h2>
        <?php if ($message): ?><p style="color:#86efac"><?= gp_sanitize($message) ?></p><?php endif; ?>
        <?php if ($error): ?><p style="color:#fca5a5"><?= gp_sanitize($error) ?></p><?php endif; ?>
        <form method="post" class="grid" enctype="multipart/form-data">
          <input type="hidden" name="csrf" value="<?= gp_csrf_token() ?>">
          <div>
            <label>Kiasi (KES)</label>
            <input required name="amount" type="number" min="1" step="0.01" placeholder="Mf. 5000">
          </div>
          <div>
            <label>Picha ya muamala (JPG/PNG/WEBP)</label>
            <input required name="screenshot" type="file" accept="image/*">
          </div>
          <div class="row">
            <button class="btn primary" type="submit">Thibitisha Amana</button>
            <a class="btn" href="/dashboard.php">Rudi</a>
          </div>
        </form>
      </div>
    </main>
  </body>
  </html>
