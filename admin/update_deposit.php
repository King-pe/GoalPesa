<?php
require __DIR__ . '/../config/functions.php';
gp_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /admin/dashboard.php');
  exit;
}

if (!gp_verify_csrf($_POST['csrf'] ?? '')) {
  header('Location: /admin/dashboard.php?err=csrf');
  exit;
}

$id = (string)($_POST['id'] ?? '');
$action = (string)($_POST['action'] ?? '');

try {
  gp_admin_process_deposit($id, $action);
  header('Location: /admin/dashboard.php?ok=1');
  exit;
} catch (Throwable $e) {
  header('Location: /admin/dashboard.php?err=' . urlencode($e->getMessage()));
  exit;
}
?>
