<?php
header("Content-Type: application/json; charset=utf-8");
require __DIR__ . "/../db_connect.php";

$res = pg_query($conn, "
  SELECT id, name, province, license_number
  FROM hospitals
  WHERE status = 'pending'
  ORDER BY id DESC
");

$list = [];
while ($row = pg_fetch_assoc($res)) {
  $list[] = $row;
}

echo json_encode([
  "success" => true,
  "hospitals" => $list
], JSON_UNESCAPED_UNICODE);
