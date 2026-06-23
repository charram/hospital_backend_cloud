<?php
header("Content-Type: application/json");
require_once __DIR__ . '/db_connect.php';

$id_raw = $_POST['id'] ?? null;
$status = $_POST['status'] ?? '';

$id = is_numeric($id_raw) ? (int)$id_raw : 0;

$department = $_POST['department'] ?? null;
$building   = $_POST['building'] ?? null;
$floor      = $_POST['floor'] ?? null;
$room       = $_POST['room'] ?? null;
$queue_no   = $_POST['queue_no'] ?? null;

if ($id <= 0 || empty($status)) {
    echo json_encode([
        "success" => false,
        "message" => "invalid id or status"
    ]);
    exit;
}

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

if (!$res) {
    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
    exit;
}

$q = pg_query_params(
    $conn,
    "SELECT user_id FROM bookings WHERE id=$1",
    [$id]
);

$row = pg_fetch_assoc($q);
$user_id = $row['user_id'] ?? null;

if ($user_id) {

    $title = "สถานะการนัดหมาย";
    $body  = "";

    if ($status === "approved") {

        $body = "การจองของคุณได้รับการอนุมัติแล้ว";

    } elseif ($status === "rejected") {

        $body = "การจองของคุณถูกปฏิเสธ";

    } elseif ($status === "arrived") {

        $title = "ใบนำทางผู้ป่วย";

        $body =
            "แผนก: {$department}\n" .
            "อาคาร: {$building}\n" .
            "ชั้น: {$floor}\n" .
            "ห้อง: {$room}\n" .
            "คิว: {$queue_no}";
    }

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
            $user_id,
            $title,
            $body,
            $id
        ]
    );
}

echo json_encode([
    "success" => true,
    "booking_id" => $id
]);