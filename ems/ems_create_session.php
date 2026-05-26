<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

$ems_id       = $_POST["ems_id"] ?? null;
$patient_name = $_POST["patient_name"] ?? null;
$case_type    = $_POST["case_type"] ?? null;

if (!$ems_id) {
    echo json_encode([
        "success" => false,
        "message" => "ems_id required"
    ]);
    exit;
}

/* 1) ตรวจ EMS */
$chk = pg_query_params(
    $conn,
    "SELECT is_active FROM ems_accounts WHERE id=$1",
    [$ems_id]
);

$ems = pg_fetch_assoc($chk);
if (!$ems || $ems["is_active"] !== "t") {
    echo json_encode([
        "success" => false,
        "message" => "EMS invalid"
    ]);
    exit;
}

/* 2) เช็ก active session */
$checkSession = pg_query_params(
    $conn,
    "SELECT id FROM emergency_sessions
     WHERE ems_id=$1 AND status='active'
     LIMIT 1",
    [$ems_id]
);

if (pg_num_rows($checkSession) > 0) {
    echo json_encode([
        "success" => false,
        "message" => "EMS already has active session"
    ]);
    exit;
}

/* 3) เลือก hospital อัตโนมัติ (ตัวอย่าง fixed / nearest) */
$hospital_id = 1; // TODO: เลือกจากระบบจริง

/* 4) สร้าง session */
$sql = "
INSERT INTO emergency_sessions
  (ems_id, hospital_id, patient_name, case_type, status, created_at)
VALUES
  ($1,$2,$3,$4,'active',NOW())
RETURNING id, started_at
";

$res = pg_query_params(
    $conn,
    $sql,
    [$ems_id, $hospital_id, $patient_name, $case_type]
);

if (!$res) {
    echo json_encode([
        "success" => false,
        "error" => pg_last_error($conn)
    ]);
    exit;
}

$row = pg_fetch_assoc($res);

/* 5) Response (ไม่ส่ง hospital_id) */
echo json_encode([
    "success" => true,
    "session" => [
        "session_id" => (int)$row["id"],
        "ems_id"     => (int)$ems_id,
        "started_at"=> $row["started_at"]
    ]
]);
