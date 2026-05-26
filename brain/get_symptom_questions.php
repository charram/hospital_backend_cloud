<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once __DIR__ . "/../db_connect.php";

// ---------------------
// RECEIVE
// ---------------------

$symptom_name =
trim(
    $_GET["symptom_name"]
    ?? ""
);

// ---------------------
// VALIDATE
// ---------------------

if (
    $symptom_name == ""
) {

    echo json_encode([
        "success" => false,
        "message" =>
            "missing symptom_name"
    ]);

    exit;
}

// ---------------------
// QUERY
// ---------------------

$q =
pg_query_params(
    $conn,
    "
    SELECT
        id,
        symptom_name,
        question,
        is_emergency,
        score,
        created_at

    FROM
        symptom_questions

    WHERE
        symptom_name = $1

    ORDER BY
        id ASC
    ",
    [
        $symptom_name
    ]
);

// ---------------------
// ERROR
// ---------------------

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

    $row["score"] =
        intval(
            $row["score"]
            ?? 0
        );

    $row["is_emergency"] =
        (
            $row["is_emergency"]
            == "t"
        );

    $data[] =
        $row;
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