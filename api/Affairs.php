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
        $stmt = $conn->prepare('SELECT id, username, role, affairs, ps, purchases, sales, harmony, products, rooms, expenses, store FROM users WHERE role= 0');
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data = json_encode(['users' => $users]);
        echo $data;
    }else if ($_SERVER["REQUEST_METHOD"] == "POST"){
        $data = json_decode(file_get_contents('php://input'), true);
        $username = trim(htmlspecialchars($data['username'] ?? ''));
        $password = trim(htmlspecialchars($data['password'] ?? ''));
        $affairs = trim(htmlspecialchars($data['affairs'] ?? ''));
        $ps = trim(htmlspecialchars($data['ps'] ?? ''));
        $purchases = trim(htmlspecialchars($data['purchases'] ?? ''));
        $sales = trim(htmlspecialchars($data['sales'] ?? ''));
        $harmony = trim(htmlspecialchars($data['harmony'] ?? ''));
        $products = trim(htmlspecialchars($data['products'] ?? ''));
        $rooms = trim(htmlspecialchars($data['rooms'] ?? ''));
        $expenses = trim(htmlspecialchars($data['expenses'] ?? ''));
        $store = trim(htmlspecialchars($data['store'] ?? ''));
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (username, password, role, affairs, ps, purchases, sales, harmony, products, rooms, expenses, store) VALUES (:username, :password, 0, :affairs, :ps, :purchases, :sales, :harmony, :products, :rooms, :expenses, :store)');
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":affairs", $affairs);
        $stmt->bindParam(":ps", $ps);
        $stmt->bindParam(":purchases", $purchases);
        $stmt->bindParam(":sales", $sales);
        $stmt->bindParam(":harmony", $harmony);
        $stmt->bindParam(":products", $products);
        $stmt->bindParam(":rooms", $rooms);
        $stmt->bindParam(":expenses", $expenses);
        $stmt->bindParam(":store", $store);
        $stmt->execute();
        $data = json_encode(['message' => "تم الإضافة"]);
        echo $data;
    }else if ($_SERVER["REQUEST_METHOD"] == "PUT"){
        $data = json_decode(file_get_contents('php://input'), true);
        $id = trim(htmlspecialchars($data['id'] ?? ''));
        $username = trim(htmlspecialchars($data['username'] ?? ''));
        $password = trim(htmlspecialchars($data['password'] ?? ''));
        $affairs = trim(htmlspecialchars($data['affairs'] ?? ''));
        $ps = trim(htmlspecialchars($data['ps'] ?? ''));
        $purchases = trim(htmlspecialchars($data['purchases'] ?? ''));
        $sales = trim(htmlspecialchars($data['sales'] ?? ''));
        $harmony = trim(htmlspecialchars($data['harmony'] ?? ''));
        $products = trim(htmlspecialchars($data['products'] ?? ''));
        $rooms = trim(htmlspecialchars($data['rooms'] ?? ''));
        $expenses = trim(htmlspecialchars($data['expenses'] ?? ''));
        $store = trim(htmlspecialchars($data['store'] ?? ''));
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        if($stmt->rowCount() > 0){
            $stmt = $conn->prepare('UPDATE users SET password = :password, affairs = :affairs, ps = :ps, purchases = :purchases, sales = :sales, harmony = :harmony, products = :products, rooms = :rooms, expenses = :expenses, store = :store  WHERE id = :id');
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":password", $hashed_password);
            $stmt->bindParam(":affairs", $affairs);
            $stmt->bindParam(":ps", $ps);
            $stmt->bindParam(":purchases", $purchases);
            $stmt->bindParam(":sales", $sales);
            $stmt->bindParam(":harmony", $harmony);
            $stmt->bindParam(":products", $products);
            $stmt->bindParam(":rooms", $rooms);
            $stmt->bindParam(":expenses", $expenses);
            $stmt->bindParam(":store", $store);

            $stmt->execute();
            $data = json_encode(['message' => "تم التعديل"]);
            echo $data;
        }else{
            $stmt = $conn->prepare('UPDATE users SET username = :username, password = :password, affairs = :affairs, ps = :ps, purchases = :purchases, sales = :sales, harmony = :harmony, products = :products, rooms = :rooms, expenses = :expenses, store = :store  WHERE id = :id');
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":password", $hashed_password);
            $stmt->bindParam(":affairs", $affairs);
            $stmt->bindParam(":ps", $ps);
            $stmt->bindParam(":purchases", $purchases);
            $stmt->bindParam(":sales", $sales);
            $stmt->bindParam(":harmony", $harmony);
            $stmt->bindParam(":products", $products);
            $stmt->bindParam(":rooms", $rooms);
            $stmt->bindParam(":expenses", $expenses);
            $stmt->bindParam(":store", $store);
            $stmt->execute();
            $data = json_encode(['message' => "تم التعديل"]);
            echo $data;
        }
    }else if ($_SERVER["REQUEST_METHOD"] == "DELETE"){
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? '';
        $stmt = $conn->prepare('DELETE FROM users WHERE id = :id');
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $data = json_encode(['message' => "تم الحذف"]);
        echo $data;
    }
?>
