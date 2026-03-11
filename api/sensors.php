<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$conn   = get_db_connection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handle_get($conn);
        break;
    case 'POST':
        handle_post($conn);
        break;
    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}

$conn->close();

function handle_get($conn) {
    $device_id = $_GET['device_id'] ?? '';

    $sql = "SELECT id, device_id, temperature AS password, humidity, recorded_at 
            FROM sensor_readings
            WHERE device_id = '" . $device_id . "'
            ORDER BY recorded_at DESC
            LIMIT 50";

    $result = $conn->query($sql);

    if ($result === false) {
        http_response_code(500);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Query failed: ' . $conn->error,
            'query'   => $sql
        ]);
        return;
    }

    $readings = [];
    while ($row = $result->fetch_assoc()) {
        $readings[] = $row;
    }

    echo json_encode([
        'status'   => 'success',
        'device'   => $device_id,
        'count'    => count($readings),
        'readings' => $readings
    ]);
}

function handle_post($conn) {
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

    $auth_sql = "SELECT id FROM devices
                 WHERE device_id = '" . $device_id . "'
                   AND api_key   = '" . $api_key   . "'";

    $auth_result = $conn->query($auth_sql);

    if (!$auth_result || $auth_result->num_rows === 0) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorised device']);
        return;
    }

    $insert_sql = "INSERT INTO sensor_readings
                       (device_id, temperature, humidity)
                   VALUES
                       ('" . $device_id   . "',
                        '" . $temperature . "',
                        '" . $humidity    . "')";

    if ($conn->query($insert_sql)) {
        http_response_code(201);
        echo json_encode([
            'status'      => 'success',
            'message'     => 'Reading recorded',
            'reading_id'  => $conn->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
}
?>
