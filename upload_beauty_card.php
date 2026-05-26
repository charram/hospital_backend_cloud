<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "db_connect.php";

$hospital_id = intval($_POST["hospital_id"] ?? 0);
$title = $_POST["title"] ?? "";
$body  = $_POST["body"] ?? "";

if ($hospital_id === 0) {
  echo json_encode(["success"=>false,"message"=>"Missing hospital_id"]);
  exit;
}


$folder = "uploads/beauty/";
if (!file_exists($folder)) mkdir($folder, 0777, true);

$ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
$filename = time() . "_" . rand(1000, 9999) . "." . $ext;
$target = $folder . $filename;

if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target)) {
    echo json_encode(["success" => false, "message" => "Upload failed"]);
    exit;
}

$sql = "
INSERT INTO beauty_center
(hospital_id, image_path, title, body)
VALUES ($1, $2, $3, $4)
";

$result = pg_query_params($conn, $sql, [
  $hospital_id,
  $target,
  $title,
  $body
]);


echo json_encode([
    "success" => $result ? true : false,
    "path" => $target
]);
?>
