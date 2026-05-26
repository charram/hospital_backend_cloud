<?php
header("Content-Type: application/json; charset=utf-8");
require_once "../db_connect.php";

if (!$conn) {
  echo json_encode([
    "success" => false,
    "message" => "DB connection failed"
  ]);
  exit;
}

$sql = "
SELECT id, name, lat, lng, has_ambulance, available
FROM hospitals
WHERE lat IS NOT NULL AND lng IS NOT NULL
";

$res = pg_query($conn, $sql);

if (!$res) {
  echo json_encode([
    "success" => false,
    "message" => "Query failed"
  ]);
  exit;
}

$data = [];

while ($row = pg_fetch_assoc($res)) {
  $data[] = [
    "id" => (int)$row["id"],
    "name" => $row["name"],
    "lat" => (float)$row["lat"],
    "lng" => (float)$row["lng"],
    "available" => $row["available"] == "t",
    "has_ambulance" => $row["has_ambulance"] == "t"
  ];
}

echo json_encode([
  "success" => true,
  "data" => $data
]);

pg_close($conn);