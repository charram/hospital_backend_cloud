<?php

declare(strict_types=1);

header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../ai/question_engine.php";
require_once __DIR__ . "/../ai/patient_context.php";

// =====================================================
// รับข้อมูล
// =====================================================

$rawInput = json_decode(file_get_contents("php://input"), true);

$text =
    trim(
        $_POST["text"]
        ?? $_GET["text"]
        ?? ($rawInput["text"] ?? "")
    );

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
    intval(
        $_POST["age"]
        ?? $_GET["age"]
        ?? ($rawInput["age"] ?? 0)
    );

$patient->gender =
    $_POST["gender"]
    ?? $_GET["gender"]
    ?? ($rawInput["gender"] ?? "");

$patient->weight =
    floatval(
        $_POST["weight"]
        ?? $_GET["weight"]
        ?? ($rawInput["weight"] ?? 0)
    );

$patient->height =
    floatval(
        $_POST["height"]
        ?? $_GET["height"]
        ?? ($rawInput["height"] ?? 0)
    );

// =====================================================
// Answers
// =====================================================

$answers =
    $rawInput["answers"]
    ?? [];

// =====================================================
// AI
// =====================================================

$engine = new QuestionEngine();

$result = $engine->analyze(
    $text,
    $answers,
    $patient
);

// =====================================================
// Output
// =====================================================

echo json_encode(
    $result,
    JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
);

exit;