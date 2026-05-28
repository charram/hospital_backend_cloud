<?php
header("Content-Type: application/json; charset=utf-8");
require_once "db_connect_railway.php";

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode([
        "success" => false,
        "message" => "invalid json"
    ]);
    exit;
}

$name = trim($data["name"] ?? "");
$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if ($name === "" || $email === "" || $password === "") {
    echo json_encode([
        "success" => false,
        "message" => "missing fields"
    ]);
    exit;
}

try {

    $stmt = $pdo->prepare(
        "SELECT id FROM users WHERE email = :email LIMIT 1"
    );

    $stmt->execute([
        ":email" => $email
    ]);

    if ($stmt->fetch()) {
        echo json_encode([
            "success" => false,
            "message" => "email exists"
        ]);
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(32));

    $stmt = $pdo->prepare("
        INSERT INTO users
        (name, email, password, role, api_token, created_at)
        VALUES
        (:name, :email, :password, 'user', :token, NOW())
        RETURNING id, api_token
    ");

    $stmt->execute([
        ":name" => $name,
        ":email" => $email,
        ":password" => $hashed,
        ":token" => $token
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "message" => "user registered",
        "user_id" => (int)$row["id"],
        "token" => $row["api_token"]
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}