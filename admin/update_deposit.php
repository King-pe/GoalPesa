<?php
require __DIR__ . '/../config/functions.php';
gp_require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /admin/');
  exit;
}

if (!gp_verify_csrf($_POST['csrf'] ?? '')) {
  header('Location: /admin/?err=csrf');
  exit;
}

$id = (string)($_POST['id'] ?? '');
$action = (string)($_POST['action'] ?? '');

try {
  gp_admin_process_deposit($id, $action);
  header('Location: /admin/?ok=1');
  exit;
} catch (Throwable $e) {
  header('Location: /admin/?err=' . urlencode($e->getMessage()));
  exit;
}
?>
