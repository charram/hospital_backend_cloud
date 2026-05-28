<?php

header(
    "Content-Type: application/json; charset=UTF-8"
);

require_once __DIR__ .
    '/db_connect.php';

// =====================
// GET PROVINCE
// =====================

$provinceCode =
    $_GET[
        "province_code"
    ] ?? "";

// =====================
// QUERY
// =====================
$query = "
SELECT
    id,
    name,
    license_number,
    contact_name,
    email AS contact_email,
    phone AS contact_phone,
    province,
    province_code,
    status,
    created_at,
    lat,
    lng,
    has_ambulance,
    available
FROM hospitals
WHERE status = 'approved'
";

// ถ้ามี province_code
// ค่อย filterผผ
$params = [];

if (
    !empty(
        $provinceCode
    )
) {

    $query .=
        " AND province_code = $1";

    $params[] =
        $provinceCode;
}

$query .=
    " ORDER BY created_at DESC";

// =====================
// RUN QUERY
// =====================

if (
    empty($params)
) {

    $result =
        pg_query(
            $conn,
            $query
        );

} else {

    $result =
        pg_query_params(
            $conn,
            $query,
            $params
        );
}

if (!$result) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Database error"
    ]);

    exit;
}

$hospitals = [];

while (
    $row =
        pg_fetch_assoc(
            $result
        )
) {

    $hospitalId =
        $row["id"];

    // =====================
    // MEDIA
    // =====================

    $mediaQuery = "
    SELECT file_path
    FROM hospital_media
    WHERE hospital_id = $1
    ";

    $mediaResult =
        pg_query_params(
            $conn,
            $mediaQuery,
            [$hospitalId]
        );

    $gallery = [];

    if (
        $mediaResult &&
        pg_num_rows(
            $mediaResult
        ) > 0
    ) {

        while (
            $m =
                pg_fetch_assoc(
                    $mediaResult
                )
        ) {

            $gallery[] =
                $m[
                    "file_path"
                ];
        }
    }

    $row["gallery"] =
        $gallery;

    $hospitals[] =
        $row;
}

// =====================
// RESPONSE
// =====================

echo json_encode([
    "success" => true,
    "province_code" =>
        $provinceCode,
    "data" =>
        $hospitals
],
JSON_UNESCAPED_UNICODE);

?>