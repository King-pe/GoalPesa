<?php
require __DIR__ . '/config/functions.php';
gp_logout_user();
header('Location: /');
exit;