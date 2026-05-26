<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require_once "db_connect.php";

$sql = "
  SELECT id, image_path, title, description, created_at
  FROM beauty_center
  WHERE title IS NOT NULL
  ORDER BY id DESC
";

$res = pg_query($conn, $sql);

$rows = [];
while ($row = pg_fetch_assoc($res)) {
    $rows[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $rows
], JSON_UNESCAPED_UNICODE);
