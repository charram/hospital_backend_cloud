<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

$session_id = $_POST["session_id"] ?? null;
$ems_id = $_POST["ems_id"] ?? null;

$lat = $_POST["lat"] ?? null;
$lng = $_POST["lng"] ?? null;

if ($session_id === null || $ems_id === null || $lat === null || $lng === null) {
  echo json_encode(["success"=>false,"message"=>"session_id, ems_id, lat, lng required"]);
  exit;
}

$sql = "UPDATE emergency_sessions
        SET ambulance_live_lat=$1, ambulance_live_lng=$2, status='enroute', updated_at=NOW()
        WHERE id=$3 AND ems_id=$4";
$res = pg_query_params($conn, $sql, [$lat, $lng, $session_id, $ems_id]);

if ($res && pg_affected_rows($res) > 0) echo json_encode(["success"=>true]);
else echo json_encode(["success"=>false,"message"=>"Not allowed or DB error","error"=>pg_last_error($conn)]);
