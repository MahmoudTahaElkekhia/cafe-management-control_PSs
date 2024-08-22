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
        $data = json_encode(['PSs' => $PSs]);
        echo $data;
    }else if ($_SERVER["REQUEST_METHOD"] == "PUT"){
        $data = json_decode(file_get_contents('php://input'), true);
        
        $name = trim(htmlspecialchars($data['name'] ?? ''));
        $normal = trim(htmlspecialchars($data['normal'] ?? ''));
        $normal_price = trim(htmlspecialchars($data['normal_price'] ?? ''));
        $multi = trim(htmlspecialchars($data['multi'] ?? ''));
        $multi_price = trim(htmlspecialchars($data['multi_price'] ?? ''));
        $plus = trim(htmlspecialchars($data['plus'] ?? ''));
        $plus_price = trim(htmlspecialchars($data['plus_price'] ?? ''));
        $netflix = trim(htmlspecialchars($data['netflix'] ?? ''));
        $netflix_price = trim(htmlspecialchars($data['netflix_price'] ?? ''));
        $type = trim(htmlspecialchars($data['type'] ?? ''));

        $stmt = $conn->prepare('UPDATE ps SET normal = :normal, normal_price = :normal_price, multi = :multi, multi_price = :multi_price, plus = :plus, plus_price = :plus_price, netflix = :netflix, netflix_price = :netflix_price, type = :type  WHERE name = :name');
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":normal", $normal);
        $stmt->bindParam(":normal_price", $normal_price);
        $stmt->bindParam(":multi", $multi);
        $stmt->bindParam(":multi_price", $multi_price);
        $stmt->bindParam(":plus", $plus);
        $stmt->bindParam(":plus_price", $plus_price);
        $stmt->bindParam(":netflix", $netflix);
        $stmt->bindParam(":netflix_price", $netflix_price);
        $stmt->bindParam(":type", $type);
        $stmt->execute();
        $data = json_encode(['message' => "تم التعديل"]);
        echo $data;
    }

?>
