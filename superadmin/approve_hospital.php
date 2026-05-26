<?php
header("Content-Type: application/json; charset=utf-8");
require_once "../db_connect.php";

$data = json_decode(file_get_contents("php://input"), true);
$hospital_id = intval($data["hospital_id"] ?? 0);

if ($hospital_id <= 0) {
  echo json_encode([
    "success" => false,
    "message" => "invalid hospital_id",
    "debug" => $data
  ]);
  exit;
}

$res = pg_query_params(
  $conn,
  "UPDATE hospitals SET status='approved' WHERE id=$1",
  [$hospital_id]
);

// ❗ สำคัญมาก: เช็คว่ามีแถวถูก update จริงไหม
if (!$res || pg_affected_rows($res) === 0) {
  echo json_encode([
    "success" => false,
    "message" => "no hospital updated",
    "hospital_id" => $hospital_id
  ]);
  exit;
}

echo json_encode([
  "success" => true,
  "message" => "hospital approved",
  "hospital_id" => $hospital_id
]);
