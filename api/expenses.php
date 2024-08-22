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
        $date = date('Y-m-d');
        $stmt = $conn->prepare('SELECT * FROM expenses WHERE date = :date ORDER BY id DESC');
        $stmt->bindParam(":date", $date);
        $stmt->execute();
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data = json_encode(['expenses' => $expenses]);
        echo $data;
    }else if ($_SERVER["REQUEST_METHOD"] == "DELETE"){
        $data = json_decode(file_get_contents('php://input'), true);
        $id = trim(htmlspecialchars($data['id'] ?? ''));
        $stmt = $conn->prepare('DELETE FROM expenses WHERE id = :id');
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $data = json_encode(['message' => "تم الحذف"]);
        echo $data;
    }else if ($_SERVER["REQUEST_METHOD"] == "POST"){
        $data = json_decode(file_get_contents('php://input'), true);
        $id_user = trim(htmlspecialchars($data['id_user'] ?? ''));
        $material = trim(htmlspecialchars($data['material'] ?? ''));
        $price = trim(htmlspecialchars($data['price'] ?? ''));
        $date = date('Y-m-d');
        $stmt = $conn->prepare('INSERT INTO expenses (id_user, material, date, price) VALUES (:id_user, :material, :date, :price)');
        $stmt->bindParam(":id_user", $id_user);
        $stmt->bindParam(":material", $material);
        $stmt->bindParam(":date", $date);
        $stmt->bindParam(":price", $price);
        $stmt->execute();
        $data = json_encode(['message' => "تم الإضافة"]);
        echo $data;
    }

?>
