<?php
require_once 'config_fixed.php';

header('Content-Type: application/json');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'POST required']);
    exit();
}

$data     = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Username and password required']);
    exit();
}

$conn = get_db_connection_fixed();

$stmt = $conn->prepare(
    "SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1"
);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();
$conn->close();

if ($user && password_verify($password, $user['password_hash'])) {
    $secret  = getenv('TOKEN_SECRET') ?: 'change-me-in-production';
    $payload = $user['id'] . '|' . $user['username'] . '|' . time();
    $token   = $payload . '.' . hash_hmac('sha256', $payload, $secret);

    http_response_code(200);
    echo json_encode([
        'status'   => 'success',
        'message'  => 'Login successful',
        'token'    => $token,
        'username' => $user['username'],
        'role'     => $user['role']
    ]);
} else {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
}
?>
