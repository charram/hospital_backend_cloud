<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

// =======================
// รับค่าจาก client (hospital side)
// =======================
$hospital_id = $_POST["hospital_id"] ?? null;
$user_id     = $_POST["user_id"] ?? null;
$lat         = $_POST["lat"] ?? null;
$lng         = $_POST["lng"] ?? null;

if ($hospital_id === null || $lat === null || $lng === null)
 {
    echo json_encode([
        "success" => false,
        "message" => "hospital_id, lat, lng required"
    ]);
    exit;
}

// =======================
// สร้าง emergency session
// =======================
$sql = "
INSERT INTO emergency_sessions
(
    hospital_id,
    user_id,
    status,
    user_init_lat,
    user_init_lng,
    user_live_lat,
    user_live_lng,
    created_at,
    updated_at
)
VALUES
(
    $1,
    $2,
    'waiting',
    $3,
    $4,
    $3,
    $4,
    NOW(),
    NOW()
)
RETURNING id, created_at
";

$res = pg_query_params($conn, $sql, [
    $hospital_id,
    $user_id,
    $lat,
    $lng
]);

if (!$res) {
    echo json_encode([
        "success" => false,
        "message" => "DB error",
        "error"   => pg_last_error($conn)
    ]);
    exit;
}

$row = pg_fetch_assoc($res);

// =======================
// Response (ไม่ส่ง hospital_id)
// =======================
echo json_encode([
    "success"    => true,
    "session_id" => (int)$row["id"],
    "created_at"=> $row["created_at"]
]);

pg_close($conn);
