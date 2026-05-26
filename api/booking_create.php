<?php
header("Content-Type: application/json");

// 🔌 DB
require_once __DIR__ . '/db_connect.php';

// ❌ กัน conn fail
if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "database connection failed"
    ]);
    exit;
}

// 📥 รับค่าจาก Flutter
$user_id    = $_POST['user_id'] ?? null;
$service    = trim($_POST['service'] ?? '');
$date       = $_POST['date'] ?? '';
$time       = $_POST['time'] ?? '';
$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$hospital_id = $_POST['hospital_id'] ?? null;

// ❌ เช็คข้อมูล
if (
    !$user_id ||
    !$service ||
    !$date ||
    !$time ||
    !$first_name ||
    !$last_name ||
    !$phone ||
    !$hospital_id
) {
    echo json_encode([
        "success" => false,
        "message" => "missing data"
    ]);
    exit;
}

// 💾 INSERT
$result = pg_query_params(
    $conn,
    "INSERT INTO bookings
    (
        user_id,
        hospital_id,
        service,
        booking_date,
        booking_time,
        first_name,
        last_name,
        phone,
        status
    )
    VALUES
    ($1,$2,$3,$4,$5,$6,$7,$8,'pending')
    RETURNING id",
    [
        $user_id,
        $hospital_id,
        $service,
        $date,
        $time,
        $first_name,
        $last_name,
        $phone
    ]
);

// ❌ insert fail
if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
    exit;
}

$row = pg_fetch_assoc($result);

echo json_encode([
    "success" => true,
    "message" => "booking success",
    "booking_id" => $row["id"]
]);