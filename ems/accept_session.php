<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

// =======================
// รับค่าจาก client
// =======================
$session_id = $_POST['session_id'] ?? null;
$ems_id     = $_POST['ems_id'] ?? null;

if (!$session_id || !$ems_id) {
    echo json_encode([
        "success" => false,
        "message" => "session_id และ ems_id ต้องถูกส่งมา"
    ]);
    exit;
}

// =======================
// Logic:
// - session ต้องอยู่ในสถานะ assigned
// - EMS ถึงจะรับงานได้
// =======================
$sql = "
UPDATE emergency_sessions
SET 
    status = 'active',
    ems_id = $2,
    updated_at = NOW()
WHERE id = $1
  AND status = 'assigned'
RETURNING id, user_id, ems_id, status, updated_at
";

$result = pg_query_params($conn, $sql, [
    $session_id,
    $ems_id
]);

// =======================
// Response
// =======================
if ($result && pg_num_rows($result) > 0) {
    $row = pg_fetch_assoc($result);

    echo json_encode([
        "success" => true,
        "message" => "EMS รับงานสำเร็จ เริ่มปฏิบัติงาน",
        "data" => [
            "session_id" => (int)$row["id"],
            "user_id"    => (int)$row["user_id"],
            "ems_id"     => (int)$row["ems_id"],
            "status"     => $row["status"],
            "updated_at" => $row["updated_at"]
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Session นี้ไม่อยู่ในสถานะ assigned หรือถูก EMS คนอื่นรับไปแล้ว"
    ]);
}

pg_close($conn);
