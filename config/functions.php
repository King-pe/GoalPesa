<?php

// Core helpers: JSON storage, auth, fee logic, mail stub, CSRF

declare(strict_types=1);

const STORAGE_FILE = __DIR__ . '/../storage/data.json';

function ensure_storage(): void {
    $dir = dirname(STORAGE_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
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
            'id' => bin2hex(random_bytes(8)),
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
        file_put_contents(STORAGE_FILE, json_encode($initial, JSON_PRETTY_PRINT));
    }
}

function read_json(): array {
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

function write_json(array $data): void {
    ensure_storage();
    file_put_contents(STORAGE_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

function generate_id(): string {
    return bin2hex(random_bytes(8));
}

function hash_password(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

function current_user(array $data): ?array {
    if (!isset($_SESSION['user_id'])) return null;
    foreach ($data['users'] as $u) {
        if ($u['id'] === $_SESSION['user_id']) return $u;
    }
    return null;
}

function find_user_by_email(array $data, string $email): ?array {
    foreach ($data['users'] as $u) {
        if (strcasecmp($u['email'] ?? '', $email) === 0) return $u;
    }
    return null;
}

function update_user(array &$data, array $user): void {
    foreach ($data['users'] as $i => $u) {
        if ($u['id'] === $user['id']) {
            $data['users'][$i] = $user;
            return;
        }
    }
    $data['users'][] = $user;
}

function is_admin(array $user): bool {
    return ($user['role'] ?? 'user') === 'admin';
}

function compute_fee_percentage(int $months, float $amount): float {
    if ($months <= 3) return 0.04;
    if ($months <= 6) return 0.03;
    return 0.02;
}

function compute_early_withdrawal_fee(): float {
    return 0.06;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_validate(string $token): bool {
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function send_mail_stub(string $to, string $subject, string $body): bool {
    // Replace with real mailer later
    $logDir = __DIR__ . '/../storage/mails';
    if (!is_dir($logDir)) mkdir($logDir, 0777, true);
    $fname = $logDir . '/' . date('Ymd_His') . '_' . preg_replace('/[^a-z0-9]+/i','_', $to) . '.txt';
    return (bool)file_put_contents($fname, "TO: $to\nSUBJECT: $subject\n\n$body");
}

?>