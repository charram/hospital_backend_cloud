<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

if (!$conn) {
  echo json_encode([
    "success" => false,
    "message" => "Database connection failed"
  ]);
  exit;
}

// รับตำแหน่งจาก user
$lat = $_GET["lat"] ?? null;
$lng = $_GET["lng"] ?? null;

if ($lat === null || $lng === null) {
  echo json_encode([
    "success" => false,
    "message" => "lat lng required"
  ]);
  exit;
}

$lat = (float)$lat;
$lng = (float)$lng;

// คำนวณระยะทางแล้วเรียงจากใกล้ที่สุด
$sql = "
SELECT
  id,
  name,
  lat,
  lng,
  (
    6371 * acos(
      cos(radians($1)) *
      cos(radians(lat)) *
      cos(radians(lng) - radians($2)) +
      sin(radians($1)) *
      sin(radians(lat))
    )
  ) AS distance
FROM hospitals
WHERE lat IS NOT NULL AND lng IS NOT NULL
ORDER BY distance ASC
LIMIT 1
";

$res = pg_query_params($conn, $sql, [$lat, $lng]);

if (!$res) {
  echo json_encode([
    "success" => false,
    "message" => "Query failed"
  ]);
  exit;
}

$row = pg_fetch_assoc($res);

if ($row) {
  echo json_encode([
    "success" => true,
    "data" => [
      "id" => (int)$row["id"],
      "name" => $row["name"],
      "lat" => (float)$row["lat"],
      "lng" => (float)$row["lng"],
      "distance" => round((float)$row["distance"], 2)
    ]
  ]);
} else {
  echo json_encode([
    "success" => false,
    "message" => "No hospital found"
  ]);
}

pg_close($conn);
?>