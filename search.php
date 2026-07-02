<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once "db_connect.php";

$q = trim($_GET["q"] ?? "");

$result = [
    "hospitals" => [],
    "diseases"  => [],
    "products"  => [],
];

if ($q !== "") {

    $keyword = "%" . $q . "%";

    // ==========================================
    // HOSPITALS
    // ==========================================

    $sql1 = "
    SELECT
        h.id,
        h.name,
        c.image_path
    FROM hospitals h
    LEFT JOIN hospital_card c
        ON c.hospital_id = h.id
    WHERE
        h.status='approved'
        AND h.name ILIKE $1
    ORDER BY c.id DESC
    LIMIT 10
    ";

    $res1 = pg_query_params(
        $conn,
        $sql1,
        [$keyword]
    );

    while ($row = pg_fetch_assoc($res1)) {
        $result["hospitals"][] = $row;
    }

    // ==========================================
    // DISEASES
    // ==========================================

    $sql2 = "

    SELECT
        'brain' AS category,
        hospital_id,
        title,
        description,
        image_path,
        upload_type
    FROM brain_center_uploads
    WHERE
        upload_type='disease'
        AND (
            title ILIKE $1
            OR description ILIKE $1
            OR meta::text ILIKE $1
        )

    UNION ALL

    SELECT
        'cancer' AS category,
        hospital_id,
        title,
        description,
        image_path,
        upload_type
    FROM cancer_center
    WHERE
        upload_type='disease'
        AND (
            title ILIKE $1
            OR description ILIKE $1
            OR meta::text ILIKE $1
        )

    UNION ALL

    SELECT
        'lung' AS category,
        hospital_id,
        title,
        description,
        image_path,
        upload_type
    FROM lung_center
    WHERE
        upload_type='disease'
        AND (
            title ILIKE $1
            OR description ILIKE $1
            OR meta::text ILIKE $1
        )

    UNION ALL

    SELECT
        'heart' AS category,
        hospital_id,
        title,
        description,
        image_path,
        upload_type
    FROM heart_center
    WHERE
        upload_type='disease'
        AND (
            title ILIKE $1
            OR description ILIKE $1
            OR meta::text ILIKE $1
        )

    UNION ALL

    SELECT
        'kidney' AS category,
        hospital_id,
        title,
        description,
        image_path,
        upload_type
    FROM kidney_center
    WHERE
        upload_type='disease'
        AND (
            title ILIKE $1
            OR description ILIKE $1
            OR meta::text ILIKE $1
        )

    LIMIT 20

    ";

    $res2 = pg_query_params(
        $conn,
        $sql2,
        [$keyword]
    );

    while ($row = pg_fetch_assoc($res2)) {
        $result["diseases"][] = $row;
    }

    // ==========================================
    // PRODUCTS
    // ==========================================

    $sql3 = "
    SELECT
        id,
        title,
        description,
        image_path,
        price
    FROM products
    WHERE
        title ILIKE $1
        OR description ILIKE $1
    LIMIT 10
    ";

    $res3 = pg_query_params(
        $conn,
        $sql3,
        [$keyword]
    );

    while ($row = pg_fetch_assoc($res3)) {
        $result["products"][] = $row;
    }
}

echo json_encode(
    $result,
    JSON_UNESCAPED_UNICODE
);