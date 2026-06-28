<?php

class HospitalRecommendationEngine
{
    public function recommend(
        string $department,
        bool $emsRequired
    ): array {

        if ($emsRequired) {

            return [
                "hospital_type" => "Emergency Center",
                "priority" => "สูง",
                "recommendation" =>
                    "ควรเลือกโรงพยาบาลที่มีศูนย์อุบัติเหตุและฉุกเฉิน (ER) ตลอด 24 ชั่วโมง"
            ];

        }

        switch ($department) {

            case "หัวใจ":

                return [
                    "hospital_type" => "Cardiac Center",
                    "priority" => "สูง",
                    "recommendation" =>
                        "แนะนำโรงพยาบาลที่มีศูนย์หัวใจ"
                ];

            case "ระบบประสาท":

                return [
                    "hospital_type" => "Stroke Center",
                    "priority" => "สูง",
                    "recommendation" =>
                        "แนะนำโรงพยาบาลที่มี Stroke Unit"
                ];

            case "โรคปอด / อายุรกรรม":

                return [
                    "hospital_type" => "Pulmonary Center",
                    "priority" => "ปานกลาง",
                    "recommendation" =>
                        "แนะนำโรงพยาบาลที่มีอายุรแพทย์โรคปอด"
                ];

            case "ทางเดินอาหาร / อายุรกรรม":

                return [
                    "hospital_type" => "GI Center",
                    "priority" => "ปานกลาง",
                    "recommendation" =>
                        "แนะนำโรงพยาบาลที่มีอายุรแพทย์ทางเดินอาหาร"
                ];

            case "สุขภาพจิต":

                return [
                    "hospital_type" => "Psychiatric Hospital",
                    "priority" => "ต่ำ",
                    "recommendation" =>
                        "แนะนำโรงพยาบาลด้านสุขภาพจิต"
                ];

            default:

                return [
                    "hospital_type" => "General Hospital",
                    "priority" => "ปานกลาง",
                    "recommendation" =>
                        "สามารถเข้ารับบริการโรงพยาบาลทั่วไป"
                ];
        }
    }
}