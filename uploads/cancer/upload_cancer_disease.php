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

/* ==========================
   RECEIVE DATA
========================== */

$hospital_id = $_POST["hospital_id"] ?? "";
$disease_key = $_POST["disease_key"] ?? "";

$title = trim($_POST["title"] ?? "");
$description = trim($_POST["description"] ?? "");
$risk_level = trim($_POST["risk_level"] ?? "");

$symptoms = $_POST["symptoms"] ?? "[]";
$machines = $_POST["machines"] ?? "[]";

$show_on_home =
    ($_POST["show_on_home"] ?? "1") === "1";

/* ==========================
   VALIDATE
========================== */

if (
    $hospital_id === "" ||
    $disease_key === "" ||
    $title === ""
) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields",
        "hospital_id" => $hospital_id,
        "disease_key" => $disease_key,
        "title" => $title
    ]);
    exit;
}

/* ==========================
   VALIDATE JSON
========================== */

if (
    json_decode($symptoms) === null &&
    $symptoms !== "[]"
) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid symptoms JSON"
    ]);
    exit;
}

if (
    json_decode($machines) === null &&
    $machines !== "[]"
) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid machines JSON"
    ]);
    exit;
}

/* ==========================
   UPLOAD IMAGE TO SUPABASE
========================== */

$image_path = "";

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
INSERT INTO cancer_diseases
(
    hospital_id,
    disease_key,
    title,
    description,
    risk_level,
    symptoms,
    machines,
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
    $6::jsonb,
    $7::jsonb,
    $8,
    $9
)
RETURNING *
";

$res = pg_query_params(
    $conn,
    $sql,
    [
        $hospital_id,
        $disease_key,
        $title,
        $description,
        $risk_level,
        $symptoms,
        $machines,
        $image_path,
        $show_on_home
    ]
);

if (!$res) {

    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);

    exit;
}

/* ==========================
   SUCCESS
========================== */

$row = pg_fetch_assoc($res);

echo json_encode([
    "success" => true,
    "message" => "Disease uploaded successfully",
    "data" => $row
], JSON_UNESCAPED_UNICODE);