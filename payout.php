<?php
session_start();
require_once __DIR__ . '/config/functions.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php?redirect=dashboard.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard.php'); exit; }

$data = read_json();
$user = current_user($data);
if (!$user) { header('Location: dashboard.php'); exit; }

if (!csrf_validate($_POST['csrf'] ?? '')) { header('Location: dashboard.php'); exit; }

$today = date('Y-m-d');
// Allow payout anytime per new requirement

$amount = (float)($user['jumla_uwekezaji'] ?? 0);
$months = (int)($user['payout_period'] ?? 0);
$feePct = compute_fee_percentage($months, $amount);
$fee = round($amount * $feePct, 2);
$net = max(0, round($amount - $fee, 2));

$data['payouts'][] = [
    'id' => generate_id(),
    'kind' => 'payout',
    'user_id' => $user['id'],
    'amount' => $amount,
    'fee' => $fee,
    'net_amount' => $net,
    'payer_name' => $user['jina'] ?? '',
    'status' => 'pending',
    'date' => date('Y-m-d H:i:s')
];

// Do not reset balance yet; wait for admin approval
write_json($data);

header('Location: dashboard.php');
exit;