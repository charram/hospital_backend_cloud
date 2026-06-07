<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

ini_set("display_errors", 1);
error_reporting(E_ALL);

require_once __DIR__ . "/db_connect.php";

/* =======================
   INPUT
======================= */

$hospital_id = intval($_POST["hospital_id"] ?? 0);

$title = trim($_POST["title"] ?? "");

$description = trim($_POST["description"] ?? "");

$province = trim($_POST["province"] ?? "");

/* =======================
   VALIDATION
======================= */

if (
    $hospital_id <= 0 ||
    $title === ""
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "hospital_id and title required"
    ]);

    exit;
}

if (
    !isset($_FILES["image"]) ||
    $_FILES["image"]["error"] !== UPLOAD_ERR_OK
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "image required"
    ]);

    exit;
}

/* =======================
   IMAGE VALIDATION
======================= */

$ext = strtolower(
    pathinfo(
        $_FILES["image"]["name"],
        PATHINFO_EXTENSION
    )
);

$allowed = [
    "jpg",
    "jpeg",
    "png",
    "webp"
];

if (!in_array($ext, $allowed)) {

    echo json_encode([
        "success" => false,
        "message" => "invalid image type"
    ]);

    exit;
}

/* =======================
   SUPABASE UPLOAD
======================= */

$supabaseUrl = getenv("SUPABASE_URL");

$supabaseKey = getenv("SUPABASE_SECRET");

if (
    !$supabaseUrl ||
    !$supabaseKey
) {
    echo json_encode([
        "success" => false,
        "message" => "Supabase ENV not found"
    ]);

    exit;
}

$fileName =
    "hospital_card_" .
    uniqid() .
    "." .
    $ext;

$bucket = "hospital-images";

$uploadUrl =
    $supabaseUrl .
    "/storage/v1/object/" .
    $bucket .
    "/" .
    $fileName;

$fileData = file_get_contents(
    $_FILES["image"]["tmp_name"]
);

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

$httpCode = curl_getinfo(
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
        "message" => "Supabase upload failed",
        "response" => $response
    ]);

    exit;
}

$image_path =
    $supabaseUrl .
    "/storage/v1/object/public/" .
    $bucket .
    "/" .
    $fileName;

/* =======================
   INSERT DB
======================= */

$sql = "
INSERT INTO hospital_card
(
    hospital_id,
    image_path,
    title,
    description,
    province
)
VALUES
(
    $1,
    $2,
    $3,
    $4,
    $5
)
";

$result = pg_query_params(
    $conn,
    $sql,
    [
        $hospital_id,
        $image_path,
        $title,
        $description,
        $province
    ]
);

if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);

    exit;
}

echo json_encode([
    "success" => true,
    "data" => [
        "hospital_id" => $hospital_id,
        "image_path" => $image_path,
        "title" => $title,
        "description" => $description,
        "province" => $province
    ]
], JSON_UNESCAPED_UNICODE);