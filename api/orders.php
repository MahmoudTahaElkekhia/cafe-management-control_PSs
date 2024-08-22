<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Include DELETE method
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header('Content-Type: application/json');
header("Cache-Control: no-cache, must-revalidate");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);
    $User = $data['Id'] ?? '';
    $Time = $data['Time'] ?? '';
    $Place = $data['Place'] ?? '';
    $Orders = $data['Orders'] ?? '';
    $stmt = $conn->prepare('INSERT INTO orders (user_id, order_time, place) VALUES (:user_id, :order_time, :place)');
    $stmt->bindParam(":user_id", $User);
    $stmt->bindParam(":order_time", $Time);
    $stmt->bindParam(":place", $Place);
    $stmt->execute();

    $order_id = $conn->prepare('SELECT id FROM orders ORDER BY id DESC LIMIT 1');
    $order_id->execute();
    $order_id = $order_id->fetch();

    foreach ($Orders as $order) {

        $order_price_before = $conn->prepare('SELECT price_before FROM products WHERE name = :name');
        $order_price_before->bindParam(":name", $order['Order']);
        $order_price_before->execute();
        $price_before = $order_price_before->fetch();

        $stmt = $conn->prepare('INSERT INTO sales (name, price, price_before, quantity, id_order) VALUES (:name, :price, :price_before, :quantity, :id_order)');
        $stmt->bindParam(":name", $order['Order']);
        $stmt->bindParam(":price", $order['Price']);
        if (!($order['Order'] == "normal" || $order['Order'] == "multi" || $order['Order'] == "plus" || $order['Order'] == "netflix")) {
            $stmt->bindValue(":price_before", $price_before['price_before']);
        } else {
            $stmt->bindValue(":price_before", 0);
        }
        $stmt->bindParam(":quantity", $order['Quantity']);
        $stmt->bindParam(":id_order", $order_id['id']);
        $stmt->execute();

        if (!($order['Order'] == "normal" || $order['Order'] == "multi" || $order['Order'] == "plus" || $order['Order'] == "netflix")) {
            $stmt2 = $conn->prepare('UPDATE products SET num_sales = num_sales + :incrementValue WHERE name = :id');
            $stmt2->bindParam(":incrementValue", $order['Quantity']);
            $stmt2->bindParam(":id", $order['Order']);
            $stmt2->execute();
        }
    }
    $data = json_encode(['message' => "تم الإضافة"]);
    echo $data;
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $SectorsQuery = $conn->prepare('SELECT * FROM sectors');
    $SectorsQuery->execute();
    $Sectors = $SectorsQuery->fetchAll(PDO::FETCH_ASSOC);
    $productsQuery = $conn->prepare('SELECT p.id, p.name, p.id_sector, p.price, p.num_sales, p.comp, p.price_before, s.id_cr FROM products p JOIN sectors s ON p.id_sector = s.id');
    $productsQuery->execute();
    $products = $productsQuery->fetchAll(PDO::FETCH_ASSOC);

    // e.id = c.employee_id;

    $JSONQuery = $conn->prepare('SELECT * FROM json_orders');
    $JSONQuery->execute();
    $Places = $JSONQuery->fetchAll(PDO::FETCH_ASSOC);
    $PSQuery = $conn->prepare('SELECT * FROM ps');
    $PSQuery->execute();
    $PSs = $PSQuery->fetchAll(PDO::FETCH_ASSOC);
    $data = json_encode(['Products' => $products, 'Places' => $Places, 'PSs' => $PSs, 'Sectors' => $Sectors]);
    echo $data;
} else if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['JSON'])) {
        $json = json_encode($data['JSON'] ?? '');
        $JSONW = json_encode($data['JSONW'] ?? '');
        $JSONT = json_encode($data['JSONT'] ?? '');

        $stmt = $conn->prepare('UPDATE json_orders SET orders = :orders WHERE id = 1');
        $stmt->bindParam(":orders", $json);
        $stmt->execute();
        $stmt2 = $conn->prepare('UPDATE json_orders SET orders = :orders WHERE id = 2');
        $stmt2->bindParam(":orders", $JSONW);
        $stmt2->execute();
        $stmt3 = $conn->prepare('UPDATE json_orders SET orders = :orders WHERE id = 3');
        $stmt3->bindParam(":orders", $JSONT);
        $stmt3->execute();

        $data = json_encode(['msg' => "تمت الإضافة"]);
        echo $data;
    } else if (isset($data['stop'])) {
        $IdPS = $data['IdPS'] ?? '';
        $stmt = $conn->prepare('UPDATE ps SET state = 0 WHERE id = :IdPS');
        $stmt->bindParam(":IdPS", $IdPS);
        $stmt->execute();
        $data = json_encode(['msg' => "تم الإطفاء"]);
        echo $data;
    } else if (isset($data['IdPS'])) {
        $IdPS = $data['IdPS'] ?? '';
        $dateTimeString = $data['Time'] ?? '';
        $stmt = $conn->prepare('UPDATE ps SET start = :dt, state = 1 WHERE id = :IdPS');
        $stmt->bindParam(":IdPS", $IdPS);
        $stmt->bindParam(":dt", $dateTimeString);
        $stmt->execute();
        $data = json_encode(['msg' => "تم التشغيل"]);
        echo $data;
    }
}
