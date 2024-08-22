<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "cafe";
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);        // Set PDO to throw exceptions on error
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
?>
