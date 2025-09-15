<?php
require __DIR__ . '/config/functions.php';
gp_logout_user();
header('Location: /');
exit;
<?php
session_start();
session_unset();
session_destroy();
header('Location: index.php');
exit;