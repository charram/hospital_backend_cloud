<?php

declare(strict_types=1);

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../db_connect.php";

$q = trim($_GET["q"] ?? $_POST["q"] ?? "");

if ($q === "") {
    echo json_encode([
        "success" => false,
        "message" => "Keyword required."
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$keyword = "%" . $q . "%";

$sql = "

SELECT
    'brain' AS category,
    hospital_id,
    title,
    description,
    image_path,
    upload_type
FROM brain_center_uploads
WHERE upload_type='disease'
AND (
    title ILIKE $1
    OR description ILIKE $1
    OR meta::text ILIKE $1
)

UNION ALL

SELECT
    'cancer' AS category,
    hospital_id,
    title,
    description,
    image_path,
    upload_type
FROM cancer_center
WHERE upload_type='disease'
AND (
    title ILIKE $1
    OR description ILIKE $1
    OR meta::text ILIKE $1
)

ORDER BY title ASC

LIMIT 50

";

$res = pg_query_params(
    $conn,
    $sql,
    [$keyword]
);

if (!$res) {

    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ], JSON_UNESCAPED_UNICODE);

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
    "keyword" => $q,
    "count" => count($data),
    "data" => $data
], JSON_UNESCAPED_UNICODE);