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

$is_hero =
    ($_POST["is_hero"] ?? "0") === "1"
    ? 1
    : 0;

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
    "hospital_" .
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

$dbPath =
    $supabaseUrl .
    "/storage/v1/object/public/" .
    $bucket .
    "/" .
    $fileName;

/* =======================
   HERO LOGIC
======================= */

if ($is_hero === 1) {

    pg_query_params(
        $conn,
        "
        UPDATE hospital_media
        SET is_hero = 0
        WHERE hospital_id = $1
        ",
        [$hospital_id]
    );
}

/* =======================
   INSERT DB
======================= */

$sql = "
INSERT INTO hospital_media
(
    hospital_id,
    file_path,
    title,
    description,
    is_hero
)
VALUES
(
    $1,
    $2,
    $3,
    $4,
    $5
)
RETURNING id
";

$res = pg_query_params(
    $conn,
    $sql,
    [
        $hospital_id,
        $dbPath,
        $title,
        $description,
        $is_hero
    ]
);

if (!$res) {

    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);

    exit;
}

$row = pg_fetch_assoc($res);

/* =======================
   RESPONSE
======================= */

echo json_encode([
    "success" => true,
    "data" => [
        "id" => intval($row["id"]),
        "hospital_id" => $hospital_id,
        "file_path" => $dbPath,
        "title" => $title,
        "description" => $description,
        "is_hero" => $is_hero
    ]
], JSON_UNESCAPED_UNICODE);