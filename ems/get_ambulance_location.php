<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

$session_id = $_GET['session_id'] ?? null;

if (!$session_id) {
  echo json_encode([
    "success" => false,
    "message" => "session_id is required"
  ]);
  exit;
}

$sql = "
  SELECT 
    ambulance_live_lat,
    ambulance_live_lng,
    status,
    updated_at
  FROM emergency_sessions
  WHERE id = $1
  LIMIT 1
";

$result = pg_query_params($conn, $sql, [$session_id]);

if ($row = pg_fetch_assoc($result)) {
  echo json_encode([
    "success" => true,
    "lat" => $row["ambulance_live_lat"],
    "lng" => $row["ambulance_live_lng"],
    "status" => $row["status"],
    "updated_at" => $row["updated_at"]
  ]);
} else {
  echo json_encode([
    "success" => false,
    "message" => "Session not found"
  ]);
}

pg_close($conn);
