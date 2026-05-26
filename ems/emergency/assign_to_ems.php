<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../../db_connect.php");

$session_id  = $_POST["session_id"] ?? null;
$hospital_id = $_POST["hospital_id"] ?? null;
$ems_id      = $_POST["ems_id"] ?? null;

if (!$session_id || !$hospital_id || !$ems_id) {
  echo json_encode(["success"=>false,"message"=>"missing params"]);
  exit;
}

$sql = "
UPDATE emergency_sessions
SET ems_id = $1,
    status = 'assigned',
    assigned_at = NOW(),
    updated_at = NOW()
WHERE id = $2
  AND hospital_id = $3
  AND status = 'pending'
RETURNING id, ems_id, status, assigned_at
";

$res = pg_query_params($conn, $sql, [(int)$ems_id, (int)$session_id, (int)$hospital_id]);

if ($res && pg_num_rows($res) > 0) {
  echo json_encode(["success"=>true,"data"=>pg_fetch_assoc($res)]);
} else {
  echo json_encode(["success"=>false,"message"=>"assign failed"]);
}