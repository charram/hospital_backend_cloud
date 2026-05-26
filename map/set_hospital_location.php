<?php
header("Content-Type: application/json; charset=utf-8");
error_reporting(0);
ini_set('display_errors', 0);

require_once("../db_connect.php");

if (!$conn) {
  echo json_encode([
    "success" => false,
    "message" => "Database connection failed"
  ]);
  exit;
}

$hospital_id = $_POST['hospital_id'] ?? null;
$lat = $_POST['lat'] ?? null;
$lng = $_POST['lng'] ?? null;
$province = $_POST['province'] ?? ""; // 🔥 เพิ่ม

if ($hospital_id === null || $lat === null || $lng === null) {
  echo json_encode([
    "success" => false,
    "message" => "hospital_id, lat, lng is required"
  ]);
  exit;
}

$hospital_id = (int)$hospital_id;
$lat = (float)$lat;
$lng = (float)$lng;

// ตรวจสอบช่วงค่าพิกัด
if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
  echo json_encode([
    "success" => false,
    "message" => "Invalid latitude or longitude"
  ]);
  exit;
}

$sql = "UPDATE hospitals SET lat = $1, lng = $2 WHERE id = $3";
$result = pg_query_params($conn, $sql, [$lat, $lng, $hospital_id]);

if ($result && pg_affected_rows($result) > 0) {
  echo json_encode([
    "success" => true,
    "message" => "Hospital location saved",
    "hospital_id" => $hospital_id,
    "lat" => $lat,
    "lng" => $lng
  ]);
} else {
  echo json_encode([
    "success" => false,
    "message" => "Hospital not found or location not changed"
  ]);
}

pg_close($conn);
