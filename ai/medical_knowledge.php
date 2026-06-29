<?php

declare(strict_types=1);

/**
 * ============================================================
 * Open Hospital AI V2
 * Medical Knowledge Base
 * ------------------------------------------------------------
 * Version : 2.0
 *
 * หน้าที่
 *  - Disease Knowledge
 *  - Clinical Symptoms
 *  - Red Flags
 *  - Risk Factors
 *  - Department
 *  - EMS Recommendation
 *  - Hospital Capability
 *  - Clinical Reasoning
 * ============================================================
 */

class MedicalKnowledge
{

    /**
     * ======================================================
     * Disease Database
     * ======================================================
     */

    private static array $diseases = [

        //====================================================
        // CARDIOLOGY
        //====================================================

        [

            "disease_id" => "CARD001",

            "disease_name_th" =>
                "กลุ่มอาการหัวใจเฉียบพลัน",

            "disease_name_en" =>
                "Acute Coronary Syndrome",

            "category" =>
                "Cardiology",

            "department" =>
                "หัวใจ",

            "icd_group" =>
                "I20-I25",

            //--------------------------------------------

            "symptoms" => [

                "เจ็บหน้าอก",

                "แน่นหน้าอก",

                "หายใจไม่ออก",

                "เหงื่อแตก",

                "เจ็บร้าวไปแขน",

                "เจ็บร้าวไปกราม",

                "คลื่นไส้",

                "หน้ามืด"

            ],

            //--------------------------------------------

            "synonyms" => [

                "แน่นอก",

                "เจ็บกลางอก",

                "เจ็บหน้าอกด้านซ้าย",

                "อกแน่น",

                "หายใจไม่เต็มปอด"

            ],

            //--------------------------------------------

            "red_flags" => [

                "หมดสติ",

                "หายใจไม่ออก",

                "ความดันต่ำ"

            ],

            //--------------------------------------------

            "risk_factors" => [

                "elderly",

                "diabetes",

                "hypertension",

                "heart_disease",

                "smoker",

                "obesity"

            ],

            //--------------------------------------------

            "severity_base_score" => 9,

            "ems_required" => true,

            //--------------------------------------------

            "hospital_capability_required" => [

                "ER",

                "Cardiology",

                "Cath Lab",

                "ICU"

            ],

            //--------------------------------------------

            "recommendation" =>

                "ควรเรียก EMS และส่งโรงพยาบาลที่มี Cath Lab",

            //--------------------------------------------

            "reasoning_note" =>

                "พบกลุ่มอาการที่เข้าได้กับ Acute Coronary Syndrome"

        ],

        //====================================================
        // NEUROLOGY
        //====================================================

        [

            "disease_id" => "NEU001",

            "disease_name_th" =>
                "โรคหลอดเลือดสมอง",

            "disease_name_en" =>
                "Stroke",

            "category" =>
                "Neurology",

            "department" =>
                "ระบบประสาท",

            "icd_group" =>
                "I60-I69",

            //--------------------------------------------

            "symptoms" => [

                "หน้าเบี้ยว",

                "พูดไม่ชัด",

                "แขนขาอ่อนแรง",

                "ชาครึ่งซีก",

                "เดินเซ",

                "ปวดหัวรุนแรง",

                "เวียนหัวรุนแรง"

            ],

            //--------------------------------------------

            "synonyms" => [

                "ปากเบี้ยว",

                "พูดลำบาก",

                "แขนอ่อนแรง",

                "ขาอ่อนแรง"

            ],

            //--------------------------------------------

            "red_flags" => [

                "หมดสติ",

                "ชัก"

            ],

            //--------------------------------------------

            "risk_factors" => [

                "elderly",

                "hypertension",

                "diabetes",

                "heart_disease",

                "smoker"

            ],

            //--------------------------------------------

            "severity_base_score" => 10,

            "ems_required" => true,

            //--------------------------------------------

            "hospital_capability_required" => [

                "ER",

                "Stroke Unit",

                "CT Scan",

                "ICU"

            ],

            //--------------------------------------------

            "recommendation" =>

                "ควรเรียก EMS ทันที",

            //--------------------------------------------

            "reasoning_note" =>

                "เข้าได้กับโรคหลอดเลือดสมองเฉียบพลัน"

        ],
                //====================================================
        // RESPIRATORY
        //====================================================

        [

            "disease_id" => "RESP001",

            "disease_name_th" =>
                "ภาวะหายใจลำบากรุนแรง",

            "disease_name_en" =>
                "Severe Dyspnea",

            "category" =>
                "Respiratory",

            "department" =>
                "โรคปอด",

            "icd_group" =>
                "J96",

            "symptoms" => [

                "หายใจไม่ออก",

                "หอบ",

                "หายใจลำบาก",

                "แน่นหน้าอก",

                "พูดเป็นประโยคไม่ได้",

                "ปากเขียว"

            ],

            "synonyms" => [

                "หายใจไม่เต็มปอด",

                "เหนื่อยมาก",

                "หอบหนัก"

            ],

            "red_flags" => [

                "ปากเขียว",

                "หมดสติ",

                "หายใจไม่ออก"

            ],

            "risk_factors" => [

                "elderly",

                "smoker",

                "low_spo2",

                "tachycardia"

            ],

            "severity_base_score" => 9,

            "ems_required" => true,

            "hospital_capability_required" => [

                "ER",

                "ICU",

                "Ventilator"

            ],

            "recommendation" =>
                "ควรเรียก EMS ทันที",

            "reasoning_note" =>
                "พบอาการเข้าได้กับภาวะหายใจล้มเหลว"

        ],

        //====================================================
        // ASTHMA
        //====================================================

        [

            "disease_id" => "RESP002",

            "disease_name_th" =>
                "โรคหอบหืดกำเริบ",

            "disease_name_en" =>
                "Acute Asthma Attack",

            "category" =>
                "Respiratory",

            "department" =>
                "โรคปอด",

            "icd_group" =>
                "J45",

            "symptoms" => [

                "หอบ",

                "หายใจมีเสียง",

                "แน่นหน้าอก",

                "ไอ",

                "หายใจลำบาก"

            ],

            "synonyms" => [

                "หอบหืด",

                "หายใจวี๊ด",

                "喘"

            ],

            "red_flags" => [

                "พูดไม่ได้",

                "ปากเขียว"

            ],

            "risk_factors" => [

                "child",

                "smoker",

                "low_spo2"

            ],

            "severity_base_score" => 8,

            "ems_required" => true,

            "hospital_capability_required" => [

                "ER",

                "Nebulizer"

            ],

            "recommendation" =>
                "ควรได้รับยาขยายหลอดลมและพบแพทย์ทันที",

            "reasoning_note" =>
                "เข้าได้กับภาวะหอบหืดกำเริบ"

        ],

        //====================================================
        // COPD
        //====================================================

        [

            "disease_id" => "RESP003",

            "disease_name_th" =>
                "โรคปอดอุดกั้นเรื้อรังกำเริบ",

            "disease_name_en" =>
                "COPD Exacerbation",

            "category" =>
                "Respiratory",

            "department" =>
                "โรคปอด",

            "icd_group" =>
                "J44",

            "symptoms" => [

                "หอบ",

                "ไอ",

                "เสมหะ",

                "หายใจลำบาก"

            ],

            "synonyms" => [

                "COPD",

                "ถุงลมโป่งพอง"

            ],

            "red_flags" => [

                "ปากเขียว",

                "หมดสติ"

            ],

            "risk_factors" => [

                "elderly",

                "smoker",

                "low_spo2"

            ],

            "severity_base_score" => 8,

            "ems_required" => true,

            "hospital_capability_required" => [

                "ER",

                "Respiratory Ward"

            ],

            "recommendation" =>
                "ควรได้รับออกซิเจนและพบแพทย์โดยด่วน",

            "reasoning_note" =>
                "เข้าได้กับ COPD กำเริบ"

        ],

        //====================================================
        // PNEUMONIA
        //====================================================

        [

            "disease_id" => "RESP004",

            "disease_name_th" =>
                "ปอดอักเสบ",

            "disease_name_en" =>
                "Pneumonia",

            "category" =>
                "Respiratory",

            "department" =>
                "อายุรกรรม",

            "icd_group" =>
                "J18",

            "symptoms" => [

                "ไข้",

                "ไอ",

                "เสมหะ",

                "หายใจลำบาก",

                "หนาวสั่น"

            ],

            "synonyms" => [

                "ปอดติดเชื้อ"

            ],

            "red_flags" => [

                "หายใจไม่ออก"

            ],

            "risk_factors" => [

                "elderly",

                "diabetes",

                "high_fever"

            ],

            "severity_base_score" => 7,

            "ems_required" => false,

            "hospital_capability_required" => [

                "X-Ray",

                "Internal Medicine"

            ],

            "recommendation" =>
                "ควรพบแพทย์ภายในวันนี้",

            "reasoning_note" =>
                "เข้าได้กับปอดอักเสบ"

        ],

        //====================================================
        // ANAPHYLAXIS
        //====================================================

        [

            "disease_id" => "ALL001",

            "disease_name_th" =>
                "ภาวะแพ้รุนแรง",

            "disease_name_en" =>
                "Anaphylaxis",

            "category" =>
                "Allergy",

            "department" =>
                "ฉุกเฉิน",

            "icd_group" =>
                "T78",

            "symptoms" => [

                "ผื่น",

                "หน้าบวม",

                "ปากบวม",

                "หายใจไม่ออก",

                "คัน"

            ],

            "synonyms" => [

                "แพ้ยา",

                "แพ้อาหาร"

            ],

            "red_flags" => [

                "หายใจไม่ออก",

                "หมดสติ"

            ],

            "risk_factors" => [

                "allergy"

            ],

            "severity_base_score" => 10,

            "ems_required" => true,

            "hospital_capability_required" => [

                "ER"

            ],

            "recommendation" =>
                "ฉีด Adrenaline และเรียก EMS ทันที",

            "reasoning_note" =>
                "เข้าได้กับภาวะแพ้รุนแรง"
        ],
                //====================================================
        // SEPSIS
        //====================================================

        [

            "disease_id" => "INF001",

            "disease_name_th" =>
                "ภาวะติดเชื้อในกระแสเลือด",

            "disease_name_en" =>
                "Sepsis",

            "category" =>
                "Infectious Disease",

            "department" =>
                "อายุรกรรม",

            "icd_group" =>
                "A40-A41",

            "symptoms" => [

                "ไข้",

                "หนาวสั่น",

                "ซึม",

                "หายใจเร็ว",

                "หายใจลำบาก",

                "อ่อนเพลีย"

            ],

            "synonyms" => [

                "ติดเชื้อรุนแรง",

                "Septic"

            ],

            "red_flags" => [

                "หมดสติ",

                "ความดันต่ำ",

                "หายใจไม่ออก"

            ],

            "risk_factors" => [

                "elderly",

                "diabetes",

                "ckd",

                "high_fever",

                "tachycardia"

            ],

            "severity_base_score" => 10,

            "ems_required" => true,

            "hospital_capability_required" => [

                "ER",

                "ICU",

                "Blood Culture"

            ],

            "recommendation" =>
                "สงสัย Sepsis ควรเรียก EMS และรักษาโดยด่วน",

            "reasoning_note" =>
                "พบอาการเข้าได้กับภาวะติดเชื้อในกระแสเลือด"

        ],

        //====================================================
        // GASTROENTERITIS
        //====================================================

        [

            "disease_id" => "GI001",

            "disease_name_th" =>
                "กระเพาะและลำไส้อักเสบ",

            "disease_name_en" =>
                "Acute Gastroenteritis",

            "category" =>
                "Gastroenterology",

            "department" =>
                "อายุรกรรม",

            "icd_group" =>
                "A09",

            "symptoms" => [

                "ปวดท้อง",

                "ท้องเสีย",

                "อาเจียน",

                "คลื่นไส้",

                "ถ่ายเหลว"

            ],

            "synonyms" => [

                "อาหารเป็นพิษ",

                "ท้องร่วง"

            ],

            "red_flags" => [

                "ถ่ายเป็นเลือด",

                "อาเจียนเป็นเลือด"

            ],

            "risk_factors" => [

                "child",

                "elderly"

            ],

            "severity_base_score" => 4,

            "ems_required" => false,

            "hospital_capability_required" => [

                "Internal Medicine"

            ],

            "recommendation" =>
                "ควรดื่มน้ำและพบแพทย์หากอาการไม่ดีขึ้น",

            "reasoning_note" =>
                "เข้าได้กับโรคกระเพาะและลำไส้อักเสบ"

        ],

        //====================================================
        // ACUTE ABDOMEN
        //====================================================

        [

            "disease_id" => "GI002",

            "disease_name_th" =>
                "ภาวะปวดท้องเฉียบพลัน",

            "disease_name_en" =>
                "Acute Abdomen",

            "category" =>
                "Surgery",

            "department" =>
                "ศัลยกรรม",

            "icd_group" =>
                "R10",

            "symptoms" => [

                "ปวดท้องรุนแรง",

                "กดเจ็บ",

                "อาเจียน",

                "ไข้"

            ],

            "synonyms" => [

                "ปวดท้องเฉียบพลัน"

            ],

            "red_flags" => [

                "หน้าท้องแข็ง",

                "หมดสติ"

            ],

            "risk_factors" => [

                "elderly"

            ],

            "severity_base_score" => 8,

            "ems_required" => true,

            "hospital_capability_required" => [

                "ER",

                "Surgery",

                "CT Scan"

            ],

            "recommendation" =>
                "ควรพบศัลยแพทย์โดยด่วน",

            "reasoning_note" =>
                "สงสัยภาวะปวดท้องเฉียบพลัน"

        ],

        //====================================================
        // GI BLEEDING
        //====================================================

        [

            "disease_id" => "GI003",

            "disease_name_th" =>
                "เลือดออกในทางเดินอาหาร",

            "disease_name_en" =>
                "GI Bleeding",

            "category" =>
                "Gastroenterology",

            "department" =>
                "อายุรกรรม",

            "icd_group" =>
                "K92",

            "symptoms" => [

                "อาเจียนเป็นเลือด",

                "ถ่ายดำ",

                "ถ่ายเป็นเลือด",

                "หน้ามืด"

            ],

            "synonyms" => [

                "เลือดออกทางเดินอาหาร"

            ],

            "red_flags" => [

                "หมดสติ",

                "ความดันต่ำ"

            ],

            "risk_factors" => [

                "elderly"

            ],

            "severity_base_score" => 9,

            "ems_required" => true,

            "hospital_capability_required" => [

                "ER",

                "Endoscopy"

            ],

            "recommendation" =>
                "ควรเรียก EMS และส่งโรงพยาบาลทันที",

            "reasoning_note" =>
                "พบอาการเข้าได้กับ GI Bleeding"

        ],

        //====================================================
        // ACUTE KIDNEY INJURY
        //====================================================

        [

            "disease_id" => "REN001",

            "disease_name_th" =>
                "ไตวายเฉียบพลัน",

            "disease_name_en" =>
                "Acute Kidney Injury",

            "category" =>
                "Nephrology",

            "department" =>
                "อายุรกรรม",

            "icd_group" =>
                "N17",

            "symptoms" => [

                "ปัสสาวะน้อย",

                "บวม",

                "อ่อนเพลีย",

                "คลื่นไส้"

            ],

            "synonyms" => [

                "AKI"

            ],

            "red_flags" => [

                "ไม่ปัสสาวะ",

                "หายใจลำบาก"

            ],

            "risk_factors" => [

                "ckd",

                "elderly",

                "diabetes"

            ],

            "severity_base_score" => 8,

            "ems_required" => true,

            "hospital_capability_required" => [

                "ER",

                "Nephrology"

            ],

            "recommendation" =>
                "ควรพบอายุรแพทย์โรคไตโดยด่วน",

            "reasoning_note" =>
                "เข้าได้กับภาวะไตวายเฉียบพลัน"

        ],
                //====================================================
        // CHRONIC KIDNEY DISEASE
        //====================================================

        [

            "disease_id" => "REN002",

            "disease_name_th" =>
                "โรคไตเรื้อรัง",

            "disease_name_en" =>
                "Chronic Kidney Disease",

            "category" =>
                "Nephrology",

            "department" =>
                "อายุรกรรม",

            "icd_group" =>
                "N18",

            "symptoms" => [

                "บวม",

                "อ่อนเพลีย",

                "ปัสสาวะผิดปกติ",

                "คัน",

                "เบื่ออาหาร"

            ],

            "synonyms" => [

                "CKD",

                "ไตเสื่อม"

            ],

            "red_flags" => [

                "หายใจลำบาก",

                "ไม่ปัสสาวะ"

            ],

            "risk_factors" => [

                "diabetes",

                "hypertension",

                "elderly"

            ],

            "severity_base_score" => 7,

            "ems_required" => false,

            "hospital_capability_required" => [

                "Nephrology",

                "Laboratory"

            ],

            "recommendation" =>
                "ควรพบอายุรแพทย์โรคไต",

            "reasoning_note" =>
                "เข้าได้กับโรคไตเรื้อรัง"

        ],

        //====================================================
        // URINARY TRACT INFECTION
        //====================================================

        [

            "disease_id" => "URI001",

            "disease_name_th" =>
                "การติดเชื้อทางเดินปัสสาวะ",

            "disease_name_en" =>
                "Urinary Tract Infection",

            "category" =>
                "Urology",

            "department" =>
                "อายุรกรรม",

            "icd_group" =>
                "N39",

            "symptoms" => [

                "ปัสสาวะแสบ",

                "ปัสสาวะบ่อย",

                "ปัสสาวะขัด",

                "ไข้"

            ],

            "synonyms" => [

                "UTI",

                "กระเพาะปัสสาวะอักเสบ"

            ],

            "red_flags" => [

                "ปวดหลัง",

                "ไข้สูง"

            ],

            "risk_factors" => [

                "pregnancy",

                "diabetes"

            ],

            "severity_base_score" => 5,

            "ems_required" => false,

            "hospital_capability_required" => [

                "Laboratory"

            ],

            "recommendation" =>
                "ควรพบแพทย์และตรวจปัสสาวะ",

            "reasoning_note" =>
                "เข้าได้กับการติดเชื้อทางเดินปัสสาวะ"

        ],

        //====================================================
        // PANIC ATTACK
        //====================================================

        [

            "disease_id" => "PSY001",

            "disease_name_th" =>
                "ภาวะแพนิค",

            "disease_name_en" =>
                "Panic Attack",

            "category" =>
                "Psychiatry",

            "department" =>
                "จิตเวช",

            "icd_group" =>
                "F41",

            "symptoms" => [

                "ใจสั่น",

                "หายใจเร็ว",

                "แน่นหน้าอก",

                "กลัว",

                "มือสั่น"

            ],

            "synonyms" => [

                "แพนิค",

                "ตื่นตระหนก"

            ],

            "red_flags" => [],

            "risk_factors" => [

                "stress"

            ],

            "severity_base_score" => 3,

            "ems_required" => false,

            "hospital_capability_required" => [

                "Psychiatry"

            ],

            "recommendation" =>
                "ควรประเมินร่วมกับแพทย์",

            "reasoning_note" =>
                "อาจเป็น Panic Attack หลังตัดภาวะฉุกเฉิน"

        ],

        //====================================================
        // DEPRESSION
        //====================================================

        [

            "disease_id" => "PSY002",

            "disease_name_th" =>
                "ภาวะซึมเศร้า",

            "disease_name_en" =>
                "Depression",

            "category" =>
                "Psychiatry",

            "department" =>
                "จิตเวช",

            "icd_group" =>
                "F32",

            "symptoms" => [

                "ซึมเศร้า",

                "นอนไม่หลับ",

                "เบื่ออาหาร",

                "หมดกำลังใจ"

            ],

            "synonyms" => [

                "ซึม",

                "Depression"

            ],

            "red_flags" => [

                "คิดฆ่าตัวตาย"

            ],

            "risk_factors" => [

                "stress"

            ],

            "severity_base_score" => 5,

            "ems_required" => false,

            "hospital_capability_required" => [

                "Psychiatry"

            ],

            "recommendation" =>
                "ควรพบจิตแพทย์",

            "reasoning_note" =>
                "เข้าได้กับภาวะซึมเศร้า"

        ],

        //====================================================
        // CANCER WARNING
        //====================================================

        [

            "disease_id" => "ONC001",

            "disease_name_th" =>
                "สัญญาณเตือนโรคมะเร็ง",

            "disease_name_en" =>
                "Cancer Warning Signs",

            "category" =>
                "Oncology",

            "department" =>
                "มะเร็งวิทยา",

            "icd_group" =>
                "C00-C97",

            "symptoms" => [

                "น้ำหนักลด",

                "คลำก้อนได้",

                "เลือดออกผิดปกติ",

                "ไอเรื้อรัง",

                "กลืนลำบาก"

            ],

            "synonyms" => [

                "สงสัยมะเร็ง"

            ],

            "red_flags" => [

                "เลือดออกผิดปกติ"

            ],

            "risk_factors" => [

                "elderly",

                "smoker"

            ],

            "severity_base_score" => 7,

            "ems_required" => false,

            "hospital_capability_required" => [

                "Oncology",

                "CT Scan",

                "MRI"

            ],

            "recommendation" =>
                "ควรตรวจเพิ่มเติมโดยแพทย์เฉพาะทาง",

            "reasoning_note" =>
                "พบอาการเตือนที่อาจสัมพันธ์กับโรคมะเร็ง"

        ],
                //====================================================
        // MAJOR TRAUMA
        //====================================================

        [

            "disease_id" => "TRA001",

            "disease_name_th" =>
                "อุบัติเหตุรุนแรง",

            "disease_name_en" =>
                "Major Trauma",

            "category" =>
                "Trauma",

            "department" =>
                "ศัลยกรรมอุบัติเหตุ",

            "icd_group" =>
                "S00-T98",

            "symptoms" => [

                "รถชน",

                "ตกจากที่สูง",

                "กระดูกหัก",

                "เลือดออก",

                "แผลลึก",

                "หมดสติ"

            ],

            "synonyms" => [

                "อุบัติเหตุ",

                "บาดเจ็บรุนแรง"

            ],

            "red_flags" => [

                "เลือดออกมาก",

                "หมดสติ",

                "หายใจไม่ออก"

            ],

            "risk_factors" => [],

            "severity_base_score" => 10,

            "ems_required" => true,

            "hospital_capability_required" => [

                "ER",

                "Trauma Center",

                "Operating Room",

                "ICU"

            ],

            "recommendation" =>
                "ควรเรียก EMS และส่ง Trauma Center",

            "reasoning_note" =>
                "เข้าได้กับอุบัติเหตุรุนแรง"

        ],

        //====================================================
        // HEAD INJURY
        //====================================================

        [

            "disease_id" => "TRA002",

            "disease_name_th" =>
                "บาดเจ็บศีรษะ",

            "disease_name_en" =>
                "Head Injury",

            "category" =>
                "Trauma",

            "department" =>
                "ศัลยกรรมประสาท",

            "icd_group" =>
                "S06",

            "symptoms" => [

                "ศีรษะกระแทก",

                "หมดสติ",

                "อาเจียน",

                "ปวดหัว",

                "ความจำหาย"

            ],

            "synonyms" => [

                "หัวกระแทก",

                "สมองกระทบกระเทือน"

            ],

            "red_flags" => [

                "หมดสติ",

                "ชัก"

            ],

            "risk_factors" => [],

            "severity_base_score" => 9,

            "ems_required" => true,

            "hospital_capability_required" => [

                "ER",

                "CT Scan",

                "Neurosurgery"

            ],

            "recommendation" =>
                "ควรพบแพทย์ฉุกเฉินทันที",

            "reasoning_note" =>
                "สงสัยบาดเจ็บศีรษะ"

        ],

        //====================================================
        // HYPOGLYCEMIA
        //====================================================

        [

            "disease_id" => "END001",

            "disease_name_th" =>
                "ภาวะน้ำตาลต่ำ",

            "disease_name_en" =>
                "Hypoglycemia",

            "category" =>
                "Endocrinology",

            "department" =>
                "อายุรกรรม",

            "icd_group" =>
                "E16",

            "symptoms" => [

                "เหงื่อแตก",

                "มือสั่น",

                "ใจสั่น",

                "หมดสติ",

                "หิว"

            ],

            "synonyms" => [

                "น้ำตาลต่ำ"

            ],

            "red_flags" => [

                "หมดสติ",

                "ชัก"

            ],

            "risk_factors" => [

                "diabetes"

            ],

            "severity_base_score" => 8,

            "ems_required" => true,

            "hospital_capability_required" => [

                "ER"

            ],

            "recommendation" =>
                "รีบให้น้ำตาลและส่งโรงพยาบาล",

            "reasoning_note" =>
                "เข้าได้กับภาวะ Hypoglycemia"

        ],

        //====================================================
        // HYPERGLYCEMIA / DKA
        //====================================================

        [

            "disease_id" => "END002",

            "disease_name_th" =>
                "ภาวะน้ำตาลสูงรุนแรง",

            "disease_name_en" =>
                "Hyperglycemia / DKA",

            "category" =>
                "Endocrinology",

            "department" =>
                "อายุรกรรม",

            "icd_group" =>
                "E10-E14",

            "symptoms" => [

                "กระหายน้ำ",

                "ปัสสาวะบ่อย",

                "อ่อนเพลีย",

                "คลื่นไส้",

                "หายใจเร็ว"

            ],

            "synonyms" => [

                "DKA",

                "น้ำตาลสูง"

            ],

            "red_flags" => [

                "หมดสติ"

            ],

            "risk_factors" => [

                "diabetes"

            ],

            "severity_base_score" => 9,

            "ems_required" => true,

            "hospital_capability_required" => [

                "ER",

                "ICU"

            ],

            "recommendation" =>
                "ควรส่งโรงพยาบาลทันที",

            "reasoning_note" =>
                "สงสัยภาวะ DKA"

        ],

        //====================================================
        // SEIZURE
        //====================================================

        [

            "disease_id" => "NEU002",

            "disease_name_th" =>
                "อาการชัก",

            "disease_name_en" =>
                "Seizure",

            "category" =>
                "Neurology",

            "department" =>
                "ระบบประสาท",

            "icd_group" =>
                "G40",

            "symptoms" => [

                "ชัก",

                "หมดสติ",

                "กัดลิ้น",

                "ปัสสาวะราด"

            ],

            "synonyms" => [

                "ลมชัก"

            ],

            "red_flags" => [

                "ชักต่อเนื่อง",

                "หมดสติ"

            ],

            "risk_factors" => [

                "child"

            ],

            "severity_base_score" => 9,

            "ems_required" => true,

            "hospital_capability_required" => [

                "ER",

                "Neurology"

            ],

            "recommendation" =>
                "เรียก EMS หากชักต่อเนื่องหรือหมดสติ",

            "reasoning_note" =>
                "เข้าได้กับภาวะชัก"

        ]
    ];
    public static function getDiseases(): array
{
    return self::$diseases;
}

public static function getDiseaseById(string $id): ?array
{
    foreach (self::$diseases as $disease) {

        if (($disease["disease_id"] ?? "") === $id) {
            return $disease;
        }

    }

    return null;
}

public static function getDepartments(): array
{
    return array_values(
        array_unique(
            array_column(self::$diseases, "department")
        )
    );
}

public static function getAllSymptoms(): array
{
    $items = [];

    foreach (self::$diseases as $disease) {

        foreach ($disease["symptoms"] ?? [] as $symptom) {
            $items[] = $symptom;
        }

    }

    return array_values(array_unique($items));
}

public static function getAllRedFlags(): array
{
    $items = [];

    foreach (self::$diseases as $disease) {

        foreach ($disease["red_flags"] ?? [] as $flag) {
            $items[] = $flag;
        }

    }

    return array_values(array_unique($items));
}

}