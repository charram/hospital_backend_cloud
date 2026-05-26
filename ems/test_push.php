<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

$session_id = $_POST["session_id"] ?? null;
$lat = $_POST["lat"] ?? null;
$lng = $_POST["lng"] ?? null;

if (!$session_id || $lat === null || $lng === null) {
  echo json_encode(["success"=>false,"message"=>"missing session_id/lat/lng"]);
  exit;
}

$q = "UPDATE emergency_sessions
      SET user_live_lat=$1, user_live_lng=$2, updated_at=NOW()
      WHERE id=$3";

$r = pg_query_params($conn, $q, [$lat, $lng, $session_id]);

if ($r) echo json_encode(["success"=>true]);
else echo json_encode(["success"=>false,"message"=>pg_last_error($conn)]);