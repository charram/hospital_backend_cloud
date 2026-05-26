<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . "/db_connect.php";

// ---------- รับค่าจาก Flutter ----------
//$hospital_id = $_POST["hospital_id"] ?? null;
$hospital_id = intval($_POST["hospital_id"] ?? 0);
$title       = trim($_POST["title"] ?? "");
$description = trim($_POST["description"] ?? "");
$province = $_POST["province"] ?? "";  // 🔥 เพิ่มตรงนี้

// ---------- Validate ----------
if ($hospital_id === null || $title === "") {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "hospital_id and title required"]);
    exit;
}

if (!isset($_FILES["image"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "image required"]);
    exit;
}

// ---------- Folder ----------
$dir = __DIR__ . "/uploads/hospital_card/";
if (!is_dir($dir)) mkdir($dir, 0777, true);

// ---------- File ----------
$ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
$allow = ["jpg","jpeg","png","webp"];
if (!in_array($ext, $allow)) {
    echo json_encode(["success" => false, "message" => "invalid image type"]);
    exit;
}

$filename = time() . "_" . rand(1000,9999) . "." . $ext;
$target   = $dir . $filename;
$db_path  = "uploads/hospital_card/" . $filename;

if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target)) {
    echo json_encode(["success" => false, "message" => "upload failed"]);
    exit;
}

// ---------- Insert DB ----------
$sql = "
INSERT INTO hospital_card
(hospital_id, image_path, title, description, province)
VALUES ($1, $2, $3, $4, $5)
";

$result = pg_query_params($conn, $sql, [
    $hospital_id,
    $db_path,
    $title,
    $description,
     $province 
]);

if (!$result) {
    @unlink($target);
    echo json_encode(["success" => false, "message" => pg_last_error($conn)]);
    exit;
}

echo json_encode([
    "success" => true,
    "data" => [
        "hospital_id" => $hospital_id,
        "image_path"  => $db_path,
        "title"       => $title,
        "description" => $description
    ]
], JSON_UNESCAPED_UNICODE);
