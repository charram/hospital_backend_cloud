<?php
ob_clean();
error_reporting(0);
ini_set('display_errors', 0);

header("Content-Type: application/json; charset=utf-8");

require_once("../db_connect.php");

// ==================== รับค่า ====================
$session_id = $_GET["session_id"] ?? null;

if ($session_id === null || !is_numeric($session_id)) {
    echo json_encode([
        "success" => false,
        "message" => "session_id จำเป็นและต้องเป็นตัวเลข"
    ]);
    exit;
}

$session_id = (int)$session_id;

// ==================== SQL ====================
$sql = "
    SELECT
        id,
        hospital_id,
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
        updated_at,

        CASE
            WHEN status = 'assigned' THEN 'โรงพยาบาลรับเคสแล้ว'
            WHEN status = 'enroute' THEN 'รถพยาบาลกำลังเดินทาง'
            WHEN status = 'completed' THEN 'เคสเสร็จสิ้น'
            ELSE 'รอการมอบหมาย'
        END AS status_text

    FROM emergency_sessions
    WHERE id = ?
    LIMIT 1
";

try {

    // ==================== execute ====================
    $stmt = $conn->prepare($sql);
    $stmt->execute([$session_id]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode([
            "success" => false,
            "message" => "ไม่พบ session"
        ]);
        exit;
    }

    // ==================== format ====================
    $data = [
        "id"                 => (int)$row["id"],
        "hospital_id"        => (int)$row["hospital_id"],
        "user_id"            => (int)$row["user_id"],

        "status"             => $row["status"],
        "status_text"        => $row["status_text"],

        "ems_id"             => $row["ems_id"] !== null
            ? (int)$row["ems_id"]
            : null,

        "ambulance_code"     => $row["ambulance_code"],

        "user_init_lat"      => $row["user_init_lat"] !== null
            ? (float)$row["user_init_lat"]
            : null,

        "user_init_lng"      => $row["user_init_lng"] !== null
            ? (float)$row["user_init_lng"]
            : null,

        "user_live_lat"      => $row["user_live_lat"] !== null
            ? (float)$row["user_live_lat"]
            : null,

        "user_live_lng"      => $row["user_live_lng"] !== null
            ? (float)$row["user_live_lng"]
            : null,

        "ambulance_live_lat" => $row["ambulance_live_lat"] !== null
            ? (float)$row["ambulance_live_lat"]
            : null,

        "ambulance_live_lng" => $row["ambulance_live_lng"] !== null
            ? (float)$row["ambulance_live_lng"]
            : null,

        "created_at"         => $row["created_at"],
        "updated_at"         => $row["updated_at"],
    ];

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => "Database error",
        "error" => $e->getMessage()
    ]);
}

exit;