<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

$session_id  = $_POST["session_id"] ?? null;
$hospital_id = $_POST["hospital_id"] ?? null;
$ems_id      = $_POST["ems_id"] ?? null;

if (!$session_id || !$hospital_id || !$ems_id) {
    echo json_encode(["success" => false, "message" => "missing params"]);
    exit;
}

$session_id  = (int)$session_id;
$hospital_id = (int)$hospital_id;
$ems_id      = (int)$ems_id;

// ====================== เช็คเพิ่มเติม (ป้องกันข้อผิดพลาด) ======================
$chk = pg_query_params(
    $conn,
    "SELECT is_active FROM ems_accounts WHERE id = $1 LIMIT 1",
    [$ems_id]
);
if (pg_num_rows($chk) === 0 || pg_fetch_result($chk, 0, 0) !== 't') {
    echo json_encode(["success" => false, "message" => "EMS ไม่ถูกต้องหรือไม่ได้เปิดใช้งาน"]);
    exit;
}

// ====================== อัปเดตเคส ======================
$sql = "
UPDATE emergency_sessions
SET
    ems_id       = $1,
    status       = 'assigned',
    assigned_at  = NOW(),           -- แนะนำเพิ่มคอลัมน์นี้ในตาราง
    updated_at   = NOW()
WHERE id = $2
  AND hospital_id = $3
  AND status = 'waiting'
RETURNING id, ems_id, status, assigned_at
";

$res = pg_query_params($conn, $sql, [$ems_id, $session_id, $hospital_id]);

if ($res && pg_num_rows($res) > 0) {
    $row = pg_fetch_assoc($res);
    
    echo json_encode([
        "success" => true,
        "message" => "มอบหมาย EMS สำเร็จ",
        "data"    => $row
    ]);

    // TODO (อนาคต): ส่ง notification ไปยังแอป EMS ที่นี่
    // เช่น insert เข้า table notifications หรือเรียก FCM

} else {
    echo json_encode([
        "success" => false,
        "message" => "assign failed (เคสไม่อยู่ในสถานะ waiting หรือไม่ใช่ของโรงพยาบาลนี้)"
    ]);
}

// ไม่ต้องปิด connection!