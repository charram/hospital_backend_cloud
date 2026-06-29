<?php

declare(strict_types=1);

header("Content-Type: application/json; charset=UTF-8");

// =====================================================
// Dependencies
// =====================================================

require_once __DIR__ . "/../db_connect.php";
require_once __DIR__ . "/../ai/patient_context.php";
require_once __DIR__ . "/../ai/question_engine.php";
require_once __DIR__ . "/../ai/medical_reasoning_engine.php";

// =====================================================
// Read Request
// รองรับ GET, POST, JSON body
// =====================================================

$rawInput = json_decode(
    file_get_contents("php://input"),
    true
);

if (!is_array($rawInput)) {
    $rawInput = [];
}

function request_value(
    string $key,
    mixed $default = null
): mixed {
    global $rawInput;

    return
        $_POST[$key]
        ?? $_GET[$key]
        ?? $rawInput[$key]
        ?? $default;
}

// =====================================================
// Get Symptom / Chief Complaint
// รองรับทั้ง symptom_name และ symptom
// =====================================================

$symptom = trim(
    (string)(
        request_value("symptom_name")
        ?? request_value("symptom")
        ?? request_value("text")
        ?? ""
    )
);

if ($symptom === "") {

    echo json_encode([
        "success" => false,
        "message" => "Symptom empty",
        "expected_params" => [
            "symptom_name",
            "symptom",
            "text"
        ]
    ], JSON_UNESCAPED_UNICODE);

    exit;

}

// =====================================================
// Answers จาก Dynamic Question Engine
// รองรับทั้ง JSON object และ JSON string
// =====================================================

$answers = request_value("answers", []);

if (is_string($answers)) {

    $decodedAnswers = json_decode($answers, true);

    if (is_array($decodedAnswers)) {
        $answers = $decodedAnswers;
    } else {
        $answers = [];
    }

}

if (!is_array($answers)) {
    $answers = [];
}
// =====================================================
// Patient Context
// =====================================================

$patient = new PatientContext();

$patient->age =
    intval(request_value("age", 0));

$patient->gender =
    (string)request_value("sex",
        request_value("gender", "")
    );

$patient->weight =
    floatval(request_value("weight", 0));

$patient->height =
    floatval(request_value("height", 0));

$patient->bloodPressure =
    (string)request_value(
        "blood_pressure",
        ""
    );

$patient->heartRate =
    intval(request_value(
        "heart_rate",
        0
    ));

$patient->spo2 =
    intval(request_value(
        "spo2",
        0
    ));

$patient->temperature =
    floatval(request_value(
        "temperature",
        0
    ));

$patient->pregnant =
    request_value("pregnant", "0") == "1";

$patient->smoker =
    request_value("smoker", "0") == "1";

$patient->drinker =
    request_value("drinker", "0") == "1";

// =====================================================
// Chronic Diseases
// =====================================================

$patient->chronicDiseases = [];

$chronicMap = [

    "diabetes" => "diabetes",

    "hypertension" => "hypertension",

    "heart_disease" => "heart_disease",

    "ckd" => "ckd",

    "copd" => "copd",

    "asthma" => "asthma",

    "stroke" => "stroke",

    "cancer" => "cancer"

];

foreach ($chronicMap as $param => $disease) {

    if (request_value($param, "0") == "1") {

        $patient->chronicDiseases[] =
            $disease;

    }

}

// =====================================================
// Allergies
// =====================================================

$allergies =
    request_value("allergies", "");

if (!empty($allergies)) {

    $patient->allergies =
        array_filter(

            array_map(

                "trim",

                explode(",", $allergies)

            )

        );

}

// =====================================================
// Medications
// =====================================================

$medications =
    request_value("medications", "");

if (!empty($medications)) {

    $patient->medications =
        array_filter(

            array_map(

                "trim",

                explode(",", $medications)

            )

        );

}

// =====================================================
// Dynamic Question Engine
// =====================================================

$questionEngine =
    new QuestionEngine();

$questionResult =
    $questionEngine->analyze(

        $symptom,

        $answers,

        $patient

    );

// ถ้ายังต้องซักประวัติ
if (

    isset($questionResult["needs_more_info"])

    &&

    $questionResult["needs_more_info"] === true

) {

    echo json_encode([

        "success" => true,

        "mode" => "question",

        "data" => $questionResult

    ], JSON_UNESCAPED_UNICODE);

    exit;

}
// =====================================================
// Medical Reasoning Engine
// =====================================================

$engine =
    new MedicalReasoningEngine();

/*
 * ใน V2 ส่ง symptom + patient ก่อน
 * (V3 สามารถส่ง answers เข้า Reasoning ได้)
 */
$result =
    $engine->analyze(

        $symptom,

        $patient,

        $answers

    );

// =====================================================
// แนบข้อมูลการซักประวัติ
// =====================================================

$result["mode"] = "result";

$result["chief_complaint"] =
    $symptom;

$result["question_answers"] =
    $answers;

$result["question_engine"] = [

    "completed" => true,

    "question_count" =>
        count($answers)

];

// =====================================================
// Debug
// =====================================================

if (

    request_value("debug", "0") == "1"

) {

    $result["debug"] = [

        "input_symptom" =>
            $symptom,

        "answers" =>
            $answers,

        "patient" => [

            "age" => $patient->age,

            "gender" => $patient->gender,

            "weight" => $patient->weight,

            "height" => $patient->height,

            "spo2" => $patient->spo2,

            "heart_rate" => $patient->heartRate,

            "temperature" => $patient->temperature,

            "blood_pressure" =>
                $patient->bloodPressure,

            "chronic_diseases" =>
                $patient->chronicDiseases

        ]

    ];

}

// =====================================================
// Output
// =====================================================

echo json_encode(

    $result,

    JSON_UNESCAPED_UNICODE

);

exit;