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

$doctor_name = trim($_POST["doctor_name"] ?? "");
$specialty = trim($_POST["specialty"] ?? "");
$experience = trim($_POST["experience"] ?? "");
$education = trim($_POST["education"] ?? "");
$language = trim($_POST["language"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$description = trim($_POST["description"] ?? "");

$sub_specialty = trim($_POST["sub_specialty"] ?? "");
$line = trim($_POST["line"] ?? "");
$related_diseases = $_POST["related_diseases"] ?? "[]";

if (
    $hospital_id === "" ||
    $doctor_name === ""
) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields"
    ]);
    exit;
}

$image_path = "";

/* ==========================
   UPLOAD IMAGE TO SUPABASE
========================== */

if (
    isset($_FILES["image"]) &&
    $_FILES["image"]["error"] === UPLOAD_ERR_OK
) {

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
        "doctor_" .
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
}

/* ==========================
   INSERT TO POSTGRESQL
========================== */

$sql = "
INSERT INTO doctor_profiles (
    hospital_id,
    doctor_name,
    specialty,
    experience,
    education,
    language,
    phone,
    description,
    image_path
)
VALUES (
    $1,$2,$3,$4,$5,
    $6,$7,$8,$9
)
RETURNING id
";

$result = pg_query_params(
    $conn,
    $sql,
    [
        $hospital_id,
        $doctor_name,
        $specialty,
        $experience,
        $education,
        $language,
        $phone,
        $description,
        $image_path
    ]
);

if ($result) {

    $row = pg_fetch_assoc($result);

    echo json_encode([
        "success" => true,
        "message" => "Doctor uploaded successfully",
        "id" => $row["id"],
        "image_path" => $image_path
    ], JSON_UNESCAPED_UNICODE);

} else {

    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
}