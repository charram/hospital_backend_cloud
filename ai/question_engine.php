<?php

declare(strict_types=1);

require_once __DIR__ . "/symptom_extraction_engine.php";
require_once __DIR__ . "/patient_context.php";

class QuestionEngine
{
    private SymptomExtractionEngine $extractor;

    public function __construct(?SymptomExtractionEngine $extractor = null)
    {
        $this->extractor = $extractor ?? new SymptomExtractionEngine();
    }

    public function analyze(
        string $inputText,
        array $answers = [],
        ?PatientContext $patient = null
    ): array {
        $extraction = $this->extractor->extract($inputText);

        $chiefComplaint = $this->detectChiefComplaint(
            $inputText,
            $extraction
        );

        if ($chiefComplaint === null) {
            return [
                "success" => false,
                "needs_more_info" => true,
                "message" => "ยังระบุกลุ่มอาการหลักไม่ได้",
                "next_question" => [
                    "question_id" => "general_001",
                    "question" => "อาการหลักที่เป็นตอนนี้คืออะไร?",
                    "type" => "text",
                    "options" => []
                ],
                "extraction" => $extraction
            ];
        }

        $pathway = $this->getPathway($chiefComplaint);

        $nextQuestion = $this->getNextQuestion(
            $pathway,
            $answers
        );

        $isComplete = $nextQuestion === null;

        return [
            "success" => true,
            "engine" => "QuestionEngine V1",
            "chief_complaint" => $chiefComplaint,
            "needs_more_info" => !$isComplete,
            "next_question" => $nextQuestion,
            "answered" => $answers,
            "extraction" => $extraction,
            "ready_for_reasoning" => $isComplete,
            "reasoning_payload" => [
                "chief_complaint" => $chiefComplaint,
                "symptoms" => $extraction["symptoms"] ?? [],
                "answers" => $answers,
                "patient" => $patient ? $patient->toArray() : null
            ]
        ];
    }

    private function detectChiefComplaint(
        string $text,
        array $extraction
    ): ?string {
        $symptoms = $extraction["symptoms"] ?? [];
        $raw = mb_strtolower($text, "UTF-8");

        if (
            in_array("ปวดท้อง", $symptoms, true) ||
            mb_strpos($raw, "ปวดท้อง") !== false
        ) {
            return "abdominal_pain";
        }

        if (
            in_array("ปวดหัว", $symptoms, true) ||
            in_array("ปวดหัวรุนแรง", $symptoms, true) ||
            mb_strpos($raw, "ปวดหัว") !== false ||
            mb_strpos($raw, "ปวดศีรษะ") !== false
        ) {
            return "headache";
        }

        if (
            in_array("เจ็บหน้าอก", $symptoms, true) ||
            in_array("แน่นหน้าอก", $symptoms, true) ||
            mb_strpos($raw, "เจ็บหน้าอก") !== false ||
            mb_strpos($raw, "แน่นอก") !== false
        ) {
            return "chest_pain";
        }

        if (
            in_array("หายใจไม่ออก", $symptoms, true) ||
            in_array("หายใจลำบาก", $symptoms, true) ||
            in_array("หอบ", $symptoms, true)
        ) {
            return "dyspnea";
        }

        if (
            in_array("ไข้", $symptoms, true) ||
            mb_strpos($raw, "ตัวร้อน") !== false
        ) {
            return "fever";
        }

        return null;
    }

    private function getNextQuestion(
        array $pathway,
        array $answers
    ): ?array {
        foreach ($pathway as $question) {
            $id = $question["question_id"];

            if (!array_key_exists($id, $answers)) {
                return $question;
            }

            if (!empty($question["followups"])) {
                $answer = $answers[$id];

                foreach ($question["followups"] as $condition => $followups) {
                    if ($answer === $condition) {
                        foreach ($followups as $followup) {
                            if (!array_key_exists($followup["question_id"], $answers)) {
                                return $followup;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    private function getPathway(string $chiefComplaint): array
    {
        return match ($chiefComplaint) {
            "abdominal_pain" => $this->abdominalPainPathway(),
            "headache" => $this->headachePathway(),
            "chest_pain" => $this->chestPainPathway(),
            "dyspnea" => $this->dyspneaPathway(),
            "fever" => $this->feverPathway(),
            default => []
        };
    }

    private function abdominalPainPathway(): array
    {
        return [
            [
                "question_id" => "abd_location",
                "question" => "ปวดท้องบริเวณไหน?",
                "type" => "choice",
                "options" => [
                    "ลิ้นปี่",
                    "รอบสะดือ",
                    "ท้องน้อยขวา",
                    "ท้องน้อยซ้าย",
                    "ทั่วท้อง"
                ],
                "clinical_purpose" => "แยกโรคกระเพาะ ไส้ติ่ง ลำไส้ นิ่ว และภาวะฉุกเฉิน"
            ],
            [
                "question_id" => "abd_duration",
                "question" => "ปวดมานานแค่ไหน?",
                "type" => "choice",
                "options" => [
                    "น้อยกว่า 6 ชั่วโมง",
                    "6-24 ชั่วโมง",
                    "1-3 วัน",
                    "มากกว่า 3 วัน"
                ],
                "clinical_purpose" => "ประเมิน acute abdomen และการดำเนินโรค"
            ],
            [
                "question_id" => "abd_pain_type",
                "question" => "ลักษณะการปวดเป็นแบบไหน?",
                "type" => "choice",
                "options" => [
                    "บิด",
                    "แสบ",
                    "ตื้อ",
                    "แทง",
                    "ปวดมากขึ้นเรื่อย ๆ"
                ],
                "clinical_purpose" => "แยก colic, gastritis, appendicitis, obstruction"
            ],
            [
                "question_id" => "abd_fever",
                "question" => "มีไข้ร่วมด้วยไหม?",
                "type" => "yes_no",
                "options" => ["มี", "ไม่มี"],
                "clinical_purpose" => "มองหาการติดเชื้อหรือไส้ติ่งอักเสบ"
            ],
            [
                "question_id" => "abd_vomit",
                "question" => "มีอาเจียนหรือคลื่นไส้ไหม?",
                "type" => "yes_no",
                "options" => ["มี", "ไม่มี"],
                "clinical_purpose" => "ประเมิน GI infection, obstruction, appendicitis"
            ],
            [
                "question_id" => "abd_stool",
                "question" => "มีถ่ายเหลว ถ่ายดำ หรือถ่ายเป็นเลือดไหม?",
                "type" => "choice",
                "options" => [
                    "ไม่มี",
                    "ถ่ายเหลว",
                    "ถ่ายดำ",
                    "ถ่ายเป็นเลือด"
                ],
                "clinical_purpose" => "แยก gastroenteritis และ GI bleeding"
            ],
            [
                "question_id" => "abd_rebound",
                "question" => "กดแล้วเจ็บมาก หรือปล่อยมือแล้วเจ็บมากขึ้นไหม?",
                "type" => "yes_no",
                "options" => ["ใช่", "ไม่ใช่"],
                "clinical_purpose" => "มองหาภาวะเยื่อบุช่องท้องอักเสบ"
            ]
        ];
    }

    private function headachePathway(): array
    {
        return [
            [
                "question_id" => "head_onset",
                "question" => "ปวดหัวเริ่มแบบไหน?",
                "type" => "choice",
                "options" => [
                    "ปวดทันทีรุนแรง",
                    "ค่อย ๆ ปวด",
                    "เป็น ๆ หาย ๆ",
                    "ปวดเรื้อรัง"
                ],
                "clinical_purpose" => "มองหา thunderclap headache, migraine, tumor"
            ],
            [
                "question_id" => "head_location",
                "question" => "ปวดบริเวณไหน?",
                "type" => "choice",
                "options" => [
                    "ข้างซ้าย",
                    "ข้างขวา",
                    "ทั้งหัว",
                    "ท้ายทอย",
                    "หน้าผาก/โหนกแก้ม"
                ],
                "clinical_purpose" => "แยก migraine, tension, sinusitis, hypertension"
            ],
            [
                "question_id" => "head_duration",
                "question" => "ปวดมานานแค่ไหน?",
                "type" => "choice",
                "options" => [
                    "น้อยกว่า 1 ชั่วโมง",
                    "1-24 ชั่วโมง",
                    "1-7 วัน",
                    "มากกว่า 1 สัปดาห์"
                ],
                "clinical_purpose" => "ดู pattern ของ migraine, infection, tumor"
            ],
            [
                "question_id" => "head_neuro",
                "question" => "มีหน้าเบี้ยว พูดไม่ชัด แขนขาอ่อนแรง หรือชาครึ่งซีกไหม?",
                "type" => "yes_no",
                "options" => ["มี", "ไม่มี"],
                "clinical_purpose" => "คัดกรอง Stroke"
            ],
            [
                "question_id" => "head_fever_neck",
                "question" => "มีไข้ คอแข็ง หรือกลัวแสงไหม?",
                "type" => "yes_no",
                "options" => ["มี", "ไม่มี"],
                "clinical_purpose" => "คัดกรอง Meningitis"
            ],
            [
                "question_id" => "head_visual",
                "question" => "มีตามัว เห็นแสงวาบ หรืออาเจียนร่วมด้วยไหม?",
                "type" => "choice",
                "options" => [
                    "ไม่มี",
                    "ตามัว",
                    "เห็นแสงวาบ",
                    "อาเจียน"
                ],
                "clinical_purpose" => "แยก migraine, glaucoma, intracranial pressure"
            ]
        ];
    }

    private function chestPainPathway(): array
    {
        return [
            [
                "question_id" => "chest_location",
                "question" => "เจ็บหรือแน่นหน้าอกบริเวณไหน?",
                "type" => "choice",
                "options" => [
                    "กลางอก",
                    "อกซ้าย",
                    "อกขวา",
                    "ทั่วหน้าอก"
                ],
                "clinical_purpose" => "แยก cardiac, musculoskeletal, pulmonary"
            ],
            [
                "question_id" => "chest_radiation",
                "question" => "เจ็บร้าวไปแขน กราม หลัง หรือไหล่ไหม?",
                "type" => "choice",
                "options" => [
                    "ไม่ร้าว",
                    "ร้าวไปแขนซ้าย",
                    "ร้าวไปกราม",
                    "ร้าวไปหลัง",
                    "ร้าวไปไหล่"
                ],
                "clinical_purpose" => "คัดกรอง ACS และ aortic dissection"
            ],
            [
                "question_id" => "chest_sweating",
                "question" => "มีเหงื่อแตก หน้ามืด หรือคลื่นไส้ไหม?",
                "type" => "yes_no",
                "options" => ["มี", "ไม่มี"],
                "clinical_purpose" => "เพิ่มน้ำหนัก ACS"
            ],
            [
                "question_id" => "chest_breath",
                "question" => "มีหายใจไม่ออกหรือเจ็บตอนหายใจไหม?",
                "type" => "choice",
                "options" => [
                    "ไม่มี",
                    "หายใจไม่ออก",
                    "เจ็บตอนหายใจ",
                    "ทั้งสองอย่าง"
                ],
                "clinical_purpose" => "แยก ACS, PE, pneumothorax"
            ],
            [
                "question_id" => "chest_duration",
                "question" => "อาการเป็นมานานแค่ไหน?",
                "type" => "choice",
                "options" => [
                    "น้อยกว่า 10 นาที",
                    "10-30 นาที",
                    "มากกว่า 30 นาที",
                    "เป็น ๆ หาย ๆ หลายวัน"
                ],
                "clinical_purpose" => "ประเมินความเร่งด่วนของ cardiac chest pain"
            ]
        ];
    }

    private function dyspneaPathway(): array
    {
        return [
            [
                "question_id" => "dyspnea_severity",
                "question" => "ตอนนี้เหนื่อยระดับไหน?",
                "type" => "choice",
                "options" => [
                    "เดินแล้วยังพอไหว",
                    "พูดเป็นประโยคได้",
                    "พูดได้เป็นคำ ๆ",
                    "หายใจไม่ออกมาก"
                ],
                "clinical_purpose" => "ประเมิน respiratory distress"
            ],
            [
                "question_id" => "dyspnea_onset",
                "question" => "อาการเหนื่อยเริ่มเมื่อไร?",
                "type" => "choice",
                "options" => [
                    "ทันที",
                    "ภายในวันนี้",
                    "หลายวัน",
                    "เรื้อรัง"
                ],
                "clinical_purpose" => "แยก asthma, PE, pneumonia, COPD"
            ],
            [
                "question_id" => "dyspnea_wheeze",
                "question" => "มีเสียงวี๊ด หรือเคยเป็นหอบหืดไหม?",
                "type" => "yes_no",
                "options" => ["มี", "ไม่มี"],
                "clinical_purpose" => "คัดกรอง asthma/COPD"
            ],
            [
                "question_id" => "dyspnea_fever",
                "question" => "มีไข้ ไอ หรือเสมหะไหม?",
                "type" => "yes_no",
                "options" => ["มี", "ไม่มี"],
                "clinical_purpose" => "คัดกรอง pneumonia/infection"
            ],
            [
                "question_id" => "dyspnea_blue",
                "question" => "ปากเขียว หน้าซีด หรือ SpO2 ต่ำกว่า 94 ไหม?",
                "type" => "yes_no",
                "options" => ["มี", "ไม่มี"],
                "clinical_purpose" => "คัดกรองภาวะหายใจล้มเหลว"
            ]
        ];
    }

    private function feverPathway(): array
    {
        return [
            [
                "question_id" => "fever_duration",
                "question" => "มีไข้มานานแค่ไหน?",
                "type" => "choice",
                "options" => [
                    "น้อยกว่า 1 วัน",
                    "1-3 วัน",
                    "มากกว่า 3 วัน",
                    "มากกว่า 1 สัปดาห์"
                ],
                "clinical_purpose" => "แยก infection acute/chronic"
            ],
            [
                "question_id" => "fever_temp",
                "question" => "ไข้สูงประมาณกี่องศา?",
                "type" => "choice",
                "options" => [
                    "ไม่ทราบ",
                    "ต่ำกว่า 38.5",
                    "38.5-39.5",
                    "มากกว่า 39.5"
                ],
                "clinical_purpose" => "ประเมินความรุนแรง"
            ],
            [
                "question_id" => "fever_focus",
                "question" => "มีอาการร่วมเด่น ๆ อะไร?",
                "type" => "choice",
                "options" => [
                    "ไอ/เสมหะ",
                    "ปวดท้อง/ท้องเสีย",
                    "ปัสสาวะแสบขัด",
                    "ผื่น",
                    "ซึม/หายใจเร็ว"
                ],
                "clinical_purpose" => "หาแหล่งติดเชื้อ"
            ],
            [
                "question_id" => "fever_redflag",
                "question" => "มีซึม หายใจเร็ว ความดันต่ำ หรือปัสสาวะน้อยไหม?",
                "type" => "yes_no",
                "options" => ["มี", "ไม่มี"],
                "clinical_purpose" => "คัดกรอง Sepsis"
            ]
        ];
    }
}