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

if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    $data = json_decode(file_get_contents('php://input'), true);
    $Type = trim(htmlspecialchars($data['Type'] ?? ''));
    if ($Type == "Sector") {
        $id = trim(htmlspecialchars($data['OldSector'] ?? ''));
        $stmt = $conn->prepare('DELETE FROM sectors WHERE id = :id');
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $data = json_encode(['message' => "تم الحذف"]);
        echo $data;
    } else if ($Type == "Product") {
        $id = trim(htmlspecialchars($data['Product'] ?? ''));
        $stmt = $conn->prepare('DELETE FROM products WHERE id = :id');
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $data = json_encode(['message' => "تم الحذف"]);
        echo $data;
    } else if ($Type == "Material") {
        $id = trim(htmlspecialchars($data['Material'] ?? ''));
        $stmt = $conn->prepare('DELETE FROM materials WHERE id = :id');
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $data = json_encode(['message' => "تم الحذف"]);
        echo $data;
    } else if ($Type == "ProductsMaterials") {
        $id = trim(htmlspecialchars($data['ProductsMaterials'] ?? ''));
        $stmt = $conn->prepare('DELETE FROM product_material WHERE id = :id');
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $data = json_encode(['message' => "تم الحذف"]);
        echo $data;
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);
    $Type = trim(htmlspecialchars($data['Type'] ?? ''));
    if ($Type == "Sectors") {
        $SelectCafeRest = trim(htmlspecialchars($data['SelectCafeRest'] ?? ''));
        $stmt = $conn->prepare('SELECT * FROM sectors Where id_cr = :SelectCafeRest');
        $stmt->bindParam(":SelectCafeRest", $SelectCafeRest);
        $stmt->execute();
        $Sectors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $conn->prepare('SELECT * FROM materials');
        $stmt->execute();
        $AllMaterials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data = json_encode(['Sectors' => $Sectors, 'AllMaterials' => $AllMaterials]);
        echo $data;
    } else if ($Type == "Products") {
        $Sector = trim(htmlspecialchars($data['Sector'] ?? ''));
        $stmt = $conn->prepare('SELECT * FROM products WHERE id_sector = :id_sector');
        $stmt->bindParam(":id_sector", $Sector);
        $stmt->execute();
        $Products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $conn->prepare('SELECT * FROM materials WHERE id_sector = :id_sector');
        $stmt->bindParam(":id_sector", $Sector);
        $stmt->execute();
        $Materials = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data = json_encode(['Products' => $Products, 'Materials' => $Materials]);
        echo $data;
    } else if ($Type == "AddSector") {
        $NewSector = trim(htmlspecialchars($data['NewSector'] ?? ''));
        $SelectCafeRest = trim(htmlspecialchars($data['SelectCafeRest'] ?? ''));
        $stmt = $conn->prepare('INSERT INTO sectors (name, id_cr) VALUES (:NewSector, :SelectCafeRest)');
        $stmt->bindParam(":NewSector", $NewSector);
        $stmt->bindParam(":SelectCafeRest", $SelectCafeRest);
        $stmt->execute();
        $data = json_encode(['message' => "تم الإضافة"]);
        echo $data;
    } else if ($Type == "AddProduct") {
        $ProductName = trim(htmlspecialchars($data['ProductName'] ?? ''));
        $ProductPrice = trim(htmlspecialchars($data['ProductPrice'] ?? ''));
        $ProductPriceBefore = trim(htmlspecialchars($data['ProductPriceBefore'] ?? ''));
        $ProductComp = trim(htmlspecialchars($data['ProductComp'] ?? ''));
        $ProductSector = trim(htmlspecialchars($data['ProductSector'] ?? ''));
        $stmt = $conn->prepare('INSERT INTO products (name, id_sector, price, price_before, num_sales, comp) VALUES (:ProductName, :ProductSector, :ProductPrice, :ProductPriceBefore ,0 ,:ProductComp)');
        $stmt->bindParam(":ProductName", $ProductName);
        $stmt->bindParam(":ProductSector", $ProductSector);
        $stmt->bindParam(":ProductPrice", $ProductPrice);
        $stmt->bindParam(":ProductComp", $ProductComp);
        $stmt->bindParam(":ProductPriceBefore", $ProductPriceBefore);
        $stmt->execute();
        $data = json_encode(['message' => "تم الإضافة"]);
        echo $data;
    } else if ($Type == "AddMaterial") {
        $MaterialName = trim(htmlspecialchars($data['MaterialName'] ?? ''));
        $MaterialSector = trim(htmlspecialchars($data['MaterialSector'] ?? ''));
        $stmt = $conn->prepare('INSERT INTO materials (name, id_sector, num_store, num_purchases) VALUES (:MaterialName, :MaterialSector, 0, 0)');
        $stmt->bindParam(":MaterialName", $MaterialName);
        $stmt->bindParam(":MaterialSector", $MaterialSector);
        $stmt->execute();
        $data = json_encode(['message' => "تم الإضافة"]);
        echo $data;
    } else if ($Type == "ProductsMaterials") {
        $ProductsMaterials = trim(htmlspecialchars($data['ProductsMaterials'] ?? ''));
        $stmt = $conn->prepare('SELECT product_material.id, materials.name, product_material.num FROM product_material LEFT JOIN materials ON product_material.id_material = materials.id WHERE id_product = :ProductsMaterials');
        $stmt->bindParam(":ProductsMaterials", $ProductsMaterials);
        $stmt->execute();
        $ProductsMaterialsArr = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data = json_encode(['ProductsMaterialsArr' => $ProductsMaterialsArr]);
        echo $data;
    } else if ($Type == "AddPM") {
        $PMmaterial = trim(htmlspecialchars($data['PMid'] ?? ''));
        $PMnum = trim(htmlspecialchars($data['PMnum'] ?? ''));
        $PMproduct = trim(htmlspecialchars($data['PMproduct'] ?? ''));
        $stmt = $conn->prepare('INSERT INTO product_material (id_material, num, id_product) VALUES (:PMmaterial, :PMnum, :PMproduct)');
        $stmt->bindParam(":PMmaterial", $PMmaterial);
        $stmt->bindParam(":PMnum", $PMnum);
        $stmt->bindParam(":PMproduct", $PMproduct);
        $stmt->execute();
        $data = json_encode(['message' => "تم الإضافة"]);
        echo $data;
    }
} else if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    $data = json_decode(file_get_contents('php://input'), true);
    $Type = trim(htmlspecialchars($data['Type'] ?? ''));
    if ($Type == "product") {
        $ProductId = trim(htmlspecialchars($data['ProductId'] ?? ''));
        $ProductName = trim(htmlspecialchars($data['ProductName'] ?? ''));
        $ProductPrice = trim(htmlspecialchars($data['ProductPrice'] ?? ''));
        $ProductPriceBefore = trim(htmlspecialchars($data['ProductPriceBefore'] ?? ''));
        $ProductComp = trim(htmlspecialchars($data['ProductComp'] ?? ''));
        $stmt = $conn->prepare('UPDATE products SET name = :ProductName, price = :ProductPrice, price_before = :ProductPriceBefore, comp = :ProductComp WHERE id = :id');
        $stmt->bindParam(":ProductName", $ProductName);
        $stmt->bindParam(":ProductPrice", $ProductPrice);
        $stmt->bindParam(":ProductPriceBefore", $ProductPriceBefore);
        $stmt->bindParam(":ProductComp", $ProductComp);
        $stmt->bindParam(":id", $ProductId);
        $stmt->execute();
        $data = json_encode(['message' => "تم التعديل"]);
        echo $data;
    }
}
