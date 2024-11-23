<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../connection/dbconfig.php';

$database = new Database();
$db = $database->getDb();

if(isset($_GET['id'])) {
    $id = $_GET['id'];

    $query = "SELECT * FROM products WHERE product_id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if($product) {
        echo json_encode($product);
    } else {
        echo json_encode(["message" => "Product not found"]);
    }
} else {
    echo json_encode(["message" => "No product ID provided"]);
}
