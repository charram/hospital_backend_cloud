<?php

header("Content-Type: application/json; charset=UTF-8");

ini_set("display_errors", 1);
error_reporting(E_ALL);

require_once __DIR__ . "/db_connect.php";

/* =======================
   INPUT
======================= */

$hospital_id = intval(
    $_POST["hospital_id"] ?? 0
);

$title = trim(
    $_POST["title"] ?? ""
);

$body = trim(
    $_POST["body"] ?? ""
);

/* =======================
   VALIDATION
======================= */

if ($hospital_id <= 0) {

    echo json_encode([
        "success" => false,
        "message" => "Missing hospital_id"
    ]);

    exit;
}

if (
    !isset($_FILES["image"]) ||
    $_FILES["image"]["error"] !== UPLOAD_ERR_OK
) {

    echo json_encode([
        "success" => false,
        "message" => "Image not found"
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
        "message" => "Invalid image type"
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
    "beauty_" .
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

$imagePath =
    $supabaseUrl .
    "/storage/v1/object/public/" .
    $bucket .
    "/" .
    $fileName;

/* =======================
   INSERT DB
======================= */

$sql = "
INSERT INTO beauty_center
(
    hospital_id,
    image_path,
    title,
    body
)
VALUES
(
    $1,
    $2,
    $3,
    $4
)
";

$result = pg_query_params(
    $conn,
    $sql,
    [
        $hospital_id,
        $imagePath,
        $title,
        $body
    ]
);

if (!$result) {

    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);

    exit;
}

/* =======================
   RESPONSE
======================= */

echo json_encode([
    "success" => true,
    "path" => $imagePath,
    "data" => [
        "hospital_id" => $hospital_id,
        "image_path" => $imagePath,
        "title" => $title,
        "body" => $body
    ]
], JSON_UNESCAPED_UNICODE);