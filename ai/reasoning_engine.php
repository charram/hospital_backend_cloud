<?php
require_once __DIR__ . "/medical_knowledge.php";
require_once __DIR__ . "/disease_engine.php";
require_once __DIR__ . "/patient_context.php";
require_once __DIR__ . "/risk_assessment_engine.php";


class ReasoningEngine
{
    private string $text = "";

   public function analyze(
    string $symptom,
    ?PatientContext $patient = null
): array
    {
        $this->text = mb_strtolower(trim($symptom), "UTF-8");
        $diseaseEngine = new DiseaseEngine();
        $riskEngine = new RiskAssessmentEngine();

if ($patient === null) {
    $patient = new PatientContext();
}

$risk = $riskEngine->assess($patient);

$disease = $diseaseEngine->findDisease($this->text);

        $score = 0;
        $department = "อายุรกรรม";
        $urgency = "ต่ำ";
        $emsRequired = false;
        $possibleDisease = "อาการทั่วไป";
        $recommendation = "แนะนำให้สังเกตอาการ หากอาการไม่ดีขึ้นหรือรุนแรงขึ้น ควรพบแพทย์";
        $note = "ระบบประเมินจากกลุ่มอาการที่ผู้ใช้ระบุ ใช้เพื่อคัดกรองเบื้องต้น ไม่ใช่การวินิจฉัยโรค";
if ($disease !== null) {

    $possibleDisease = $disease["name"];
    $department = $disease["department"];

    // คะแนนจาก Disease Engine
    $score = $disease["severity"];

    // เพิ่มคะแนนจากความเสี่ยงของผู้ป่วย
    $score += $risk["risk_score"];

    if ($score > 10) {
        $score = 10;
    }

    $emsRequired = $disease["ems"];
    $recommendation = $disease["recommendation"];

    if ($score >= 8) {
        $urgency = "สูง";
    } elseif ($score >= 5) {
        $urgency = "ปานกลาง";
    } else {
        $urgency = "ต่ำ";
    }

    return [
        "success" => true,
        "symptom_name" => $possibleDisease,
        "urgency_level" => $urgency,
        "recommendation" => $recommendation,
        "department" => $department,
        "ems_required" => $emsRequired,
        "severity_score" => $score,
        "risk_level" => $risk["risk_level"],
        "risk_score" => $risk["risk_score"],
        "risk_reasons" => $risk["reasons"],
        "ai_note" => "วิเคราะห์โดย Disease Engine + Risk Assessment Engine"
    ];
}
       

        $heartWords = MedicalKnowledge::heartWords();

$brainWords = MedicalKnowledge::brainWords();

$respiratoryWords = MedicalKnowledge::respiratoryWords();

$feverWords = MedicalKnowledge::feverWords();

$stomachWords = MedicalKnowledge::stomachWords();

$traumaWords = MedicalKnowledge::traumaWords();

$allergyWords = MedicalKnowledge::allergyWords();

$mentalWords = MedicalKnowledge::mentalWords();

$emergencyWords = MedicalKnowledge::emergencyWords();

      

        $score += $this->countMatchedWords($emergencyWords) * 4;

        if ($this->hasAny($heartWords)) {

            $score += $this->countMatchedWords($heartWords) * 3;

            $department = "หัวใจ";

            $possibleDisease = "กลุ่มอาการหัวใจหรือระบบไหลเวียนเลือด";

            $note = "พบกลุ่มอาการที่อาจเกี่ยวข้องกับหัวใจ เช่น แน่นหน้าอก เหนื่อยง่าย หรือหายใจลำบาก";
        }

        if ($this->hasAny($brainWords)) {

            $score += $this->countMatchedWords($brainWords) * 4;

            $department = "ระบบประสาท";

            $possibleDisease = "กลุ่มอาการทางระบบประสาท";

            $note = "พบกลุ่มอาการที่อาจเกี่ยวข้องกับระบบประสาท ควรระวังภาวะฉุกเฉิน เช่น Stroke";
        }

        if ($this->hasAny($respiratoryWords)) {

            $score += $this->countMatchedWords($respiratoryWords) * 2;

            $department = "โรคปอด / อายุรกรรม";

            $possibleDisease = "กลุ่มอาการทางระบบทางเดินหายใจ";

            $note = "พบกลุ่มอาการทางระบบหายใจ ควรประเมินระดับการหายใจและอาการเหนื่อย";
        }

        if ($this->hasAny($feverWords)) {

            $score += $this->countMatchedWords($feverWords);

            $department = "อายุรกรรม";

            $possibleDisease = "กลุ่มอาการติดเชื้อหรือไข้";

            $note = "พบกลุ่มอาการไข้หรือติดเชื้อ ควรติดตามไข้และอาการร่วม";
        }

        if ($this->hasAny($stomachWords)) {

            $score += $this->countMatchedWords($stomachWords) * 2;

            $department = "ทางเดินอาหาร / อายุรกรรม";

            $possibleDisease = "กลุ่มอาการทางเดินอาหาร";

            $note = "พบกลุ่มอาการทางเดินอาหาร ควรประเมินภาวะขาดน้ำหรืออาการปวดรุนแรง";
        }

        if ($this->hasAny($traumaWords)) {

            $score += $this->countMatchedWords($traumaWords) * 3;

            $department = "ฉุกเฉิน / อุบัติเหตุ";

            $possibleDisease = "กลุ่มอาการบาดเจ็บหรืออุบัติเหตุ";

            $note = "พบประวัติอุบัติเหตุหรือบาดเจ็บ ควรประเมินเลือดออก กระดูกหัก หรือการกระแทกศีรษะ";
        }

        if ($this->hasAny($allergyWords)) {

            $score += $this->countMatchedWords($allergyWords) * 2;

            $department = "อายุรกรรม / ภูมิแพ้";

            $possibleDisease = "กลุ่มอาการแพ้";

            $note = "พบกลุ่มอาการแพ้ หากมีหน้าบวม ปากบวม หรือหายใจลำบาก ควรรีบพบแพทย์";
        }

        if ($this->hasAny($mentalWords)) {

            $score += $this->countMatchedWords($mentalWords);

            $department = "สุขภาพจิต";

            $possibleDisease = "กลุ่มอาการความเครียดหรือสุขภาพจิต";

            $note = "พบกลุ่มอาการด้านความเครียดหรือสุขภาพจิต ควรประเมินร่วมกับอาการทางกาย";
        }
         

        if ($this->hasAny(["หน้าเบี้ยว","พูดไม่ชัด","แขนขาอ่อนแรง","ชาครึ่งซีก"])) {
            $score = max($score, 9);
            $department = "ระบบประสาท";
            $possibleDisease = "สงสัยภาวะ Stroke หรือระบบประสาทเฉียบพลัน";
            $note = "พบอาการสำคัญที่อาจเกี่ยวข้องกับโรคหลอดเลือดสมอง ควรเข้ารับการประเมินโดยเร็ว";
        }

        if (
            $this->hasAny(["หายใจไม่ออก","หายใจลำบาก"]) &&
            $this->hasAny(["ปากเขียว","หน้าซีด","หมดสติ","แน่นหน้าอก"])
        ) {
            $score = max($score, 9);
            $department = "ฉุกเฉิน / โรคปอด";
            $possibleDisease = "กลุ่มอาการหายใจลำบากรุนแรง";
            $note = "พบอาการหายใจลำบากร่วมกับสัญญาณอันตราย ควรเข้ารับการช่วยเหลือโดยเร็ว";
        }

        if ($this->hasAny(["เลือดออกมาก","แผลลึก","รถชน","ตกจากที่สูง"])) {
            $score = max($score, 8);
            $department = "ฉุกเฉิน / อุบัติเหตุ";
            $possibleDisease = "ภาวะบาดเจ็บที่ต้องประเมินเร่งด่วน";
            $note = "อาการบาดเจ็บรุนแรงควรได้รับการประเมินโดยบุคลากรทางการแพทย์";
        }

        $score = min($score, 10);

        if ($score >= 8) {
            $urgency = "สูง";
            $emsRequired = true;
            $recommendation = "ควรเรียก EMS หรือไปโรงพยาบาลทันที";
        } elseif ($score >= 5) {
            $urgency = "ปานกลาง";
            $recommendation = "ควรพบแพทย์ภายในวันนี้";
        } elseif ($score >= 2) {
            $urgency = "ต่ำ";
            $recommendation = "สามารถสังเกตอาการได้ หากไม่ดีขึ้นควรพบแพทย์";
        }

        return [
            "success" => true,
            "symptom_name" => $possibleDisease,
            "urgency_level" => $urgency,
            "recommendation" => $recommendation,
            "department" => $department,
            "ems_required" => $emsRequired,
            "severity_score" => $score,
            "ai_note" => $note
        ];
    }

    private function hasAny(array $words): bool
    {
        foreach ($words as $word) {
            if (mb_strpos($this->text, $word) !== false) {
                return true;
            }
        }
        return false;
    }

    private function countMatchedWords(array $words): int
    {
        $count = 0;

        foreach ($words as $word) {
            if (mb_strpos($this->text, $word) !== false) {
                $count++;
            }
        }

        return $count;
    }
}