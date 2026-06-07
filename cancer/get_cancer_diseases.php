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

$sql = "
SELECT
    id,
    hospital_id,
    disease_key,
    title,
    description,
    risk_level,
    symptoms,
    machines,
    image_path,
    show_on_home,
    created_at
FROM cancer_diseases
WHERE hospital_id = $1
AND show_on_home = true
ORDER BY created_at DESC
";

$res = pg_query_params(
    $conn,
    $sql,
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
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
], JSON_UNESCAPED_UNICODE);