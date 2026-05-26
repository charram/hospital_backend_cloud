<?php
header("Content-Type: application/json");

require_once __DIR__ . '/db_connect.php';

// ================= GET ID =================

$id_raw = $_GET['id'] ?? null;

$id = is_numeric($id_raw)
    ? (int)$id_raw
    : 0;

if ($id <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "invalid id"
    ]);

    exit;
}

// ================= GET NAVIGATION =================

$q = pg_query_params(
    $conn,
    "SELECT *
     FROM patient_navigation
     WHERE id=$1",
    [$id]
);

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

// ================= SUCCESS =================

echo json_encode([
    "success" => true,
    "data" => $row
]);