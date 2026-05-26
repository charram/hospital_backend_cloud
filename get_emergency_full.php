<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/db_connect.php';

$hospital_id = $_GET['hospital_id'] ?? null;

if (!$hospital_id) {
    echo json_encode(["success" => false, "message" => "no hospital_id"]);
    exit;
}

$q = pg_query_params($conn,
  "SELECT 
    es.id,
    es.status,
    es.user_init_lat,
    es.user_init_lng,
    es.ambulance_live_lat,
    es.ambulance_live_lng,
    es.hospital_id,
    es.ems_id,

    h.name AS hospital_name,
    eu.ems_name AS ems_name   -- 🔥 แก้ตรงนี้

   FROM emergency_sessions es
   LEFT JOIN hospitals h ON es.hospital_id = h.id
   LEFT JOIN ems_units eu ON es.ems_id = eu.id

   WHERE es.hospital_id = $1
   ORDER BY es.id DESC",
  [$hospital_id]
);

$data = [];

while ($row = pg_fetch_assoc($q)) {
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
], JSON_UNESCAPED_UNICODE);