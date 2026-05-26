<?php
header("Content-Type: application/json; charset=UTF-8");

// 🔥 กัน JSON พัง
error_reporting(0);
ini_set('display_errors', 0);

try {
    require_once __DIR__ . '/../db_connect.php';

    // 🔥 รับค่า
    $hospital_id =
        $_POST['hospital_id']
        ?? '';

    $invite_token =
        $_POST['invite_token']
        ?? '';

    $ems_name =
        trim(
            $_POST['name']
            ?? ''
        );

    $phone =
        trim(
            $_POST['phone']
            ?? ''
        );

    $vehicle_code =
        trim(
            $_POST['unit_code']
            ?? ''
        );

    // 🔥 validate
    if (
        empty($hospital_id)
        || empty($ems_name)
    ) {
        throw new Exception(
            "Missing required fields"
        );
    }

    // 🔥 EMS ใหม่ = พร้อมใช้งาน
    $query = "
      INSERT INTO ems_units (
        hospital_id,
        ems_name,
        phone,
        vehicle_code,
        status
      )
      VALUES (
        $1,
        $2,
        $3,
        $4,
        'available'
      )
      RETURNING id
    ";

    $result =
        pg_query_params(
            $conn,
            $query,
            [
                $hospital_id,
                $ems_name,
                $phone,
                $vehicle_code
            ]
        );

    if (!$result) {
        throw new Exception(
            pg_last_error($conn)
        );
    }

    $row =
        pg_fetch_assoc(
            $result
        );

    echo json_encode([
        "success" => true,
        "ems_id" =>
            (int)$row["id"]
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" =>
            $e->getMessage()
    ]);
}