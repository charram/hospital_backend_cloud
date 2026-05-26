<?php
header("Content-Type: application/json");
require_once __DIR__ . '/db_connect.php';

// ✅ รับค่า
$id_raw = $_POST['id'] ?? null;
$status = $_POST['status'] ?? '';

$id = is_numeric($id_raw)
    ? (int)$id_raw
    : 0;

// 🔥 รับข้อมูลใบนำทาง
$department = $_POST['department'] ?? null;
$building = $_POST['building'] ?? null;
$floor = $_POST['floor'] ?? null;
$room = $_POST['room'] ?? null;
$queue_no = $_POST['queue_no'] ?? null;

error_log("BOOKING ID = " . $id);
error_log("STATUS = " . $status);

if ($id <= 0 || !$status) {
    echo json_encode([
        "success" => false,
        "message" => "invalid id or status"
    ]);
    exit;
}

// 🔥 update booking + navigation
$res = pg_query_params(
    $conn,
    "UPDATE bookings
     SET
        status=$1,
        department=$2,
        building=$3,
        floor=$4,
        room=$5,
        queue_no=$6
     WHERE id=$7",
    [
        $status,
        $department,
        $building,
        $floor,
        $room,
        $queue_no,
        $id
    ]
);

// 🔥 ดึง user_id
$q = pg_query_params(
    $conn,
    "SELECT user_id
     FROM bookings
     WHERE id=$1",
    [$id]
);

$row = pg_fetch_assoc($q);
$user_id = $row['user_id'] ?? null;

// 🔥 insert notification
if ($user_id) {

    $insert = pg_query_params(
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
            $user_id,
            "สถานะการนัดหมาย",
            $status == "approved"
                ? "การจองของคุณได้รับการอนุมัติแล้ว"
                : "การจองของคุณถูกปฏิเสธ",
            $id
        ]
    );

    if (!$insert) {
        error_log(
            "INSERT ERROR: "
            . pg_last_error($conn)
        );
    }
}

echo json_encode([
    "success" => $res ? true : false,
    "booking_id" => $id
]);