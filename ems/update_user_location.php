<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

$session_id = $_POST["session_id"] ?? null;
$lat = $_POST["lat"] ?? null;
$lng = $_POST["lng"] ?? null;

if (!$session_id || !$lat || !$lng) {
  echo json_encode([
    "success"=>false,
    "message"=>"missing params"
  ]);
  exit;
}

if (!is_numeric($lat) || !is_numeric($lng)) {
  echo json_encode([
    "success"=>false,
    "message"=>"invalid lat/lng"
  ]);
  exit;
}

$lat = floatval($lat);
$lng = floatval($lng);
$session_id = intval($session_id);

$sql = "
UPDATE emergency_sessions
SET user_live_lat = $1,
    user_live_lng = $2,
    gps_updated_at = NOW(),
    updated_at = NOW()
WHERE id = $3
RETURNING id
";

$res = pg_query_params($conn, $sql, [$lat,$lng,$session_id]);

if ($res && pg_num_rows($res)>0){
  echo json_encode([
    "success"=>true,
    "message"=>"location updated"
  ]);
}else{
  echo json_encode([
    "success"=>false,
    "message"=>"session not found"
  ]);
}