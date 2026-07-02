<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/db_connect.php";

$title = trim($_GET["title"] ?? "");

if ($title == "") {
    echo json_encode([
        "success" => false,
        "message" => "missing title"
    ]);
    exit;
}

$sql = "

SELECT DISTINCT
    hc.id,
    hc.hospital_id,
    hc.image_path,
    hc.title,
    hc.description,
    hc.accreditation,
    hc.open_24h,
    hc.province,
    h.province_code,
    hc.lat,
    hc.lng

FROM hospital_card hc

INNER JOIN hospitals h
ON hc.hospital_id = h.id

INNER JOIN (

    SELECT hospital_id
    FROM brain_center_uploads
    WHERE upload_type='disease'
    AND (
        title ILIKE $1
        OR description ILIKE $1
        OR meta::text ILIKE $1
    )

    UNION

    SELECT hospital_id
    FROM cancer_center
    WHERE upload_type='disease'
    AND (
        title ILIKE $1
        OR description ILIKE $1
        OR meta::text ILIKE $1
    )

) d

ON d.hospital_id = hc.hospital_id

WHERE h.status='approved'

ORDER BY hc.id DESC

";

$res = pg_query_params(
    $conn,
    $sql,
    [
        "%".$title."%"
    ]
);

if(!$res){

    echo json_encode([
        "success"=>false,
        "message"=>pg_last_error($conn)
    ]);

    exit;
}

$data=[];

while($row=pg_fetch_assoc($res)){

    $data[]=$row;

}

echo json_encode([
    "success"=>true,
    "count"=>count($data),
    "cards"=>$data
],JSON_UNESCAPED_UNICODE);