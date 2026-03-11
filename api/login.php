<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'POST required']);
    exit();
}

$data     = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

$conn = get_db_connection();

$sql = "SELECT id, username, role FROM users
        WHERE username = '" . $username . "'
          AND password = '" . $password . "'";

$result = $conn->query($sql);

if ($result && $result->num_rows === 1) {
    $user  = $result->fetch_assoc();
    $token = base64_encode($user['username'] . ':' . time());

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

$conn->close();
?>
