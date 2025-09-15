<?php
session_start();
require_once __DIR__ . '/config/functions.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php?redirect=dashboard.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard.php'); exit; }

$data = read_json();
$user = current_user($data);
if (!$user) { header('Location: dashboard.php'); exit; }
if (!csrf_validate($_POST['csrf'] ?? '')) { header('Location: dashboard.php'); exit; }

$amount = (float)($user['jumla_uwekezaji'] ?? 0);
$penaltyPct = compute_early_withdrawal_fee();
$penalty = round($amount * $penaltyPct, 2);

$data['payouts'][] = [
    'id' => generate_id(),
    'kind' => 'cancel',
    'user_id' => $user['id'],
    'amount' => $amount,
    'fee' => $penalty,
    'status' => 'processed',
    'date' => date('Y-m-d H:i:s')
];

$user['jumla_uwekezaji'] = 0;
$user['makato'] = 0;
update_user($data, $user);
write_json($data);

header('Location: dashboard.php');
exit;