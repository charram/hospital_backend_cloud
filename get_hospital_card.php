<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/db_connect.php";

// =====================
// GET PROVINCE
// =====================

$provinceCode =
    $_GET["province_code"]
    ?? "";

// =====================
// QUERY
// =====================

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
    ON hc.hospital_id = h.id
WHERE h.status = 'approved'
";

$params = [];

// 🔥 filter จังหวัด
if (!empty($provinceCode)) {

 $sql .=
"
AND LOWER(
    TRIM(
        h.province_code
    )
)
=
LOWER(
    TRIM(
        $1
    )
)
";

    $params[] =
        $provinceCode;
}

$sql .=
    " ORDER BY hc.id DESC";

// =====================
// RUN QUERY
// =====================

$res = empty($params)
    ? pg_query($conn, $sql)
    : pg_query_params(
        $conn,
        $sql,
        $params
    );

if (!$res) {

    echo json_encode([
        "success" => false,
        "message" =>
            pg_last_error($conn)
    ]);

    exit;
}

// =====================
// RESPONSE
// =====================

$rows = [];

while (
    $row =
        pg_fetch_assoc($res)
) {
    $rows[] =
        $row;
}

echo json_encode([
    "success" => true,
    "province_code" =>
        $provinceCode,
    "cards" =>
        $rows
],
JSON_UNESCAPED_UNICODE);