<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header("Content-Type: application/json; charset=utf-8");

require_once("../../db_connect.php");

if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "DB connect failed"
    ]);
    exit;
}

$hospital_id = $_POST["hospital_id"] ?? null;
$user_id     = $_POST["user_id"] ?? null;
$lat         = $_POST["lat"] ?? null;
$lng         = $_POST["lng"] ?? null;

error_log("POST DATA: " . json_encode($_POST));

if (!$hospital_id || !$user_id || !$lat || !$lng) {
    echo json_encode([
        "success" => false,
        "message" => "ข้อมูลไม่ครบ"
    ]);
    exit;
}

$sql = "
INSERT INTO emergency_sessions
(
  hospital_id,
  user_id,
  user_init_lat,
  user_init_lng,
  user_live_lat,
  user_live_lng,
  status,
  created_at,
  updated_at
)
VALUES
($1,$2,$3,$4,$3,$4,'pending',NOW(),NOW())
RETURNING id
";

$res = pg_query_params($conn, $sql, [
    (int)$hospital_id,
    (int)$user_id,
    (float)$lat,
    (float)$lng
]);

if ($res && pg_num_rows($res) > 0) {
    $row = pg_fetch_assoc($res);

    echo json_encode([
        "success" => true,
        "session_id" => (int)$row["id"]
    ]);
    exit;
}

$error = pg_last_error($conn);

echo json_encode([
    "success" => false,
    "message" => "สร้างเคสไม่ได้",
    "error" => $error
]);

exit;