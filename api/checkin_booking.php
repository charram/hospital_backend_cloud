<?php
header("Content-Type: application/json");
require_once __DIR__ . '/db_connect.php';

// รับค่า (รองรับทั้ง string/int)
$id_raw = $_POST['id'] ?? null;
$id = is_numeric($id_raw) ? (int)$id_raw : 0;

if ($id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "invalid id"
    ]);
    exit;
}

// 🔎 เช็คว่ามี booking นี้จริงไหม
$check = pg_query_params(
    $conn,
    "SELECT id, status FROM bookings WHERE id=$1",
    [$id]
);

if (!$check || pg_num_rows($check) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "booking not found"
    ]);
    exit;
}

$row = pg_fetch_assoc($check);
$currentStatus = $row['status'] ?? '';

// ❗ กันการเช็คอินซ้ำ (optional)
if ($currentStatus === 'arrived') {
    echo json_encode([
        "success" => true,
        "message" => "already checked-in",
        "status" => $currentStatus
    ]);
    exit;
}

// 🔥 อัปเดตสถานะเป็น arrived + เวลามาถึง
$res = pg_query_params(
    $conn,
    "UPDATE bookings 
     SET status='arrived', arrived_at = NOW()
     WHERE id=$1",
    [$id]
);

if (!$res) {
    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
    exit;
}

// 🔔 (ออปชัน) สร้าง notification แจ้งผู้ใช้ว่าเช็คอินแล้ว
$qUser = pg_query_params(
    $conn,
    "SELECT user_id FROM bookings WHERE id=$1",
    [$id]
);

$userRow = pg_fetch_assoc($qUser);
$user_id = $userRow['user_id'] ?? null;

if ($user_id) {
    pg_query_params(
        $conn,
        "INSERT INTO notifications (user_id, title, body, booking_id)
         VALUES ($1,$2,$3,$4)",
        [
            $user_id,
            "เช็คอินสำเร็จ",
            "คุณได้เช็คอินที่โรงพยาบาลเรียบร้อยแล้ว",
            $id
        ]
    );
}

// ✅ response
echo json_encode([
    "success" => true,
    "message" => "check-in success",
    "booking_id" => $id,
    "status" => "arrived"
]);