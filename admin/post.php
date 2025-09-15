<?php
session_start();
require_once __DIR__ . '/../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /admin/dashboard.php'); exit; }
$data = read_json();
$admin = current_user($data);
if (!$admin || !is_admin($admin)) { header('Location: /admin/login.php?redirect=/admin/dashboard.php'); exit; }
if (!csrf_validate($_POST['csrf'] ?? '')) { header('Location: /admin/dashboard.php'); exit; }

$id = $_POST['id'] ?? '';
$action = $_POST['action'] ?? '';

$payouts =& $data['payouts'];
$idx = null;
$record = null;
foreach ($payouts as $i => $p) {
    if (($p['id'] ?? '') === $id) { $idx = $i; $record = $p; break; }
}
if ($idx === null || !$record) { header('Location: /admin/dashboard.php'); exit; }

// Resolve action by kind
$kind = $record['kind'] ?? 'deposit';

if ($kind === 'deposit') {
    // find the user
    $userIndex = null;
    foreach ($data['users'] as $i => $u) {
        if ($u['id'] === $record['user_id']) { $userIndex = $i; break; }
    }

    if ($action === 'approve_deposit') {
        $payouts[$idx]['status'] = 'approved';
        if ($userIndex !== null) {
            $data['users'][$userIndex]['jumla_uwekezaji'] = (float)($data['users'][$userIndex]['jumla_uwekezaji'] ?? 0) + (float)$record['amount'];
        }
    } elseif ($action === 'reject_deposit') {
        $payouts[$idx]['status'] = 'rejected';
    } elseif ($action === 'delete_deposit') {
        $approved = ($record['status'] ?? '') === 'approved';
        if ($approved && $userIndex !== null) {
            $current = (float)($data['users'][$userIndex]['jumla_uwekezaji'] ?? 0);
            $newBal = $current - (float)$record['amount'];
            if ($newBal < 0) $newBal = 0;
            $data['users'][$userIndex]['jumla_uwekezaji'] = $newBal;
        }
        array_splice($payouts, $idx, 1);
    }
} elseif ($kind === 'payout') {
    // find the user
    $userIndex = null;
    foreach ($data['users'] as $i => $u) {
        if ($u['id'] === $record['user_id']) { $userIndex = $i; break; }
    }

    if ($action === 'approve_payout') {
        $payouts[$idx]['status'] = 'approved';
        if ($userIndex !== null) {
            $current = (float)($data['users'][$userIndex]['jumla_uwekezaji'] ?? 0);
            $fee = (float)($record['fee'] ?? 0);
            $amount = (float)($record['amount'] ?? 0);
            $newBal = $current - $amount;
            if ($newBal < 0) $newBal = 0;
            $data['users'][$userIndex]['jumla_uwekezaji'] = $newBal;
            $data['users'][$userIndex]['makato'] = (float)($data['users'][$userIndex]['makato'] ?? 0) + $fee;
        }
    } elseif ($action === 'reject_payout') {
        $payouts[$idx]['status'] = 'rejected';
    } elseif ($action === 'delete_payout') {
        array_splice($payouts, $idx, 1);
    }
}

write_json($data);
header('Location: /admin/dashboard.php');
exit;
?>

