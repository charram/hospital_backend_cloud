<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once "db_connect.php";

$category =
trim(
    $_GET["category"]
    ?? ""
);

if ($category == "") {

    echo json_encode([
        "success" => false,
        "message" =>
            "Missing category"
    ]);

    exit;
}

$q =
pg_query_params(
    $conn,
    "
    SELECT
        image_path,
        title
    FROM hospital_diseases
    WHERE
        LOWER(category)
        = LOWER($1)
    AND
        image_path
        IS NOT NULL
    ORDER BY id DESC
    LIMIT 1
    ",
    [$category]
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

$row =
pg_fetch_assoc($q);

if ($row) {

    echo json_encode([
        "success" => true,
        "image_path" =>
            $row["image_path"],
        "title" =>
            $row["title"]
    ]);

} else {

    echo json_encode([
        "success" => false,
        "image_path" =>
            null
    ]);
}