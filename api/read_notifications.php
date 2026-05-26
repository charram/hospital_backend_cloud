<?php
header("Content-Type: application/json");
require_once __DIR__ . '/db_connect.php';

$user_id = $_POST['user_id'] ?? '';

if (!$user_id) {
    echo json_encode([
        "success" => false
    ]);
    exit;
}

$res = pg_query_params(
    $conn,
    "UPDATE notifications
     SET is_read = true
     WHERE user_id = $1",
    [$user_id]
);

echo json_encode([
    "success" => $res ? true : false
]);