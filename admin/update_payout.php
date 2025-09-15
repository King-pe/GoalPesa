<?php
session_start();
require_once __DIR__ . '/../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /admin/index.php'); exit; }
$data = read_json();
$admin = current_user($data);
if (!$admin || !is_admin($admin)) { header('Location: /login.php?redirect=/admin/index.php'); exit; }
if (!csrf_validate($_POST['csrf'] ?? '')) { header('Location: /admin/index.php'); exit; }

$id = $_POST['id'] ?? '';
$action = $_POST['action'] ?? '';

$payouts =& $data['payouts'];
$idx = null;
$record = null;
foreach ($payouts as $i => $p) {
    if (($p['id'] ?? '') === $id) { $idx = $i; $record = $p; break; }
}
if ($idx === null || !$record || ($record['kind'] ?? '') !== 'payout') { header('Location: /admin/index.php'); exit; }

// find the user
$userIndex = null;
foreach ($data['users'] as $i => $u) {
    if ($u['id'] === $record['user_id']) { $userIndex = $i; break; }
}

if ($action === 'approve') {
    $payouts[$idx]['status'] = 'approved';
    // deduct from user's balance when payout approved
    if ($userIndex !== null) {
        $current = (float)($data['users'][$userIndex]['jumla_uwekezaji'] ?? 0);
        $fee = (float)($record['fee'] ?? 0);
        $amount = (float)($record['amount'] ?? 0);
        $newBal = $current - $amount;
        if ($newBal < 0) $newBal = 0;
        $data['users'][$userIndex]['jumla_uwekezaji'] = $newBal;
        $data['users'][$userIndex]['makato'] = (float)($data['users'][$userIndex]['makato'] ?? 0) + $fee;
    }
} elseif ($action === 'reject') {
    $payouts[$idx]['status'] = 'rejected';
} elseif ($action === 'delete') {
    array_splice($payouts, $idx, 1);
}

write_json($data);
header('Location: /admin/index.php');
exit;
?>

