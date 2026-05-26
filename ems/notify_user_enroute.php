<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

$session_id = $_POST["session_id"] ?? null;

if (!$session_id) {
  echo json_encode(["success"=>false]);
  exit;
}

$sql = "
UPDATE emergency_sessions
SET status = 'enroute',
    updated_at = NOW()
WHERE id = $1
";

pg_query_params($conn, $sql, [$session_id]);

echo json_encode(["success"=>true]);