<?php
require_once("../db_connect.php");

header("Content-Type: application/json");

$emergency_id = $_POST['emergency_id'] ?? null;
$ems_id = $_POST['ems_id'] ?? null;

if (!$emergency_id || !$ems_id) {
    echo json_encode([
        "success" => false,
        "error" => "Missing parameters"
    ]);
    exit;
}

// 🔥 update emergency
$sql = "UPDATE emergencies 
        SET assigned_ems_id = $1, status = 'assigned'
        WHERE id = $2";

$result = pg_query_params($conn, $sql, [$ems_id, $emergency_id]);

if (!$result) {
    echo json_encode([
        "success" => false,
        "error" => pg_last_error($conn)
    ]);
    exit;
}

// 🔥 update EMS เป็น busy
$sql2 = "UPDATE ems_units 
         SET status = 'busy'
         WHERE id = $1";

pg_query_params($conn, $sql2, [$ems_id]);

echo json_encode([
    "success" => true
]);