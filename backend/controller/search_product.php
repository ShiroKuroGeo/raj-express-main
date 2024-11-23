<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../connection/dbconfig.php';

$database = new Database();
$db = $database->getDb();

$searchQuery = isset($_GET['query']) ? $_GET['query'] : '';

$query = "SELECT * FROM products WHERE product_name LIKE :search OR product_description LIKE :search";
$stmt = $db->prepare($query);
$searchParam = "%$searchQuery%";
$stmt->bindParam(':search', $searchParam);
$stmt->execute();

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($products);
