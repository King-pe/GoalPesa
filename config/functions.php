<?php
declare(strict_types=1);

date_default_timezone_set('Africa/Nairobi');

const GP_DATA_FILE = __DIR__ . '/../data.json';
const GP_APP_NAME = 'GoalPesa';

// Admin credentials
const GP_ADMIN_EMAIL = 'admin@goalpesa.local';
// For production, set a real hash here and remove the plain fallback in gp_admin_login
const GP_ADMIN_PASS_HASH = '';

function gp_start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        ]);
        session_start();
    }
}

function gp_csrf_token(): string {
    gp_start_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function gp_verify_csrf(string $token): bool {
    gp_start_session();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function gp_sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function gp_default_data(): array {
    return [
        'users' => [],
        'deposits' => [],
        'withdrawals' => [],
        'posts' => [
            [
                'title' => 'Jinsi ya Kuongeza Faida',
                'content' => 'Wekeza mapema na toa baada ya muda uliopangwa.'
            ]
        ]
    ];
}

function gp_load_data(): array {
    if (!file_exists(GP_DATA_FILE)) {
        $data = gp_default_data();
        gp_save_data($data);
        return $data;
    }
    $json = file_get_contents(GP_DATA_FILE);
    if ($json === false || $json === '') {
        $data = gp_default_data();
        gp_save_data($data);
        return $data;
    }
    $data = json_decode($json, true);
    if (!is_array($data)) {
        $data = gp_default_data();
    }
    foreach (['users','deposits','withdrawals','posts'] as $key) {
        if (!array_key_exists($key, $data)) {
            $data[$key] = [];
        }
    }
    return $data;
}

function gp_save_data(array $data): void {
    $dir = dirname(GP_DATA_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $tmp = GP_DATA_FILE . '.tmp';
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        throw new RuntimeException('Failed to encode data.json');
    }
    $fp = fopen($tmp, 'c');
    if ($fp === false) {
        throw new RuntimeException('Failed to open temp data file');
    }
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        throw new RuntimeException('Failed to acquire file lock');
    }
    ftruncate($fp, 0);
    fwrite($fp, $json);
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    rename($tmp, GP_DATA_FILE);
}

function gp_next_user_id(array $data): int {
    if (empty($data['users'])) {
        return 1;
    }
    $ids = array_map(static function($u){ return (int)$u['id']; }, $data['users']);
    return max($ids) + 1;
}

function gp_get_user_by_id(int $id): ?array {
    $data = gp_load_data();
    $key = (string)$id;
    return $data['users'][$key] ?? null;
}

function gp_get_user_by_name(string $name): ?array {
    $data = gp_load_data();
    foreach ($data['users'] as $user) {
        if (mb_strtolower($user['jina']) === mb_strtolower($name)) {
            return $user;
        }
    }
    return null;
}

function gp_create_user(string $name, string $pin): array {
    $name = trim($name);
    if ($name === '' || $pin === '') {
        throw new InvalidArgumentException('Jina na PIN vinahitajika.');
    }
    $data = gp_load_data();
    if (gp_get_user_by_name($name) !== null) {
        throw new RuntimeException('Mtumiaji tayari upo.');
    }
    $id = gp_next_user_id($data);
    $user = [
        'id' => $id,
        'jina' => $name,
        'balance' => 0,
        'initialDeposit' => 0,
        'pinHash' => password_hash($pin, PASSWORD_DEFAULT)
    ];
    $data['users'][(string)$id] = $user;
    gp_save_data($data);
    return $user;
}

function gp_verify_user_login(string $name, string $pin): ?array {
    $user = gp_get_user_by_name($name);
    if ($user === null) {
        return null;
    }
    if (!isset($user['pinHash']) || !password_verify($pin, $user['pinHash'])) {
        return null;
    }
    return $user;
}

function gp_login_user(array $user): void {
    gp_start_session();
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
}

function gp_logout_user(): void {
    gp_start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function gp_current_user(): ?array {
    gp_start_session();
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return gp_get_user_by_id((int)$_SESSION['user_id']);
}

function gp_require_login(): void {
    if (gp_current_user() === null) {
        header('Location: /login.php');
        exit;
    }
}

function gp_admin_login(string $email, string $password): bool {
    // Accept exact email and plain password 'admin123' for setup convenience
    // Also supports verifying against GP_ADMIN_PASS_HASH if provided
    $isEmailMatch = ($email === GP_ADMIN_EMAIL);
    $isPlainOk = ($password === 'admin123');
    $isHashOk = (!empty(GP_ADMIN_PASS_HASH) && password_verify($password, GP_ADMIN_PASS_HASH));
    if ($isEmailMatch && ($isPlainOk || $isHashOk)) {
        gp_start_session();
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        return true;
    }
    return false;
}

function gp_admin_logout(): void {
    gp_start_session();
    unset($_SESSION['admin']);
}

function gp_require_admin(): void {
    gp_start_session();
    if (empty($_SESSION['admin'])) {
        header('Location: /admin/login.php');
        exit;
    }
}

function gp_add_deposit(int $userId, float $amount): array {
    if ($amount <= 0) {
        throw new InvalidArgumentException('Kiasi lazima kiwe zaidi ya sifuri.');
    }
    $data = gp_load_data();
    $key = (string)$userId;
    if (!isset($data['users'][$key])) {
        throw new RuntimeException('Mtumiaji hajapatikana.');
    }
    $user = $data['users'][$key];
    $user['balance'] = (float)$user['balance'] + $amount;
    if ((float)$user['initialDeposit'] <= 0) {
        $user['initialDeposit'] = $amount;
    }
    $data['users'][$key] = $user;
    $deposit = [
        'user_id' => $userId,
        'jina' => $user['jina'],
        'amount' => $amount,
        'date' => date('Y-m-d H:i:s')
    ];
    $data['deposits'][] = $deposit;
    gp_save_data($data);
    return [$user, $deposit];
}

function gp_calculate_payout_fee(float $balance, float $initialDeposit): float {
    if ($balance <= 0) {
        return 0.0;
    }
    $rate = 0.06;
    if ($initialDeposit > 0 && $balance >= 3 * $initialDeposit) {
        $rate = 0.02;
    }
    return round($balance * $rate, 2);
}

function gp_perform_payout(int $userId): array {
    $data = gp_load_data();
    $key = (string)$userId;
    if (!isset($data['users'][$key])) {
        throw new RuntimeException('Mtumiaji hajapatikana.');
    }
    $user = $data['users'][$key];
    $balance = (float)($user['balance'] ?? 0);
    if ($balance <= 0) {
        throw new RuntimeException('Hakuna salio la kutolewa.');
    }
    $fee = gp_calculate_payout_fee($balance, (float)($user['initialDeposit'] ?? 0));
    $net = max($balance - $fee, 0);
    $withdrawal = [
        'user_id' => $userId,
        'jina' => $user['jina'],
        'amount' => $balance,
        'fee' => $fee,
        'after' => $net,
        'date' => date('Y-m-d H:i:s')
    ];
    $data['withdrawals'][] = $withdrawal;
    $user['balance'] = 0;
    $data['users'][$key] = $user;
    gp_save_data($data);
    return [$user, $withdrawal];
}

function gp_user_deposits(int $userId): array {
    $data = gp_load_data();
    return array_values(array_filter($data['deposits'], static fn($d) => (int)$d['user_id'] === $userId));
}

function gp_user_withdrawals(int $userId): array {
    $data = gp_load_data();
    return array_values(array_filter($data['withdrawals'], static fn($w) => (int)$w['user_id'] === $userId));
}

function gp_generate_id(): string {
    return bin2hex(random_bytes(8));
}

function gp_store_upload(array $file): string {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Picha haijapakiwa.');
    }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Aina ya picha si sahihi. Ruhusa: JPG, PNG, WEBP.');
    }
    $ext = $allowed[$mime];
    $dir = __DIR__ . '/../uploads';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
    $name = date('Ymd_His') . '_' . gp_generate_id() . '.' . $ext;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Imeshindwa kuhifadhi picha.');
    }
    return 'uploads/' . $name;
}

function gp_add_deposit_request(int $userId, float $amount, array $file): array {
    if ($amount <= 0) {
        throw new InvalidArgumentException('Kiasi lazima kiwe zaidi ya sifuri.');
    }
    $data = gp_load_data();
    $key = (string)$userId;
    if (!isset($data['users'][$key])) {
        throw new RuntimeException('Mtumiaji hajapatikana.');
    }
    $user = $data['users'][$key];
    $screenshot = gp_store_upload($file);
    $deposit = [
        'id' => gp_generate_id(),
        'user_id' => $userId,
        'jina' => $user['jina'],
        'amount' => $amount,
        'date' => date('Y-m-d H:i:s'),
        'status' => 'pending',
        'screenshot' => $screenshot
    ];
    $data['deposits'][] = $deposit;
    gp_save_data($data);
    return $deposit;
}

function gp_admin_process_deposit(string $depositId, string $action): array {
    $data = gp_load_data();
    $foundIndex = null;
    foreach ($data['deposits'] as $i => $d) {
        if (($d['id'] ?? '') === $depositId) { $foundIndex = $i; break; }
    }
    if ($foundIndex === null) {
        throw new RuntimeException('Deposit haikupatikana.');
    }
    $deposit = $data['deposits'][$foundIndex];
    if (($deposit['status'] ?? 'pending') !== 'pending') {
        throw new RuntimeException('Tayari imeshashughulikiwa.');
    }
    if ($action === 'approve') {
        $deposit['status'] = 'approved';
        $data['deposits'][$foundIndex] = $deposit;
        // credit user balance
        $key = (string)$deposit['user_id'];
        if (!isset($data['users'][$key])) {
            throw new RuntimeException('Mtumiaji hajapatikana.');
        }
        $user = $data['users'][$key];
        $user['balance'] = (float)($user['balance'] ?? 0) + (float)$deposit['amount'];
        if ((float)($user['initialDeposit'] ?? 0) <= 0) {
            $user['initialDeposit'] = (float)$deposit['amount'];
        }
        $data['users'][$key] = $user;
    } elseif ($action === 'reject') {
        $deposit['status'] = 'rejected';
        $data['deposits'][$foundIndex] = $deposit;
    } else {
        throw new InvalidArgumentException('Hatua batili.');
    }
    gp_save_data($data);
    return $deposit;
}

?>