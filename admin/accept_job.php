<?php
require_once("../db_connect.php");
header("Content-Type: application/json");

ini_set('display_errors', 0);
error_reporting(E_ALL);

$session_id = $_POST["session_id"] ?? null;
$ems_id     = $_POST["ems_id"] ?? null;
$lat        = $_POST["lat"] ?? null;
$lng        = $_POST["lng"] ?? null;

if (!$session_id || !$ems_id || !$lat || !$lng) {
  echo json_encode([
    "success" => false,
    "message" => "missing params"
  ]);
  pg_close($conn);
  exit;
}

function sendFCM($token, $title, $body, $session_id) {
  $serviceAccount = json_decode(file_get_contents(__DIR__ . "/../firebase.json"), true);
  $now = time();

  $header = ["alg" => "RS256", "typ" => "JWT"];
  $payload = [
    "iss" => $serviceAccount["client_email"],
    "scope" => "https://www.googleapis.com/auth/firebase.messaging",
    "aud" => "https://oauth2.googleapis.com/token",
    "iat" => $now,
    "exp" => $now + 3600
  ];

  $base64Header = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
  $base64Payload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');

  openssl_sign(
    $base64Header . "." . $base64Payload,
    $signature,
    $serviceAccount["private_key"],
    "SHA256"
  );

  $jwt = $base64Header . "." . $base64Payload . "." .
      rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

  $ch = curl_init("https://oauth2.googleapis.com/token");
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
    "assertion" => $jwt
  ]));

  $res = curl_exec($ch);
  curl_close($ch);

  $data = json_decode($res, true);
  $accessToken = $data["access_token"] ?? null;

  if (!$accessToken) {
    return null;
  }

  $projectId = $serviceAccount["project_id"];

  $message = [
    "message" => [
      "token" => $token,
      "notification" => [
        "title" => $title,
        "body" => $body
      ],
      "data" => [
        "type" => "ems_enroute",
        "session_id" => (string)$session_id
      ],
      "android" => [
        "priority" => "high"
      ]
    ]
  ];

  $ch = curl_init("https://fcm.googleapis.com/v1/projects/$projectId/messages:send");
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $accessToken",
    "Content-Type: application/json"
  ]);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

  $res = curl_exec($ch);
  $err = curl_error($ch);
  curl_close($ch);

  return [
    "response" => $res,
    "error" => $err
  ];
}

$sql = "
UPDATE emergency_sessions
SET
    status = 'enroute',
    ems_id = $1,
    ambulance_live_lat = $2,
    ambulance_live_lng = $3,
    accepted_at = NOW(),
    updated_at = NOW()
WHERE id = $4
AND status = 'assigned'
AND ems_id = $1
";

$result = pg_query_params($conn, $sql, [
  $ems_id,
  $lat,
  $lng,
  $session_id
]);

if ($result && pg_affected_rows($result) > 0) {
  $q = pg_query_params($conn,
    "SELECT u.fcm_token
     FROM emergency_sessions e
     JOIN users u ON e.user_id = u.id
     WHERE e.id = $1",
    [$session_id]
  );

  $row = pg_fetch_assoc($q);
  $user_token = $row["fcm_token"] ?? null;

  $fcmResult = null;
  if ($user_token) {
    $fcmResult = sendFCM(
      $user_token,
      "🚑 EMS กำลังมา",
      "ทีมแพทย์กำลังเดินทางมาหาคุณ",
      $session_id
    );
  }

  echo json_encode([
    "success" => true,
    "message" => "EMS accepted and enroute",
    "fcm_result" => $fcmResult
  ]);
} else {
  echo json_encode([
    "success" => false,
    "message" => "update failed",
    "rows" => 0,
    "error" => pg_last_error($conn)
  ]);
}

pg_close($conn);