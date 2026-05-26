<?php
header("Content-Type: application/json; charset=utf-8");

// 🔥 เปิด error เพื่อ debug (ตอน deploy ค่อยปิด)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once("../db_connect.php");

// =========================
// 🔥 รับค่า + บังคับ type
// =========================
$session_id = intval($_POST["session_id"] ?? 0);

if ($session_id <= 0) {
  echo json_encode([
    "success" => false,
    "message" => "invalid session_id"
  ]);
  exit;
}

// =========================
// 🔥 SQL (ตรงกับ DB จริง)
// =========================
$sql = "
SELECT
  es.user_live_lat,
  es.user_live_lng,
  es.ambulance_live_lat,
  es.ambulance_live_lng,
  h.lat AS hospital_lat,
  h.lng AS hospital_lng,
  h.name AS hospital_name   -- 🔥 เพิ่ม
FROM emergency_sessions es

LEFT JOIN hospitals h ON h.id = es.hospital_id  -- 🔥 ใช้ตัวนี้

WHERE es.id = $1
";

// =========================
// 🔥 QUERY
// =========================
$res = pg_query_params($conn, $sql, [$session_id]);

// =========================
// 🔥 HANDLE ERROR (สำคัญมาก)
// =========================
if (!$res) {
  echo json_encode([
    "success" => false,
    "message" => pg_last_error($conn)
  ]);
  exit;
}

// =========================
// 🔥 FETCH DATA
// =========================
$row = pg_fetch_assoc($res);

if (!$row) {
  echo json_encode([
    "success" => false,
    "message" => "no data found"
  ]);
  exit;
}

// =========================
// 🔥 SUCCESS
// =========================
echo json_encode([
  "success" => true,
  "data" => $row
]);