<?php
require_once("../../db_connect.php");
header("Content-Type: application/json");

$user_id = $_GET["user_id"] ?? null;

if (!$user_id) {
  echo json_encode([
    "success" => false,
    "error" => "missing user_id"
  ]);
  exit;
}

$sql = "
SELECT id
FROM emergencies
WHERE user_id = $1
AND status IN ('pending','assigned','enroute')
ORDER BY id DESC
LIMIT 1
";

$res = pg_query_params($conn, $sql, [$user_id]);

// 🔥 กัน query fail
if (!$res) {
  echo json_encode([
    "success" => false,
    "error" => pg_last_error($conn)
  ]);
  exit;
}

$row = pg_fetch_assoc($res);

// 🔥 เช็ค id ชัดๆ
if ($row && isset($row["id"])) {
  echo json_encode([
    "success" => true,
    "session_id" => strval($row["id"])
  ]);
} else {
  echo json_encode([
    "success" => false
  ]);
}