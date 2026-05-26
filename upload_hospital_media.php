<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/db_connect.php";

// =======================
// INPUT
// =======================
$hospital_id = intval($_POST["hospital_id"] ?? 0);
$title       = trim($_POST["title"] ?? "");
$description = trim($_POST["description"] ?? "");
$is_hero     = ($_POST["is_hero"] ?? "0") === "1" ? 1 : 0;


// =======================
// VALIDATION
// =======================
if ($hospital_id <= 0 || $title === "") {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "hospital_id and title required"
    ]);
    exit;
}

if (!isset($_FILES["image"])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "image required"
    ]);
    exit;
}

// =======================
// FILE VALIDATION
// =======================
$ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
$allowed = ["jpg", "jpeg", "png", "webp"];

if (!in_array($ext, $allowed)) {
    echo json_encode([
        "success" => false,
        "message" => "invalid image type"
    ]);
    exit;
}

// =======================
// PREPARE DIRECTORY
// =======================
$uploadDir = __DIR__ . "/uploads/hospital_media/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// =======================
// SAVE FILE
// =======================
$filename = time() . "_" . rand(1000, 9999) . "." . $ext;
$targetPath = $uploadDir . $filename;
$dbPath = "uploads/hospital_media/" . $filename;

if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
    echo json_encode([
        "success" => false,
        "message" => "upload failed"
    ]);
    exit;
}

// =======================
// HERO LOGIC (สำคัญมาก)
// =======================
if ($is_hero === 1) {
    pg_query_params(
        $conn,
        "UPDATE hospital_media SET is_hero = 0 WHERE hospital_id = $1",
        [$hospital_id]
    );
}

// =======================
// INSERT DB
// =======================
$sql = "
INSERT INTO hospital_media
(hospital_id, file_path, title, description, is_hero)
VALUES ($1,$2,$3,$4,$5)
RETURNING id
";

$res = pg_query_params(
    $conn,
    $sql,
    [$hospital_id, $dbPath, $title, $description, $is_hero]
);

if (!$res) {
    @unlink($targetPath);
    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
    exit;
}

$row = pg_fetch_assoc($res);

// =======================
// RESPONSE
// =======================
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
