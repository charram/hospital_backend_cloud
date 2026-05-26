<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../db_connect.php';

$id = $_GET['hospital_id'] ?? null;

if (!$id) {
  echo json_encode(["success" => false]);
  exit;
}

$q = pg_query_params($conn,
  "SELECT id, name FROM hospitals WHERE id = $1",
  [$id]
);

$row = pg_fetch_assoc($q);

echo json_encode([
  "success" => true,
  "data" => $row
], JSON_UNESCAPED_UNICODE);