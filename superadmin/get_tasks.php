<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../db_connect.php";

$query = "SELECT id, hospital_name, license_number, contact_name, contact_email, contact_phone, province 
          FROM hospital_verification
          ORDER BY id ASC";

$result = pg_query($conn, $query);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Database query failed"
    ]);
    exit;
}

$data = pg_fetch_all($result);

echo json_encode([
    "success" => true,
    "data" => $data ?: []
]);
