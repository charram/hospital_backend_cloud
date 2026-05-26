<?php
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . "/../db_connect.php";

$id = $_POST['id'] ?? null;
if (!$id) { echo json_encode(['success'=>false,'message'=>'Missing ID']); exit; }

$update = pg_query_params($conn, "UPDATE users SET role='admin' WHERE id=$1 AND role='pending'", [$id]);
if ($update) {
  echo json_encode(['success'=>true, 'message'=>'Admin approved']);
} else {
  echo json_encode(['success'=>false, 'message'=>'Approval failed']);
}
?>
