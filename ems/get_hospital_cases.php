<?php
require_once "../db_connect.php";

$hospital_id = $_GET['hospital_id'] ?? null;

if (!$hospital_id) {
  echo json_encode(["success" => false]);
  exit;
}

$sql = "SELECT * FROM emergency_sessions
        WHERE hospital_id = $1
        AND status != 'completed'
        ORDER BY created_at DESC";

$res = pg_query_params($conn, $sql, [$hospital_id]);

$rows = [];

while ($r = pg_fetch_assoc($res)) {
  $rows[] = $r;
}

echo json_encode([
  "success" => true,
  "data" => $rows
]);

