<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

$ems_id = $_GET["ems_id"] ?? null;

if (!$ems_id || !is_numeric($ems_id)) {
  echo json_encode([
    "success"=>false,
    "message"=>"ems_id required"
  ]);
  exit;
}

/* ตรวจ EMS active */
$chk = pg_query_params(
  $conn,
  "SELECT is_active FROM ems_accounts WHERE id=$1",
  [(int)$ems_id]
);

$ems = $chk ? pg_fetch_assoc($chk) : null;

if (!$ems || $ems["is_active"] !== "t") {
  echo json_encode([
    "success"=>false,
    "message"=>"EMS not active"
  ]);
  exit;
}

/* หาเคสที่ assign ให้ EMS */
$sql = "
SELECT
  id,
  status,
  user_init_lat,
  user_init_lng,
  created_at
FROM emergency_sessions
WHERE ems_id = $1
  AND status = 'assigned'
ORDER BY assigned_at ASC, created_at ASC
LIMIT 1
";

$res = pg_query_params($conn, $sql, [(int)$ems_id]);

$row = $res ? pg_fetch_assoc($res) : null;

if ($row) {

  echo json_encode([
    "success"=>true,
    "has_job"=>true,
    "session"=>[
      "session_id" => (int)$row["id"],
      "status"     => $row["status"],
      "user_lat"   => $row["user_init_lat"] !== null ? (float)$row["user_init_lat"] : null,
      "user_lng"   => $row["user_init_lng"] !== null ? (float)$row["user_init_lng"] : null,
      "created_at" => $row["created_at"]
    ]
  ]);

} else {

  echo json_encode([
    "success"=>true,
    "has_job"=>false
  ]);

}