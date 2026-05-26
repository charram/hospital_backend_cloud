<?php
require_once("../db_connect.php");

header("Content-Type: application/json");

$hospital_id = $_GET['hospital_id'] ?? null;

if (!$hospital_id) {
    echo json_encode([
        "success" => false,
        "error" => "Missing hospital_id"
    ]);
    exit;
}

$sql = "SELECT 
          id,
          ems_name,
          vehicle_code,
          status,
          phone
        FROM ems_units
        WHERE hospital_id = $1
        ORDER BY 
          CASE 
            WHEN status = 'available' THEN 1
            WHEN status = 'busy' THEN 2
            ELSE 3
          END";

$result = pg_query_params($conn, $sql, [$hospital_id]);

if (!$result) {
    echo json_encode([
        "success" => false,
        "error" => pg_last_error($conn)
    ]);
    exit;
}

$data = [];

while ($row = pg_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);