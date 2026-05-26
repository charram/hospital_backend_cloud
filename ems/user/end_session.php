<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

$session_id = $_POST["session_id"] ?? null;

if ($session_id === null) {
    echo json_encode(["success"=>false,"message"=>"session_id required"]);
    exit;
}

$sql = "
UPDATE emergency_sessions
SET 
    status = 'completed',
    user_live_lat = NULL,
    user_live_lng = NULL,
    ambulance_live_lat = NULL,
    ambulance_live_lng = NULL,
    updated_at = NOW()
WHERE id = $1
RETURNING id
";

$res = pg_query_params($conn, $sql, [$session_id]);

if ($res && pg_num_rows($res) > 0) {
    echo json_encode(["success"=>true]);
} else {
    echo json_encode(["success"=>false,"message"=>"Update failed"]);
}

pg_close($conn);
