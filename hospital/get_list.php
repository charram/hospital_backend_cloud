<?php
header("Content-Type: application/json");
require_once "../db_connect.php";

if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "DB error"
    ]);
    exit;
}

$result = pg_query(
    $conn,
    "SELECT id, name FROM hospitals ORDER BY id DESC"
);

$data = [];

while ($row = pg_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);
