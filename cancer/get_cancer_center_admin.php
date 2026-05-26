<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../db_connect.php";

if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

$hospital_id = $_GET["hospital_id"] ?? "";

if ($hospital_id === "") {
    echo json_encode([
        "success" => false,
        "message" => "missing hospital_id"
    ]);
    exit;
}

$res = pg_query_params(
    $conn,
    "SELECT
        id,
        hospital_id,
        upload_type,
        title,
        description,
        image_path,
        meta::text AS meta,
        CASE WHEN is_hero THEN 't' ELSE 'f' END AS is_hero,
        created_at
     FROM cancer_center
     WHERE hospital_id = $1
     ORDER BY id DESC",
    [$hospital_id]
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
    foreach ($row as $k => $v) {
        if ($v !== null) {
            $row[$k] = (string)$v;
        }
    }

    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
], JSON_UNESCAPED_UNICODE);