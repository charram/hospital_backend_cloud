<?php

header("Content-Type: application/json");

require_once __DIR__ . '/db_connect.php';

// ================= BOOKING ID =================

$booking_id_raw =
    $_GET['booking_id'] ?? null;

$booking_id = is_numeric($booking_id_raw)
    ? (int)$booking_id_raw
    : 0;

// ================= USER ID =================

$user_id_raw =
    $_GET['user_id'] ?? null;

$user_id = is_numeric($user_id_raw)
    ? (int)$user_id_raw
    : 0;

// ================= QUERY =================

// 🔥 ของใหม่: ดึงจาก booking_id
// 🔥 ของใหม่: ดึงจาก booking_id
if ($booking_id > 0) {

    $q = pg_query_params(
        $conn,
        "SELECT
            b.id,
            b.user_id,
            b.first_name,
            b.last_name,
            b.department,
            b.building,
            b.floor,
            b.room,
            b.queue_no
         FROM bookings b
         WHERE b.id=$1
         LIMIT 1",
        [$booking_id]
    );

}
// 🔥 ของเดิม: ดึงล่าสุดจาก user_id
else if ($user_id > 0) {

    $q = pg_query_params(
        $conn,
        "SELECT
            b.id,
            b.user_id,
            b.first_name,
            b.last_name,
            b.department,
            b.building,
            b.floor,
            b.room,
            b.queue_no
         FROM bookings b
         WHERE b.user_id=$1
         AND b.status='arrived'
         ORDER BY b.id DESC
         LIMIT 1",
        [$user_id]
    );

} else {

    echo json_encode([
        "success" => false,
        "message" => "missing id"
    ]);

    exit;
}

// ================= CHECK =================

if (!$q || pg_num_rows($q) === 0) {

    echo json_encode([
        "success" => false,
        "message" => "navigation not found"
    ]);

    exit;
}

$row = pg_fetch_assoc($q);

// ================= STRING =================

foreach ($row as $k => $v) {

    if ($v !== null) {
        $row[$k] = (string)$v;
    }
}

// ================= RESPONSE =================

echo json_encode([
    "success" => true,
    "data" => $row
]);