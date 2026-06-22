<?php
require_once("../db_connect.php");
header("Content-Type: application/json");

$ems_id = $_GET['ems_id'] ?? null;

if (!$ems_id) {
    echo json_encode([
        "success" => false,
        "error" => "Missing ems_id"
    ]);
    exit;
}

// 🔥 เอาเฉพาะ assigned เท่านั้น (ไม่เอา enroute)
// 🔥 เริ่ม Transaction เพื่อล็อคข้อมูล
pg_query($conn, "BEGIN");

// 1. ค้นหางานและล็อคแถวนี้ไว้ (FOR UPDATE)
$sql = "
    SELECT *
    FROM emergency_sessions
    WHERE ems_id = $1
    AND status = 'assigned'
    ORDER BY id ASC
    LIMIT 1
    FOR UPDATE 
";

$result = pg_query_params($conn, $sql, [$ems_id]);
$row = pg_fetch_assoc($result);

if ($row) {
    // 2. อัปเดตสถานะภายใน Transaction เดียวกัน
    $update = pg_query_params($conn,
        "UPDATE emergency_sessions SET status = 'enroute', updated_at = NOW() WHERE id = $1",
        [$row['id']]
    );

    if ($update) {
        pg_query($conn, "COMMIT"); // ยืนยันการเปลี่ยนแปลง

        // จัดการ Format ข้อมูลก่อนส่งออก
        foreach ($row as $k => $v) {
            $row[$k] = ($v !== null) ? strval($v) : "";
        }

        echo json_encode([
            "success" => true,
            "data" => $row
        ]);
    } else {
        pg_query($conn, "ROLLBACK");
        echo json_encode(["success" => false, "message" => "update failed"]);
    }
} else {
    pg_query($conn, "ROLLBACK"); // คืนค่ายกเลิกการล็อค
    echo json_encode(["success" => false, "message" => "no job"]);
}

pg_close($conn);