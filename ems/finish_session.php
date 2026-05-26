<?php
header("Content-Type: application/json");
require_once("../db_connect.php");

$session_id = $_POST["session_id"] ?? null;

if (!$session_id) {
    echo json_encode([
        "success" => false,
        "message" => "missing session_id"
    ]);
    exit;
}

// 🔥 เริ่ม transaction (กันพลาด)
pg_query($conn, "BEGIN");

// 1️⃣ จบเคส
$result = pg_query_params($conn,
    "UPDATE emergency_sessions
     SET status = 'completed', updated_at = NOW()
     WHERE id = $1",
    [$session_id]
);

// 🔥 ถ้า update สำเร็จ
if ($result && pg_affected_rows($result) > 0) {

    // 2️⃣ เอา EMS กลับมา available
    pg_query_params($conn,
        "UPDATE ems_units
         SET status = 'available'
         WHERE id = (
            SELECT ems_id FROM emergency_sessions WHERE id = $1
         )",
        [$session_id]
    );

    pg_query($conn, "COMMIT");

    echo json_encode([
        "success" => true,
        "message" => "mission completed + EMS available"
    ]);

} else {

    pg_query($conn, "ROLLBACK");

    echo json_encode([
        "success" => false,
        "message" => "update failed"
    ]);
}

pg_close($conn);