<?php

require_once __DIR__ . "/MedicalKnowledge.php";
require_once __DIR__ . "/DiseaseEngine.php";
require_once __DIR__ . "/RiskAssessmentEngine.php";
require_once __DIR__ . "/PatientContext.php";
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
        PatientContext $patient
    ): array
    {
        $symptom = mb_strtolower(trim($symptom), "UTF-8");

        // ==========================
        // Disease Prediction
        // ==========================
        $disease = $this->diseaseEngine->findDisease($symptom);

        // ==========================
        // Patient Risk
        // ==========================
        $risk = $this->riskEngine->assess($patient);
if ($disease === null) {

    return [
        "success" => false,
        "message" => "ไม่สามารถระบุโรคได้",
        "risk_level" => $risk["risk_level"],
        "risk_score" => $risk["risk_score"],
        "risk_reasons" => $risk["reasons"],
        "patient" => $patient->toArray()
    ];

}

        // ==========================
        // Combine Disease + Risk
        // ==========================
        $severity = $disease["severity"] + $risk["risk_score"];

        if ($severity > 10) {
            $severity = 10;
        }

        $urgency = "ต่ำ";

        if ($severity >= 8) {
            $urgency = "สูง";
        } elseif ($severity >= 5) {
            $urgency = "ปานกลาง";
        }

        $emsRequired = $disease["ems"];

        if ($risk["risk_level"] === "สูงมาก") {

            $severity = 10;
            $urgency = "สูง";
            $emsRequired = true;

        }

        $aiNote = "วิเคราะห์โดย Clinical Decision Engine";
        $hospital = $this->hospitalEngine->recommend(
    $disease["department"],
    $emsRequired
    
);
    $result = [

    "success" => true,

    "symptom_name" => $disease["name"],

    "department" => $disease["department"],

    "urgency_level" => $urgency,

    "severity_score" => $severity,

    "ems_required" => $emsRequired,

    "recommendation" => $disease["recommendation"],

    "hospital_type" => $hospital["hospital_type"],

    "hospital_priority" => $hospital["priority"],

    "hospital_recommendation" => $hospital["recommendation"],

    "risk_level" => $risk["risk_level"],

    "risk_score" => $risk["risk_score"],

    "risk_reasons" => $risk["reasons"],

    "patient" => $patient->toArray(),

    "ai_note" => $aiNote

];$result["explanation"] = $this->explainEngine->explain($result);

return $result;


    }

}