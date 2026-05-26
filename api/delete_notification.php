<?php
header("Content-Type: application/json");
require_once __DIR__ . '/db_connect.php';

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode([
        "success" => false,
        "message" => "missing id"
    ]);
    exit;
}

$res = pg_query_params(
    $conn,
    "DELETE FROM notifications
     WHERE id=$1",
    [$id]
);

echo json_encode([
    "success" => $res ? true : false
]);