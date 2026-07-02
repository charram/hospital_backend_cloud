<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/db_connect.php";

$title = trim($_GET["title"] ?? "");

if ($title === "") {
    echo json_encode([
        "success" => false,
        "message" => "missing title"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$keyword = "%" . $title . "%";

$sql = "

SELECT
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

WHERE
h.status='approved'

AND (

    EXISTS (

        SELECT 1

        FROM brain_center_uploads b

        WHERE
        b.hospital_id = hc.hospital_id
        AND b.upload_type='disease'
        AND (
            b.title ILIKE $1
            OR b.description ILIKE $1
            OR b.meta::text ILIKE $1
        )

    )

    OR

    EXISTS (

        SELECT 1

        FROM cancer_center c

        WHERE
        c.hospital_id = hc.hospital_id
        AND c.upload_type='disease'
        AND (
            c.title ILIKE $1
            OR c.description ILIKE $1
            OR c.meta::text ILIKE $1
        )

    )

)

ORDER BY hc.id DESC

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
    "keyword" => $title,
    "count" => count($data),
    "cards" => $data
], JSON_UNESCAPED_UNICODE);