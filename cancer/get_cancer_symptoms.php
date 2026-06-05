<?php
header("Content-Type: application/json; charset=UTF-8");

require_once("../db_connect.php");

$hospital_id = intval($_GET["hospital_id"] ?? 0);

if ($hospital_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "hospital_id required"
    ]);
    exit;
}

$sql = "
SELECT
    id,
    hospital_id,
    title,
    description,
    image_path,
    symptom_score,
    related_cancer,
    is_emergency,
    min_price,
    max_price,
    avg_price,
    insurance_note,
    
    created_at
FROM cancer_symptoms
WHERE hospital_id = $1
ORDER BY id DESC
";

$result = pg_query_params(
    $conn,
    $sql,
    [$hospital_id]
);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
    exit;
}

$data = [];

while ($row = pg_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $data
]);