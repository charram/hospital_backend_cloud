<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once "db_connect.php";

$hospital_id = intval($_GET["hospital_id"] ?? 0);
$is_hero     = intval($_GET["is_hero"] ?? 0);

$sql = "

SELECT
    id,
    hospital_id,
    'brain' AS category,
    title,
    description,
    image_path,
    upload_type
FROM brain_center_uploads
WHERE hospital_id=$1
AND upload_type='disease'

UNION ALL

SELECT
    id,
    hospital_id,
    'cancer' AS category,
    title,
    description,
    image_path,
    upload_type
FROM cancer_center
WHERE hospital_id=$1
AND upload_type='disease'

ORDER BY id DESC

";

if ($is_hero == 1) {
    $sql .= " LIMIT 1";
}

$q = pg_query_params($conn, $sql, [$hospital_id]);

if (!$q) {
    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$rows = [];

while ($r = pg_fetch_assoc($q)) {
    $rows[] = $r;
}

echo json_encode([
    "success" => true,
    "data" => $rows
], JSON_UNESCAPED_UNICODE);