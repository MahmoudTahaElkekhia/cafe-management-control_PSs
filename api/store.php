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
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $data = json_decode(file_get_contents('php://input'), true);
        $GetType = $data['GetType'] ?? '';
        if($GetType == "Sectors"){
            $GetSectors = $data['GetSectors'] ?? '';
            if($GetSectors == 0){
                $stmt = $conn->prepare('SELECT * FROM sectors WHERE id_cr = 0');
                $stmt->execute();
                $Sectors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $data = json_encode(['Sectors' => $Sectors]);
                echo $data;
            }else if($GetSectors == 1){
                $stmt = $conn->prepare('SELECT * FROM sectors WHERE id_cr = 1');
                $stmt->execute();
                $Sectors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $data = json_encode(['Sectors' => $Sectors]);
                echo $data;
            }else{
                $Sectors = [];
                $data = json_encode(['Sectors' => $Sectors]);
                echo $data;
            }
        }else if($GetType == "Materials"){
            $GetSector = $data['GetSector'] ?? '';
            if($GetSector != ""){
                $stmt = $conn->prepare('SELECT id, name, num_store FROM materials WHERE id_sector = :id_sector');
                $stmt->bindParam(":id_sector", $GetSector);
                $stmt->execute();
                $Materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $data = json_encode(['Materials' => $Materials]);
                echo $data;
            }else{
                $Materials = [];
                $data = json_encode(['Materials' => $Materials]);
                echo $data;
            }
        }
    }else if ($_SERVER["REQUEST_METHOD"] == "PUT"){
        $data = json_decode(file_get_contents('php://input'), true);
        $Type = $data['Type'] ?? '';
        if($Type == 1){
            $id = $data['id'] ?? '';
            $id_user = $data['id_user'] ?? '';
            $num = $data['num'] + 1;
            $stmt = $conn->prepare('UPDATE materials SET num_store = :num WHERE id = :id');
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":num", $num);
            $stmt->execute();
            $data = json_encode(['message' => "تم التعديل"]);
            echo $data;
            $date = date('Y-m-d');
            $stmt2 = $conn->prepare('INSERT INTO store (id_material, id_user, op, date) VALUES (:id, :id_user, 1, :date)');
            $stmt2->bindParam(":id", $id);
            $stmt2->bindParam(":id_user", $id_user);
            $stmt2->bindParam(":date", $date);
            $stmt2->execute();
        }else{
            if( $data['num'] != 0){
                $id = $data['id'] ?? '';
                $id_user = $data['id_user'] ?? '';
                $num = $data['num'] - 1;
                $stmt = $conn->prepare('UPDATE materials SET num_store = :num WHERE id = :id');
                $stmt->bindParam(":id", $id);
                $stmt->bindParam(":num", $num);
                $stmt->execute();
                $data = json_encode(['message' => "تم التعديل"]);
                echo $data;
                $date = date('Y-m-d');
                $stmt2 = $conn->prepare('INSERT INTO store (id_material, id_user, op, date) VALUES (:id, :id_user, 0 ,:date)');
                $stmt2->bindParam(":id", $id);
                $stmt2->bindParam(":id_user", $id_user);
                $stmt2->bindParam(":date", $date);
                $stmt2->execute();
            }
        }
    }

?>
