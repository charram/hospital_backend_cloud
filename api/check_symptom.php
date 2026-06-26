<?php

header("Content-Type: application/json; charset=UTF-8");

require_once(__DIR__ . "/../db_connect.php");

function containsAny($text, $words) {
    foreach ($words as $word) {
        if (mb_strpos($text, $word) !== false) {
            return true;
        }
    }
    return false;
}

function countMatchedWords($text, $words) {
    $count = 0;
    foreach ($words as $word) {
        if (mb_strpos($text, $word) !== false) {
            $count++;
        }
    }
    return $count;
}

function smartSymptomAnalyze($symptom) {
    $text = mb_strtolower(trim($symptom), "UTF-8");

    $score = 0;
    $department = "อายุรกรรม";
    $urgency = "ต่ำ";
    $emsRequired = false;
    $possibleDisease = "อาการทั่วไป";
    $recommendation = "แนะนำให้สังเกตอาการ หากอาการไม่ดีขึ้นหรือมีอาการรุนแรง ควรพบแพทย์";
    $note = "ระบบประเมินจากกลุ่มอาการที่ผู้ใช้ระบุ ใช้เพื่อคัดกรองเบื้องต้น ไม่ใช่การวินิจฉัยโรค";

    $emergencyWords = [
        "หมดสติ", "ไม่รู้สึกตัว", "ชัก", "หายใจไม่ออก",
        "เจ็บหน้าอก", "แน่นหน้าอก", "พูดไม่ชัด",
        "หน้าเบี้ยว", "แขนขาอ่อนแรง", "เลือดออกมาก"
    ];

    $heartWords = [
        "เจ็บหน้าอก", "แน่นหน้าอก", "ใจสั่น",
        "เหนื่อยง่าย", "หายใจไม่ออก", "เจ็บร้าวไปแขน",
        "เหงื่อแตก", "หน้ามืด"
    ];

    $brainWords = [
        "หน้าเบี้ยว", "พูดไม่ชัด", "แขนขาอ่อนแรง",
        "ชาครึ่งซีก", "เวียนหัวรุนแรง", "หมดสติ",
        "ปวดหัวรุนแรง", "เดินเซ"
    ];

    $respiratoryWords = [
        "ไอ", "หอบ", "หายใจลำบาก", "หายใจไม่ออก",
        "เจ็บคอ", "มีเสมหะ", "แน่นหน้าอก", "หายใจมีเสียง"
    ];

    $feverWords = [
        "ไข้", "ตัวร้อน", "หนาวสั่น", "ปวดเมื่อย",
        "ไอ", "เจ็บคอ", "น้ำมูก", "อ่อนเพลีย"
    ];

    $stomachWords = [
        "ปวดท้อง", "ท้องเสีย", "อาเจียน", "คลื่นไส้",
        "ถ่ายเหลว", "ปวดท้องรุนแรง", "ท้องอืด", "ถ่ายเป็นเลือด"
    ];

    $traumaWords = [
        "รถชน", "ล้ม", "ตกจากที่สูง", "กระแทก",
        "บาดเจ็บ", "กระดูกหัก", "เลือดออก", "แผลลึก"
    ];

    $allergyWords = [
        "ผื่น", "คัน", "บวม", "หน้าบวม", "ปากบวม",
        "แพ้ยา", "แพ้อาหาร", "หายใจติดขัด"
    ];

    $mentalWords = [
        "เครียด", "นอนไม่หลับ", "วิตกกังวล", "ซึมเศร้า",
        "ใจสั่น", "หายใจเร็ว", "กลัว", "แพนิค"
    ];

    $emergencyCount = countMatchedWords($text, $emergencyWords);
    $heartCount = countMatchedWords($text, $heartWords);
    $brainCount = countMatchedWords($text, $brainWords);
    $respiratoryCount = countMatchedWords($text, $respiratoryWords);
    $feverCount = countMatchedWords($text, $feverWords);
    $stomachCount = countMatchedWords($text, $stomachWords);
    $traumaCount = countMatchedWords($text, $traumaWords);
    $allergyCount = countMatchedWords($text, $allergyWords);
    $mentalCount = countMatchedWords($text, $mentalWords);

    if ($emergencyCount > 0) {
        $score += $emergencyCount * 4;
    }

    if ($heartCount > 0) {
        $score += $heartCount * 3;
        $department = "หัวใจ";
        $possibleDisease = "กลุ่มอาการหัวใจหรือระบบไหลเวียนเลือด";
        $note = "พบกลุ่มอาการที่อาจเกี่ยวข้องกับหัวใจ เช่น แน่นหน้าอก เหนื่อยง่าย หรือหายใจลำบาก";
    }

    if ($brainCount > 0) {
        $score += $brainCount * 4;
        $department = "ระบบประสาท";
        $possibleDisease = "กลุ่มอาการทางระบบประสาท";
        $note = "พบกลุ่มอาการที่อาจเกี่ยวข้องกับระบบประสาท ควรระวังภาวะฉุกเฉิน เช่น Stroke";
    }

    if ($respiratoryCount > 0) {
        $score += $respiratoryCount * 2;
        $department = "โรคปอด / อายุรกรรม";
        $possibleDisease = "กลุ่มอาการทางระบบทางเดินหายใจ";
        $note = "พบกลุ่มอาการทางระบบหายใจ ควรประเมินระดับการหายใจและอาการเหนื่อย";
    }

    if ($feverCount > 0) {
        $score += $feverCount * 1;
        $department = "อายุรกรรม";
        $possibleDisease = "กลุ่มอาการติดเชื้อหรือไข้";
        $note = "พบกลุ่มอาการไข้หรือติดเชื้อ ควรติดตามไข้และอาการร่วม";
    }

    if ($stomachCount > 0) {
        $score += $stomachCount * 2;
        $department = "ทางเดินอาหาร / อายุรกรรม";
        $possibleDisease = "กลุ่มอาการทางเดินอาหาร";
        $note = "พบกลุ่มอาการทางเดินอาหาร ควรประเมินภาวะขาดน้ำหรืออาการปวดรุนแรง";
    }

    if ($traumaCount > 0) {
        $score += $traumaCount * 3;
        $department = "ฉุกเฉิน / อุบัติเหตุ";
        $possibleDisease = "กลุ่มอาการบาดเจ็บหรืออุบัติเหตุ";
        $note = "พบประวัติอุบัติเหตุหรือบาดเจ็บ ควรประเมินเลือดออก กระดูกหัก หรือการกระแทกศีรษะ";
    }

    if ($allergyCount > 0) {
        $score += $allergyCount * 2;
        $department = "อายุรกรรม / ภูมิแพ้";
        $possibleDisease = "กลุ่มอาการแพ้";
        $note = "พบกลุ่มอาการแพ้ หากมีหน้าบวม ปากบวม หรือหายใจลำบาก ควรรีบพบแพทย์";
    }

    if ($mentalCount > 0) {
        $score += $mentalCount * 1;
        $department = "สุขภาพจิต";
        $possibleDisease = "กลุ่มอาการความเครียดหรือสุขภาพจิต";
        $note = "พบกลุ่มอาการด้านความเครียดหรือสุขภาพจิต ควรประเมินร่วมกับอาการทางกาย";
    }

    if (
        containsAny($text, ["เจ็บหน้าอก", "แน่นหน้าอก"]) &&
        containsAny($text, ["หายใจไม่ออก", "เหงื่อแตก", "หน้ามืด", "เจ็บร้าวไปแขน"])
    ) {
        $score = max($score, 9);
        $department = "หัวใจ";
        $possibleDisease = "สงสัยกลุ่มอาการหัวใจเฉียบพลัน";
        $note = "อาการเจ็บหรือแน่นหน้าอกร่วมกับหายใจลำบาก เหงื่อแตก หรือหน้ามืด เป็นกลุ่มอาการที่ควรรีบประเมิน";
    }

    if (
        containsAny($text, ["หน้าเบี้ยว", "พูดไม่ชัด", "แขนขาอ่อนแรง", "ชาครึ่งซีก"])
    ) {
        $score = max($score, 9);
        $department = "ระบบประสาท";
        $possibleDisease = "สงสัยภาวะ Stroke หรือระบบประสาทเฉียบพลัน";
        $note = "พบอาการสำคัญที่อาจเกี่ยวข้องกับโรคหลอดเลือดสมอง ควรเข้ารับการประเมินโดยเร็ว";
    }

    if (
        containsAny($text, ["หายใจไม่ออก", "หายใจลำบาก"]) &&
        containsAny($text, ["ปากเขียว", "หน้าซีด", "หมดสติ", "แน่นหน้าอก"])
    ) {
        $score = max($score, 9);
        $department = "ฉุกเฉิน / โรคปอด";
        $possibleDisease = "กลุ่มอาการหายใจลำบากรุนแรง";
        $note = "พบอาการหายใจลำบากร่วมกับสัญญาณอันตราย ควรเข้ารับการช่วยเหลือโดยเร็ว";
    }

    if (
        containsAny($text, ["เลือดออกมาก", "แผลลึก", "รถชน", "ตกจากที่สูง"])
    ) {
        $score = max($score, 8);
        $department = "ฉุกเฉิน / อุบัติเหตุ";
        $possibleDisease = "ภาวะบาดเจ็บที่ต้องประเมินเร่งด่วน";
        $note = "อาการบาดเจ็บรุนแรงควรได้รับการประเมินโดยบุคลากรทางการแพทย์";
    }

    $score = min($score, 10);

    if ($score >= 8) {
        $urgency = "สูง";
        $emsRequired = true;
        $recommendation = "ควรเรียก EMS หรือไปโรงพยาบาลทันที โดยหลีกเลี่ยงการขับรถเอง";
    } elseif ($score >= 5) {
        $urgency = "ปานกลาง";
        $emsRequired = false;
        $recommendation = "ควรพบแพทย์ภายในวันนี้หรือเร็วที่สุด หากอาการแย่ลงให้เรียก EMS";
    } elseif ($score >= 2) {
        $urgency = "ต่ำ";
        $emsRequired = false;
        $recommendation = "สามารถสังเกตอาการเบื้องต้นได้ แต่ควรพบแพทย์หากอาการไม่ดีขึ้น";
    } else {
        $urgency = "ต่ำ";
        $emsRequired = false;
        $recommendation = "ข้อมูลอาการยังไม่เพียงพอ กรุณาอธิบายอาการเพิ่มเติม เช่น ระยะเวลา ความรุนแรง และโรคประจำตัว";
        $note = "ระบบยังไม่พบกลุ่มอาการที่ชัดเจนจากข้อความที่ระบุ";
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

$symptom = trim($_GET["symptom_name"] ?? "");

if ($symptom === "") {
    echo json_encode([
        "success" => false,
        "message" => "Symptom empty"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$keywords = preg_split('/[\s,、，]+/u', $symptom);

$conditions = [];
$params = [];

foreach ($keywords as $word) {
    $word = trim($word);

    if ($word === "") {
        continue;
    }

    $index = count($params) + 1;

    $conditions[] = "
    (
        symptom_name ILIKE $" . $index . "
        OR symptom_keywords ILIKE $" . $index . "
        OR department ILIKE $" . $index . "
    )
    ";

    $params[] = "%{$word}%";
}

if (empty($conditions)) {
    echo json_encode(
        smartSymptomAnalyze($symptom),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

$sql = "
SELECT
    symptom_name,
    symptom_keywords,
    urgency_level,
    recommendation,
    department,
    ems_required,
    severity_score,
    ai_note
FROM symptom_assessment
WHERE
    " . implode(" OR ", $conditions) . "
ORDER BY severity_score DESC
LIMIT 1
";

$result = pg_query_params($conn, $sql, $params);

if (!$result || pg_num_rows($result) == 0) {
    echo json_encode(
        smartSymptomAnalyze($symptom),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

$row = pg_fetch_assoc($result);

$emsRequired = (
    $row["ems_required"] === "t" ||
    $row["ems_required"] === true ||
    $row["ems_required"] == 1
);

$aiNote = trim($row["ai_note"] ?? "");

if ($aiNote === "") {
    $aiNote = "ระบบประเมินว่าอาการนี้เกี่ยวข้องกับแผนก " .
        ($row["department"] ?? "อายุรกรรม") .
        " และควรปฏิบัติตามคำแนะนำที่แสดง";
}

echo json_encode([
    "success" => true,
    "symptom_name" => $row["symptom_name"],
    "urgency_level" => $row["urgency_level"],
    "recommendation" => $row["recommendation"],
    "department" => $row["department"],
    "ems_required" => $emsRequired,
    "severity_score" => (int)$row["severity_score"],
    "ai_note" => $aiNote
], JSON_UNESCAPED_UNICODE);

exit;

?>