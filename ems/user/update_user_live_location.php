<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../../db_connect.php";

$session_id = $_POST['session_id'] ?? null;
$lat = $_POST['lat'] ?? null;
$lng = $_POST['lng'] ?? null;

if (!$session_id || $lat === null || $lng === null) {
    echo json_encode([
        "success" => false,
        "message" => "Missing session_id, lat or lng"
    ]);
    exit;
}

if (!is_numeric($lat) || !is_numeric($lng)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid lat/lng format"
    ]);
    exit;
}

$sql = "
    UPDATE emergency_sessions
    SET
        user_live_lat = $1,
        user_live_lng = $2,
        updated_at = NOW()
    WHERE id = $3
";

$result = pg_query_params($conn, $sql, [
    (float)$lat,
    (float)$lng,
    (int)$session_id
]);

if ($result && pg_affected_rows($result) > 0) {
    echo json_encode([
        "success" => true,
        "message" => "User live location updated"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Session not found"
    ]);
}

pg_close($conn);
exit;