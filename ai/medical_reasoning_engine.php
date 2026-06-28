<?php

require_once __DIR__ . "/DiseaseEngine.php";
require_once __DIR__ . "/RiskAssessmentEngine.php";
require_once __DIR__ . "/PatientContext.php";
require_once __DIR__ . "/ClinicalDecisionEngine.php";
require_once __DIR__ . "/LearningEngine.php";
require_once __DIR__ . "/CaseMemoryEngine.php";
require_once __DIR__ . "/ExplainableAIEngine.php";
require_once __DIR__ . "/HospitalRecommendationEngine.php";

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