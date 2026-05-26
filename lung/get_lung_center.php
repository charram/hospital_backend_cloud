<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../db_connect.php";

// ---------------------
// hospital_id
// ---------------------

$hospital_id =
intval(
    $_GET["hospital_id"]
    ?? 0
);

if ($hospital_id <= 0) {

    echo json_encode([
        "success" => false,
        "message" =>
            "missing hospital_id"
    ]);

    exit;
}

// ---------------------
// ดึงเฉพาะปอด
// ---------------------

$sql = "
SELECT
    id,
    hospital_id,
    category,
    title,
    description,
    image_path,
    show_on_home,
    created_at
FROM hospital_diseases
WHERE hospital_id = $1
AND category = 'lung'
ORDER BY id DESC
";

$res =
pg_query_params(
    $conn,
    $sql,
    [$hospital_id]
);

if (!$res) {

    echo json_encode([
        "success" => false,
        "message" =>
            pg_last_error(
                $conn
            )
    ]);

    exit;
}

$data = [];

while (
    $row =
    pg_fetch_assoc($res)
) {

    $data[] = [
        "id" =>
            intval(
                $row["id"]
            ),

        "hospital_id" =>
            intval(
                $row["hospital_id"]
            ),

        "category" =>
            $row["category"],

        "title" =>
            $row["title"],

        "description" =>
            $row["description"],

        "image_path" =>
            $row["image_path"],

        "show_on_home" =>
            $row["show_on_home"],

        "created_at" =>
            $row["created_at"],
    ];
}

echo json_encode([
    "success" => true,
    "count" =>
        count($data),
    "data" =>
        $data
], JSON_UNESCAPED_UNICODE);