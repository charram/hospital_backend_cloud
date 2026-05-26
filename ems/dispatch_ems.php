<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

$session_id = $_POST["session_id"] ?? null;

if (!$session_id) {
 echo json_encode([
  "success"=>false,
  "message"=>"session_id required"
 ]);
 exit;
}

/* หา EMS ว่าง */

$sql = "
SELECT id
FROM ems_accounts
WHERE is_active = true
ORDER BY id
LIMIT 1
";

$res = pg_query($conn,$sql);

$ems = pg_fetch_assoc($res);

if (!$ems){
 echo json_encode([
  "success"=>false,
  "message"=>"no EMS available"
 ]);
 exit;
}

$ems_id = $ems["id"];

/* assign EMS */

$update = "
UPDATE emergency_sessions
SET
 ems_id = $1,
 status = 'assigned'
WHERE id = $2
";

pg_query_params($conn,$update,[
 $ems_id,
 $session_id
]);

echo json_encode([
 "success"=>true,
 "ems_id"=>$ems_id
]);