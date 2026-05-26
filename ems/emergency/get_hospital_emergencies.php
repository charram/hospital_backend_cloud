<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../../db_connect.php");

// ==========================
// 1. รับค่า hospital_id
// ==========================
$hospital_id = $_GET["hospital_id"] ?? null;

if (!$hospital_id) {
    echo json_encode([
        "success" => false,
        "message" => "Missing hospital_id"
    ]);
    exit;
}

$hospital_id = intval($hospital_id);

// ==========================
// 2. QUERY (แก้แล้ว)
// ==========================
$query = "
SELECT 
    s.id,

    -- 👤 USER GPS
    s.user_live_lat,
    s.user_live_lng,
    s.user_init_lat,
    s.user_init_lng,

    -- 🚑 EMS GPS
    s.ambulance_live_lat,
    s.ambulance_live_lng,

    -- 🔥 EMS NAME
    s.ems_id,
    eu.ems_name,

    -- 🏥 hospital
    h.lat AS hospital_lat,
    h.lng AS hospital_lng,

    LOWER(s.status) AS status,
    s.created_at,
    s.updated_at

FROM emergency_sessions s

LEFT JOIN hospitals h ON h.id = s.hospital_id
LEFT JOIN ems_units eu ON s.ems_id = eu.id

-- 🔥 FIX WHERE (สำคัญที่สุด)
WHERE 
    s.hospital_id = $1
    AND s.status != 'completed'

ORDER BY s.created_at DESC
";

// ==========================
// 3. Execute
// ==========================
$result = pg_query_params($conn, $query, [$hospital_id]);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
    exit;
}

// ==========================
// 4. Build Data
// ==========================
$data = [];

while ($row = pg_fetch_assoc($result)) {

    // USER GPS
    if (!empty($row["user_live_lat"]) && !empty($row["user_live_lng"])) {
        $row["user_lat"] = floatval($row["user_live_lat"]);
        $row["user_lng"] = floatval($row["user_live_lng"]);
        $row["is_live"] = true;
    } else {
        $row["user_lat"] = floatval($row["user_init_lat"]);
        $row["user_lng"] = floatval($row["user_init_lng"]);
        $row["is_live"] = false;
    }

    // EMS GPS
    $row["ems_live_lat"] = $row["ambulance_live_lat"] !== null 
        ? floatval($row["ambulance_live_lat"]) 
        : 0.0;

    $row["ems_live_lng"] = $row["ambulance_live_lng"] !== null 
        ? floatval($row["ambulance_live_lng"]) 
        : 0.0;

    // hospital
    $row["hospital_lat"] = $row["hospital_lat"] !== null 
        ? floatval($row["hospital_lat"]) 
        : 0.0;

    $row["hospital_lng"] = $row["hospital_lng"] !== null 
        ? floatval($row["hospital_lng"]) 
        : 0.0;

    $row["id"] = intval($row["id"]);

    $data[] = $row;
}

// ==========================
echo json_encode([
    "success" => true,
    "data" => $data
]);

pg_close($conn);
?>