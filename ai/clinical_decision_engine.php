<?php

require_once __DIR__ . "/medical_knowledge.php";
require_once __DIR__ . "/disease_engine.php";
require_once __DIR__ . "/risk_assessment_engine.php";
require_once __DIR__ . "/patient_context.php";
require_once __DIR__ . "/hospital_recommendation_engine.php";
require_once __DIR__ . "/explainable_ai_engine.php";

class ClinicalDecisionEngine
{
    private DiseaseEngine $diseaseEngine;
    private RiskAssessmentEngine $riskEngine;
    private HospitalRecommendationEngine $hospitalEngine;
    private ExplainableAIEngine $explainEngine;

    public function __construct()
    {
        $this->diseaseEngine = new DiseaseEngine();
        $this->riskEngine = new RiskAssessmentEngine();
        $this->hospitalEngine = new HospitalRecommendationEngine();
        $this->explainEngine = new ExplainableAIEngine();
    }

    public function evaluate(
        string $symptom,
        PatientContext $patient,
        array $answers = []
    ): array {
        $symptom = mb_strtolower(trim($symptom), "UTF-8");

        // ==========================
        // Disease Ranking + Re-ranking
        // ==========================
        $diseases = $this->diseaseEngine->findDiseases($symptom, $answers);

        // ==========================
        // Patient Risk
        // ==========================
        $risk = $this->riskEngine->assess($patient);

        if (count($diseases) === 0) {
            return [
                "success" => false,
                "message" => "ไม่สามารถระบุโรคได้",
                "possible_diseases" => [],
                "clinical_questions" => $answers,
                "risk_level" => $risk["risk_level"],
                "risk_score" => $risk["risk_score"],
                "risk_reasons" => $risk["reasons"],
                "patient" => $patient->toArray()
            ];
        }

        // เอาโรคที่คะแนนสูงสุด
        $disease = $diseases[0];

        // กัน key severity หาย
        if (!isset($disease["severity"])) {
            $disease["severity"] = $disease["severity_score"] ?? 0;
        }

        // ==========================
        // Combine Disease + Patient Risk
        // ==========================
        $severity = (int)$disease["severity"] + (int)$risk["risk_score"];

        if ($severity > 10) {
            $severity = 10;
        }

        $urgency = "ต่ำ";

        if ($severity >= 8) {
            $urgency = "สูง";
        } elseif ($severity >= 5) {
            $urgency = "ปานกลาง";
        }

        $emsRequired = $disease["ems"] ?? false;

        if ($risk["risk_level"] === "สูงมาก") {
            $severity = 10;
            $urgency = "สูง";
            $emsRequired = true;
        }

        $department = $disease["department"] ?? "อายุรกรรม";

        $hospital = $this->hospitalEngine->recommend(
            $department,
            $emsRequired
        );

        $result = [
            "success" => true,

            "symptom_name" => $disease["name"],

            "possible_diseases" => $diseases,

            "department" => $department,

            "urgency_level" => $urgency,

            "severity_score" => $severity,

            "ems_required" => $emsRequired,

            "recommendation" => $disease["recommendation"] ?? "ควรพบแพทย์เพื่อประเมินเพิ่มเติม",

            "hospital_type" => $hospital["hospital_type"],
            "hospital_priority" => $hospital["priority"],
            "hospital_recommendation" => $hospital["recommendation"],

            "risk_level" => $risk["risk_level"],
            "risk_score" => $risk["risk_score"],
            "risk_reasons" => $risk["reasons"],

            "patient" => $patient->toArray(),

            "clinical_questions" => $answers,

            "ai_note" => "วิเคราะห์โดย Clinical Decision Engine"
        ];

        $result["explanation"] = $this->explainEngine->explain($result);

        return $result;
    }
}