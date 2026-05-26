<?php

header("Content-Type: application/json; charset=UTF-8");

include "../db_connect.php";

// =====================
// CHECK DB CONNECTION
// =====================

if (!$conn) {

    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);

    exit;
}

// =====================
// GET INPUT
// =====================

$symptom =
    trim(
        $_GET["symptom_name"] ?? ""
    );

if ($symptom == "") {

    echo json_encode([
        "success" => false,
        "message" => "Symptom empty"
    ]);

    exit;
}

// =====================
// QUERY POSTGRESQL
// =====================

$sql = "
SELECT
    symptom_name,
    symptom_keywords,
    urgency_level,
    recommendation,
    department,
    ems_required,
    severity_score,
    ai_note
FROM symptom_assessment
WHERE
    symptom_name ILIKE $1
    OR symptom_keywords ILIKE $1
ORDER BY severity_score DESC
LIMIT 1
";

$result = pg_query_params(
    $conn,
    $sql,
    [
        "%$symptom%"
    ]
);

// =====================
// NO DATA FOUND
// =====================

if (
    !$result ||
    pg_num_rows($result) == 0
) {

    echo json_encode([

        "success" => false,

        "message" =>
            "No assessment data found"
    ]);

    exit;
}

// =====================
// FETCH DATA
// =====================

$row =
    pg_fetch_assoc(
        $result
    );

// =====================
// RESPONSE
// =====================

echo json_encode([

    "success" => true,

    "symptom_name" =>
        $row["symptom_name"],

    "urgency_level" =>
        $row["urgency_level"],

    "recommendation" =>
        $row["recommendation"],

    "department" =>
        $row["department"],

    "ems_required" =>
        $row["ems_required"],

    "severity_score" =>
        (int)$row["severity_score"],

    "ai_note" =>
        $row["ai_note"]
]);