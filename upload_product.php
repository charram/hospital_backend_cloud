<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once "db_connect.php";

//$hospital_id = $_POST["hospital_id"] ?? null;
$hospital_id = intval($_POST["hospital_id"] ?? 0);
$title       = trim($_POST["title"] ?? "");
$description = trim($_POST["description"] ?? "");
$price       = $_POST["price"] ?? 0;

if ($hospital_id === null || $title === "") {
    echo json_encode([
        "success" => false,
        "message" => "hospital_id and title required"
    ]);
    exit;
}

if (!isset($_FILES["image"])) {
    echo json_encode([
        "success" => false,
        "message" => "No image uploaded"
    ]);
    exit;
}

$folder = __DIR__ . "/uploads/product/";
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

$ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
$filename = time() . "_" . rand(1000,9999) . "." . $ext;
$target = $folder . $filename;

if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target)) {
    echo json_encode([
        "success" => false,
        "message" => "Upload failed"
    ]);
    exit;
}

$db_path = "uploads/product/" . $filename;

$sql = "
INSERT INTO products (
  hospital_id,
  image_path,
  title,
  description,
  price
) VALUES ($1, $2, $3, $4, $5)
";

$res = pg_query_params($conn, $sql, [
    $hospital_id,
    $db_path,
    $title,
    $description,
    $price
]);

echo json_encode([
    "success" => $res ? true : false
], JSON_UNESCAPED_UNICODE);
