<?php

header("Content-Type: application/json; charset=UTF-8");

require_once(__DIR__ . "/../db_connect.php");
require_once(__DIR__ . "/../ai/medical_reasoning_engine.php");
require_once(__DIR__ . "/../ai/patient_context.php");




$symptom = trim($_GET["symptom_name"] ?? "");

if ($symptom === "") {

    echo json_encode([
        "success" => false,
        "message" => "Symptom empty"
    ], JSON_UNESCAPED_UNICODE);

    exit;

}

// ==========================
// Patient Context
// ==========================

$patient = new PatientContext();

$patient->age = intval($_GET["age"] ?? 0);
$patient->sex = $_GET["sex"] ?? "";

$patient->pregnant =
    ($_GET["pregnant"] ?? "0") == "1";

$patient->diabetes =
    ($_GET["diabetes"] ?? "0") == "1";

$patient->hypertension =
    ($_GET["hypertension"] ?? "0") == "1";

$patient->heartDisease =
    ($_GET["heart_disease"] ?? "0") == "1";

$patient->chronicKidneyDisease =
    ($_GET["ckd"] ?? "0") == "1";

// ==========================
// Medical AI
// ==========================

$engine = new MedicalReasoningEngine();

$result = $engine->analyze(
    $symptom,
    $patient
);

echo json_encode(
    $result,
    JSON_UNESCAPED_UNICODE
);

exit;
?>