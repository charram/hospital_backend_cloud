<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../db_connect.php";

$keyword = trim($_GET['q'] ?? '');
$hospital_id = $_GET['hospital_id'] ?? '';

if ($keyword == '') {
    echo json_encode([
        "success" => true,
        "data" => []
    ]);
    exit;
}

$like = "%{$keyword}%";

$sql = "

-- ==========================
-- CANCER CENTER
-- ==========================
SELECT
    'cancer_center' AS source,
    id,
    hospital_id,
    title,
    description,
    upload_type AS type
FROM cancer_center
WHERE hospital_id = $1
AND (
    title ILIKE $2
    OR description ILIKE $2
)

UNION ALL

-- ==========================
-- CANCER DISEASE
-- ==========================
SELECT
    'cancer_diseases' AS source,
    id,
    hospital_id,
    title,
    description,
    'disease' AS type
FROM cancer_diseases
WHERE hospital_id = $1
AND (
    title ILIKE $2
    OR description ILIKE $2
    OR disease_key ILIKE $2
)

UNION ALL

-- ==========================
-- CANCER SYMPTOMS
-- ==========================
SELECT
    'cancer_symptoms' AS source,
    id,
    hospital_id,
    title,
    description,
    'symptom' AS type
FROM cancer_symptoms
WHERE hospital_id = $1
AND (
    title ILIKE $2
    OR description ILIKE $2
    OR symptom_key ILIKE $2
    OR related_cancer ILIKE $2
)

UNION ALL

-- ==========================
-- BRAIN CENTER
-- ==========================
SELECT
    'brain_center_uploads' AS source,
    id,
    hospital_id,
    title,
    description,
    upload_type AS type
FROM brain_center_uploads
WHERE hospital_id = $1
AND (
    title ILIKE $2
    OR description ILIKE $2
    OR category ILIKE $2
)

ORDER BY title;

";

$res = pg_query_params(
    $conn,
    $sql,
    [
        $hospital_id,
        $like
    ]
);

if (!$res) {
    echo json_encode([
        "success"=>false,
        "message"=>pg_last_error($conn)
    ]);
    exit;
}

$data=[];

while($row=pg_fetch_assoc($res)){
    $data[]=$row;
}

echo json_encode([
    "success"=>true,
    "count"=>count($data),
    "data"=>$data
],JSON_UNESCAPED_UNICODE);

pg_close($conn);