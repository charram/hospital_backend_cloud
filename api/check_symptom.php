<?php

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../db_connect_railway.php';

// =====================
// CHECK DB CONNECTION
// =====================

if (!isset($pdo)) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ], JSON_UNESCAPED_UNICODE);
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
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// =====================
// SPLIT KEYWORDS
// =====================

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

    $conditions[] = "
        (
            symptom_name ILIKE ?
            OR symptom_keywords ILIKE ?
        )
    ";

    $params[] = "%{$word}%";
    $params[] = "%{$word}%";
}

if (empty($conditions)) {
    echo json_encode([
        "success" => false,
        "message" => "No valid symptom"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// =====================
// QUERY
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

try {

    $stmt = $pdo->prepare($sql);

    $stmt->execute($params);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {

        echo json_encode([
            "success" => false,
            "message" => "No assessment data found"
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

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
            filter_var(
                $row["ems_required"],
                FILTER_VALIDATE_BOOLEAN
            ),

        "severity_score" =>
            (int)$row["severity_score"],

        "ai_note" =>
            $row["ai_note"]

    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;

?>