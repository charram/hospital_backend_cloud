<?php

require_once __DIR__ . "/medical_knowledge.php";

class SymptomExtractionEngine
{
    private array $synonyms = [
        "เจ็บอก" => "เจ็บหน้าอก",
        "เจ็บกลางอก" => "เจ็บหน้าอก",
        "แน่นอก" => "แน่นหน้าอก",
        "จุกอก" => "แน่นหน้าอก",
        "ร้าวแขนซ้าย" => "เจ็บร้าวไปแขน",
        "เหงื่อออกมาก" => "เหงื่อแตก",
        "ใจเต้นเร็ว" => "ใจสั่น",

        "เหนื่อย" => "หายใจลำบาก",
        "หอบ" => "หายใจลำบาก",
        "หอบเหนื่อย" => "หายใจลำบาก",
        "หายใจไม่เต็มปอด" => "หายใจลำบาก",
        "หายใจไม่สะดวก" => "หายใจลำบาก",
        "ปากม่วง" => "ปากเขียว",
        "ตัวเขียว" => "ปากเขียว",
        "ไอเยอะ" => "ไอ",
        "ไอมาก" => "ไอ",
        "เสมหะ" => "มีเสมหะ",

        "ปากเบี้ยว" => "หน้าเบี้ยว",
        "พูดลำบาก" => "พูดไม่ชัด",
        "พูดไม่รู้เรื่อง" => "พูดไม่ชัด",
        "แขนไม่มีแรง" => "แขนขาอ่อนแรง",
        "ขาไม่มีแรง" => "แขนขาอ่อนแรง",
        "ชาครึ่งตัว" => "ชาครึ่งซีก",
        "เดินไม่ตรง" => "เดินเซ",
        "เวียนศีรษะ" => "เวียนหัว",
        "บ้านหมุน" => "เวียนหัว",
        "มึนหัว" => "เวียนหัว",
        "ปวดศีรษะ" => "ปวดหัว",

        "ตัวร้อน" => "ไข้",
        "ไข้สูง" => "ไข้",
        "มีไข้" => "ไข้",
        "ปวดตัว" => "ปวดเมื่อย",
        "เพลีย" => "อ่อนเพลีย",
        "ไม่มีแรง" => "อ่อนเพลีย",

        "ถ่ายเหลว" => "ท้องเสีย",
        "ถ่ายบ่อย" => "ท้องเสีย",
        "อ้วก" => "อาเจียน",
        "ถ่ายเลือด" => "ถ่ายเป็นเลือด",
        "ถ่ายมีเลือด" => "ถ่ายเป็นเลือด",
        "อ้วกเป็นเลือด" => "อาเจียนเป็นเลือด",

        "มอไซค์ล้ม" => "รถชน",
        "ตกที่สูง" => "ตกจากที่สูง",
        "โดนกระแทก" => "กระแทก",
        "เลือดไหลเยอะ" => "เลือดออกมาก",
        "เลือดไหลไม่หยุด" => "เลือดออกมาก",

        "ผื่นขึ้น" => "ผื่น",
        "ลมพิษ" => "ผื่น",
        "ตื่นตระหนก" => "แพนิค",
        "วิตก" => "วิตกกังวล",
        "กลัวตาย" => "กลัว"
    ];

    private array $negationWords = [
        "ไม่มี", "ไม่ได้", "ไม่เป็น", "ไม่เจ็บ", "ไม่ปวด",
        "ไม่ไอ", "ไม่มีไข้", "ไม่มีอาการ", "ไม่รู้สึก",
        "ปฏิเสธ", "ไม่พบ", "ไม่เคย"
    ];

    private array $timeWords = [
        "ตอนนี้", "วันนี้", "เมื่อคืน", "เมื่อวาน", "เมื่อเช้า",
        "เมื่อครู่", "ทันที", "เฉียบพลัน", "เรื้อรัง",
        "หลายวัน", "หลายชั่วโมง", "เป็นๆหายๆ", "เป็น ๆ หาย ๆ"
    ];

    private array $severityWords = [
        "รุนแรง", "มาก", "มากๆ", "มาก ๆ", "สุดๆ",
        "ทนไม่ไหว", "แย่ลง", "หนักขึ้น", "เฉียบพลัน", "ทันที"
    ];

    private array $bodyLocations = [
        "หน้าอก", "กลางอก", "อกซ้าย", "หัว", "ศีรษะ",
        "ท้อง", "ท้องน้อย", "ชายโครง", "แขนซ้าย",
        "แขนขวา", "แขน", "ขา", "ใบหน้า", "ปาก",
        "คอ", "หลัง", "เอว", "ตา", "หู"
    ];

    private array $diseaseEntities = [
        "เบาหวาน" => "diabetes",
        "ความดัน" => "hypertension",
        "ความดันโลหิตสูง" => "hypertension",
        "โรคหัวใจ" => "heart_disease",
        "โรคไต" => "kidney_disease",
        "ไตเรื้อรัง" => "ckd",
        "หอบหืด" => "asthma",
        "ถุงลมโป่งพอง" => "copd",
        "มะเร็ง" => "cancer"
    ];

    public function extract(string $text): array
    {
        $originalText = trim($text);
        $normalizedText = $this->normalize($originalText);

        $dictionary = $this->buildDictionary();

        $symptoms = $this->extractSymptoms($normalizedText, $dictionary);
        $excluded = $this->extractNegations($normalizedText, $dictionary);

        if (!empty($excluded)) {
            $symptoms = array_values(array_diff($symptoms, $excluded));
        }

        $redFlags = $this->extractRedFlags($symptoms);
        $locations = $this->extractFromList($normalizedText, $this->bodyLocations);
        $timeExpressions = $this->extractFromList($normalizedText, $this->timeWords);
        $severityWords = $this->extractFromList($normalizedText, $this->severityWords);

        $duration = $this->extractDuration($normalizedText);
        $painScore = $this->extractPainScore($normalizedText);
        $laterality = $this->extractLaterality($normalizedText);
        $vitals = $this->extractVitals($normalizedText);
        $medicalEntities = $this->extractMedicalEntities($normalizedText);
        $pregnancy = $this->extractPregnancy($normalizedText);
        $smoking = $this->extractSmoking($normalizedText);
        $alcohol = $this->extractAlcohol($normalizedText);
        $triggers = $this->extractTriggers($normalizedText);

        $confidence = $this->calculateConfidence(
            $symptoms,
            $redFlags,
            $locations,
            $timeExpressions,
            $duration,
            $painScore,
            $vitals,
            $medicalEntities
        );

        return [
            "original_text" => $originalText,
            "normalized_text" => $normalizedText,

            "symptoms" => $symptoms,
            "excluded_symptoms" => $excluded,
            "red_flags" => $redFlags,

            "body_locations" => $locations,
            "laterality" => $laterality,

            "time_expressions" => $timeExpressions,
            "duration" => $duration,

            "severity_words" => $severityWords,
            "pain_score" => $painScore,

            "vitals" => $vitals,
            "medical_entities" => $medicalEntities,

            "pregnancy" => $pregnancy,
            "smoking" => $smoking,
            "alcohol" => $alcohol,

            "triggers" => $triggers,

            "symptom_count" => count($symptoms),
            "red_flag_count" => count($redFlags),
            "confidence" => $confidence,

            "ready_for_reasoning" => count($symptoms) > 0
        ];
    }

    private function normalize(string $text): string
    {
        $text = trim($text);
        $text = mb_strtolower($text, "UTF-8");
        $text = str_replace(["\n", "\r", "\t"], " ", $text);
        $text = preg_replace('/\s+/u', ' ', $text);

        return $text ?? "";
    }

    private function buildDictionary(): array
    {
        $dictionary = [];

        if (method_exists("MedicalKnowledge", "getAllSymptoms")) {
            $dictionary = array_merge($dictionary, MedicalKnowledge::getAllSymptoms());
        }

        $fallbackMethods = [
            "emergencyWords",
            "heartWords",
            "brainWords",
            "respiratoryWords",
            "feverWords",
            "stomachWords",
            "traumaWords",
            "allergyWords",
            "mentalWords"
        ];

        foreach ($fallbackMethods as $method) {
            if (method_exists("MedicalKnowledge", $method)) {
                $dictionary = array_merge($dictionary, MedicalKnowledge::$method());
            }
        }

        foreach ($this->synonyms as $standard) {
            $dictionary[] = $standard;
        }

        return array_values(array_unique($dictionary));
    }

    private function extractSymptoms(string $text, array $dictionary): array
    {
        $symptoms = [];

        foreach ($dictionary as $symptom) {
            if ($symptom !== "" && mb_strpos($text, mb_strtolower($symptom, "UTF-8")) !== false) {
                $symptoms[] = $symptom;
            }
        }

        foreach ($this->synonyms as $alias => $standard) {
            if (mb_strpos($text, mb_strtolower($alias, "UTF-8")) !== false) {
                $symptoms[] = $standard;
            }
        }

        return array_values(array_unique($symptoms));
    }

    private function extractNegations(string $text, array $dictionary): array
    {
        $excluded = [];

        foreach ($dictionary as $symptom) {
            foreach ($this->negationWords as $negation) {
                $patterns = [
                    $negation . $symptom,
                    $negation . " " . $symptom,
                    $negation . "อาการ" . $symptom,
                    $negation . "มี" . $symptom
                ];

                foreach ($patterns as $pattern) {
                    if (mb_strpos($text, $pattern) !== false) {
                        $excluded[] = $symptom;
                    }
                }
            }
        }

        return array_values(array_unique($excluded));
    }

    private function extractRedFlags(array $symptoms): array
    {
        $flags = [];

        $redFlagDictionary = [];

        if (method_exists("MedicalKnowledge", "getAllRedFlags")) {
            $redFlagDictionary = MedicalKnowledge::getAllRedFlags();
        } else {
            $redFlagDictionary = [
                "หมดสติ", "หายใจไม่ออก", "เจ็บหน้าอก", "พูดไม่ชัด",
                "หน้าเบี้ยว", "แขนขาอ่อนแรง", "เลือดออกมาก",
                "ถ่ายเป็นเลือด", "อาเจียนเป็นเลือด", "ปากเขียว"
            ];
        }

        foreach ($redFlagDictionary as $flag) {
            if (in_array($flag, $symptoms, true)) {
                $flags[] = $flag;
            }
        }

        return array_values(array_unique($flags));
    }

    private function extractFromList(string $text, array $list): array
    {
        $found = [];

        foreach ($list as $item) {
            if (mb_strpos($text, $item) !== false) {
                $found[] = $item;
            }
        }

        return array_values(array_unique($found));
    }

    private function extractDuration(string $text): ?array
    {
        $patterns = [
            '/(\d+)\s*(นาที|ชั่วโมง|วัน|สัปดาห์|เดือน|ปี)/u',
            '/(หลาย)\s*(นาที|ชั่วโมง|วัน|สัปดาห์|เดือน|ปี)/u'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return [
                    "raw" => $matches[0],
                    "value" => is_numeric($matches[1]) ? intval($matches[1]) : null,
                    "unit" => $matches[2]
                ];
            }
        }

        if (mb_strpos($text, "เรื้อรัง") !== false) {
            return [
                "raw" => "เรื้อรัง",
                "value" => null,
                "unit" => "chronic"
            ];
        }

        return null;
    }

    private function extractPainScore(string $text): ?int
    {
        if (preg_match('/(\d{1,2})\s*\/\s*10/u', $text, $matches)) {
            $score = intval($matches[1]);
            return max(0, min(10, $score));
        }

        if (preg_match('/ปวด\s*(\d{1,2})/u', $text, $matches)) {
            $score = intval($matches[1]);
            return max(0, min(10, $score));
        }

        if (mb_strpos($text, "ปวดมาก") !== false || mb_strpos($text, "ทนไม่ไหว") !== false) {
            return 8;
        }

        if (mb_strpos($text, "ปวดนิด") !== false || mb_strpos($text, "ปวดเล็กน้อย") !== false) {
            return 2;
        }

        return null;
    }

    private function extractLaterality(string $text): array
    {
        $laterality = [];

        if (mb_strpos($text, "ซ้าย") !== false) {
            $laterality[] = "left";
        }

        if (mb_strpos($text, "ขวา") !== false) {
            $laterality[] = "right";
        }

        if (mb_strpos($text, "สองข้าง") !== false || mb_strpos($text, "ทั้งสองข้าง") !== false) {
            $laterality[] = "bilateral";
        }

        return array_values(array_unique($laterality));
    }

    private function extractVitals(string $text): array
    {
        $vitals = [];

        if (preg_match('/spo2\s*[:=]?\s*(\d{2,3})/iu', $text, $m)) {
            $vitals["spo2"] = intval($m[1]);
        }

        if (preg_match('/ออกซิเจน\s*(\d{2,3})/u', $text, $m)) {
            $vitals["spo2"] = intval($m[1]);
        }

        if (preg_match('/(?:ไข้|อุณหภูมิ|temp)\s*[:=]?\s*(\d{2}(?:\.\d)?)/iu', $text, $m)) {
            $vitals["temperature"] = floatval($m[1]);
        }

        if (preg_match('/(?:bp|ความดัน)\s*[:=]?\s*(\d{2,3})\s*\/\s*(\d{2,3})/iu', $text, $m)) {
            $vitals["blood_pressure"] = $m[1] . "/" . $m[2];
            $vitals["systolic"] = intval($m[1]);
            $vitals["diastolic"] = intval($m[2]);
        }

        if (preg_match('/(?:ชีพจร|hr|heart rate)\s*[:=]?\s*(\d{2,3})/iu', $text, $m)) {
            $vitals["heart_rate"] = intval($m[1]);
        }

        return $vitals;
    }

    private function extractMedicalEntities(string $text): array
    {
        $entities = [];

        foreach ($this->diseaseEntities as $word => $code) {
            if (mb_strpos($text, $word) !== false) {
                $entities[] = $code;
            }
        }

        return array_values(array_unique($entities));
    }

    private function extractPregnancy(string $text): ?array
    {
        if (
            mb_strpos($text, "ตั้งครรภ์") === false &&
            mb_strpos($text, "คนท้อง") === false &&
            mb_strpos($text, "ท้อง") === false
        ) {
            return null;
        }

        $weeks = null;

        if (preg_match('/(\d{1,2})\s*สัปดาห์/u', $text, $m)) {
            $weeks = intval($m[1]);
        }

        return [
            "pregnant" => true,
            "gestational_weeks" => $weeks
        ];
    }

    private function extractSmoking(string $text): ?array
    {
        if (mb_strpos($text, "สูบบุหรี่") === false) {
            return null;
        }

        return [
            "smoker" => true
        ];
    }

    private function extractAlcohol(string $text): ?array
    {
        if (
            mb_strpos($text, "ดื่มเหล้า") === false &&
            mb_strpos($text, "ดื่มแอลกอฮอล์") === false
        ) {
            return null;
        }

        return [
            "drinker" => true
        ];
    }

    private function extractTriggers(string $text): array
    {
        $triggers = [];

        $triggerWords = [
            "หลังอาหาร",
            "หลังออกกำลังกาย",
            "หลังล้ม",
            "หลังรถชน",
            "หลังกินยา",
            "หลังแพ้อาหาร",
            "ตอนเดิน",
            "ตอนนอน",
            "ตอนหายใจ",
            "เวลาไอ"
        ];

        foreach ($triggerWords as $trigger) {
            if (mb_strpos($text, $trigger) !== false) {
                $triggers[] = $trigger;
            }
        }

        return array_values(array_unique($triggers));
    }

    private function calculateConfidence(
        array $symptoms,
        array $redFlags,
        array $locations,
        array $timeExpressions,
        ?array $duration,
        ?int $painScore,
        array $vitals,
        array $medicalEntities
    ): int {
        $confidence = 40;

        $confidence += count($symptoms) * 8;
        $confidence += count($redFlags) * 10;
        $confidence += count($locations) * 3;
        $confidence += count($timeExpressions) * 2;
        $confidence += count($vitals) * 5;
        $confidence += count($medicalEntities) * 4;

        if ($duration !== null) {
            $confidence += 5;
        }

        if ($painScore !== null) {
            $confidence += 5;
        }

        return min(100, max(0, $confidence));
    }

    public function extractMultiple(array $texts): array
    {
        $combined = implode(" ", $texts);
        return $this->extract($combined);
    }

    public function exportForReasoning(string $text): array
    {
        $data = $this->extract($text);

        return [
            "symptoms" => $data["symptoms"],
            "excluded_symptoms" => $data["excluded_symptoms"],
            "red_flags" => $data["red_flags"],
            "confidence" => $data["confidence"],
            "locations" => $data["body_locations"],
            "laterality" => $data["laterality"],
            "severity_words" => $data["severity_words"],
            "pain_score" => $data["pain_score"],
            "time" => $data["time_expressions"],
            "duration" => $data["duration"],
            "vitals" => $data["vitals"],
            "medical_entities" => $data["medical_entities"],
            "pregnancy" => $data["pregnancy"],
            "smoking" => $data["smoking"],
            "alcohol" => $data["alcohol"],
            "triggers" => $data["triggers"]
        ];
    }

    public function debug(string $text): void
    {
        echo "<pre>";
        print_r($this->extract($text));
        echo "</pre>";
    }
}