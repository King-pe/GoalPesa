<?php
session_start();
require_once __DIR__ . '/../config/functions.php';

$data = read_json();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf'] ?? '';
    if (!csrf_validate($token)) {
        $errors[] = 'Hali ya usalama haipo sahihi.';
    } else {
        $user = find_user_by_email($data, $email);
        if (!$user || !verify_password($password, $user['password'] ?? '') || !is_admin($user)) {
            $errors[] = 'Taarifa sio sahihi au si Admin.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $redirect = $_GET['redirect'] ?? '/admin/dashboard.php';
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
  <title>Admin Login - GoalPesa</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  <div class="form-container">
    <h2>Ingia - Admin</h2>
    <?php if ($errors): ?><div class="note"><?= htmlspecialchars(implode(' ', $errors)) ?></div><?php endif; ?>
    <form method="POST">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <label>Email</label>
      <input type="email" name="email" required>
      <label>Nenosiri</label>
      <input type="password" name="password" required>
      <button type="submit" class="btn btn-primary">Ingia</button>
    </form>
    <p><a href="/index.php">Nyumbani</a></p>
  </div>
</body>
</html>

