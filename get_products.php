<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once "db_connect.php";

$sql = "
SELECT 
    id,
    hospital_id,
    image_path,
    title,
    description
FROM products
ORDER BY id DESC
";

$res = pg_query($conn, $sql);

if (!$res) {
    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
    exit;
}

$data = pg_fetch_all($res);

echo json_encode([
    "success" => true,
    "data" => $data ?: []
], JSON_UNESCAPED_UNICODE);
