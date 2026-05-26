<?php

header(
    "Content-Type: application/json"
);

require_once
    __DIR__ .
    '/db_connect.php';

// รับ hospital_id
$hospital_id =
    intval(
        $_GET[
            'hospital_id'
        ] ?? 0
    );

// ถ้าไม่มี hospital_id
if (
    $hospital_id <= 0
) {

    echo json_encode([
        "success" =>
            false,
        "message" =>
            "hospital_id missing"
    ]);

    exit;
}

// ดึงเฉพาะ booking ของโรงบาลนี้
$res =
    pg_query_params(
        $conn,
        "
        SELECT *
        FROM bookings
        WHERE hospital_id = $1
        ORDER BY id DESC
        ",
        [
            $hospital_id
        ]
    );

$data = [];

while (
    $row =
    pg_fetch_assoc(
        $res
    )
) {
    $data[] =
        $row;
}

echo json_encode([
    "success" =>
        true,
    "data" =>
        $data
]);