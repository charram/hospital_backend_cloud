<?php

header("Content-Type: application/json");

require_once __DIR__ . '/db_connect.php';

// ================= BOOKING ID =================

$booking_id = $_POST['booking_id'] ?? 0;

// ================= GET BOOKING =================

$q = pg_query_params(
    $conn,
    "SELECT
        user_id,
        department,
        room,
        queue_no
     FROM bookings
     WHERE id=$1",
    [$booking_id]
);

if (!$q || pg_num_rows($q) == 0) {

    echo json_encode([
        "success" => false,
        "message" => "booking not found"
    ]);

    exit;
}

$row = pg_fetch_assoc($q);

// ================= INSERT USER NAVIGATION =================

$insert = pg_query_params(
    $conn,
    "INSERT INTO user_navigation
    (
        user_id,
        department,
        room,
        queue_no
    )
    VALUES
    ($1,$2,$3,$4)",
    [
        $row['user_id'],
        $row['department'],
        $row['room'],
        $row['queue_no']
    ]
);

if (!$insert) {

    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);

    exit;
}

// ================= INSERT NOTIFICATION =================

pg_query_params(
    $conn,
    "INSERT INTO notifications
    (
        user_id,
        title,
        body,
        booking_id
    )
    VALUES
    ($1,$2,$3,$4)",
    [
        $row['user_id'],
        'ใบนำทางผู้ป่วย',
        'กรุณาไปที่ ' . $row['department'],
        $booking_id
    ]
);

// ================= RESPONSE =================

echo json_encode([
    "success" => true
]);