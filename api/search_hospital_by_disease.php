<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/db_connect.php";

// ========================================
// RECEIVE
// ========================================

$category = trim($_GET["category"] ?? "");
$title    = trim($_GET["title"] ?? "");

if ($category === "" || $title === "") {

    echo json_encode([
        "success" => false,
        "message" => "missing parameter"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// ========================================
// SELECT TABLE
// ========================================

switch ($category) {

    case "brain":
        $table = "brain_center_uploads";
        break;

    case "cancer":
        $table = "cancer_center";
        break;

    case "lung":
        $table = "lung_center";
        break;

    case "heart":
        $table = "heart_center";
        break;

    case "kidney":
        $table = "kidney_center";
        break;

    default:

        echo json_encode([
            "success" => false,
            "message" => "unknown category"
        ], JSON_UNESCAPED_UNICODE);

        exit;
}

$keyword = "%" . $title . "%";

// ========================================
// QUERY
// ========================================

$sql = "

SELECT DISTINCT

    hc.id,
    hc.hospital_id,
    hc.image_path,
    hc.title,
    hc.description,
    hc.accreditation,
    hc.open_24h,
    hc.province,
    h.province_code,
    hc.lat,
    hc.lng

FROM hospital_card hc

INNER JOIN hospitals h
ON h.id = hc.hospital_id

INNER JOIN {$table} d
ON d.hospital_id = hc.hospital_id

WHERE

h.status='approved'

AND d.upload_type='disease'

AND (

    d.title ILIKE $1

    OR d.description ILIKE $1

    OR d.meta::text ILIKE $1

)

ORDER BY hc.id DESC

";

// ========================================
// EXECUTE
// ========================================

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

// ========================================
// RESULT
// ========================================

$data = [];

while ($row = pg_fetch_assoc($res)) {

    foreach ($row as $k => $v) {

        if ($v !== null) {
            $row[$k] = (string)$v;
        }
    }

    $data[] = $row;
}

// ========================================
// RESPONSE
// ========================================

echo json_encode([
    "success"  => true,
    "category" => $category,
    "keyword"  => $title,
    "count"    => count($data),
    "cards"    => $data
], JSON_UNESCAPED_UNICODE);