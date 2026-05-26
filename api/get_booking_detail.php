<?php
header("Content-Type: application/json");
require_once __DIR__ . '/db_connect.php';

$id = $_GET['id'] ?? '';

if (!$id) {
    echo json_encode([
        "success" => false,
        "message" => "missing id"
    ]);
    exit;
}

$res = pg_query_params(
    $conn,
    "SELECT 
        id,
        service,
        booking_date,
        booking_time,
        first_name,
        last_name,
        phone,
        status,

        department,
        building,
        floor,
        room,
        queue_no

     FROM bookings
     WHERE id = $1",
    [$id]
);

if (!$res) {
    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
    exit;
}

$row = pg_fetch_assoc($res);

if (!$row) {
    echo json_encode([
        "success" => false,
        "message" => "not found"
    ]);
    exit;
}

// 🔥 แปลงเป็น string กัน Flutter crash
foreach ($row as $k => $v) {
    if ($v !== null) {
        $row[$k] = (string)$v;
    }
}

echo json_encode([
    "success" => true,
    "data" => $row
]);