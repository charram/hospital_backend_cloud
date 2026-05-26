<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "db_connect.php";

// รับ hospital_id จาก Flutter (GET หรือ POST ก็ได้)
$hospital_id = $_GET["hospital_id"] ?? null;

if ($hospital_id === null) {
    echo json_encode([
        "success" => false,
        "message" => "hospital_id required"
    ]);
    exit;
}

// ✅ ใช้ pg_query_params (สำคัญมาก)
$sql = "
SELECT 
    id,
    hospital_id,
    image_path,
    title,
    description
FROM products
WHERE hospital_id = $1
ORDER BY id DESC
";

$res = pg_query_params($conn, $sql, [$hospital_id]);

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
