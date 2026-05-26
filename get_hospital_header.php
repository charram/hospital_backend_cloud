<?php
require_once "db_connect.php";

$hospital_id = intval($_GET['hospital_id'] ?? 0);

$q = pg_query_params(
  $conn,
  "SELECT file_path, title, description
   FROM hospital_media
   WHERE hospital_id = $1 AND is_hero = 1
   ORDER BY id DESC
   LIMIT 1",
  [$hospital_id]
);

if ($row = pg_fetch_assoc($q)) {
  echo json_encode([
    "success" => true,
    "hero" => [
      "file_path" => $row["file_path"],
      "title" => $row["title"],
      "description" => $row["description"],
    ]
  ]);
} else {
  echo json_encode([
    "success" => false,
    "hero" => null
  ]);
}
