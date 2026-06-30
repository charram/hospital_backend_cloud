<?php

declare(strict_types=1);

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../ai/question_engine.php";
require_once __DIR__ . "/../ai/patient_context.php";
require_once __DIR__ . "/../ai/medical_reasoning_engine.php";

// =====================================================
// Read Request
// =====================================================

$rawInput = json_decode(
    file_get_contents("php://input"),
    true
);

if (!is_array($rawInput)) {
    $rawInput = [];
}

function requestValue(string $key, mixed $default = null): mixed
{
    global $rawInput;

    return
        $_POST[$key]
        ?? $_GET[$key]
        ?? $rawInput[$key]
        ?? $default;
}

// =====================================================
// Chief Complaint
// รองรับ API ใหม่ + API เก่า
// =====================================================

$text = trim((string)(

    requestValue("text")

    ?? requestValue("symptom")

    ?? requestValue("symptom_name")

    ?? requestValue("chief_complaint")

    ?? ""

));

if ($text === "") {

    echo json_encode([
        "success" => false,
        "message" => "Chief complaint is required."
    ], JSON_UNESCAPED_UNICODE);

    exit;

}

// =====================================================
// Patient Context
// =====================================================

$patient = new PatientContext();

$patient->age =
    intval(requestValue("age",0));

$patient->gender =
    (string)requestValue(
        "gender",
        requestValue("sex","")
    );

$patient->weight =
    floatval(requestValue("weight",0));

$patient->height =
    floatval(requestValue("height",0));

$patient->bloodPressure =
    (string)requestValue(
        "blood_pressure",
        ""
    );

$patient->heartRate =
    intval(requestValue(
        "heart_rate",
        0
    ));

$patient->spo2 =
    intval(requestValue(
        "spo2",
        0
    ));

$patient->temperature =
    floatval(requestValue(
        "temperature",
        0
    ));

$patient->pregnant =
    requestValue("pregnant","0")=="1";

$patient->smoker =
    requestValue("smoker","0")=="1";

$patient->drinker =
    requestValue("drinker","0")=="1";

// =====================================================
// Answers
// =====================================================

$answers = requestValue("answers",[]);

if (is_string($answers)) {

    $decoded = json_decode(
        $answers,
        true
    );

    if (is_array($decoded)) {
        $answers = $decoded;
    } else {
        $answers = [];
    }

}

if (!is_array($answers)) {
    $answers = [];
}

// =====================================================
// Question Engine
// =====================================================

$questionEngine = new QuestionEngine();

$questionResult =
    $questionEngine->analyze(

        $text,

        $answers,

        $patient

    );

// =====================================================
// ยังต้องถามต่อ
// =====================================================

if (

    ($questionResult["needs_more_info"] ?? false)

) {

    echo json_encode(

        $questionResult,

        JSON_UNESCAPED_UNICODE

    );

    exit;

}

// =====================================================
// Medical Reasoning
// =====================================================

$engine =
    new MedicalReasoningEngine();

$result =
    $engine->analyze(

        $text,

        $patient,

        $answers

    );

// =====================================================
// Extra
// =====================================================

$result["question_answers"] = $answers;

$result["chief_complaint"] = $text;

$result["mode"] = "result";

// =====================================================
// Output
// =====================================================

echo json_encode(

    $result,

    JSON_UNESCAPED_UNICODE |
    JSON_PRETTY_PRINT

);

exit;