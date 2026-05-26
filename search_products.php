<?php
require_once "db_connect.php";

$q = trim($_GET["q"] ?? "");
$result = ["data" => []];

if ($q !== "") {
  $sql = "
    SELECT id, title, description, image_path, price
    FROM products
    WHERE 
      LOWER(title) LIKE LOWER($1)
      OR LOWER(description) LIKE LOWER($1)
    ORDER BY id DESC
    LIMIT 20
  ";

  $res = pg_query_params($conn, $sql, ["%$q%"]);
  while ($row = pg_fetch_assoc($res)) {
    $result["data"][] = $row;
  }
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
