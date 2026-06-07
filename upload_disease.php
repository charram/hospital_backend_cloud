<?php

header("Access-Control-Allow-Origin: *");
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

$description = trim(
    $_POST["description"] ?? ""
);

$category = trim(
    $_POST["category"] ?? ""
);

$show_home = intval(
    $_POST["show_on_home"] ?? 1
);

/* =======================
   VALIDATION
======================= */

if (
    $hospital_id <= 0 ||
    $title === "" ||
    $category === ""
) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields"
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
    "disease_" .
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

$q = pg_query_params(
    $conn,
    "
    INSERT INTO hospital_diseases
    (
        hospital_id,
        category,
        title,
        description,
        image_path,
        show_on_home
    )
    VALUES
    (
        $1,
        $2,
        $3,
        $4,
        $5,
        $6
    )
    ",
    [
        $hospital_id,
        $category,
        $title,
        $description,
        $imagePath,
        $show_home
    ]
);

if (!$q) {

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
    "data" => [
        "hospital_id" => $hospital_id,
        "category" => $category,
        "title" => $title,
        "description" => $description,
        "image_path" => $imagePath,
        "show_on_home" => $show_home
    ]
], JSON_UNESCAPED_UNICODE);