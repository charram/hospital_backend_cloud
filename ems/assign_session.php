<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

$session_id = $_POST["session_id"] ?? null;
$ems_id = $_POST["ems_id"] ?? null;
$ambulance_code = $_POST["ambulance_code"] ?? "";

if ($session_id === null || $ems_id === null) {
  echo json_encode(["success"=>false,"message"=>"session_id, ems_id required"]);
  exit;
}

$sql = "UPDATE emergency_sessions
        SET ems_id=$1, ambulance_code=$2, status='assigned', updated_at=NOW()
        WHERE id=$3";
$res = pg_query_params($conn, $sql, [$ems_id, $ambulance_code, $session_id]);

if ($res) echo json_encode(["success"=>true]);
else echo json_encode(["success"=>false,"message"=>"DB error","error"=>pg_last_error($conn)]);
