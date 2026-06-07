<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../../db_connect.php";

if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

$hospital_id = $_POST["hospital_id"] ?? "";
$title = trim($_POST["title"] ?? "");
$description = trim($_POST["description"] ?? "");
$risk_level = trim($_POST["risk_level"] ?? "");

if (
    $hospital_id === "" ||
    $title === ""
) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields"
    ]);
    exit;
}

$image_path = "";

/* Upload รูปไป Supabase */
if (
    isset($_FILES["image"]) &&
    $_FILES["image"]["error"] === UPLOAD_ERR_OK
) {

    $supabaseUrl = getenv("SUPABASE_URL");
    $supabaseKey = getenv("SUPABASE_SECRET");

    $ext = strtolower(
        pathinfo(
            $_FILES["image"]["name"],
            PATHINFO_EXTENSION
        )
    );

    $fileName = "disease_" . uniqid() . "." . $ext;

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

    curl_exec($ch);

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
            "message" => "Supabase upload failed"
        ]);
        exit;
    }

    $image_path =
        $supabaseUrl .
        "/storage/v1/object/public/" .
        $bucket .
        "/" .
        $fileName;
}

/* บันทึกเข้า PG */
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
    'disease',
    $2,
    $3,
    $4,
    $5::jsonb,
    false
)
RETURNING *
";

$meta = json_encode([
    "risk_level" => $risk_level
]);

$res = pg_query_params(
    $conn,
    $sql,
    [
        $hospital_id,
        $title,
        $description,
        $image_path,
        $meta
    ]
);

if (!$res) {
    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => "Disease uploaded successfully",
    "data" => pg_fetch_assoc($res)
], JSON_UNESCAPED_UNICODE);