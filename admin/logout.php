<?php
require __DIR__ . '/../config/functions.php';
gp_admin_logout();
header('Location: /admin/login.php');
exit;
