<?php
header("Content-Type: application/json; charset=utf-8");
error_reporting(0);
ini_set("display_errors", 0);

require_once("../db_connect.php");

/* ===== INPUT ===== */
$name  = trim($_POST["name"] ?? "");
$phone = trim($_POST["phone"] ?? "");

if ($name === "" || $phone === "") {
    echo json_encode([
        "success" => false,
        "message" => "name/phone required"
    ]);
    exit;
}

/* ===== QUERY EMS ===== */
$q = pg_query_params(
    $conn,
    "SELECT id, ems_name, vehicle_code, phone
     FROM ems_units
     WHERE ems_name = $1 AND phone = $2
     LIMIT 1",
    [$name, $phone]
);

if (!$q || pg_num_rows($q) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "EMS not found"
    ]);
    exit;
}

$row = pg_fetch_assoc($q);

/* ===== SUCCESS ===== */
echo json_encode([
    "success" => true,
    "ems" => [
        "ems_id" => (int)$row["id"],
        "name" => $row["ems_name"],
        "vehicle_code" => $row["vehicle_code"],
        "phone" => $row["phone"]
    ]
]);

pg_close($conn);