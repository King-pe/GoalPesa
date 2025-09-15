<?php
session_start();
require_once __DIR__ . '/../config/functions.php';

$data = read_json();
$admin = current_user($data);
if (!$admin || !is_admin($admin)) { header('Location: /login.php?redirect=/admin/posts.php'); exit; }

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf'] ?? '')) { header('Location: /admin/posts.php'); exit; }
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        if ($title === '' || $content === '') {
            $message = 'Jaza title na content.';
        } else {
            $data['posts'][] = [
                'id' => generate_id(),
                'title' => $title,
                'content' => $content,
                'date' => date('Y-m-d H:i:s')
            ];
            write_json($data);
            $message = 'Post imeongezwa.';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if ($id !== '') {
            foreach (($data['posts'] ?? []) as $i => $p) {
                if (($p['id'] ?? '') === $id) { array_splice($data['posts'], $i, 1); break; }
            }
            write_json($data);
            $message = 'Post imefutwa.';
        }
    }
}

$posts = $data['posts'] ?? [];
?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Posts</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="navbar">
  <div class="logo">
    <img src="/assets/images/logo.png" height="40" alt="GoalPesa">
    <span class="brand">Admin</span>
  </div>
  <nav>
    <a href="/admin/index.php" class="btn">Dashboard</a>
    <a href="/logout.php" class="btn">Toka</a>
  </nav>
</header>
<main class="dashboard">
  <div class="card">
    <h2>Ongeza Post</h2>
    <?php if ($message): ?><p class="note"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <form method="POST">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
      <input type="hidden" name="action" value="create">
      <label>Title</label>
      <input type="text" name="title" required>
      <label>Content</label>
      <textarea name="content" rows="5" required></textarea>
      <button class="btn btn-primary" type="submit">Hifadhi</button>
    </form>
  </div>

  <div class="card">
    <h2>Orodha ya Posts</h2>
    <?php if (!$posts): ?>
      <p>Hakuna post.</p>
    <?php else: ?>
      <?php foreach ($posts as $p): ?>
        <div class="card" style="margin: 10px 0;">
          <h3 style="margin:0; color:#273c75;"><?= htmlspecialchars($p['title']) ?></h3>
          <p><?= nl2br(htmlspecialchars($p['content'])) ?></p>
          <form method="POST" onsubmit="return confirm('Futa post hii?')" style="display:inline;">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= htmlspecialchars($p['id'] ?? '') ?>">
            <button class="btn btn-danger" type="submit">Futa</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>
</body>
</html>

