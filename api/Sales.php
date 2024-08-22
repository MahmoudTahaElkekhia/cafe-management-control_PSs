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
    $stmt = $conn->prepare('SELECT id, username FROM users');
    $stmt->execute();
    $Users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt2 = $conn->prepare('SELECT * FROM materials');
    $stmt2->execute();
    $Materials = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    $data = json_encode(['Users' => $Users, 'Materials' => $Materials]);
    echo $data;
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);
    $Type = $data['Type'] ?? '';
    if ($Type == "DataPerDay") {
        $SelectedUser = $data['SelectedUser'] ?? '';
        $SelectedDate = $data['SelectedDate'] ?? '';
        $stmt = $conn->prepare('SELECT details FROM harmony WHERE id_user = :SelectedUser AND date = :SelectedDate');
        $stmt->bindParam(":SelectedUser", $SelectedUser);
        $stmt->bindParam(":SelectedDate", $SelectedDate);
        $stmt->execute();
        $Harmonies = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt2 = $conn->prepare('SELECT material, price FROM expenses WHERE id_user = :SelectedUser AND date = :SelectedDate');
        $stmt2->bindParam(":SelectedUser", $SelectedUser);
        $stmt2->bindParam(":SelectedDate", $SelectedDate);
        $stmt2->execute();
        $Expenses = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $storedistinctNames = $conn->prepare('SELECT DISTINCT materials.name FROM store INNER JOIN materials ON store.id_material = materials.id WHERE id_user = :SelectedUser AND date = :SelectedDate');
        $storedistinctNames->bindParam(":SelectedUser", $SelectedUser);
        $storedistinctNames->bindParam(":SelectedDate", $SelectedDate);
        $storedistinctNames->execute();
        $distinctNames = $storedistinctNames->fetchAll(PDO::FETCH_ASSOC);
        $nameCounts = [];
        foreach ($distinctNames as $row) {
            $name = $row['name'];
            $nameCounts[$name] = 0;
            $PCount = $conn->prepare('SELECT COUNT(*) as count FROM store INNER JOIN materials ON store.id_material = materials.id  WHERE materials.name = :name AND id_user = :SelectedUser AND date = :SelectedDate AND op = 1');
            $PCount->bindParam(":SelectedUser", $SelectedUser);
            $PCount->bindParam(":SelectedDate", $SelectedDate);;
            $PCount->bindParam(':name', $name);
            $PCount->execute();
            $PCountValue = $PCount->fetch(PDO::FETCH_ASSOC)['count'];
            $nameCounts[$name] += $PCountValue;

            $NCount = $conn->prepare('SELECT COUNT(*) as count FROM store INNER JOIN materials ON store.id_material = materials.id  WHERE materials.name = :name AND id_user = :SelectedUser AND date = :SelectedDate AND op = 0');
            $NCount->bindParam(":SelectedUser", $SelectedUser);
            $NCount->bindParam(":SelectedDate", $SelectedDate);;
            $NCount->bindParam(':name', $name);
            $NCount->execute();
            $NCountValue = $NCount->fetch(PDO::FETCH_ASSOC)['count'];
            $nameCounts[$name] -= $NCountValue;
        }

        $purchasesdistinctNames = $conn->prepare('SELECT DISTINCT materials.name FROM purchases INNER JOIN materials ON purchases.id_material = materials.id WHERE id_user = :SelectedUser AND date = :SelectedDate');
        $purchasesdistinctNames->bindParam(":SelectedUser", $SelectedUser);
        $purchasesdistinctNames->bindParam(":SelectedDate", $SelectedDate);
        $purchasesdistinctNames->execute();
        $distinctNames2 = $purchasesdistinctNames->fetchAll(PDO::FETCH_ASSOC);
        $nameCounts2 = [];
        foreach ($distinctNames2 as $row) {
            $name = $row['name'];
            $nameCounts2[$name] = 0;
            $PCount = $conn->prepare('SELECT COUNT(*) as count FROM purchases INNER JOIN materials ON purchases.id_material = materials.id  WHERE materials.name = :name AND id_user = :SelectedUser AND date = :SelectedDate AND op = 1');
            $PCount->bindParam(":SelectedUser", $SelectedUser);
            $PCount->bindParam(":SelectedDate", $SelectedDate);;
            $PCount->bindParam(':name', $name);
            $PCount->execute();
            $PCountValue = $PCount->fetch(PDO::FETCH_ASSOC)['count'];
            $nameCounts2[$name] += $PCountValue;

            $NCount = $conn->prepare('SELECT COUNT(*) as count FROM purchases INNER JOIN materials ON purchases.id_material = materials.id  WHERE materials.name = :name AND id_user = :SelectedUser AND date = :SelectedDate AND op = 0');
            $NCount->bindParam(":SelectedUser", $SelectedUser);
            $NCount->bindParam(":SelectedDate", $SelectedDate);;
            $NCount->bindParam(':name', $name);
            $NCount->execute();
            $NCountValue = $NCount->fetch(PDO::FETCH_ASSOC)['count'];
            $nameCounts2[$name] -= $NCountValue;
        }

        $salesdistinctNames = $conn->prepare('SELECT DISTINCT s.name FROM orders o JOIN sales s ON o.id = s.id_order WHERE o.user_id = :SelectedUser AND DATE(order_time) = :SelectedDate');
        $salesdistinctNames->bindParam(":SelectedUser", $SelectedUser);
        $salesdistinctNames->bindParam(":SelectedDate", $SelectedDate);;
        $salesdistinctNames->execute();
        $distinctNames3 = $salesdistinctNames->fetchAll(PDO::FETCH_ASSOC);
        $Sales = [];
        $SalesDiff = [];
        for ($i = 0; $i < count($distinctNames3); $i++) {
            $obj = [];
            $obj2 = [];
            $obj['name'] = $distinctNames3[$i]['name'];
            $obj2['name'] = $distinctNames3[$i]['name'];

            if ($obj['name'] == "normal" || $obj['name'] == "multi" || $obj['name'] == "plus" || $obj['name'] == "netflix") {
                $obj['sector'] = $obj['name'];
                $obj2['sector'] = $obj['name'];
            } else {
                $Sector = $conn->prepare('SELECT s.name as sector FROM sectors as s INNER JOIN products as p ON s.id = p.id_sector  WHERE p.name = :name');
                $Sector->bindParam(':name', $obj['name']);
                $Sector->execute();
                $SectorValue = $Sector->fetch(PDO::FETCH_ASSOC)['sector'];
                $obj['sector'] = $SectorValue;
                $obj2['sector'] = $SectorValue;
            }

            $salesdistinctNames = $conn->prepare('SELECT SUM(s.quantity * s.price) as sum, SUM(s.quantity) as quantity, SUM(s.price) as price FROM orders o JOIN sales s ON o.id = s.id_order WHERE o.user_id = :SelectedUser AND DATE(order_time) = :SelectedDate AND s.name = :name');
            $salesdistinctNames->bindParam(":SelectedUser", $SelectedUser);
            $salesdistinctNames->bindParam(":SelectedDate", $SelectedDate);;
            $salesdistinctNames->bindParam(':name', $obj['name']);
            $salesdistinctNames->execute();
            $PCountValue = $salesdistinctNames->fetch(PDO::FETCH_ASSOC)['sum'];
            $obj['sum'] = (float)$PCountValue;

            $salesdistinctPrice = $conn->prepare('SELECT s.price as price FROM orders o JOIN sales s ON o.id = s.id_order WHERE o.user_id = :SelectedUser AND DATE(order_time) = :SelectedDate AND s.name = :name LIMIT 1');
            $salesdistinctPrice->bindParam(":SelectedUser", $SelectedUser);
            $salesdistinctPrice->bindParam(":SelectedDate", $SelectedDate);;
            $salesdistinctPrice->bindParam(':name', $obj['name']);
            $salesdistinctPrice->execute();
            $PCountPrice = $salesdistinctPrice->fetch(PDO::FETCH_ASSOC)['price'];
            $obj['price'] = (float)$PCountPrice;
            $Sales[$i] = $obj;

            $salesdiffdistinctNames = $conn->prepare('SELECT SUM(s.quantity * s.price) - SUM(s.quantity * s.price_before) as sum FROM orders o JOIN sales s ON o.id = s.id_order WHERE o.user_id = :SelectedUser AND DATE(order_time) = :SelectedDate AND s.name = :name');
            $salesdiffdistinctNames->bindParam(":SelectedUser", $SelectedUser);
            $salesdiffdistinctNames->bindParam(":SelectedDate", $SelectedDate);;
            $salesdiffdistinctNames->bindParam(':name', $obj['name']);
            $salesdiffdistinctNames->execute();
            $salesdiffdistinctNames = $salesdiffdistinctNames->fetch(PDO::FETCH_ASSOC)['sum'];
            $obj2['sum'] = (float)$salesdiffdistinctNames;
            $SalesDiff[$i] = $obj2;
        }

        $OrdersNamesdb = $conn->prepare('SELECT * FROM orders WHERE user_id = :SelectedUser AND DATE(order_time) = :SelectedDate');
        $OrdersNamesdb->bindParam(":SelectedUser", $SelectedUser);
        $OrdersNamesdb->bindParam(":SelectedDate", $SelectedDate);
        $OrdersNamesdb->execute();
        $OrdersNames = $OrdersNamesdb->fetchAll(PDO::FETCH_ASSOC);
        $Orders = [];
        for ($i = 0; $i < count($OrdersNames); $i++) {
            $obj = [];
            $obj['id'] = $OrdersNames[$i]['id'];
            $obj['user_id'] = $OrdersNames[$i]['user_id'];
            $obj['order_time'] = $OrdersNames[$i]['order_time'];
            $obj['place'] = $OrdersNames[$i]['place'];
            $itemsdb = $conn->prepare('SELECT * FROM sales WHERE id_order = :SelectedUser');
            $itemsdb->bindParam(":SelectedUser", $obj['id']);
            $itemsdb->execute();
            $items = $itemsdb->fetchAll(PDO::FETCH_ASSOC);
            $obj['items'] = $items;
            $Orders[$i] = $obj;
        }

        $Roomsdb = $conn->prepare('SELECT (s.quantity * s.price) as sum, o.place, s.name FROM orders o JOIN sales s ON o.id = s.id_order WHERE o.user_id = :SelectedUser AND DATE(order_time) = :SelectedDate');
        $Roomsdb->bindParam(":SelectedUser", $SelectedUser);
        $Roomsdb->bindParam(":SelectedDate", $SelectedDate);;
        $Roomsdb->execute();
        $Rooms = $Roomsdb->fetchAll(PDO::FETCH_ASSOC);
        $FilteredRooms = [];
        foreach ($Rooms as $Room) {
            if ($Room['name'] == "normal" || $Room['name'] == "multi" || $Room['name'] == "plus" || $Room['name'] == "netflix") {
                $FilteredRooms[] = $Room;
            }
        }

        $data = json_encode([
            'Expenses' => $Expenses, 'Harmonies' => $Harmonies, 'Store' => $nameCounts, 'SalesDiff' => $SalesDiff,
            'Purchases' => $nameCounts2, 'Sales' => $Sales, 'Orders' => $Orders, 'Rooms' => $FilteredRooms
        ]);
        echo $data;
    } else if ($Type == "Materials") {
        $SelectedMaterial = $data['SelectedMaterial'] ?? '';
        $ProductsNamesdb = $conn->prepare('SELECT DISTINCT p.id as id, p.name as name, num FROM product_material pm INNER JOIN products p ON p.id = pm.id_product WHERE pm.id_material = :SelectedMaterial');
        $ProductsNamesdb->bindParam(":SelectedMaterial", $SelectedMaterial);
        $ProductsNamesdb->execute();
        $ProPerM = $ProductsNamesdb->fetchAll(PDO::FETCH_ASSOC);
        $SalesM = [];
        for ($i = 0; $i < count($ProPerM); $i++) {
            $obj = [];
            $obj['name'] = $ProPerM[$i]['name'];
            $obj['num'] = $ProPerM[$i]['num'];
            $obj['id'] = $ProPerM[$i]['id'];
            $SalesCountdb = $conn->prepare('SELECT num_sales as sales FROM products WHERE id = :id');
            $SalesCountdb->bindParam(":id", $obj['id']);
            $SalesCountdb->execute();
            $SalesCount = $SalesCountdb->fetch(PDO::FETCH_ASSOC)['sales'];
            $obj['count'] = $SalesCount;
            $SalesM[$i] = $obj;
        }
        $data = json_encode([
            'SalesM' => $SalesM
        ]);
        echo $data;
    }
}
