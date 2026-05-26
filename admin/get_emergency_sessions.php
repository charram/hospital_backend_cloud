<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

// 🔥 SQL
$sql = "
SELECT
  es.id,
  es.status,
  es.user_id,
  es.ems_id,
  es.created_at,

  -- 👤 USER
  es.user_live_lat,
  es.user_live_lng,
  es.user_init_lat,
  es.user_init_lng,

  -- 🚑 EMS
  e.name AS ems_name,
  es.ambulance_live_lat AS ems_lat,
  es.ambulance_live_lng AS ems_lng

FROM emergency_sessions es

LEFT JOIN ems e 
  ON es.ems_id = e.id

WHERE es.status IN ('pending','assigned','enroute')

ORDER BY es.created_at DESC
";

$res = pg_query($conn, $sql);

if (!$res) {
  echo json_encode([
    "success" => false,
    "error" => pg_last_error($conn)
  ]);
  exit;
}

$list = [];

while ($row = pg_fetch_assoc($res)) {

  foreach ($row as $k => $v) {
    if ($v !== null) {
      $row[$k] = strval($v);
    }
  }

  $list[] = $row;
}

echo json_encode([
  "success" => true,
  "count" => count($list), // 🔥 เพิ่ม debug
  "data" => $list
]);

pg_close($conn);