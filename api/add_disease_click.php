<?php

header("Content-Type: application/json; charset=UTF-8");

include "../db_connect.php";

if (!$conn) {

    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);

    exit;
}

$disease =
    $_POST["disease_category"] ?? "";

$hospitalId =
    $_POST["hospital_id"] ?? 0;

if ($disease == "") {

    echo json_encode([
        "success" => false,
        "message" => "Disease empty"
    ]);

    exit;
}

// CHECK EXIST
$checkSql = "
SELECT *
FROM disease_stats
WHERE disease_category = $1
AND hospital_id = $2
";

$check = pg_query_params(
    $conn,
    $checkSql,
    [$disease, $hospitalId]
);

if (pg_num_rows($check) > 0) {

    // UPDATE +1
    $updateSql = "
    UPDATE disease_stats
    SET total = total + 1
    WHERE disease_category = $1
    AND hospital_id = $2
    ";

    $result = pg_query_params(
        $conn,
        $updateSql,
        [$disease, $hospitalId]
    );

} else {

    // INSERT NEW
    $insertSql = "
    INSERT INTO disease_stats
    (
        disease_category,
        total,
        hospital_id
    )
    VALUES
    (
        $1,
        1,
        $2
    )
    ";

    $result = pg_query_params(
        $conn,
        $insertSql,
        [$disease, $hospitalId]
    );
}

if ($result) {

    echo json_encode([
        "success" => true,
        "message" => "Saved"
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
}