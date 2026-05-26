<?php
require_once "db_connect.php";

$q = trim($_GET["q"] ?? "");

$result = [
  "hospitals" => [],
  "diseases" => [],
   "products" => [],
];

if ($q !== "") {

  // ---------- hospitals ----------
  $sql1 = "
    SELECT 
      h.id,
      h.name,
      c.image_path
    FROM hospitals h
    LEFT JOIN hospital_card c ON c.hospital_id = h.id
    WHERE LOWER(h.name) LIKE LOWER($1)
      AND h.status = 'approved'
    ORDER BY c.id DESC
    LIMIT 10
  ";
  $res1 = pg_query_params($conn, $sql1, ["%$q%"]);

  while ($row = pg_fetch_assoc($res1)) {
    $result["hospitals"][] = $row;
  }

  // ---------- diseases ----------
 // ---------- diseases ----------
$sql2 = "
  SELECT 
    d.id,
    d.title,
    d.hospital_id,
    h.name AS hospital_name,
    d.image_path
  FROM hospital_diseases d
  JOIN hospitals h ON h.id = d.hospital_id
  WHERE LOWER(d.title) LIKE LOWER($1)
  LIMIT 10
";
// ---------- products ----------
$sql3 = "
  SELECT 
    p.id,
    p.title,
    p.description,
    p.image_path,
    p.price
  FROM products p
  WHERE 
    LOWER(p.title) LIKE LOWER($1)
    OR LOWER(p.description) LIKE LOWER($1)
  LIMIT 10
";


$res3 = pg_query_params($conn, $sql3, ["%$q%"]);

while ($row = pg_fetch_assoc($res3)) {
  $result["products"][] = $row;
}



  $res2 = pg_query_params($conn, $sql2, ["%$q%"]);

  while ($row = pg_fetch_assoc($res2)) {
    $result["diseases"][] = $row;
  }
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
