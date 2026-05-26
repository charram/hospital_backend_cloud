<?php
header("Content-Type: application/json; charset=utf-8");
require_once "../db_connect.php";

$lat = $_GET["lat"] ?? null;
$lng = $_GET["lng"] ?? null;

if ($lat === null || $lng === null) {
    echo json_encode([
        "success"=>false,
        "message"=>"lat lng required"
    ]);
    exit;
}

$lat = (float)$lat;
$lng = (float)$lng;

$query = "
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
ORDER BY distance ASC
LIMIT 10
";

$result = pg_query_params($conn, $query, [$lat, $lng]);

$data = [];

while ($row = pg_fetch_assoc($result)) {

    $distance = (float)$row["distance"];

    $data[] = [
        "id" => (int)$row["id"],
        "name" => $row["name"],
        "lat" => (float)$row["lat"],
        "lng" => (float)$row["lng"],
        "distance" => $distance,
        "eta" => round($distance * 2),   // 30km/h
        "available" => $row["available"] == "t",
        "has_ambulance" => $row["has_ambulance"] == "t"
    ];
}

echo json_encode([
    "success"=>true,
    "data"=>$data
]);

pg_close($conn);