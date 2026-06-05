<?php
header("Content-Type: application/json; charset=utf-8");

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../../db_connect.php";

if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

$hospital_id = $_POST["hospital_id"] ?? "";
$upload_type = $_POST["upload_type"] ?? "";
$title = $_POST["title"] ?? "";
$description = $_POST["description"] ?? "";
$meta = $_POST["meta"] ?? "{}";
$is_hero = $_POST["is_hero"] ?? "false";

$show_on_home = $_POST["show_on_home"] ?? "0";
$category = $_POST["category"] ?? "cancer";

$min_price = $_POST["min_price"] ?? "";
$max_price = $_POST["max_price"] ?? "";
$avg_price = $_POST["avg_price"] ?? "";
$insurance_note = $_POST["insurance_note"] ?? "";

if (
    $hospital_id === "" ||
    $upload_type === "" ||
    $title === ""
) {
echo json_encode([
    "success" => false,
    "message" => "missing required fields",
    "hospital_id" => $hospital_id,
    "upload_type" => $upload_type,
    "title" => $title
]);
exit;
}

$allowed_types = [
    "hero",
    "service",
    "machine",
    "symptom",
    "disease",
    "doctor"
];

if (!in_array($upload_type, $allowed_types)) {
    echo json_encode([
        "success" => false,
        "message" => "invalid upload_type",
        "received" => $upload_type
    ]);
    exit;
}

if (
    json_decode($meta) === null &&
    json_last_error() !== JSON_ERROR_NONE
) {
    echo json_encode([
        "success" => false,
        "message" => "invalid meta json"
    ]);
    exit;
}

$image_path = "";

if (
    isset($_FILES["image"]) &&
    $_FILES["image"]["error"] === UPLOAD_ERR_OK
) {
$supabaseUrl =
getenv("SUPABASE_URL");

$supabaseKey =
getenv("SUPABASE_SECRET");



   $tmp = $_FILES["image"]["tmp_name"];

$original =
    $_FILES["image"]["name"];

$ext = strtolower(
    pathinfo(
        $original,
        PATHINFO_EXTENSION
    )
);

$allowed_ext = [
    "jpg",
    "jpeg",
    "png",
    "webp"
];

if (!in_array($ext, $allowed_ext)) {

    echo json_encode([
        "success" => false,
        "message" => "invalid image type"
    ]);
    exit;
}

$file_name =
    uniqid() .
    "." .
    $ext;

$fileData =
    file_get_contents($tmp);

$bucket =
    "hospital-images";

$uploadUrl =
    $supabaseUrl .
    "/storage/v1/object/" .
    $bucket .
    "/" .
    $file_name;

$ch = curl_init($uploadUrl);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $fileData,
   CURLOPT_HTTPHEADER => [
    "apikey: " . $supabaseKey,
    "Authorization: Bearer " . $supabaseKey,
        "Content-Type: image/" . $ext,
        "x-upsert: true"
    ]
]);

$response = curl_exec($ch);

$httpCode =
    curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );

curl_close($ch);

if (
    $httpCode < 200 ||
    $httpCode >= 300
) {

    echo json_encode([
        "success" => false,
        "message" => "supabase upload failed",
        "response" => $response
    ]);
    exit;
}
$image_path =
    $supabaseUrl .
    "/storage/v1/object/public/" .
    $bucket .
    "/" .
    $file_name;

}

$is_hero_bool = (
    $is_hero === "true" ||
    $is_hero === "1" ||
    $upload_type === "hero"
);

if ($upload_type === "hero") {

    pg_query_params(
        $conn,
        "
        UPDATE cancer_center
        SET is_hero = false
        WHERE hospital_id = $1
        AND upload_type = 'hero'
        ",
        [$hospital_id]
    );
}

$sql = "
INSERT INTO cancer_center
(
hospital_id,
upload_type,
title,
description,
image_path,
meta,
is_hero
)
VALUES
(
$1,
$2,
$3,
$4,
$5,
$6::jsonb,
$7
)
RETURNING *
";
echo json_encode([
    "hospital_id" => $hospital_id,
    "upload_type" => $upload_type,
    "title" => $title,
    "is_hero" => $is_hero,
    "is_hero_bool" => $is_hero_bool,
    "meta" => $meta
]);

exit;

$res = pg_query_params(
    $conn,
    $sql,
    [
        $hospital_id,
        $upload_type,
        $title,
        $description,
        $image_path,
        $meta,
        $is_hero_bool
    ]
);

if (!$res) {

    echo json_encode([
        "success" => false,
        "message" =>
            pg_last_error($conn)
    ]);
    exit;
}

$row =
    pg_fetch_assoc($res);

echo json_encode([
    "success" => true,
    "message" => "upload success",
    "data" => $row
], JSON_UNESCAPED_UNICODE);