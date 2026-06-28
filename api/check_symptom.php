<?php

header("Content-Type: application/json; charset=UTF-8");

require_once(__DIR__ . "/../db_connect.php");
require_once(__DIR__ . "/../ai/medical_reasoning_engine.php");
require_once(__DIR__ . "/../ai/patient_context.php");

// ==========================
// Get Symptom
// ==========================

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

$patient->gender = $_GET["sex"] ?? "";

$patient->weight = floatval($_GET["weight"] ?? 0);

$patient->height = floatval($_GET["height"] ?? 0);

$patient->bloodPressure =
    $_GET["blood_pressure"] ?? "";

$patient->heartRate =
    intval($_GET["heart_rate"] ?? 0);

$patient->spo2 =
    intval($_GET["spo2"] ?? 0);

$patient->temperature =
    floatval($_GET["temperature"] ?? 0);

$patient->pregnant =
    ($_GET["pregnant"] ?? "0") == "1";

$patient->smoker =
    ($_GET["smoker"] ?? "0") == "1";

$patient->drinker =
    ($_GET["drinker"] ?? "0") == "1";

// ==========================
// Chronic Diseases
// ==========================

if (($_GET["diabetes"] ?? "0") == "1") {
    $patient->chronicDiseases[] = "diabetes";
}

if (($_GET["hypertension"] ?? "0") == "1") {
    $patient->chronicDiseases[] = "hypertension";
}

if (($_GET["heart_disease"] ?? "0") == "1") {
    $patient->chronicDiseases[] = "heart_disease";
}

if (($_GET["ckd"] ?? "0") == "1") {
    $patient->chronicDiseases[] = "ckd";
}

// ==========================
// Allergies
// ==========================

if (!empty($_GET["allergies"])) {

    $patient->allergies =
        array_map(
            "trim",
            explode(",", $_GET["allergies"])
        );

}

// ==========================
// Medications
// ==========================

if (!empty($_GET["medications"])) {

    $patient->medications =
        array_map(
            "trim",
            explode(",", $_GET["medications"])
        );

}

// ==========================
// Medical AI
// ==========================

$engine = new MedicalReasoningEngine();

$result = $engine->analyze(
    $symptom,
    $patient
);

// ==========================
// Output
// ==========================

echo json_encode(
    $result,
    JSON_UNESCAPED_UNICODE
);

exit;