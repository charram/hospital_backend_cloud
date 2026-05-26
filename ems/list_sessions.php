<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

$session_id = $_GET["session_id"] ?? null;

if (!$session_id || !is_numeric($session_id)) {
    echo json_encode([
        "success" => false,
        "message" => "session_id required"
    ]);
    exit;
}

$sql = "
SELECT
    id,
    user_id,
    status,
    ems_id,
    ambulance_code,
    user_init_lat,
    user_init_lng,
    user_live_lat,
    user_live_lng,
    ambulance_live_lat,
    ambulance_live_lng,
    created_at,
    updated_at
FROM emergency_sessions
WHERE id = $1
LIMIT 1
";

$res = pg_query_params($conn, $sql, [$session_id]);
$row = pg_fetch_assoc($res);

if (!$row) {
    echo json_encode([
        "success" => false,
        "message" => "Not found"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "data" => [
        "session_id" => (int)$row["id"],
        "user_id" => $row["user_id"] !== null ? (int)$row["user_id"] : null,
        "ems_id" => $row["ems_id"] !== null ? (int)$row["ems_id"] : null,
        "ambulance_code" => $row["ambulance_code"],
        "status" => $row["status"],
        "user_init_lat" => $row["user_init_lat"] !== null ? (float)$row["user_init_lat"] : null,
        "user_init_lng" => $row["user_init_lng"] !== null ? (float)$row["user_init_lng"] : null,
        "user_live_lat" => $row["user_live_lat"] !== null ? (float)$row["user_live_lat"] : null,
        "user_live_lng" => $row["user_live_lng"] !== null ? (float)$row["user_live_lng"] : null,
        "ambulance_live_lat" => $row["ambulance_live_lat"] !== null ? (float)$row["ambulance_live_lat"] : null,
        "ambulance_live_lng" => $row["ambulance_live_lng"] !== null ? (float)$row["ambulance_live_lng"] : null,
        "created_at" => $row["created_at"],
        "updated_at" => $row["updated_at"]
    ]
]);

pg_close($conn);
