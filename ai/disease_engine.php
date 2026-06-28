<?php

class DiseaseEngine
{
    private array $diseases = [

        [
            "name" => "สงสัยกลุ่มอาการหัวใจเฉียบพลัน",
            "department" => "หัวใจ",
            "keywords" => [
                "เจ็บหน้าอก",
                "แน่นหน้าอก",
                "หายใจไม่ออก",
                "เหงื่อแตก",
                "เจ็บร้าวไปแขน",
                "หน้ามืด"
            ],
            "severity" => 9,
            "ems" => true,
            "recommendation" => "ควรเรียก EMS หรือไปโรงพยาบาลทันที"
        ],

        [
            "name" => "สงสัยภาวะ Stroke",
            "department" => "ระบบประสาท",
            "keywords" => [
                "หน้าเบี้ยว",
                "พูดไม่ชัด",
                "แขนขาอ่อนแรง",
                "ชาครึ่งซีก",
                "เดินเซ",
                "หมดสติ"
            ],
            "severity" => 9,
            "ems" => true,
            "recommendation" => "ควรเรียก EMS ทันที"
        ],

        [
            "name" => "กลุ่มอาการหายใจลำบาก",
            "department" => "โรคปอด",
            "keywords" => [
                "หายใจไม่ออก",
                "หายใจลำบาก",
                "แน่นหน้าอก",
                "ปากเขียว",
                "หน้าซีด"
            ],
            "severity" => 8,
            "ems" => true,
            "recommendation" => "ควรพบแพทย์โดยด่วน"
        ],

        [
            "name" => "กลุ่มอาการติดเชื้อหรือไข้",
            "department" => "อายุรกรรม",
            "keywords" => [
                "ไข้",
                "ตัวร้อน",
                "หนาวสั่น",
                "ไอ",
                "เจ็บคอ",
                "อ่อนเพลีย"
            ],
            "severity" => 3,
            "ems" => false,
            "recommendation" => "ควรพบแพทย์ภายในวันนี้"
        ],

        [
            "name" => "กลุ่มอาการทางเดินอาหาร",
            "department" => "อายุรกรรม",
            "keywords" => [
                "ปวดท้อง",
                "ท้องเสีย",
                "อาเจียน",
                "คลื่นไส้",
                "ถ่ายเหลว"
            ],
            "severity" => 3,
            "ems" => false,
            "recommendation" => "ควรพบแพทย์หากอาการไม่ดีขึ้น"
        ],

        [
            "name" => "อุบัติเหตุหรือบาดเจ็บ",
            "department" => "ฉุกเฉิน",
            "keywords" => [
                "รถชน",
                "ล้ม",
                "ตกจากที่สูง",
                "กระดูกหัก",
                "เลือดออก",
                "แผลลึก"
            ],
            "severity" => 8,
            "ems" => true,
            "recommendation" => "ควรเรียก EMS หรือไปโรงพยาบาลทันที"
        ]

    ];

    public function findDisease(string $text): ?array
    {
        $text = mb_strtolower(trim($text), "UTF-8");

        $bestDisease = null;
        $bestScore = 0;

        foreach ($this->diseases as $disease) {

            $score = 0;

            foreach ($disease["keywords"] as $keyword) {

                if (mb_strpos($text, $keyword) !== false) {
                    $score++;
                }

            }

            if ($score > $bestScore) {

                $bestScore = $score;
                $bestDisease = $disease;

            }

        }

        return $bestDisease;
    }

    public function getAllDiseases(): array
    {
        return $this->diseases;
    }
}