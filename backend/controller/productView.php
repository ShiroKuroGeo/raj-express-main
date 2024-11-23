<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '0';

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "../connection/dbconfig.php"; 

function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
}

$response = [];
$product_id = $authHeader;

try {
    $database = new Database();
    $db = $database->getDb();

    $query = "SELECT `product_id`, `category_id`, `product_name`, `product_description`, `product_price`, `product_status`, `product_image`, `created_at`, `updated_at` FROM `products` WHERE product_id = :product_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":product_id", $product_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        sendResponse([
            "success" => true,
            "product_id" => $user['product_id'],
            "category_id" => $user['category_id'],
            "product_name" => $user['product_name'],
            "product_description" => $user['product_description'],
            "product_price" => $user['product_price'],
            "product_status" => $user['product_status'],
            "product_image" => $user['product_image'],
            "created_at" => $user['created_at'],
            "updated_at" => $user['updated_at'],
        ]);
    } else {
        sendResponse(["error" => "User not found"], 404);
    }
} catch (PDOException $e) {
    sendResponse(["error" => "Database error: " . $e->getMessage()], 500);
}
