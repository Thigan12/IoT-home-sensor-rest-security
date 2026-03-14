<?php
require_once 'config_fixed.php';


header('Content-Type: application/json');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Access-Control-Allow-Origin: https://your-dashboard-domain.com');

$conn   = get_db_connection_fixed();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handle_get_fixed($conn);
        break;
    case 'POST':
        handle_post_fixed($conn);
        break;
    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}

$conn->close();

function handle_get_fixed($conn) {
    $device_id = $_GET['device_id'] ?? '';

    if (!preg_match('/^[A-Z0-9_]{1,50}$/', $device_id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid device ID format']);
        return;
    }

    $stmt = $conn->prepare(
        "SELECT id, device_id, temperature, humidity, recorded_at
         FROM sensor_readings
         WHERE device_id = ?
         ORDER BY recorded_at DESC
         LIMIT 50"
    );

    $stmt->bind_param('s', $device_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $readings = [];
    while ($row = $result->fetch_assoc()) {
        $readings[] = $row;
    }

    $stmt->close();

    echo json_encode([
        'status'   => 'success',
        'device'   => $device_id,
        'count'    => count($readings),
        'readings' => $readings
    ]);
}

function handle_post_fixed($conn) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON body']);
        return;
    }

    $device_id   = $data['device_id']   ?? '';
    $api_key     = $data['api_key']      ?? '';
    $temperature = $data['temperature'] ?? null;
    $humidity    = $data['humidity']    ?? null;

    if (!preg_match('/^[A-Z0-9_]{1,50}$/', $device_id)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid device ID']);
        return;
    }

    if (!is_numeric($temperature) || $temperature < -50 || $temperature > 100) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Temperature out of valid range']);
        return;
    }

    if (!is_numeric($humidity) || $humidity < 0 || $humidity > 100) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Humidity out of valid range']);
        return;
    }

    $api_key_hash = hash('sha256', $api_key);

    $stmt = $conn->prepare(
        "SELECT id FROM devices WHERE device_id = ? AND api_key_hash = ?"
    );
    $stmt->bind_param('ss', $device_id, $api_key_hash);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorised']);
        return;
    }
    $stmt->close();

    $temperature = round((float)$temperature, 2);
    $humidity    = round((float)$humidity,    2);

    $insert = $conn->prepare(
        "INSERT INTO sensor_readings (device_id, temperature, humidity)
         VALUES (?, ?, ?)"
    );
    $insert->bind_param('sdd', $device_id, $temperature, $humidity);

    if ($insert->execute()) {
        http_response_code(201);
        echo json_encode([
            'status'     => 'success',
            'message'    => 'Reading recorded',
            'reading_id' => $conn->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Server error']);
    }

    $insert->close();
}
?>
