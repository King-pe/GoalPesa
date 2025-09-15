<?php
session_start();
require_once __DIR__ . '/../config/functions.php';

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
        if (!$user || !verify_password($password, $user['password']) || !is_admin($user)) {
            $errors[] = 'Taarifa za kuingia sio sahihi au huna ruhusa.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '/admin/dashboard.php';
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
  <title>Admin - Ingia</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="logo">
    <img src="/assets/images/logo.png" height="40" alt="GoalPesa">
    <span class="brand">Admin</span>
  </div>
  <nav>
    <a href="/index.php" class="btn">Nyumbani</a>
  </nav>
</header>
<main class="dashboard">
  <div class="card">
    <h2>Admin Ingia</h2>
    <?php if ($errors): ?><div class="note"><?= htmlspecialchars(implode(' ', $errors)) ?></div><?php endif; ?>
    <form method="POST">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <label>Barua Pepe</label>
      <input type="email" name="email" required>
      <label>Nenosiri</label>
      <input type="password" name="password" required>
      <button type="submit" class="btn btn-primary">Ingia</button>
    </form>
  </div>
</main>
</body>
</html>

