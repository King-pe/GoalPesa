<?php

// Core helpers: JSON storage, auth, fee logic, mail stub, CSRF

// Compatibility: provide random_bytes fallback for older PHP environments
if (!function_exists('random_bytes_compat')) {
    function random_bytes_compat($length) {
        if (function_exists('random_bytes')) {
            return random_bytes($length);
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            $strong = false;
            $bytes = openssl_random_pseudo_bytes($length, $strong);
            if ($bytes !== false) return $bytes;
        }
        $bytes = '';
        for ($i = 0; $i < $length; $i++) {
            $bytes .= chr(mt_rand(0, 255));
        }
        return $bytes;
    }
}

// Fallback for hash_equals on older PHP
if (!function_exists('hash_equals')) {
    function hash_equals($known_string, $user_string) {
        if (!is_string($known_string) || !is_string($user_string)) {
            return false;
        }
        if (strlen($known_string) !== strlen($user_string)) {
            return false;
        }
        $res = 0;
        $len = strlen($known_string);
        for ($i = 0; $i < $len; $i++) {
            $res |= ord($known_string[$i]) ^ ord($user_string[$i]);
        }
        return $res === 0;
    }
}

const STORAGE_FILE = __DIR__ . '/../storage/data.json';

function ensure_storage() {
    $dir = dirname(STORAGE_FILE);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    if (!file_exists(STORAGE_FILE)) {
        $initial = [
            'users' => [],
            'posts' => [],
            'payouts' => [],
            'password_resets' => []
        ];
        // seed default admin
        $initial['users'][] = [
            'id' => bin2hex(random_bytes_compat(8)),
            'jina' => 'Admin',
            'email' => 'admin@goalpesa.local',
            'password' => password_hash('admin12345', PASSWORD_DEFAULT),
            'role' => 'admin',
            'jumla_uwekezaji' => 0,
            'makato' => 0,
            'payout_period' => 0,
            'payout_date' => null,
            'status' => 'active'
        ];
        @file_put_contents(STORAGE_FILE, json_encode($initial, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }
}

function read_json() {
    ensure_storage();
    $json = file_get_contents(STORAGE_FILE);
    if ($json === false || $json === '') {
        return [
            'users' => [],
            'posts' => [],
            'payouts' => [],
            'password_resets' => []
        ];
    }
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function write_json($data) {
    ensure_storage();
    @file_put_contents(STORAGE_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function generate_id() {
    return bin2hex(random_bytes_compat(8));
}

function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

function current_user($data) {
    if (!isset($_SESSION['user_id'])) return null;
    foreach ($data['users'] as $u) {
        if ($u['id'] === $_SESSION['user_id']) return $u;
    }
    return null;
}

function find_user_by_email($data, $email) {
    foreach ($data['users'] as $u) {
        if (strcasecmp($u['email'] ?? '', $email) === 0) return $u;
    }
    return null;
}

function update_user(&$data, $user) {
    foreach ($data['users'] as $i => $u) {
        if ($u['id'] === $user['id']) {
            $data['users'][$i] = $user;
            return;
        }
    }
    $data['users'][] = $user;
}

function is_admin($user) {
    return ($user['role'] ?? 'user') === 'admin';
}

function compute_fee_percentage($months, $amount) {
    if ($months <= 3) return 0.04;
    if ($months <= 6) return 0.03;
    return 0.02;
}

function compute_early_withdrawal_fee() {
    return 0.06;
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes_compat(16));
    }
    return $_SESSION['csrf'];
}

function csrf_validate($token) {
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function send_mail_stub($to, $subject, $body) {
    // Replace with real mailer later
    $logDir = __DIR__ . '/../storage/mails';
    if (!is_dir($logDir)) mkdir($logDir, 0777, true);
    $fname = $logDir . '/' . date('Ymd_His') . '_' . preg_replace('/[^a-z0-9]+/i','_', $to) . '.txt';
    return (bool)file_put_contents($fname, "TO: $to\nSUBJECT: $subject\n\n$body");
}

?>