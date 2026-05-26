<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/db_connect.php";

// ----------------------
// 🔥 รับค่า
// ----------------------
$hospital_id = intval($_POST["hospital_id"] ?? 0);
$title       = trim($_POST["title"] ?? "");
$description = trim($_POST["description"] ?? "");
$province    = trim($_POST["province"] ?? "");
$lat         = $_POST["lat"] ?? null;
$lng         = $_POST["lng"] ?? null;

// ❌ ไม่ต้องบังคับ title แล้ว
if ($hospital_id <= 0) {
    echo json_encode(["success" => false, "message" => "Missing hospital_id"]);
    exit;
}

// ----------------------
// 🔥 1. UPDATE hospitals
// ----------------------
if ($lat !== null && $lng !== null) {
    pg_query_params($conn,
        "UPDATE hospitals SET lat = $1, lng = $2 WHERE id = $3",
        [$lat, $lng, $hospital_id]
    );
}

// ----------------------
// 🔥 2. UPLOAD รูป
// ----------------------
$imagePath = null;

if (isset($_FILES["image"])) {

    if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
        echo json_encode([
            "success" => false,
            "message" => "UPLOAD ERROR",
            "code" => $_FILES["image"]["error"]
        ]);
        exit;
    }

    $uploadDir = __DIR__ . "/uploads/hospital_card/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    $filename = time() . "_" . rand(1000, 9999) . "." . $ext;
    $targetPath = $uploadDir . $filename;

    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
        echo json_encode([
            "success" => false,
            "message" => "MOVE FILE FAILED",
            "tmp" => $_FILES["image"]["tmp_name"],
            "target" => $targetPath
        ]);
        exit;
    }

    $imagePath = "uploads/hospital_card/" . $filename;
}

// ----------------------
// 🔥 3. INSERT / UPDATE hospital_card
// ----------------------
$check = pg_query_params($conn,
    "SELECT id FROM hospital_card WHERE hospital_id = $1 LIMIT 1",
    [$hospital_id]
);

if (pg_num_rows($check) > 0) {

    // 👉 UPDATE
    $sql = "
        UPDATE hospital_card SET
            title = $1,
            description = $2,
            province = $3,
            lat = $4,
            lng = $5,
            image_path = COALESCE($6, image_path)
        WHERE hospital_id = $7
    ";

    $res = pg_query_params($conn, $sql, [
        $title,
        $description,
        $province,
        $lat,
        $lng,
        $imagePath,
        $hospital_id
    ]);

} else {

    // 👉 INSERT
    $sql = "
        INSERT INTO hospital_card
        (hospital_id, image_path, title, description, province, lat, lng)
        VALUES ($1, $2, $3, $4, $5, $6, $7)
    ";

    $res = pg_query_params($conn, $sql, [
        $hospital_id,
        $imagePath,
        $title,
        $description,
        $province,
        $lat,
        $lng
    ]);
}

// ----------------------
// 🔥 RESPONSE
// ----------------------
if ($res) {
    echo json_encode([
        "success" => true,
        "message" => "Saved hospital + card success",
        "image" => $imagePath
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => pg_last_error($conn)
    ]);
}