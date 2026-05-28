<?php

require_once 'db_connect.php';

$query = "
SELECT column_name
FROM information_schema.columns
WHERE table_name = 'hospitals'
ORDER BY ordinal_position
";

$result = pg_query($conn, $query);

$data = [];

while ($row = pg_fetch_assoc($result)) {
    $data[] = $row["column_name"];
}

echo json_encode($data, JSON_PRETTY_PRINT);