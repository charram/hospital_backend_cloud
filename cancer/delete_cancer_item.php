<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../db_connect.php";

if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

$id = $_POST["id"] ?? "";
$hospital_id = $_POST["hospital_id"] ?? "";

if ($id === "" || $hospital_id === "") {
    echo json_encode([
        "success" => false,
        "message" => "missing id or hospital_id"
    ]);
    exit;
}

$find = pg_query_params(
    $conn,
    "SELECT image_path FROM cancer_center WHERE id = $1 AND hospital_id = $2",
    [$id, $hospital_id]
);

if (!$find || pg_num_rows($find) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "item not found"
    ]);
    exit;
}

$row = pg_fetch_assoc($find);
$image_path = $row["image_path"] ?? "";

$res = pg_query_params(
    $conn,
    "DELETE FROM cancer_center WHERE id = $1 AND hospital_id = $2",
    [$id, $hospital_id]
);

if (!$res) {
    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
    exit;
}

if ($image_path !== "") {
    $file = __DIR__ . "/../" . $image_path;

    if (file_exists($file)) {
        @unlink($file);
    }
}

echo json_encode([
    "success" => true,
    "message" => "delete success"
], JSON_UNESCAPED_UNICODE);