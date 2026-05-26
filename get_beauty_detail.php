<?php
require_once "db_connect.php";

$id = intval($_GET["id"] ?? 0);
$result = ["success" => false, "data" => null];

if ($id > 0) {
  $sql = "SELECT * FROM beauty_center WHERE id = $1 LIMIT 1";
  $res = pg_query_params($conn, $sql, [$id]);

  if ($row = pg_fetch_assoc($res)) {
    $result["success"] = true;
    $result["data"] = $row;
  }
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
