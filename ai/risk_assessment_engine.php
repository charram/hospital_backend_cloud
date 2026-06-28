<?php

require_once __DIR__ . "/patient_context.php";

class RiskAssessmentEngine
{
    public function assess(PatientContext $patient): array
    {
        $score = 0;
        $reasons = [];

        // ==========================
        // Age
        // ==========================

        if ($patient->isElderly()) {
            $score += 2;
            $reasons[] = "ผู้ป่วยอายุ 60 ปีขึ้นไป";
        }

        if ($patient->isChild()) {
            $score += 1;
            $reasons[] = "ผู้ป่วยเป็นเด็ก";
        }

        // ==========================
        // Vital Signs
        // ==========================

        if ($patient->hasLowSpo2()) {
            $score += 4;
            $reasons[] = "ค่าออกซิเจนในเลือดต่ำ (SpO₂ < 94%)";
        }

        if ($patient->hasHighFever()) {
            $score += 2;
            $reasons[] = "มีไข้สูง";
        }

        if ($patient->hasTachycardia()) {
            $score += 2;
            $reasons[] = "หัวใจเต้นเร็วผิดปกติ";
        }

        // ==========================
        // Chronic Diseases
        // ==========================

        if ($patient->hasDisease("diabetes")) {
            $score += 2;
            $reasons[] = "มีโรคเบาหวาน";
        }

        if ($patient->hasDisease("hypertension")) {
            $score += 2;
            $reasons[] = "มีโรคความดันโลหิตสูง";
        }

        if ($patient->hasDisease("heart_disease")) {
            $score += 3;
            $reasons[] = "มีโรคหัวใจ";
        }

        if ($patient->hasDisease("lung_disease")) {
            $score += 3;
            $reasons[] = "มีโรคปอด";
        }

        if ($patient->hasDisease("ckd")) {
            $score += 2;
            $reasons[] = "มีโรคไตเรื้อรัง";
        }

        if ($patient->hasDisease("copd")) {
            $score += 3;
            $reasons[] = "มีโรคปอดอุดกั้นเรื้อรัง";
        }

        if ($patient->hasDisease("asthma")) {
            $score += 2;
            $reasons[] = "มีโรคหอบหืด";
        }

        if ($patient->hasDisease("cancer")) {
            $score += 3;
            $reasons[] = "มีโรคมะเร็ง";
        }

        if ($patient->hasDisease("stroke")) {
            $score += 3;
            $reasons[] = "มีประวัติโรคหลอดเลือดสมอง";
        }

        // ==========================
        // Pregnancy
        // ==========================

        if ($patient->pregnant) {
            $score += 2;
            $reasons[] = "กำลังตั้งครรภ์";
        }

        // ==========================
        // Lifestyle
        // ==========================

        if ($patient->smoker) {
            $score += 1;
            $reasons[] = "สูบบุหรี่";
        }

        if ($patient->drinker) {
            $score += 1;
            $reasons[] = "ดื่มแอลกอฮอล์";
        }

        // ==========================
        // BMI
        // ==========================

        $bmi = $patient->getBMI();

        if ($bmi >= 30) {
            $score += 2;
            $reasons[] = "ภาวะอ้วน";
        } elseif ($bmi >= 25) {
            $score += 1;
            $reasons[] = "น้ำหนักเกิน";
        } elseif ($bmi > 0 && $bmi < 18.5) {
            $score += 1;
            $reasons[] = "น้ำหนักต่ำกว่าเกณฑ์";
        }

        // ==========================
        // Blood Pressure
        // ==========================

        if (!empty($patient->bloodPressure)) {

            $bp = explode("/", $patient->bloodPressure);

            if (count($bp) == 2) {

                $sys = intval($bp[0]);
                $dia = intval($bp[1]);

                if ($sys >= 180 || $dia >= 120) {
                    $score += 4;
                    $reasons[] = "ความดันโลหิตสูงวิกฤต";
                } elseif ($sys >= 140 || $dia >= 90) {
                    $score += 2;
                    $reasons[] = "ความดันโลหิตสูง";
                }
            }
        }

        // ==========================
        // Temperature
        // ==========================

        if ($patient->temperature >= 40) {
            $score += 3;
            $reasons[] = "ไข้สูงมาก";
        }

        // ==========================
        // Oxygen
        // ==========================

        if ($patient->spo2 > 0 && $patient->spo2 < 90) {
            $score += 4;
            $reasons[] = "ออกซิเจนต่ำรุนแรง";
        }

        // ==========================
        // Heart Rate
        // ==========================

        if ($patient->heartRate >= 140) {
            $score += 3;
            $reasons[] = "หัวใจเต้นเร็วมาก";
        }

        // ==========================
        // Normalize
        // ==========================

        if ($score > 10) {
            $score = 10;
        }

        // ==========================
        // Risk Level
        // ==========================

        if ($score >= 10) {
            $level = "สูงมาก";
        } elseif ($score >= 7) {
            $level = "สูง";
        } elseif ($score >= 4) {
            $level = "ปานกลาง";
        } else {
            $level = "ต่ำ";
        }

        return [

            "risk_score" => $score,

            "risk_level" => $level,

            "reasons" => $reasons

        ];
    }
}