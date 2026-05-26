<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once "db_connect.php";

$hospital_id = intval($_GET["hospital_id"] ?? 0);
$category    = $_GET["category"] ?? "";
$is_hero     = intval($_GET["is_hero"] ?? 0);

$sql = "
  SELECT id, title, description, image_path
  FROM hospital_diseases
  WHERE hospital_id = $1 AND category = $2
  ORDER BY id DESC
";

if ($is_hero === 1) {
  $sql .= " LIMIT 1";
}

$q = pg_query_params($conn, $sql, [$hospital_id, $category]);

$rows = [];
while ($r = pg_fetch_assoc($q)) {
  $rows[] = $r;
}

echo json_encode([
  "success" => true,
  "data" => $rows
]);
