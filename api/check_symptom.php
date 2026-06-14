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

$symptom = trim($_GET["symptom_name"] ?? "");

if ($symptom === "") {

    echo json_encode([
        "success" => false,
        "message" => "Symptom empty"
    ]);

    exit;
}

// =====================
// SPLIT KEYWORDS
// =====================
// เช่น
// "เจ็บหน้าอก หายใจไม่ออก"
// "chest pain shortness of breath"

$keywords = preg_split('/[\s,]+/u', $symptom);

$conditions = [];
$params = [];

// =====================
// BUILD SQL CONDITIONS
// =====================

foreach ($keywords as $word) {

    $word = trim($word);

    if ($word === "") {
        continue;
    }

    $index = count($params) + 1;

    $conditions[] = "
    (
        symptom_name ILIKE $$index
        OR symptom_keywords ILIKE $$index
    )
    ";

    $params[] = "%{$word}%";
}

if (empty($conditions)) {

    echo json_encode([
        "success" => false,
        "message" => "No valid symptom"
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
    " . implode(" OR ", $conditions) . "
ORDER BY
    severity_score DESC
LIMIT 1
";

$result = pg_query_params(
    $conn,
    $sql,
    $params
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
        "message" => "No assessment data found"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// =====================
// FETCH DATA
// =====================

$row = pg_fetch_assoc($result);

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
        (
            $row["ems_required"] === true ||
            $row["ems_required"] === "t" ||
            $row["ems_required"] === "true" ||
            $row["ems_required"] == 1
        ),

    "severity_score" =>
        (int)$row["severity_score"],

    "ai_note" =>
        $row["ai_note"]

], JSON_UNESCAPED_UNICODE);

exit;

?>