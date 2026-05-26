<?php
header("Content-Type: application/json");
require_once __DIR__ . '/db_connect.php';

$user_id_raw =
    $_GET['user_id'] ?? null;

// 🔥 กัน null / string / int
$user_id = is_numeric($user_id_raw)
    ? (int)$user_id_raw
    : 0;

// 🔥 admin ไม่มี user_id
if ($user_id <= 0) {

    echo json_encode([
        "success" => true,
        "data" => []
    ]);

    exit;
}

$res = pg_query_params(
    $conn,
    "SELECT
id,
user_id,
title,
body,
is_read,
created_at,
booking_id
FROM notifications
     WHERE user_id = $1
     ORDER BY id DESC",
    [$user_id]
);

if (!$res) {
    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
    exit;
}

$data = [];

while ($row = pg_fetch_assoc($res)) {
    // 🔥 แปลงเป็น string กัน Flutter งง
    foreach ($row as $k => $v) {
        if ($v !== null) $row[$k] = (string)$v;
    }
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);