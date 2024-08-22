<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Include DELETE method
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $stmt = $conn->prepare('SELECT * FROM ps');
    $stmt->execute();
    $PSs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $PSsState = [];
    for ($i = 0; $i < count($PSs); $i++) {
        $PSsState[$i] = $PSs[$i]['state'];
    }
    $data = json_encode(['PSsState' => $PSsState]);
    echo $data;
}
