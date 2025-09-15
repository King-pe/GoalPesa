<?php
// Temporary helper to generate a bcrypt hash for admin password
// Usage: visit /hash.php?pass=YourNewPassword
// Copy the output and paste into GP_ADMIN_PASS_HASH in config/functions.php, then DELETE this file.

declare(strict_types=1);

$pass = (string)($_GET['pass'] ?? '');
if ($pass === '') {
  echo 'Usage: /hash.php?pass=YourNewPassword';
  exit;
}
$hash = password_hash($pass, PASSWORD_DEFAULT);
header('Content-Type: text/plain');
echo $hash;
?>
