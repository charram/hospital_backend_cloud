<?php

header("Content-Type: application/json");

include "../db_connect.php";

$hospitalId =
    $_GET["hospital_id"] ?? 0;

// =====================
// GLOBAL
// =====================

$globalSql = "
SELECT
    disease_category,
    SUM(total) AS total
FROM disease_stats
GROUP BY disease_category
ORDER BY total DESC
";

$globalResult =
    pg_query($conn, $globalSql);

$globalData = [];

while (
    $row =
        pg_fetch_assoc(
            $globalResult
        )
) {

    $globalData[] = [
        "disease_category" =>
            $row["disease_category"],

        "total" =>
            (int)$row["total"]
    ];
}

// =====================
// HOSPITAL
// =====================

$hospitalSql = "
SELECT
    disease_category,
    total
FROM disease_stats
WHERE hospital_id = $1
";

$hospitalResult =
    pg_query_params(
        $conn,
        $hospitalSql,
        [$hospitalId]
    );

$hospitalData = [];

while (
    $row =
        pg_fetch_assoc(
            $hospitalResult
        )
) {

    $hospitalData[] = [
        "disease_category" =>
            $row["disease_category"],

        "total" =>
            (int)$row["total"]
    ];
}

echo json_encode([

    "success" => true,

    "global_data" =>
        $globalData,

    "hospital_data" =>
        $hospitalData
]);