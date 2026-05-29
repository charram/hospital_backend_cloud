<?php
header("Content-Type: application/json; charset=utf-8");

// 1. เรียกไฟล์เชื่อมต่อฐานข้อมูลตัวเดิมที่คุณทำรหัสไว้แล้วมาใช้งาน
require_once 'db_connect_railway.php';

try {
    // 2. ใช้คำสั่ง SQL ยิงไปดึงข้อมูลจากตาราง admins บน Railway
    $stmt = $conn->prepare("SELECT * FROM admins");
    $stmt->execute();
    
    // 3. ดึงดาต้าออกมาเก็บไว้ในรูปแบบ Array
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. พ่นผลลัพธ์เป็น JSON ออกไปให้แอป Flutter เรียกใช้งาน
    echo json_encode([
        "success" => true,
        "data" => $data
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "ดึงข้อมูลจากคลาวด์ไม่สำเร็จ",
        "error" => $e->getMessage()
    ]);
}