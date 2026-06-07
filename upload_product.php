<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/db_connect.php";

$hospital_id = intval($_POST["hospital_id"] ?? 0);

$title = trim($_POST["title"] ?? "");

$description = trim($_POST["description"] ?? "");

$price = $_POST["price"] ?? 0;

if (
    $hospital_id <= 0 ||
    $title === ""
) {
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
    echo json_encode([
        "success" => false,
        "message" => "No image uploaded"
    ]);
    exit;
}

/* ==========================
   UPLOAD IMAGE TO SUPABASE
========================== */

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

$fileName =
    "product_" .
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

/* ==========================
   INSERT TO POSTGRESQL
========================== */

$sql = "
INSERT INTO products (
    hospital_id,
    image_path,
    title,
    description,
    price
)
VALUES (
    $1,
    $2,
    $3,
    $4,
    $5
)
";

$res = pg_query_params(
    $conn,
    $sql,
    [
        $hospital_id,
        $image_path,
        $title,
        $description,
        $price
    ]
);

echo json_encode([
    "success" => $res ? true : false,
    "image_path" => $image_path
], JSON_UNESCAPED_UNICODE);