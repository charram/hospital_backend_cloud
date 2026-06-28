<?php

require_once __DIR__ . "/clinical_decision_engine.php";
require_once __DIR__ . "/learning_engine.php";
require_once __DIR__ . "/case_memory_engine.php";
require_once __DIR__ . "/patient_context.php";
require_once __DIR__ . "/disease_engine.php";
require_once __DIR__ . "/risk_assessment_engine.php";
require_once __DIR__ . "/hospital_recommendation_engine.php";
require_once __DIR__ . "/explainable_ai_engine.php";

class MedicalReasoningEngine
{
    private ClinicalDecisionEngine $clinicalEngine;
    private LearningEngine $learningEngine;
    private CaseMemoryEngine $caseMemory;

    public function __construct()
    {
        $this->clinicalEngine = new ClinicalDecisionEngine();
        $this->learningEngine = new LearningEngine();
        $this->caseMemory = new CaseMemoryEngine();
    }

    public function analyze(
        string $symptom,
        PatientContext $patient
    ): array
    {

        // ===============================
        // Clinical Decision
        // ===============================
        $result = $this->clinicalEngine->evaluate(
            $symptom,
            $patient
        );

        // ===============================
        // Save Case Memory
        // ===============================
        if ($result["success"] === true) {

            $case = [

                "symptom" => $symptom,

                "disease" => $result["symptom_name"],

                "department" => $result["department"],

                "severity" => $result["severity_score"],

                "risk" => $result["risk_level"],

                "ems" => $result["ems_required"],

                "patient" => $patient->toArray(),

                "time" => date("Y-m-d H:i:s")

            ];

            $this->learningEngine->learn($case);

            $this->caseMemory->remember($case);

        }

        // ===============================
        // Similar Case
        // ===============================
        $similarCase = $this->learningEngine
            ->findSimilarCase($symptom);

        if ($similarCase !== null) {

            $result["similar_case"] = $similarCase;

        }

        // ===============================
        // Statistics
        // ===============================
        $result["learning_cases"] =
            $this->learningEngine->countCases();

        $result["memory_cases"] =
            $this->caseMemory->count();

        return $result;
    }

    public function getLearningCases(): array
    {
        return $this->learningEngine->getCases();
    }

    public function getMemoryCases(): array
    {
        return $this->caseMemory->getAll();
    }

    public function clearMemory(): void
    {
        $this->learningEngine->clear();
        $this->caseMemory->clear();
    }
}