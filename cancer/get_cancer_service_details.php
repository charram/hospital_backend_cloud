<?php

header('Content-Type: application/json');

include("../db_connect.php");

$hospital_id =
    $_GET['hospital_id'] ?? '';

if (empty($hospital_id)) {

    echo json_encode([
        "success" => false,
        "message" =>
            "hospital_id missing"
    ]);

    exit;
}

$sql = "
SELECT
    id,
    service_key,
    title,
    description,
    image_path,
    price
FROM cancer_service_details
WHERE hospital_id = $1
ORDER BY id ASC
";

$res =
    pg_query_params(
        $conn,
        $sql,
        [$hospital_id]
    );

$data = [];

while (
    $row =
        pg_fetch_assoc($res)
) {

    $data[] = [
        "id" => $row["id"],
        "service_key" =>
            $row["service_key"],
        "title" =>
            $row["title"],
        "description" =>
            $row["description"],
        "image_path" =>
            $row["image_path"],
        "price" =>
            $row["price"],
    ];
}

echo json_encode([
    "success" => true,
    "data" => $data
]);