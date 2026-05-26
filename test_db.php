<?php

include 'db_connect_railway.php';

echo json_encode([
    "success" => true,
    "message" => "railway connected"
]);