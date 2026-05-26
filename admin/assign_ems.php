<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

ini_set('display_errors', 0);
error_reporting(E_ALL);

$session_id = $_POST["session_id"] ?? null;
$ems_id     = $_POST["ems_id"] ?? null;

if (!$session_id || !$ems_id) {
  echo json_encode([
    "success" => false,
    "message" => "missing params"
  ]);
  exit;
}

// ================== 🔥 FCM FUNCTION ==================
function sendFCM($token, $title, $body, $session_id) {

  $serviceAccount = json_decode(file_get_contents(__DIR__ . "/../firebase.json"), true);
  $now = time();

  $header = ["alg"=>"RS256","typ"=>"JWT"];
  $payload = [
    "iss"=>$serviceAccount["client_email"],
    "scope"=>"https://www.googleapis.com/auth/firebase.messaging",
    "aud"=>"https://oauth2.googleapis.com/token",
    "iat"=>$now,
    "exp"=>$now+3600
  ];

  $base64Header = rtrim(strtr(base64_encode(json_encode($header)),'+/','-_'),'=');
  $base64Payload = rtrim(strtr(base64_encode(json_encode($payload)),'+/','-_'),'=');

  openssl_sign(
    $base64Header.".".$base64Payload,
    $signature,
    $serviceAccount["private_key"],
    "SHA256"
  );

  $jwt = $base64Header.".".$base64Payload.".".rtrim(strtr(base64_encode($signature),'+/','-_'),'=');

  // 🔥 ขอ access token
  $ch = curl_init("https://oauth2.googleapis.com/token");
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    "grant_type"=>"urn:ietf:params:oauth:grant-type:jwt-bearer",
    "assertion"=>$jwt
  ]));

  $res = curl_exec($ch);
  curl_close($ch);

  $data = json_decode($res,true);
  $accessToken = $data["access_token"] ?? null;

  if (!$accessToken) return null;

  $projectId = $serviceAccount["project_id"];

  $message = [
    "message"=>[
      "token"=>$token,
      "notification"=>[
        "title"=>$title,
        "body"=>$body
      ],
      "data"=>[
        "type"=>"new_job",
        "session_id"=>(string)$session_id
      ],
      "android"=>[
        "priority"=>"high"
      ]
    ]
  ];

  $ch = curl_init("https://fcm.googleapis.com/v1/projects/$projectId/messages:send");
  curl_setopt($ch, CURLOPT_POST,true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch, CURLOPT_HTTPHEADER,[
    "Authorization: Bearer $accessToken",
    "Content-Type: application/json"
  ]);
  curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($message));

  $res = curl_exec($ch);
  curl_close($ch);

  return $res;
}

// ================== 🔥 TRANSACTION ==================
pg_query($conn, "BEGIN");

// 🔥 กัน assign ซ้ำ + เคสต้องเป็น pending เท่านั้น
$sql = "
UPDATE emergency_sessions
SET
    ems_id = $1,
    status = 'assigned',
    updated_at = NOW()
WHERE id = $2
AND ems_id IS NULL
AND status = 'pending'
";

$result = pg_query_params($conn, $sql, [$ems_id, $session_id]);

if ($result && pg_affected_rows($result) > 0) {

  pg_query($conn, "COMMIT");

  // 🔥 ยิง FCM แค่ตอน assign สำเร็จ
  $q = pg_query_params($conn,
    "SELECT fcm_token FROM ems_users WHERE id = $1",
    [$ems_id]
  );

  $row = pg_fetch_assoc($q);
  $ems_token = $row["fcm_token"] ?? null;

  $fcmResult = null;

  if ($ems_token) {
    $fcmResult = sendFCM(
      $ems_token,
      "🚨 มีเคสใหม่",
      "มีผู้ป่วยรอความช่วยเหลือ",
      $session_id
    );
  }

  echo json_encode([
    "success" => true,
    "session_id" => strval($session_id),
    "ems_id" => strval($ems_id),
    "fcm_result" => $fcmResult
  ]);

} else {

  pg_query($conn, "ROLLBACK");

  echo json_encode([
    "success" => false,
    "message" => "เคสนี้ถูก assign ไปแล้ว หรือไม่ใช่ pending"
  ]);
}

pg_close($conn);