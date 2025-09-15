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
if ($idx === null || !$record || ($record['kind'] ?? 'deposit') !== 'deposit') { header('Location: /admin/index.php'); exit; }

// find the user
$userIndex = null;
foreach ($data['users'] as $i => $u) {
    if ($u['id'] === $record['user_id']) { $userIndex = $i; break; }
}

if ($action === 'approve') {
    $payouts[$idx]['status'] = 'approved';
    // add money to user's jumla_uwekezaji
    if ($userIndex !== null) {
        $data['users'][$userIndex]['jumla_uwekezaji'] = (float)($data['users'][$userIndex]['jumla_uwekezaji'] ?? 0) + (float)$record['amount'];
    }
    // notify user
    if ($userIndex !== null) {
        $to = (string)($data['users'][$userIndex]['email'] ?? '');
        if ($to !== '') {
            $subject = 'GoalPesa: Muamala wako umethibitishwa';
            $body = "Habari,\n\nMuamala wako wa deposit (Txn: " . ($record['transaction_no'] ?? '') . ") kiasi " . number_format((float)$record['amount']) . " TZS umethibitishwa.\n\nAsante kwa kuwekeza na GoalPesa.";
            send_mail_stub($to, $subject, $body);
        }
    }
} elseif ($action === 'reject') {
    $payouts[$idx]['status'] = 'rejected';
    // rejected does not change balance
    if ($userIndex !== null) {
        $to = (string)($data['users'][$userIndex]['email'] ?? '');
        if ($to !== '') {
            $subject = 'GoalPesa: Muamala wako umekataliwa';
            $body = "Habari,\n\nMuamala wako wa deposit (Txn: " . ($record['transaction_no'] ?? '') . ") kiasi " . number_format((float)$record['amount']) . " TZS umekataliwa. Tafadhali hakiki taarifa na ujaribu tena.";
            send_mail_stub($to, $subject, $body);
        }
    }
} elseif ($action === 'delete') {
    // deletion should revert only the specific fake amount effect per spec:
    // If it was previously approved we need to subtract it; otherwise leave user balance unchanged.
    $approved = ($record['status'] ?? '') === 'approved';
    if ($approved && $userIndex !== null) {
        $current = (float)($data['users'][$userIndex]['jumla_uwekezaji'] ?? 0);
        $newBal = $current - (float)$record['amount'];
        if ($newBal < 0) $newBal = 0; // never negative
        $data['users'][$userIndex]['jumla_uwekezaji'] = $newBal;
    }
    array_splice($payouts, $idx, 1);
    // notify user about deletion as fake/invalid
    if ($userIndex !== null) {
        $to = (string)($data['users'][$userIndex]['email'] ?? '');
        if ($to !== '') {
            $subject = 'GoalPesa: Muamala wako umefutwa';
            $body = "Habari,\n\nMuamala wako wa deposit (Txn: " . ($record['transaction_no'] ?? '') . ") kiasi " . number_format((float)$record['amount']) . " TZS umefutwa kwa kuwa taarifa si sahihi. Tafadhali tuma muamala sahihi na uwasilishe ushahidi sahihi.";
            send_mail_stub($to, $subject, $body);
        }
    }
}

write_json($data);
header('Location: /admin/index.php');
exit;