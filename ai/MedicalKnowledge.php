<?php

class MedicalKnowledge
{
    public static function emergencyWords(): array
    {
        return [
            "หมดสติ",
            "ไม่รู้สึกตัว",
            "ชัก",
            "หายใจไม่ออก",
            "เจ็บหน้าอก",
            "แน่นหน้าอก",
            "พูดไม่ชัด",
            "หน้าเบี้ยว",
            "แขนขาอ่อนแรง",
            "เลือดออกมาก"
        ];
    }

    public static function heartWords(): array
    {
        return [
            "เจ็บหน้าอก",
            "แน่นหน้าอก",
            "ใจสั่น",
            "เหนื่อยง่าย",
            "หายใจไม่ออก",
            "เจ็บร้าวไปแขน",
            "เหงื่อแตก",
            "หน้ามืด"
        ];
    }

    public static function brainWords(): array
    {
        return [
            "หน้าเบี้ยว",
            "พูดไม่ชัด",
            "แขนขาอ่อนแรง",
            "ชาครึ่งซีก",
            "เวียนหัวรุนแรง",
            "หมดสติ",
            "ปวดหัวรุนแรง",
            "เดินเซ"
        ];
    }

    public static function respiratoryWords(): array
    {
        return [
            "ไอ",
            "หอบ",
            "หายใจลำบาก",
            "หายใจไม่ออก",
            "เจ็บคอ",
            "มีเสมหะ",
            "แน่นหน้าอก",
            "หายใจมีเสียง"
        ];
    }

    public static function feverWords(): array
    {
        return [
            "ไข้",
            "ตัวร้อน",
            "หนาวสั่น",
            "ปวดเมื่อย",
            "ไอ",
            "เจ็บคอ",
            "น้ำมูก",
            "อ่อนเพลีย"
        ];
    }

    public static function stomachWords(): array
    {
        return [
            "ปวดท้อง",
            "ท้องเสีย",
            "อาเจียน",
            "คลื่นไส้",
            "ถ่ายเหลว",
            "ปวดท้องรุนแรง",
            "ท้องอืด",
            "ถ่ายเป็นเลือด"
        ];
    }

    public static function traumaWords(): array
    {
        return [
            "รถชน",
            "ล้ม",
            "ตกจากที่สูง",
            "กระแทก",
            "บาดเจ็บ",
            "กระดูกหัก",
            "เลือดออก",
            "แผลลึก"
        ];
    }

    public static function allergyWords(): array
    {
        return [
            "ผื่น",
            "คัน",
            "บวม",
            "หน้าบวม",
            "ปากบวม",
            "แพ้ยา",
            "แพ้อาหาร",
            "หายใจติดขัด"
        ];
    }

    public static function mentalWords(): array
    {
        return [
            "เครียด",
            "นอนไม่หลับ",
            "วิตกกังวล",
            "ซึมเศร้า",
            "ใจสั่น",
            "หายใจเร็ว",
            "กลัว",
            "แพนิค"
        ];
    }
}