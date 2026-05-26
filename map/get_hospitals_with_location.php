<?php
header("Content-Type: application/json; charset=utf-8");
require_once("../db_connect.php");

/* ===============================
   ตรวจสอบการเชื่อมต่อ DB
================================ */
if (!$conn) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

/* ===============================
   รับค่าพิกัดจาก Flutter
================================ */
$lat = $_GET["lat"] ?? null;
$lng = $_GET["lng"] ?? null;
$radius = $_GET["radius"] ?? 10; // ค่า default 10 km

if ($lat === null || $lng === null) {
    echo json_encode([
        "success" => false,
        "message" => "lat,lng required"
    ]);
    exit;
}

/* ===============================
   Query (ใช้ Subquery ปลอดภัยกว่า HAVING)
================================ */
$sql = "
SELECT *
FROM (
    SELECT
        id,
        name,
        lat,
        lng,
        has_ambulance,
        available,
        (
            6371 * acos(
                cos(radians($1)) *
                cos(radians(lat)) *
                cos(radians(lng) - radians($2)) +
                sin(radians($1)) *
                sin(radians(lat))
            )
        ) AS distance
    FROM hospitals
    WHERE lat IS NOT NULL
      AND lng IS NOT NULL
) AS sub
WHERE distance <= $3
ORDER BY distance ASC
LIMIT 50
";

/* ===============================
   Execute Query
================================ */
$res = pg_query_params($conn, $sql, [$lat, $lng, $radius]);

if (!$res) {
    echo json_encode([
        "success" => false,
        "message" => "Query failed"
    ]);
    exit;
}

/* ===============================
   แปลงผลลัพธ์ให้ type ถูกต้อง
================================ */
$list = [];

while ($row = pg_fetch_assoc($res)) {

    $distance = (float)$row["distance"];

    $list[] = [
        "id" => (int)$row["id"],
        "name" => $row["name"],
        "lat" => (float)$row["lat"],
        "lng" => (float)$row["lng"],
        "distance" => round($distance, 2),
        "eta" => round($distance * 2), // คิดเวลาเดินทางคร่าว ๆ
        "available" => $row["available"] === "t",
        "has_ambulance" => $row["has_ambulance"] === "t"
    ];
}

/* ===============================
   ส่ง JSON กลับ Flutter
================================ */
echo json_encode([
    "success" => true,
    "data" => $list
]);
