<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once __DIR__ . "/../db_connect.php";

// ---------------------
// RECEIVE
// ---------------------

$hospital_id =
intval(
    $_GET["hospital_id"]
    ?? 0
);

$upload_type =
trim(
    $_GET["upload_type"]
    ?? ""
);

// ---------------------
// VALIDATE
// ---------------------

if ($hospital_id <= 0) {

    echo json_encode([
        "success" => false,
        "message" =>
            "missing hospital_id"
    ]);

    exit;
}

// ---------------------
// QUERY
// ---------------------

$sql = "
SELECT
    id,
    hospital_id,
    category,
    upload_type,

    title,
    description,

    -- 🔥 เพิ่มใหม่
    meta,

    image_path,

    show_on_home,
    is_hero,

    service_type,
    machine_type,
    disease_type,

    price,
    duration,

    doctor_name,
    doctor_specialty,

    phone,
    line,

    symptoms,
    machines,

    created_at

FROM brain_center_uploads
WHERE hospital_id = $1
";

$params = [
    $hospital_id
];

// ---------------------
// FILTER TYPE
// ---------------------

if ($upload_type != "") {

    $sql .= "
    AND upload_type = $2
    ";

    $params[] =
        $upload_type;
}

$sql .= "
ORDER BY id DESC
";

// ---------------------
// EXECUTE
// ---------------------

$q =
pg_query_params(
    $conn,
    $sql,
    $params
);

if (!$q) {

    echo json_encode([
        "success" => false,
        "message" =>
            pg_last_error(
                $conn
            )
    ]);

    exit;
}

// ---------------------
// DATA
// ---------------------

$data = [];

while (
    $row =
    pg_fetch_assoc($q)
) {

    // symptoms
    $row["symptoms"] =
        json_decode(
            $row["symptoms"]
            ?? "[]",
            true
        );

    // machines
    $row["machines"] =
        json_decode(
            $row["machines"]
            ?? "[]",
            true
        );

    // 🔥 meta safe decode
    if (
        isset($row["meta"]) &&
        !empty($row["meta"])
    ) {

        $decodedMeta =
            json_decode(
                $row["meta"],
                true
            );

        $row["meta"] =
            $decodedMeta
            ?: [];
    } else {

        $row["meta"] = [];
    }

    $data[] = $row;
}

// ---------------------
// RESPONSE
// ---------------------

echo json_encode([
    "success" => true,
    "count" =>
        count($data),
    "data" =>
        $data
], JSON_UNESCAPED_UNICODE);