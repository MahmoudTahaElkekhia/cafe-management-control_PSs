<?php
    header("Access-Control-Allow-Origin: http://localhost:8080"); // Specify your Vue.js development server URL
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");    
    
    include 'db.php';
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        $username = trim(htmlspecialchars($username));

        // Authenticate user
        $stmt = $conn->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $data = '';
        $user = $stmt->fetch();
        if($stmt->rowCount() > 0){
            if ( password_verify($password, $user['password']) ){
                $data = json_encode(['message' => true, 'user' => $user]);
            }else{
                $data = json_encode(['message' => false]);
            }
        }else{
            $data = json_encode(['message' => false]);
        }
        echo $data;
    }
?>
