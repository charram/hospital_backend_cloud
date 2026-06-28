<?php

require_once __DIR__ . "/patient_context.php";

class RiskAssessmentEngine
{
    public function assess(PatientContext $patient): array
    {
        $score = 0;
        $reasons = [];

        // อายุ
        if ($patient->isElderly()) {
            $score += 2;
            $reasons[] = "ผู้ป่วยอายุ 60 ปีขึ้นไป";
        }

        if ($patient->isChild()) {
            $score += 1;
            $reasons[] = "ผู้ป่วยเป็นเด็ก";
        }

        // ค่าออกซิเจน
        if ($patient->hasLowSpo2()) {
            $score += 4;
            $reasons[] = "ค่าออกซิเจนในเลือดต่ำ";
        }

        // ไข้สูง
        if ($patient->hasHighFever()) {
            $score += 2;
            $reasons[] = "มีไข้สูง";
        }

        // หัวใจเต้นเร็ว
        if ($patient->hasTachycardia()) {
            $score += 2;
            $reasons[] = "หัวใจเต้นเร็วผิดปกติ";
        }

        // โรคประจำตัว
        if ($patient->hasDisease("เบาหวาน")) {
            $score += 2;
            $reasons[] = "มีโรคเบาหวาน";
        }

        if ($patient->hasDisease("ความดันโลหิตสูง")) {
            $score += 2;
            $reasons[] = "มีโรคความดันโลหิตสูง";
        }

        if ($patient->hasDisease("โรคหัวใจ")) {
            $score += 3;
            $reasons[] = "มีโรคหัวใจ";
        }

        if ($patient->hasDisease("โรคปอด")) {
            $score += 3;
            $reasons[] = "มีโรคปอด";
        }

        if ($patient->hasDisease("โรคไต")) {
            $score += 2;
            $reasons[] = "มีโรคไต";
        }

        // การตั้งครรภ์
        if ($patient->pregnant) {
            $score += 2;
            $reasons[] = "กำลังตั้งครรภ์";
        }

        // สูบบุหรี่
        if ($patient->smoker) {
            $score += 1;
            $reasons[] = "สูบบุหรี่";
        }

        // ดื่มสุรา
        if ($patient->drinker) {
            $score += 1;
            $reasons[] = "ดื่มแอลกอฮอล์";
        }

        // BMI
        $bmi = $patient->getBMI();

        if ($bmi >= 30) {
            $score += 2;
            $reasons[] = "ภาวะอ้วน";
        }

        if ($bmi > 0 && $bmi < 18.5) {
            $score += 1;
            $reasons[] = "น้ำหนักต่ำกว่าเกณฑ์";
        }

        // ระดับความเสี่ยง
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