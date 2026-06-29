<?php

declare(strict_types=1);

require_once __DIR__ . "/medical_knowledge.php";
require_once __DIR__ . "/symptom_extraction_engine.php";
require_once __DIR__ . "/patient_context.php";

/**
 * ============================================================
 * Open Hospital Medical AI V2
 * Reasoning Engine
 * ------------------------------------------------------------
 * หน้าที่
 * - วิเคราะห์อาการ
 * - โหลด Medical Knowledge
 * - สร้าง Candidate Disease
 * - ส่งต่อ Differential Engine
 * - ไม่คำนวณ EMS
 * - ไม่คำนวณ Severity
 * - ไม่คำนวณ Hospital
 * ============================================================
 */

class ReasoningEngine
{

    /**
     * NLP Engine
     */
    private SymptomExtractionEngine $extractor;

    /**
     * Medical Knowledge
     */
    private array $knowledge = [];

    /**
     * Candidate Diseases
     */
    private array $candidates = [];

    /**
     * Extraction Result
     */
    private array $extraction = [];

    /**
     * Patient Context
     */
    private ?PatientContext $patient = null;

    /**
     * Rule Weight
     */
    private const SYMPTOM_SCORE = 10;
    private const RED_FLAG_SCORE = 20;
    private const RISK_SCORE = 5;

    /**
     * Constructor
     */
    public function __construct(
        ?SymptomExtractionEngine $extractor = null
    )
    {

        $this->extractor =
            $extractor ??
            new SymptomExtractionEngine();

        $this->knowledge =
            MedicalKnowledge::getDiseases();

    }

    /**
     * ======================================================
     * Main Entry
     * ======================================================
     */

    public function analyze(

        string $text,

        ?PatientContext $patient = null

    ): array
    {

        if ($patient === null) {

            $patient = new PatientContext();

        }

        $this->patient = $patient;

        /**
         * STEP 1
         * NLP
         */

        $this->extraction =
            $this->extractor
                ->extract($text);
                if (empty($this->extraction["symptoms"])) {

    return [

        "success" => false,

        "message" => "ไม่พบอาการที่สามารถวิเคราะห์ได้",

        "engine" => "Reasoning Engine V2"

    ];

}

        /**
         * STEP 2
         * Candidate Disease
         */

        $this->candidates =
            [];

        foreach (

            $this->knowledge

            as

            $disease

        ) {

            $candidate =
                $this->buildCandidate(
                    $disease
                );

            if (

                $candidate["match_score"] > 0

            ) {

                $this->candidates[] =
                    $candidate;

            }

        }

        /**
         * STEP 3
         * Ranking
         */

        usort(

            $this->candidates,

            function ($a, $b) {

                return

                    $b["match_score"]

                    <=>

                    $a["match_score"];

            }

        );

        /**
         * STEP 4
         * Return
         */

        return

            $this->buildResult($text);

    }
        /**
     * ======================================================
     * Build Disease Candidate
     * ======================================================
     */

    private function buildCandidate(
        array $disease
    ): array
    {

        $matchedSymptoms = [];
        $matchedRedFlags = [];
        $matchedRiskFactors = [];

        $score = 0;

        //--------------------------------------------
        // Match Symptoms
        //--------------------------------------------

        foreach (

            $disease["symptoms"] ?? []

            as

            $symptom

        ) {

            if (

                in_array(

                    $symptom,

                    $this->extraction["symptoms"] ?? [],

                    true

                )

            ) {

                $matchedSymptoms[] =
                    $symptom;

                $score +=
                    self::SYMPTOM_SCORE;

            }

        }

        //--------------------------------------------
        // Match Red Flags
        //--------------------------------------------

        foreach (

            $disease["red_flags"] ?? []

            as

            $flag

        ) {

            if (

                in_array(

                    $flag,

                    $this->extraction["red_flags"] ?? [],

                    true

                )

            ) {

                $matchedRedFlags[] =
                    $flag;

                $score +=
                    self::RED_FLAG_SCORE;

            }

        }

        //--------------------------------------------
        // Match Risk Factors
        //--------------------------------------------

        foreach (

            $disease["risk_factors"] ?? []

            as

            $risk

        ) {

            if (

                $this->hasRiskFactor($risk)

            ) {

                $matchedRiskFactors[] =
                    $risk;

                $score +=
                    self::RISK_SCORE;

            }

        }

        //--------------------------------------------
        // Candidate Object
        //--------------------------------------------

        $candidate = [

           "disease_id" => $disease["disease_id"] ?? "",
         "disease_name_th" => $disease["disease_name_th"] ?? "",
"disease_name_en" => $disease["disease_name_en"] ?? "",
"category" => $disease["category"] ?? "",
"department" => $disease["department"] ?? "",
"severity_base_score" => $disease["severity_base_score"] ?? 0,
"ems_required" => $disease["ems_required"] ?? false,
"hospital_capability_required" => $disease["hospital_capability_required"] ?? [],
"recommendation" => $disease["recommendation"] ?? "",
"reasoning_note" => $disease["reasoning_note"] ?? "",

            //--------------------------------

            "matched_symptoms" =>

                $matchedSymptoms,

            "matched_red_flags" =>

                $matchedRedFlags,

            "matched_risk_factors" =>

                $matchedRiskFactors,

            //--------------------------------

            "matched_rules" => [],

            "clinical_evidence" => [],

            "confidence" => 0,

            "match_score" =>

                $score

        ];

        //--------------------------------------------
        // Rule Engine
        //--------------------------------------------

        $candidate =
            $this->applyMedicalRules(
                $candidate
            );

        //--------------------------------------------
        // Clinical Evidence
        //--------------------------------------------

        $candidate =
            $this->buildClinicalEvidence(
                $candidate
            );

        //--------------------------------------------
        // Confidence
        //--------------------------------------------

        $candidate["confidence"] =
            $this->calculateConfidence(
                $candidate
            );

        return

            $candidate;

    }
        /**
     * ======================================================
     * Patient Risk Evaluation
     * ======================================================
     */

    private function hasRiskFactor(
        string $risk
    ): bool
    {

        if ($this->patient === null) {
            return false;
        }

        $risk = mb_strtolower(trim($risk));

        //--------------------------------------------------
        // Age
        //--------------------------------------------------

        if ($risk === "elderly") {
            return $this->patient->isElderly();
        }

        if ($risk === "child") {
            return $this->patient->isChild();
        }

        //--------------------------------------------------
        // Pregnancy
        //--------------------------------------------------

        if ($risk === "pregnancy") {
            return $this->patient->pregnant;
        }

        //--------------------------------------------------
        // Smoking
        //--------------------------------------------------

        if ($risk === "smoker") {
            return $this->patient->smoker;
        }

        //--------------------------------------------------
        // Alcohol
        //--------------------------------------------------

        if ($risk === "alcohol") {
            return $this->patient->drinker;
        }

        //--------------------------------------------------
        // BMI
        //--------------------------------------------------

        if ($risk === "obesity") {
            return $this->patient->getBMI() >= 30;
        }

        if ($risk === "underweight") {
            $bmi = $this->patient->getBMI();

            return $bmi > 0 && $bmi < 18.5;
        }

        //--------------------------------------------------
        // Vital Signs
        //--------------------------------------------------

        if ($risk === "low_spo2") {
            return $this->patient->hasLowSpo2();
        }

        if ($risk === "high_fever") {
            return $this->patient->hasHighFever();
        }

        if ($risk === "tachycardia") {
            return $this->patient->hasTachycardia();
        }

        //--------------------------------------------------
        // Chronic Diseases
        //--------------------------------------------------

        if ($this->patient->hasDisease($risk)) {
            return true;
        }

        //--------------------------------------------------
        // Medical Entity จาก NLP
        //--------------------------------------------------

        foreach (
            $this->extraction["medical_entities"] ?? []
            as
            $entity
        ) {

            if (
                mb_strtolower($entity)
                ===
                $risk
            ) {
                return true;
            }

        }

        return false;

    }
        /**
     * ======================================================
     * Medical Rule Engine
     * ======================================================
     */

    private function applyMedicalRules(
        array $candidate
    ): array
    {
        $symptoms =
            $this->extraction["symptoms"] ?? [];

        $redFlags =
            $this->extraction["red_flags"] ?? [];

        $severityWords =
            $this->extraction["severity_words"] ?? [];

        $vitals =
            $this->extraction["vitals"] ?? [];

        //--------------------------------------------
        // ACS / Heart Emergency Rules
        //--------------------------------------------

        if (
            in_array("เจ็บหน้าอก", $symptoms, true) &&
            in_array("หายใจไม่ออก", $symptoms, true)
        ) {
            $candidate["match_score"] += 15;
            $candidate["matched_rules"][] =
                "Chest pain with dyspnea";
        }

        if (
            in_array("เจ็บหน้าอก", $symptoms, true) &&
            in_array("เหงื่อแตก", $symptoms, true)
        ) {
            $candidate["match_score"] += 15;
            $candidate["matched_rules"][] =
                "Chest pain with sweating";
        }

        if (
            in_array("เจ็บหน้าอก", $symptoms, true) &&
            in_array("เจ็บร้าวไปแขน", $symptoms, true)
        ) {
            $candidate["match_score"] += 20;
            $candidate["matched_rules"][] =
                "Chest pain radiating to arm";
        }

        //--------------------------------------------
        // Stroke FAST Rules
        //--------------------------------------------

        if (
            in_array("หน้าเบี้ยว", $symptoms, true) &&
            in_array("พูดไม่ชัด", $symptoms, true)
        ) {
            $candidate["match_score"] += 20;
            $candidate["matched_rules"][] =
                "FAST positive: face and speech";
        }

        if (
            in_array("แขนขาอ่อนแรง", $symptoms, true) &&
            (
                in_array("หน้าเบี้ยว", $symptoms, true) ||
                in_array("พูดไม่ชัด", $symptoms, true)
            )
        ) {
            $candidate["match_score"] += 20;
            $candidate["matched_rules"][] =
                "FAST positive: motor weakness";
        }

        //--------------------------------------------
        // Respiratory Failure Rules
        //--------------------------------------------

        if (
            in_array("หายใจไม่ออก", $symptoms, true) &&
            in_array("ปากเขียว", $symptoms, true)
        ) {
            $candidate["match_score"] += 20;
            $candidate["matched_rules"][] =
                "Severe respiratory distress";
        }

        if (
            isset($vitals["spo2"]) &&
            (int)$vitals["spo2"] < 94
        ) {
            $candidate["match_score"] += 15;
            $candidate["matched_rules"][] =
                "Low SpO2";
        }

        //--------------------------------------------
        // Trauma Rules
        //--------------------------------------------

        if (
            in_array("รถชน", $symptoms, true) ||
            in_array("ตกจากที่สูง", $symptoms, true) ||
            in_array("เลือดออกมาก", $symptoms, true)
        ) {
            $candidate["match_score"] += 15;
            $candidate["matched_rules"][] =
                "Major trauma pattern";
        }

        //--------------------------------------------
        // Sepsis / Infection Rules
        //--------------------------------------------

        if (
            in_array("ไข้", $symptoms, true) &&
            (
                in_array("ซึม", $redFlags, true) ||
                in_array("หายใจลำบาก", $symptoms, true)
            )
        ) {
            $candidate["match_score"] += 15;
            $candidate["matched_rules"][] =
                "Possible severe infection";
        }

        if (
            isset($vitals["temperature"]) &&
            (float)$vitals["temperature"] >= 38.5
        ) {
            $candidate["match_score"] += 10;
            $candidate["matched_rules"][] =
                "High fever";
        }

        //--------------------------------------------
        // General Red Flag Bonus
        //--------------------------------------------

        foreach ($redFlags as $flag) {
            $candidate["match_score"] += 5;
            $candidate["matched_rules"][] =
                "Red flag: " . $flag;
        }

        //--------------------------------------------
        // Severity Word Bonus
        //--------------------------------------------

        foreach ($severityWords as $word) {
            $candidate["match_score"] += 3;
            $candidate["matched_rules"][] =
                "Severity word: " . $word;
        }

        //--------------------------------------------
        // Patient Context Bonus
        //--------------------------------------------

        if ($this->patient !== null) {

            if ($this->patient->isElderly()) {
                $candidate["match_score"] += 5;
                $candidate["matched_rules"][] =
                    "Elderly patient";
            }

            if ($this->patient->isChild()) {
                $candidate["match_score"] += 3;
                $candidate["matched_rules"][] =
                    "Pediatric patient";
            }

            if ($this->patient->pregnant) {
                $candidate["match_score"] += 5;
                $candidate["matched_rules"][] =
                    "Pregnancy";
            }

            if ($this->patient->smoker) {
                $candidate["match_score"] += 2;
                $candidate["matched_rules"][] =
                    "Smoking history";
            }

            if ($this->patient->hasLowSpo2()) {
                $candidate["match_score"] += 15;
                $candidate["matched_rules"][] =
                    "Patient context: low SpO2";
            }

            if ($this->patient->hasHighFever()) {
                $candidate["match_score"] += 10;
                $candidate["matched_rules"][] =
                    "Patient context: high fever";
            }

            if ($this->patient->hasTachycardia()) {
                $candidate["match_score"] += 5;
                $candidate["matched_rules"][] =
                    "Patient context: tachycardia";
            }

        }

        //--------------------------------------------
        // Limit Score
        //--------------------------------------------

        if ($candidate["match_score"] > 100) {
            $candidate["match_score"] = 100;
        }

        $candidate["matched_rules"] =
            array_values(
                array_unique($candidate["matched_rules"])
            );

        return $candidate;
    }
        /**
     * ======================================================
     * Clinical Evidence Builder
     * ======================================================
     */

    private function buildClinicalEvidence(
        array $candidate
    ): array
    {
        $evidence = [];

        foreach ($candidate["matched_symptoms"] ?? [] as $symptom) {
            $evidence[] = [
                "type" => "symptom",
                "label" => "Matched Symptom",
                "value" => $symptom,
                "weight" => self::SYMPTOM_SCORE
            ];
        }

        foreach ($candidate["matched_red_flags"] ?? [] as $flag) {
            $evidence[] = [
                "type" => "red_flag",
                "label" => "Red Flag",
                "value" => $flag,
                "weight" => self::RED_FLAG_SCORE
            ];
        }

        foreach ($candidate["matched_risk_factors"] ?? [] as $risk) {
            $evidence[] = [
                "type" => "risk_factor",
                "label" => "Risk Factor",
                "value" => $risk,
                "weight" => self::RISK_SCORE
            ];
        }

        foreach ($candidate["matched_rules"] ?? [] as $rule) {
            $evidence[] = [
                "type" => "clinical_rule",
                "label" => "Clinical Rule",
                "value" => $rule,
                "weight" => 15
            ];
        }

        if (!empty($this->extraction["duration"])) {
            $evidence[] = [
                "type" => "duration",
                "label" => "Duration",
                "value" => $this->extraction["duration"],
                "weight" => 3
            ];
        }

        if (!empty($this->extraction["pain_score"])) {
            $evidence[] = [
                "type" => "pain_score",
                "label" => "Pain Score",
                "value" => $this->extraction["pain_score"],
                "weight" => 5
            ];
        }

        if (!empty($this->extraction["vitals"])) {
            foreach ($this->extraction["vitals"] as $key => $value) {
                $evidence[] = [
                    "type" => "vital_sign",
                    "label" => $key,
                    "value" => $value,
                    "weight" => 5
                ];
            }
        }

        $candidate["clinical_evidence"] = $evidence;

        return $candidate;
    }
        /**
     * ======================================================
     * Confidence Engine
     * ======================================================
     */

    private function calculateConfidence(
        array $candidate
    ): int
    {

        $confidence = 0;

        //------------------------------------------
        // Symptoms
        //------------------------------------------

        $symptomCount =
            count(
                $candidate["matched_symptoms"]
            );

        $confidence +=
            min(
                40,
                $symptomCount * 8
            );

        //------------------------------------------
        // Red Flag
        //------------------------------------------

        $redFlagCount =
            count(
                $candidate["matched_red_flags"]
            );

        $confidence +=
            min(
                25,
                $redFlagCount * 12
            );

        //------------------------------------------
        // Risk
        //------------------------------------------

        $riskCount =
            count(
                $candidate["matched_risk_factors"]
            );

        $confidence +=
            min(
                10,
                $riskCount * 3
            );

        //------------------------------------------
        // Clinical Rules
        //------------------------------------------

        $ruleCount =
            count(
                $candidate["matched_rules"]
            );

        $confidence +=
            min(
                15,
                $ruleCount * 5
            );

        //------------------------------------------
        // Match Score
        //------------------------------------------

        $confidence +=
            intval(
                $candidate["match_score"] * 0.10
            );

        //------------------------------------------
        // Severity
        //------------------------------------------

        $confidence +=
            intval(
                ($candidate["severity_base_score"] ?? 0)
            );

        //------------------------------------------
        // Extraction Confidence
        //------------------------------------------

        if (

            isset(
                $this->extraction["confidence"]
            )

        ) {

            $confidence +=
                intval(
                    $this->extraction["confidence"] * 0.10
                );

        }

        //------------------------------------------
        // Normalize
        //------------------------------------------

        if ($confidence > 100) {
            $confidence = 100;
        }

        if ($confidence < 0) {
            $confidence = 0;
        }

        return $confidence;

    }

    /**
     * ======================================================
     * Need More Questions ?
     * ======================================================
     */

    private function needMoreQuestions(
        array $candidate
    ): bool
    {

        return
            $candidate["confidence"] < 60;

    }

    /**
     * ======================================================
     * Suggested Questions
     * ======================================================
     */

    private function buildQuestionList(
        array $candidate
    ): array
    {

        $questions = [];

        if (
            in_array(
                "เจ็บหน้าอก",
                $candidate["matched_symptoms"],
                true
            )
        ) {

            $questions[] =
                "อาการเจ็บหน้าอกเป็นมานานกี่นาที";

            $questions[] =
                "เจ็บร้าวไปแขนหรือกรามหรือไม่";

            $questions[] =
                "มีเหงื่อแตกหรือคลื่นไส้หรือไม่";

        }

        if (

            in_array(
                "หายใจไม่ออก",
                $candidate["matched_symptoms"],
                true
            )

        ) {

            $questions[] =
                "สามารถพูดเป็นประโยคยาวได้หรือไม่";

            $questions[] =
                "ริมฝีปากเขียวหรือไม่";

        }

        if (

            in_array(
                "ไข้",
                $candidate["matched_symptoms"],
                true
            )

        ) {

            $questions[] =
                "ไข้มากกว่า 38.5°C หรือไม่";

            $questions[] =
                "มีหนาวสั่นร่วมด้วยหรือไม่";

        }

        return
            array_values(
                array_unique(
                    $questions
                )
            );

    }
        /**
     * ======================================================
     * Build Final Result
     * ======================================================
     */

    private function buildResult(
        string $text
    ): array
    {
        $topDiagnosis =
            $this->candidates[0] ?? null;

        $differentialDiagnosis =
            array_slice(
                $this->candidates,
                0,
                5
            );

        $requiresMoreQuestions = false;
        $followUpQuestions = [];

        if ($topDiagnosis !== null) {

            $requiresMoreQuestions =
                $this->needMoreQuestions(
                    $topDiagnosis
                );

            $followUpQuestions =
                $this->buildQuestionList(
                    $topDiagnosis
                );

        }

        return [

            "success" => true,

            "engine" => "Reasoning Engine V2",

            "input_text" => $text,

            "extraction" => $this->extraction,

            "top_diagnosis" => $topDiagnosis,

            "differential_diagnosis" =>
                $differentialDiagnosis,

            "candidate_count" =>
                count($this->candidates),

            "requires_more_questions" =>
                $requiresMoreQuestions,

            "follow_up_questions" =>
                $followUpQuestions,

            "ai_note" =>
                "วิเคราะห์โดย Open Hospital Medical Reasoning AI V2"

        ];
    }
        /**
     * ======================================================
     * Utility : Get Candidate By Disease ID
     * ======================================================
     */

    private function findCandidateByDiseaseId(
        string $diseaseId
    ): ?array
    {

        foreach ($this->candidates as $candidate) {

            if (
                ($candidate["disease_id"] ?? "")
                ===
                $diseaseId
            ) {
                return $candidate;
            }

        }

        return null;

    }

    /**
     * ======================================================
     * Utility : Check Red Flag
     * ======================================================
     */

    private function hasRedFlag(): bool
    {

        return !empty(
            $this->extraction["red_flags"] ?? []
        );

    }

    /**
     * ======================================================
     * Utility : Get Extraction
     * ======================================================
     */

    public function getExtraction(): array
    {

        return $this->extraction;

    }

    /**
     * ======================================================
     * Utility : Get Candidates
     * ======================================================
     */

    public function getCandidates(): array
    {

        return $this->candidates;

    }

    /**
     * ======================================================
     * Utility : Get Knowledge
     * ======================================================
     */

    public function getKnowledge(): array
    {

        return $this->knowledge;

    }

    /**
     * ======================================================
     * Utility : Reset Engine
     * ======================================================
     */

    public function reset(): void
    {

        $this->candidates = [];

        $this->extraction = [];

        $this->patient = null;

    }

}